<?php
/**
 * Copyright (c) Enalean 2021 -  Present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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

declare(strict_types=1);

namespace Tuleap\ProgramManagement\Adapter\Workspace;

use Tuleap\ProgramManagement\Domain\Team\SearchProgramsOfTeam;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveFullProjectStub;
use Tuleap\ProgramManagement\Tests\Stub\SearchProgramsOfTeamStub;
use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;

final class ProgramsSearcherTest extends TestCase
{
    private SearchProgramsOfTeam $program_ids_searcher;
    private RetrieveFullProject $retrieve_full_project;

    protected function setUp(): void
    {
        $this->program_ids_searcher  = SearchProgramsOfTeamStub::buildPrograms(110, 111);
        $this->retrieve_full_project = RetrieveFullProjectStub::withoutProject();
    }

    private function getSearcher(): ProgramsSearcher
    {
        return new ProgramsSearcher($this->program_ids_searcher, $this->retrieve_full_project);
    }

    public function testItReturnsTheProgramProjectsOfATeam(): void
    {
        $program_red  = ProjectTestBuilder::aProject()->withId(110)->build();
        $program_blue = ProjectTestBuilder::aProject()->withId(111)->build();

        $this->retrieve_full_project = RetrieveFullProjectStub::withProjects($program_red, $program_blue);

        $team     = ProjectTestBuilder::aProject()->withId(123)->build();
        $programs = $this->getSearcher()->searchLinkedProjects($team);
        self::assertCount(2, $programs);
        self::assertContains($program_red, $programs);
        self::assertContains($program_blue, $programs);
    }
}
