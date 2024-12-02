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

namespace Tuleap\Artidoc\Stubs\Document;

use Tuleap\Artidoc\Domain\Document\Section\RawSection;
use Tuleap\Artidoc\Domain\Document\Section\SearchOneSection;
use Tuleap\Artidoc\Domain\Document\Section\Identifier\SectionIdentifier;
use Tuleap\NeverThrow\Err;
use Tuleap\NeverThrow\Fault;
use Tuleap\NeverThrow\Ok;
use Tuleap\NeverThrow\Result;

final class SearchOneSectionStub implements SearchOneSection
{
    private function __construct(private Ok|Err $result)
    {
    }

    public static function withResults(RawSection $row): self
    {
        return new self(Result::ok($row));
    }

    public static function withoutResults(): self
    {
        return new self(Result::err(Fault::fromMessage('Section not found')));
    }

    public function searchSectionById(SectionIdentifier $section_id): Ok|Err
    {
        return $this->result;
    }
}
