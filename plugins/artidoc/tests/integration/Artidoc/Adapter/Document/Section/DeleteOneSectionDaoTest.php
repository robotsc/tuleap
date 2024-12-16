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

namespace Tuleap\Artidoc\Adapter\Document\Section;

use Tuleap\Artidoc\Adapter\Document\ArtidocDocument;
use Tuleap\Artidoc\Adapter\Document\Section\Freetext\Identifier\UUIDFreetextIdentifierFactory;
use Tuleap\Artidoc\Adapter\Document\Section\Identifier\UUIDSectionIdentifierFactory;
use Tuleap\Artidoc\Domain\Document\ArtidocWithContext;
use Tuleap\Artidoc\Domain\Document\Section\ContentToInsert;
use Tuleap\Artidoc\Domain\Document\Section\Freetext\FreetextContent;
use Tuleap\Artidoc\Domain\Document\Section\Identifier\SectionIdentifier;
use Tuleap\DB\DatabaseUUIDV7Factory;
use Tuleap\DB\DBFactory;
use Tuleap\Test\PHPUnit\TestIntegrationTestCase;

final class DeleteOneSectionDaoTest extends TestIntegrationTestCase
{
    private ArtidocWithContext $artidoc;
    private SectionIdentifier $uuid_intro;
    private SectionIdentifier $uuid_2;

    protected function setUp(): void
    {
        $this->artidoc = new ArtidocWithContext(new ArtidocDocument(['item_id' => 101]));

        $save_dao = new SaveSectionDao(
            new UUIDSectionIdentifierFactory(new DatabaseUUIDV7Factory()),
            new UUIDFreetextIdentifierFactory(new DatabaseUUIDV7Factory()),
        );

        $this->uuid_intro = $save_dao->saveSectionAtTheEnd(
            $this->artidoc,
            ContentToInsert::fromFreetext(new FreetextContent('Introduction', 'Lorem ipsum')),
        );
        $save_dao->saveSectionAtTheEnd(
            $this->artidoc,
            ContentToInsert::fromArtifactId(1001),
        );
        $this->uuid_2 = $save_dao->saveSectionAtTheEnd(
            $this->artidoc,
            ContentToInsert::fromArtifactId(1002),
        );
        $save_dao->saveSectionAtTheEnd(
            $this->artidoc,
            ContentToInsert::fromArtifactId(1003),
        );
        $save_dao->saveSectionAtTheEnd(
            $this->artidoc,
            ContentToInsert::fromFreetext(new FreetextContent('Legal', 'doloret')),
        );
    }

    public function testDeleteArtifactSectionById(): void
    {
        $delete_dao = new DeleteOneSectionDao();

        SectionsAsserter::assertSectionsForDocument($this->artidoc, ['Introduction', 1001, 1002, 1003, 'Legal']);
        $delete_dao->deleteSectionById($this->uuid_2);
        SectionsAsserter::assertSectionsForDocument($this->artidoc, ['Introduction', 1001, 1003, 'Legal']);
    }

    public function testDeleteFreetextSectionById(): void
    {
        $db         = DBFactory::getMainTuleapDBConnection()->getDB();
        $delete_dao = new DeleteOneSectionDao();

        SectionsAsserter::assertSectionsForDocument($this->artidoc, ['Introduction', 1001, 1002, 1003, 'Legal']);
        self::assertSame(2, $db->cell('SELECT count(*) FROM plugin_artidoc_section_freetext'));

        $delete_dao->deleteSectionById($this->uuid_intro);

        SectionsAsserter::assertSectionsForDocument($this->artidoc, [1001, 1002, 1003, 'Legal']);
        self::assertSame(1, $db->cell('SELECT count(*) FROM plugin_artidoc_section_freetext'));
    }
}
