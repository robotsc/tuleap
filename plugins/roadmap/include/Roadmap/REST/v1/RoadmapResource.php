<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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
 */

declare(strict_types=1);

namespace Tuleap\Roadmap\REST\v1;

use Luracast\Restler\RestException;
use Psr\Log\LoggerInterface;
use Tuleap\REST\Header;
use Tuleap\Roadmap\RoadmapWidgetDao;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\Nature\NatureDao;
use Tuleap\Tracker\Semantic\Progress\SemanticProgressBuilder;
use Tuleap\Tracker\Semantic\Progress\SemanticProgressDao;
use Tuleap\Tracker\Semantic\Status\SemanticStatusRetriever;
use Tuleap\Tracker\Semantic\Timeframe\SemanticTimeframeBuilder;
use Tuleap\Tracker\Semantic\Timeframe\SemanticTimeframeDao;
use Tuleap\Tracker\Semantic\Timeframe\TimeframeBuilder;

final class RoadmapResource
{
    public const MAX_LIMIT = 100;

    /**
     * @url OPTIONS {id}/tasks
     *
     * @param int $id Id of the roadmap
     */
    public function optionsTasks(int $id): void
    {
        Header::allowOptionsGet();
    }

    /**
     * Get the tasks
     *
     * Retrieve paginated tasks of a given roadmap
     *
     * <pre>
     * /!\ Roadmap REST route is under construction and subject to changes /!\
     * </pre>
     *
     * @url GET {id}/tasks
     * @access hybrid
     *
     * @param int $id     Id of the roadmap
     * @param int $offset Position of the first element to display{ @min 0}
     * @param int $limit  Number of elements displayed per page {@min 0} {@max 100}
     *
     * @return array {@type TaskRepresentation}
     * @psalm-return TaskRepresentation[]
     *
     * @throws RestException 400
     * @throws RestException 403
     * @throws RestException 404
     */
    public function getTasks(int $id, int $offset = 0, int $limit = self::MAX_LIMIT): array
    {
        $this->optionsTasks($id);

        $form_element_factory       = \Tracker_FormElementFactory::instance();
        $semantic_timeframe_builder = new SemanticTimeframeBuilder(
            new SemanticTimeframeDao(),
            $form_element_factory
        );

        $timeframe_builder = new TimeframeBuilder($semantic_timeframe_builder, \BackendLogger::getDefaultLogger());
        $retriever         = new RoadmapTasksRetriever(
            new RoadmapWidgetDao(),
            \ProjectManager::instance(),
            \UserManager::instance(),
            new \URLVerification(),
            \TrackerFactory::instance(),
            $semantic_timeframe_builder,
            $timeframe_builder,
            \Tracker_ArtifactFactory::instance(),
            new DependenciesRetriever(new NatureDao()),
            new RoadmapTasksOutOfDateFilter(
                new SemanticStatusRetriever(),
                $timeframe_builder,
                $this->getLogger()
            ),
            new SemanticProgressBuilder(
                new SemanticProgressDao(),
                $form_element_factory
            )
        );

        $tasks = $retriever->getTasks($id, $limit, $offset);

        Header::sendPaginationHeaders($limit, $offset, $tasks->getTotalSize(), self::MAX_LIMIT);

        return $tasks->getRepresentations();
    }

    private function getLogger(): LoggerInterface
    {
        return \BackendLogger::getDefaultLogger();
    }
}
