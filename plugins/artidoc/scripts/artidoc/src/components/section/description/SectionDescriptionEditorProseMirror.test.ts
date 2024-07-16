/*
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
import { beforeAll, describe, expect, it, vi } from "vitest";
import type { VueWrapper } from "@vue/test-utils";
import { shallowMount } from "@vue/test-utils";
import type { ComponentPublicInstance } from "vue";
import SectionDescriptionEditorProseMirror from "@/components/section/description/SectionDescriptionEditorProseMirror.vue";
import VueDOMPurifyHTML from "vue-dompurify-html";

describe("SectionDescriptionEditorProseMirror", () => {
    let wrapper: VueWrapper<ComponentPublicInstance>;

    beforeAll(() => {
        wrapper = shallowMount(SectionDescriptionEditorProseMirror, {
            props: {
                editable_description: "<h1>description</h1>",
                input_current_description: vi.fn(),
                toggle_has_been_canceled: false,
            },
            global: {
                plugins: [VueDOMPurifyHTML],
            },
        });
    });

    it("should display the editor", () => {
        const editorProseMirror = wrapper.find(".ProseMirror");
        expect(editorProseMirror.exists()).toBe(true);
    });
    it("should focus the editor", () => {
        expect(wrapper.find(".ProseMirror-focused").exists()).toBe(true);
    });
});
