<?php
/**
 * Copyright (c) Enalean, 2022 - Present. All Rights Reserved.
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

namespace Tuleap\Docman\REST\v1\Files;

use Tuleap\Docman\Version\VersionDao;
use Tuleap\Docman\Version\VersionRetriever;
use Tuleap\REST\AuthenticatedResource;
use Tuleap\REST\Header;

final class FileVersionsResource extends AuthenticatedResource
{
    public const NAME = 'docman_file_versions';

    /**
     * @url OPTIONS {id}
     */
    public function optionsId(int $id): void
    {
        $this->sendAllowHeaders();
    }

    /**
     * Delete version
     *
     * Delete a version of a file. Please note that the last version of a file cannot be deleted.
     *
     * @url    DELETE {id}
     * @access hybrid
     * @status 200
     */
    public function deleteId(int $id): void
    {
        $this->checkAccess();
        $this->sendAllowHeaders();

        (new FileVersionsDeletor(
            new VersionRetriever(new VersionDao()),
            new \Docman_VersionFactory(),
            new \Docman_ItemFactory()
        ))->delete(
            $id,
            \UserManager::instance()->getCurrentUser()
        );
    }

    private function sendAllowHeaders(): void
    {
        Header::allowOptionsDelete();
    }
}
