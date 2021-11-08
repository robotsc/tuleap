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

namespace Tuleap\ProgramManagement\Adapter\Program\Backlog\ProgramIncrement\Content;

use Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement\Content\AddFeatureException;
use Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement\Content\FeatureAddition;
use Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement\ProgramIncrementNotFoundException;
use Tuleap\ProgramManagement\Domain\UserCanPrioritize;
use Tuleap\ProgramManagement\Tests\Builder\FeatureIdentifierBuilder;
use Tuleap\ProgramManagement\Tests\Builder\ProgramIdentifierBuilder;
use Tuleap\ProgramManagement\Tests\Builder\ProgramIncrementIdentifierBuilder;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveFullArtifactStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveUserStub;
use Tuleap\ProgramManagement\Tests\Stub\UserIdentifierStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyCanBePlannedInProgramIncrementStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyPrioritizeFeaturesPermissionStub;
use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\ArtifactLinkUpdater;
use Tuleap\Tracker\Test\Builders\ArtifactTestBuilder;

final class FeatureAdditionProcessorTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private const PROGRAM_INCREMENT_ID = 37;
    private const FEATURE_ID           = 76;
    private const PROGRAM_ID           = 110;
    private RetrieveFullArtifactStub $artifact_retriever;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&ArtifactLinkUpdater
     */
    private $artifact_link_updater;
    private Artifact $artifact;

    protected function setUp(): void
    {
        $this->artifact              = ArtifactTestBuilder::anArtifact(self::PROGRAM_INCREMENT_ID)->build();
        $this->artifact_retriever    = RetrieveFullArtifactStub::withArtifact($this->artifact);
        $this->artifact_link_updater = $this->createMock(ArtifactLinkUpdater::class);
    }

    private function getProcessor(): FeatureAdditionProcessor
    {
        return new FeatureAdditionProcessor(
            $this->artifact_retriever,
            $this->artifact_link_updater,
            RetrieveUserStub::withGenericUser()
        );
    }

    public function testItThrowsWhenProgramIncrementArtifactCannotBeFound(): void
    {
        $feature_addition         = $this->buildFeatureAddition();
        $this->artifact_retriever = RetrieveFullArtifactStub::withError();

        $this->expectException(ProgramIncrementNotFoundException::class);
        $this->getProcessor()->add($feature_addition);
    }

    public function dataProviderExceptions(): array
    {
        return [
            'it wraps Tracker_Exception'                    => [new \Tracker_Exception()],
            'it wraps Tracker_NoArtifactLinkFieldException' => [new \Tracker_NoArtifactLinkFieldException()],
        ];
    }

    /**
     * @dataProvider dataProviderExceptions
     */
    public function testItWrapsExceptions(\Throwable $exception): void
    {
        $feature_addition = $this->buildFeatureAddition();
        $this->artifact_link_updater->method('updateArtifactLinks')->willThrowException($exception);

        $this->expectException(AddFeatureException::class);
        $this->getProcessor()->add($feature_addition);
    }

    public function testItUpdatesArtifactLinksToAddFeatureToProgramIncrement(): void
    {
        $this->artifact_link_updater->expects(self::once())
            ->method('updateArtifactLinks')
            ->with(
                self::isInstanceOf(\PFUser::class),
                $this->artifact,
                [self::FEATURE_ID],
                [],
                \Tracker_FormElement_Field_ArtifactLink::NO_NATURE
            );

        $this->getProcessor()->add($this->buildFeatureAddition());
    }

    private function buildFeatureAddition(): FeatureAddition
    {
        $user              = UserIdentifierStub::buildGenericUser();
        $program           = ProgramIdentifierBuilder::buildWithId(self::PROGRAM_ID);
        $program_increment = ProgramIncrementIdentifierBuilder::buildWithIdAndUser(self::PROGRAM_INCREMENT_ID, $user);

        $feature = FeatureIdentifierBuilder::build(self::FEATURE_ID, self::PROGRAM_ID);

        return FeatureAddition::fromFeature(
            VerifyCanBePlannedInProgramIncrementStub::buildCanBePlannedVerifier(),
            $feature,
            $program_increment,
            UserCanPrioritize::fromUser(
                VerifyPrioritizeFeaturesPermissionStub::canPrioritize(),
                $user,
                $program,
                null
            )
        );
    }
}
