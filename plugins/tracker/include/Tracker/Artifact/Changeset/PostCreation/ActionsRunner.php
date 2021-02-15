<?php
/**
 * Copyright (c) Enalean, 2017-Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Tuleap\Tracker\Artifact\Changeset\PostCreation;

use ConfigNotificationAssignedTo;
use ConfigNotificationAssignedToDao;
use Exception;
use Psr\Log\LoggerInterface;
use Tracker_Artifact_Changeset;
use Tracker_Artifact_MailGateway_RecipientFactory;
use Tracker_FormElementFactory;
use Tracker_GlobalNotificationDao;
use Tuleap\Http\HttpClientFactory;
use Tuleap\Http\HTTPFactoryBuilder;
use Tuleap\Mail\MailLogger;
use Tuleap\Markdown\CommonMarkInterpreter;
use Tuleap\Queue\QueueFactory;
use Tuleap\Queue\Worker;
use Tuleap\Queue\WorkerAvailability;
use Tuleap\Tracker\Artifact\Changeset\Comment\PrivateComment\PermissionChecker;
use Tuleap\Tracker\Artifact\Changeset\Comment\PrivateComment\TrackerPrivateCommentUGroupPermissionDao;
use Tuleap\Tracker\Artifact\MailGateway\MailGatewayConfig;
use Tuleap\Tracker\Artifact\MailGateway\MailGatewayConfigDao;
use Tuleap\Tracker\Notifications\ConfigNotificationEmailCustomSender;
use Tuleap\Tracker\Notifications\ConfigNotificationEmailCustomSenderDao;
use Tuleap\Tracker\Notifications\InvolvedNotificationDao;
use Tuleap\Tracker\Notifications\RecipientsManager;
use Tuleap\Tracker\Notifications\Settings\UserNotificationSettingsRetriever;
use Tuleap\Tracker\Notifications\UnsubscribersNotificationDAO;
use Tuleap\Tracker\Notifications\UserNotificationOnlyStatusChangeDAO;
use Tuleap\Tracker\REST\Artifact\Changeset\ChangesetRepresentationBuilder;
use Tuleap\Tracker\REST\Artifact\Changeset\Comment\CommentRepresentationBuilder;
use Tuleap\Tracker\Webhook\ArtifactPayloadBuilder;
use Tuleap\Tracker\Webhook\WebhookDao;
use Tuleap\Tracker\Webhook\WebhookFactory;
use Tuleap\Tracker\Webhook\WebhookStatusLogger;
use Tuleap\Webhook\Emitter as WebhookEmitter;
use UserHelper;
use UserManager;
use WrapperLogger;

class ActionsRunner
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ActionsRunnerDao
     */
    private $actions_runner_dao;
    /**
     * @var QueueFactory
     */
    private $queue_factory;
    /**
     * @var WorkerAvailability
     */
    private $worker_availability;
    /**
     * @var PostCreationTask[]
     */
    private $post_creation_tasks;

    public function __construct(
        LoggerInterface $logger,
        ActionsRunnerDao $actions_runner_dao,
        QueueFactory $queue_factory,
        WorkerAvailability $worker_availability,
        PostCreationTask ...$post_creation_tasks
    ) {
        $this->logger              = new WrapperLogger($logger, self::class);
        $this->actions_runner_dao  = $actions_runner_dao;
        $this->queue_factory       = $queue_factory;
        $this->worker_availability = $worker_availability;
        $this->post_creation_tasks = $post_creation_tasks;
    }

    public static function build(LoggerInterface $logger): self
    {
        $webhook_dao          = new WebhookDao();
        $user_manager         = UserManager::instance();
        $form_element_factory = Tracker_FormElementFactory::instance();

        return new ActionsRunner(
            $logger,
            new ActionsRunnerDao(),
            new QueueFactory($logger),
            new WorkerAvailability(),
            new ClearArtifactChangesetCacheTask(),
            new EmailNotificationTask(
                new MailLogger(),
                UserHelper::instance(),
                new RecipientsManager(
                    $form_element_factory,
                    $user_manager,
                    new UnsubscribersNotificationDAO(),
                    new UserNotificationSettingsRetriever(
                        new Tracker_GlobalNotificationDao(),
                        new UnsubscribersNotificationDAO(),
                        new UserNotificationOnlyStatusChangeDAO(),
                        new InvolvedNotificationDao()
                    ),
                    new UserNotificationOnlyStatusChangeDAO()
                ),
                Tracker_Artifact_MailGateway_RecipientFactory::build(),
                new MailGatewayConfig(
                    new MailGatewayConfigDao()
                ),
                new MailSender(),
                new ConfigNotificationAssignedTo(new ConfigNotificationAssignedToDao()),
                new ConfigNotificationEmailCustomSender(new ConfigNotificationEmailCustomSenderDao())
            ),
            new WebhookNotificationTask(
                $logger,
                new WebhookEmitter(
                    HTTPFactoryBuilder::requestFactory(),
                    HTTPFactoryBuilder::streamFactory(),
                    HttpClientFactory::createAsyncClient(),
                    new WebhookStatusLogger($webhook_dao)
                ),
                new WebhookFactory($webhook_dao),
                new ArtifactPayloadBuilder(
                    new ChangesetRepresentationBuilder(
                        $user_manager,
                        $form_element_factory,
                        new CommentRepresentationBuilder(
                            CommonMarkInterpreter::build(\Codendi_HTMLPurifier::instance())
                        ),
                        new PermissionChecker(new TrackerPrivateCommentUGroupPermissionDao())
                    )
                )
            )
        );
    }

    /**
     * Manage notification for a changeset
     *
     */
    public function executePostCreationActions(Tracker_Artifact_Changeset $changeset)
    {
        if ($this->worker_availability->canProcessAsyncTasks()) {
            $this->queuePostCreationEvent($changeset);
        } else {
            $this->processPostCreationActions($changeset);
        }
    }

    /**
     * Process notification when executed in background (should not be called by front-end)
     *
     */
    public function processAsyncPostCreationActions(Tracker_Artifact_Changeset $changeset)
    {
        $this->actions_runner_dao->addStartDate($changeset->getId());
        $this->processPostCreationActions($changeset);
        $this->actions_runner_dao->addEndDate($changeset->getId());
    }

    private function queuePostCreationEvent(Tracker_Artifact_Changeset $changeset)
    {
        try {
            $this->actions_runner_dao->addNewPostCreationEvent($changeset->getId());
            $queue = $this->queue_factory->getPersistentQueue(Worker::EVENT_QUEUE_NAME, QueueFactory::REDIS);
            $queue->pushSinglePersistentMessage(
                AsynchronousActionsRunner::TOPIC,
                [
                    'artifact_id'  => (int) $changeset->getArtifact()->getId(),
                    'changeset_id' => (int) $changeset->getId(),
                ]
            );
        } catch (Exception $exception) {
            $this->logger->error("Unable to queue notification for {$changeset->getId()}, fallback to online notif", ['exception' => $exception]);
            $this->processPostCreationActions($changeset);
            $this->actions_runner_dao->addEndDate($changeset->getId());
        }
    }

    private function processPostCreationActions(Tracker_Artifact_Changeset $changeset)
    {
        foreach ($this->post_creation_tasks as $notification_task) {
            $notification_task->execute($changeset);
        }
    }
}
