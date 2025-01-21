<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
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

namespace Tuleap\CrossTracker;

use Tuleap\CrossTracker\Report\RetrieveReport;

final readonly class CrossTrackerReportFactory
{
    public function __construct(
        private RetrieveReport $report_retriever,
    ) {
    }

    /**
     * @throws CrossTrackerReportNotFoundException
     */
    public function getById($id): CrossTrackerReport
    {
        $report_row = $this->report_retriever->searchReportById($id);
        if (! $report_row) {
            throw new CrossTrackerReportNotFoundException();
        }

        return new CrossTrackerExpertReport($id, $report_row['query']);
    }
}
