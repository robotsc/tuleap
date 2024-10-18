/*
 *  Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
 *
 *  Tuleap is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  Tuleap is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

import { keymap } from "prosemirror-keymap";
import { baseKeymap } from "prosemirror-commands";
import type { Plugin } from "prosemirror-state";
import { buildKeymap } from "./keymap";
import { custom_schema } from "../../custom_schema";
import { buildInputRules } from "./input-rules";
import { setupMonoToolbar } from "./mono-toolbar";
import type { ToolbarBus } from "./helper/toolbar-bus";
import { SingleListInSelectionDetector } from "./list/SingleListInSelectionDetector";

export { buildKeymap };
export type { LinkState } from "./links/LinkState";
export type { ImageState } from "./image/ImageState";
export type { ListState } from "./list/ListState";
export type { Heading } from "./text-style/Heading";

export const NB_HEADING = 3;

export function setupToolbar(toolbar_bus: ToolbarBus): Plugin[] {
    return [
        keymap(
            buildKeymap(
                SingleListInSelectionDetector(custom_schema.nodes.ordered_list),
                SingleListInSelectionDetector(custom_schema.nodes.bullet_list),
                NB_HEADING,
            ),
        ),
        keymap(baseKeymap),
        buildInputRules(custom_schema),
        setupMonoToolbar(toolbar_bus),
    ];
}
