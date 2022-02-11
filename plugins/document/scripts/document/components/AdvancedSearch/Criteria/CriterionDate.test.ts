/**
 * Copyright (c) Enalean, 2022 - present. All Rights Reserved.
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

import { shallowMount } from "@vue/test-utils";
import CriterionDate from "./CriterionDate.vue";
import type { SearchDate } from "../../../type";
import localVue from "../../../helpers/local-vue";

describe("CriterionDate", () => {
    it("should render the component when no date set", async () => {
        const wrapper = shallowMount(CriterionDate, {
            localVue,
            propsData: {
                name: "create_date",
                value: null,
                label: "Creation date",
            },
        });

        await wrapper.vm.$nextTick();

        expect(wrapper.element).toMatchSnapshot();
    });

    it("should render the component when date is set", async () => {
        const value: SearchDate = { date: "2022-01-01", operator: "=" };
        const wrapper = shallowMount(CriterionDate, {
            localVue,
            propsData: {
                name: "create_date",
                value,
                label: "Creation date",
            },
        });

        await wrapper.vm.$nextTick();

        expect(wrapper.element).toMatchSnapshot();
    });

    it("should warn parent component when user is changing date", () => {
        const wrapper = shallowMount(CriterionDate, {
            localVue,
            propsData: {
                name: "create_date",
                value: null,
                label: "Creation date",
            },
        });

        wrapper.find("[data-test=document-criterion-date-create_date]").setValue("2022-01-01");

        const expected: SearchDate = { date: "2022-01-01", operator: ">" };
        expect(wrapper.emitted().input).toStrictEqual([[expected]]);
    });

    it("should warn parent component when user is changing operator", () => {
        const wrapper = shallowMount(CriterionDate, {
            localVue,
            propsData: {
                name: "create_date",
                value: null,
                label: "Creation date",
            },
        });

        wrapper.find("[data-test=equal]").setSelected();

        const expected: SearchDate = { date: "", operator: "=" };
        expect(wrapper.emitted().input).toStrictEqual([[expected]]);
    });
});
