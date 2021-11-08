<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement;

use Tuleap\ProgramManagement\Domain\Program\Backlog\Timebox\RetrieveCrossRef;
use Tuleap\ProgramManagement\Domain\Program\Backlog\Timebox\RetrieveStatusValueUserCanSee;
use Tuleap\ProgramManagement\Domain\Program\Backlog\Timebox\RetrieveTimeframeValueUserCanSee;
use Tuleap\ProgramManagement\Domain\Program\Backlog\Timebox\RetrieveTitleValueUserCanSee;
use Tuleap\ProgramManagement\Domain\Program\Backlog\Timebox\RetrieveUri;
use Tuleap\ProgramManagement\Domain\Program\Backlog\Timebox\VerifyUserCanUpdateTimebox;
use Tuleap\ProgramManagement\Domain\Workspace\UserIdentifier;
use Tuleap\ProgramManagement\Domain\Workspace\VerifyUserCanPlanInProgramIncrement;

/**
 * @psalm-immutable
 */
final class ProgramIncrement
{
    private function __construct(
        public int $id,
        public string $title,
        public string $uri,
        public string $xref,
        public bool $user_can_update,
        public bool $user_can_plan,
        public ?string $status,
        public ?int $start_date,
        public ?int $end_date
    ) {
    }

    public static function build(
        RetrieveStatusValueUserCanSee $retrieve_status_value,
        RetrieveTitleValueUserCanSee $retrieve_title_value,
        RetrieveTimeframeValueUserCanSee $retrieve_timeframe_value,
        RetrieveUri $retrieve_uri,
        RetrieveCrossRef $retrieve_cross_ref,
        VerifyUserCanUpdateTimebox $verify_user_can_update,
        VerifyUserCanPlanInProgramIncrement $can_plan_in_program_increment_verifier,
        UserIdentifier $user_identifier,
        ProgramIncrementIdentifier $program_increment_identifier,
    ): ?self {
        $title = $retrieve_title_value->getTitle($program_increment_identifier);
        if (! $title) {
            return null;
        }
        $status     = $retrieve_status_value->getLabel($program_increment_identifier, $user_identifier);
        $start_date = $retrieve_timeframe_value->getStartDateValueTimestamp($program_increment_identifier, $user_identifier);
        $end_date   = $retrieve_timeframe_value->getEndDateValueTimestamp($program_increment_identifier, $user_identifier);

        return new self(
            $program_increment_identifier->getId(),
            $title,
            $retrieve_uri->getUri($program_increment_identifier),
            $retrieve_cross_ref->getXRef($program_increment_identifier),
            $verify_user_can_update->canUserUpdate($program_increment_identifier, $user_identifier),
            $can_plan_in_program_increment_verifier->userCanPlan(
                $program_increment_identifier,
                $user_identifier
            ),
            $status,
            $start_date,
            $end_date,
        );
    }
}
