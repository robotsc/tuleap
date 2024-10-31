<?php
/**
 * Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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

namespace Tuleap\Artidoc\Document;

use Psr\Log\LoggerInterface;
use Tuleap\Tracker\RetrieveTracker;

final readonly class ConfiguredTrackerRetriever
{
    public function __construct(
        private SearchConfiguredTracker $dao,
        private RetrieveTracker $retrieve_tracker,
        private LoggerInterface $logger,
    ) {
    }

    public function getTracker(ArtidocDocument $document): ?\Tracker
    {
        $tracker_id = $this->dao->getTracker((int) $document->getId());

        if ($tracker_id === null) {
            return null;
        }

        $tracker = $this->retrieve_tracker->getTrackerById($tracker_id);
        if ($tracker === null) {
            $this->logger->warning("Artidoc #{$document->getId()} is configured with not found tracker #{$tracker_id}.");
            return null;
        }

        if ($tracker->isDeleted()) {
            $this->logger->warning("Artidoc #{$document->getId()} is configured with a deleted tracker #{$tracker_id}.");
            return null;
        }

        return $tracker;
    }
}
