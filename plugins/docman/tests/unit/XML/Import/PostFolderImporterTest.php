<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\Docman\XML\Import;

use Docman_Item;
use SimpleXMLElement;
use Tuleap\Test\PHPUnit\TestCase;

final class PostFolderImporterTest extends TestCase
{
    public function testPostImport(): void
    {
        $node = new SimpleXMLElement(
            <<<EOS
            <?xml version="1.0" encoding="UTF-8"?>
            <item type="folder">
                <item type="empty" />
                <item type="wiki" />
            </item>
            EOS
        );

        $node_importer = $this->createMock(NodeImporter::class);
        $item          = new Docman_Item();

        $node_importer->expects(self::exactly(2))->method('import')
            ->withConsecutive(
                [self::callback(static fn(SimpleXMLElement $node) => (string) $node['type'] === 'empty'), $item],
                [self::callback(static fn(SimpleXMLElement $node) => (string) $node['type'] === 'wiki'), $item],
            );

        $importer = new PostFolderImporter();
        $importer->postImport($node_importer, $node, $item);
    }
}
