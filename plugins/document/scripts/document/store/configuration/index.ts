/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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

import type { ListOfSearchResultColumnDefinition, RootState, SearchCriteria } from "../../type";
import type { Module } from "vuex";
import type { ProjectFlag } from "@tuleap/vue-breadcrumb-privacy";
import type { ProjectPrivacy } from "@tuleap/project-privacy-helper";
import * as getters from "./getters";

export interface ConfigurationState {
    readonly user_id: string;
    readonly project_id: string;
    readonly root_id: number;
    readonly project_name: string;
    readonly project_public_name: string;
    readonly user_is_admin: boolean;
    readonly user_can_create_wiki: boolean;
    readonly embedded_are_allowed: boolean;
    readonly is_item_status_metadata_used: boolean;
    readonly is_obsolescence_date_metadata_used: boolean;
    readonly max_files_dragndrop: number;
    readonly max_size_upload: number;
    readonly warning_threshold: number;
    readonly max_archive_size: number;
    readonly project_url: string;
    readonly date_time_format: string;
    readonly privacy: ProjectPrivacy;
    readonly project_flags: ProjectFlag[];
    readonly is_changelog_proposed_after_dnd: boolean;
    readonly is_deletion_allowed: boolean;
    readonly user_locale: string;
    readonly relative_dates_display: string;
    readonly project_icon: string;
    readonly search_for_document_with_criteria: boolean;
    readonly criteria: SearchCriteria;
    readonly columns: ListOfSearchResultColumnDefinition;
}

export function createConfigurationModule(
    initial_configuration_state: ConfigurationState
): Module<ConfigurationState, RootState> {
    return {
        namespaced: true,
        state: initial_configuration_state,
        getters,
    };
}
