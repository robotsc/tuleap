<?php
/*
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\Reference\REST;

use Psr\EventDispatcher\EventDispatcherInterface;
use Tuleap\Project\Admin\Reference\Browse\ExternalSystemReferencePresentersCollector;

final class ReferenceRepresentationBuilder
{
    public function __construct(private \ReferenceManager $reference_manager, private EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * @return ReferenceRepresentation[]
     */
    public function getProjectReferences(\Project $project): array
    {
        $representations = [];

        $collector = $this->dispatcher->dispatch(new ExternalSystemReferencePresentersCollector());
        foreach ($collector->getExternalSystemReferencePresenters() as $external_system_reference) {
            $representations[] = new ReferenceRepresentation(
                $external_system_reference->keyword,
                $external_system_reference->description,
            );
        }

        foreach ($this->reference_manager->getReferencesByProject($project) as $reference) {
            if (! $reference->isActive()) {
                continue;
            }
            $representations[] = new ReferenceRepresentation(
                $reference->getKeyword(),
                $reference->getResolvedDescription(),
            );
        }
        return $representations;
    }
}
