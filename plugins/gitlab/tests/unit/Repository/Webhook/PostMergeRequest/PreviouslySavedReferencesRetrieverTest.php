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

namespace Tuleap\Gitlab\Repository\Webhook\PostMergeRequest;

use Project;
use Tuleap\Gitlab\Reference\TuleapReferencedArtifactNotFoundException;
use Tuleap\Gitlab\Reference\TuleapReferenceNotFoundException;
use Tuleap\Gitlab\Reference\TuleapReferenceRetriever;
use Tuleap\Gitlab\Repository\GitlabRepositoryIntegration;
use Tuleap\Gitlab\Repository\Webhook\WebhookTuleapReference;
use Tuleap\Gitlab\Repository\Webhook\WebhookTuleapReferencesParser;

class PreviouslySavedReferencesRetrieverTest extends \Tuleap\Test\PHPUnit\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&TuleapReferenceRetriever
     */
    private $tuleap_reference_retriever;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&MergeRequestTuleapReferenceDao
     */
    private $dao;

    private PreviouslySavedReferencesRetriever $retriever;

    protected function setUp(): void
    {
        $this->tuleap_reference_retriever = $this->createMock(TuleapReferenceRetriever::class);
        $this->dao                        = $this->createMock(MergeRequestTuleapReferenceDao::class);

        $this->retriever = new PreviouslySavedReferencesRetriever(
            new TuleapReferencesFromMergeRequestDataExtractor(
                new WebhookTuleapReferencesParser()
            ),
            $this->tuleap_reference_retriever,
            $this->dao,
        );
    }

    public function testItReturnsEmptyArrayIfNothingFoundInDatabase(): void
    {
        $integration = new GitlabRepositoryIntegration(
            1,
            123654,
            'root/repo01',
            '',
            'https://example.com/root/repo01',
            new \DateTimeImmutable(),
            Project::buildForTest(),
            false
        );

        $webhook_data = new PostMergeRequestWebhookData(
            'merge_request',
            123,
            'https://example.com',
            2,
            "My Title TULEAP-58",
            'TULEAP-666 TULEAP-45',
            'opened',
            (new \DateTimeImmutable())->setTimestamp(1611315112),
            10
        );

        $this->dao
            ->method('searchMergeRequestInRepositoryWithId')
            ->with(1, 2)
            ->willReturn([]);

        self::assertEmpty(
            $this->retriever->retrievePreviousReferences($webhook_data, $integration)
        );
    }

    public function testItReturnsEmptyArrayIfNoReferencesAreFoundInThePreviouslySavedData(): void
    {
        $integration = new GitlabRepositoryIntegration(
            1,
            123654,
            'root/repo01',
            '',
            'https://example.com/root/repo01',
            new \DateTimeImmutable(),
            Project::buildForTest(),
            false
        );

        $webhook_data = new PostMergeRequestWebhookData(
            'merge_request',
            123,
            'https://example.com',
            2,
            "My Title TULEAP-58",
            'TULEAP-666 TULEAP-45',
            'opened',
            (new \DateTimeImmutable())->setTimestamp(1611315112),
            10
        );

        $this->dao
            ->method('searchMergeRequestInRepositoryWithId')
            ->with(1, 2)
            ->willReturn(
                [
                    'title'       => 'Title of merge request',
                    'description' => 'Description of merge request',
                ]
            );

        self::assertEmpty(
            $this->retriever->retrievePreviousReferences($webhook_data, $integration)
        );
    }

    public function testItReturnsEmptyArrayIfReferenceIsNotFound(): void
    {
        $integration = new GitlabRepositoryIntegration(
            1,
            123654,
            'root/repo01',
            '',
            'https://example.com/root/repo01',
            new \DateTimeImmutable(),
            Project::buildForTest(),
            false
        );

        $webhook_data = new PostMergeRequestWebhookData(
            'merge_request',
            123,
            'https://example.com',
            2,
            "My Title TULEAP-58",
            'TULEAP-666 TULEAP-45',
            'opened',
            (new \DateTimeImmutable())->setTimestamp(1611315112),
            10
        );

        $this->dao
            ->method('searchMergeRequestInRepositoryWithId')
            ->with(1, 2)
            ->willReturn(
                [
                    'title'       => 'Title of merge request TULEAP-8',
                    'description' => 'Description of merge request',
                ]
            );

        $this->tuleap_reference_retriever
            ->expects(self::once())
            ->method('retrieveTuleapReference')
            ->with(8)
            ->willThrowException(new TuleapReferenceNotFoundException());

        self::assertEmpty(
            $this->retriever->retrievePreviousReferences($webhook_data, $integration)
        );
    }

    public function testItReturnsEmptyArrayIfReferencedArtifactIsNotFound(): void
    {
        $integration = new GitlabRepositoryIntegration(
            1,
            123654,
            'root/repo01',
            '',
            'https://example.com/root/repo01',
            new \DateTimeImmutable(),
            Project::buildForTest(),
            false
        );

        $webhook_data = new PostMergeRequestWebhookData(
            'merge_request',
            123,
            'https://example.com',
            2,
            "My Title TULEAP-58",
            'TULEAP-666 TULEAP-45',
            'opened',
            (new \DateTimeImmutable())->setTimestamp(1611315112),
            10
        );

        $this->dao
            ->method('searchMergeRequestInRepositoryWithId')
            ->with(1, 2)
            ->willReturn(
                [
                    'title'       => 'Title of merge request TULEAP-8',
                    'description' => 'Description of merge request',
                ]
            );

        $this->tuleap_reference_retriever
            ->expects(self::once())
            ->method('retrieveTuleapReference')
            ->with(8)
            ->willThrowException(new TuleapReferencedArtifactNotFoundException(8));

        self::assertEmpty(
            $this->retriever->retrievePreviousReferences($webhook_data, $integration)
        );
    }

    public function testItReturnsPreviousReferences(): void
    {
        $integration = new GitlabRepositoryIntegration(
            1,
            123654,
            'root/repo01',
            '',
            'https://example.com/root/repo01',
            new \DateTimeImmutable(),
            Project::buildForTest(),
            false
        );

        $webhook_data = new PostMergeRequestWebhookData(
            'merge_request',
            123,
            'https://example.com',
            2,
            "My Title TULEAP-58",
            'TULEAP-666 TULEAP-45',
            'opened',
            (new \DateTimeImmutable())->setTimestamp(1611315112),
            10
        );

        $this->dao
            ->method('searchMergeRequestInRepositoryWithId')
            ->with(1, 2)
            ->willReturn(
                [
                    'title'       => 'Title of merge request TULEAP-8',
                    'description' => 'Description of merge request TULEAP-58',
                ]
            );

        $this->tuleap_reference_retriever
            ->expects(self::exactly(2))
            ->method('retrieveTuleapReference')
            ->withConsecutive(
                [8],
                [58]
            );

        self::assertEquals(
            [
                new WebhookTuleapReference(8),
                new WebhookTuleapReference(58),
            ],
            $this->retriever->retrievePreviousReferences($webhook_data, $integration)
        );
    }
}
