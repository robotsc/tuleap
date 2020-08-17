/*
 * Copyright (c) Enalean, 2015-Present. All Rights Reserved.
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

import planning_module from "../app.js";
import angular from "angular";
import "angular-mocks";
import { createAngularPromiseWrapper } from "../../../../../../../tests/jest/angular-promise-wrapper.js";

describe("MilestoneService", () => {
    let mockBackend, wrapPromise, MilestoneService, BacklogItemFactory;

    beforeEach(() => {
        BacklogItemFactory = { augment: jest.fn() };

        angular.mock.module(planning_module, function ($provide) {
            $provide.value("BacklogItemFactory", BacklogItemFactory);
        });

        let $rootScope;
        angular.mock.inject(function (_$rootScope_, _MilestoneService_, $httpBackend) {
            $rootScope = _$rootScope_;
            MilestoneService = _MilestoneService_;
            mockBackend = $httpBackend;
        });

        wrapPromise = createAngularPromiseWrapper($rootScope);
    });

    afterEach(function () {
        mockBackend.verifyNoOutstandingExpectation();
        mockBackend.verifyNoOutstandingRequest();
    });

    describe(`getMilestone`, () => {
        beforeEach(() => {
            mockBackend.expectGET("/api/v1/milestones/97").respond({
                id: 97,
                label: "Release 1.5.4",
                resources: {
                    backlog: {
                        accept: {
                            trackers: [
                                { id: 36, label: "User Stories" },
                                { id: 91, label: "Bugs" },
                            ],
                            parent_trackers: [{ id: 71, label: "Epics" }],
                        },
                    },
                    content: {
                        accept: {
                            trackers: [
                                { id: 23, label: "Tasks" },
                                { id: 78, label: "Activities" },
                            ],
                        },
                    },
                },
            });
        });

        it(`will call GET on the milestones
            and will format as string the accepted trackers
            and will augment the milestone object with the given scope items`, async () => {
            const promise = MilestoneService.getMilestone(97, []);
            mockBackend.flush();

            const response = await wrapPromise(promise);
            const milestone = response.results;

            expect(milestone.initialEffort).toEqual(0);
            expect(milestone.collapsed).toBe(true);
            expect(milestone.content).toEqual([]);
            expect(milestone.getContent).toBeDefined();
            expect(milestone.backlog_accepted_types.content).toContainEqual({
                id: 36,
                label: "User Stories",
            });
            expect(milestone.backlog_accepted_types.content).toContainEqual({
                id: 91,
                label: "Bugs",
            });
            expect(milestone.backlog_accepted_types.toString()).toEqual("trackerId36|trackerId91");
            expect(milestone.content_accepted_types.content).toContainEqual({
                id: 23,
                label: "Tasks",
            });
            expect(milestone.content_accepted_types.content).toContainEqual({
                id: 78,
                label: "Activities",
            });
            expect(milestone.content_accepted_types.toString()).toEqual("trackerId23|trackerId78");
        });

        it(`after getting the milestone, when I call getContent() on it,
            it will call GET on the milestone's content`, async () => {
            const first_backlog_item = { id: 704, label: "First user Story", initial_effort: 1 };
            const second_backlog_item = { id: 999, label: "Second user Story", initial_effort: 3 };

            const scope_items = [];

            const promise = MilestoneService.getMilestone(97, scope_items);
            mockBackend.flush();
            const milestone_response = await wrapPromise(promise);
            const milestone = milestone_response.results;

            mockBackend
                .expectGET("/api/v1/milestones/97/content?limit=50&offset=0")
                .respond([first_backlog_item, second_backlog_item], {
                    "X-PAGINATION-SIZE": "2",
                });

            const second_promise = milestone.getContent();
            expect(milestone.loadingContent).toBe(true);
            expect(milestone.alreadyLoaded).toBe(true);

            mockBackend.flush();
            await wrapPromise(second_promise);
            expect(scope_items[704]).toEqual(expect.objectContaining({ id: 704 }));
            expect(scope_items[999]).toEqual(expect.objectContaining({ id: 999 }));
            expect(milestone.content[0]).toEqual(expect.objectContaining({ id: 704 }));
            expect(milestone.content[1]).toEqual(expect.objectContaining({ id: 999 }));
            expect(milestone.loadingContent).toBe(false);
        });
    });

    describe(`getContent`, () => {
        it(`will call GET on the milestone's content
            and will return the X-PAGINATION-SIZE header as the total number of items`, async () => {
            const first_backlog_item = { id: 140, label: "First User Story" };
            const second_backlog_item = { id: 142, label: "Second User Story" };
            mockBackend
                .expectGET("/api/v1/milestones/25/content?limit=50&offset=0")
                .respond([first_backlog_item, second_backlog_item], {
                    "X-PAGINATION-SIZE": "2",
                });

            const promise = MilestoneService.getContent(25, 50, 0);
            mockBackend.flush();
            const response = await wrapPromise(promise);

            expect(response.total).toEqual("2");
            expect(response.results[0]).toEqual(expect.objectContaining({ id: 140 }));
            expect(response.results[1]).toEqual(expect.objectContaining({ id: 142 }));
        });
    });

    describe(`milestones GET`, () => {
        it.each([
            [
                "/api/v1/projects/104/milestones?fields=slim&limit=50&offset=0&order=desc&query=%7B%22status%22:%22open%22%7D",
                () => MilestoneService.getOpenMilestones(104, 50, 0, []),
            ],
            [
                "/api/v1/milestones/26/milestones?fields=slim&limit=50&offset=0&order=desc&query=%7B%22status%22:%22open%22%7D",
                () => MilestoneService.getOpenSubMilestones(26, 50, 0, []),
            ],
            [
                "/api/v1/projects/104/milestones?fields=slim&limit=50&offset=0&order=desc&query=%7B%22status%22:%22closed%22%7D",
                () => MilestoneService.getClosedMilestones(104, 50, 0, []),
            ],
            [
                "/api/v1/milestones/26/milestones?fields=slim&limit=50&offset=0&order=desc&query=%7B%22status%22:%22closed%22%7D",
                () => MilestoneService.getClosedSubMilestones(26, 50, 0, []),
            ],
        ])(
            `will call GET on the endpoint
            and will return the milestones
            and the X-PAGINATION-SIZE header as the total number of items`,
            async (endpoint_uri, functionUnderTest) => {
                mockBackend.expectGET(endpoint_uri).respond(
                    [
                        {
                            id: 77,
                            label: "First Sprint",
                            resources: {
                                backlog: {
                                    accept: {
                                        trackers: [
                                            { id: 36, label: "User Stories" },
                                            { id: 91, label: "Bugs" },
                                        ],
                                        parent_trackers: [{ id: 71, label: "Epics" }],
                                    },
                                },
                                content: {
                                    accept: {
                                        trackers: [
                                            { id: 23, label: "Tasks" },
                                            { id: 78, label: "Activities" },
                                        ],
                                    },
                                },
                            },
                        },
                        {
                            id: 98,
                            label: "Second Sprint",
                            resources: {
                                backlog: {
                                    accept: {
                                        trackers: [
                                            { id: 36, label: "User Stories" },
                                            { id: 91, label: "Bugs" },
                                        ],
                                        parent_trackers: [{ id: 71, label: "Epics" }],
                                    },
                                },
                                content: {
                                    accept: {
                                        trackers: [
                                            { id: 23, label: "Tasks" },
                                            { id: 78, label: "Activities" },
                                        ],
                                    },
                                },
                            },
                        },
                    ],
                    { "X-PAGINATION-SIZE": 2 }
                );

                const promise = functionUnderTest();
                mockBackend.flush();
                const response = await wrapPromise(promise);

                expect(response.results[0]).toEqual(expect.objectContaining({ id: 77 }));
                expect(response.results[1]).toEqual(expect.objectContaining({ id: 98 }));
                expect(response.total).toEqual("2");
            }
        );
    });

    describe(`putSubMilestones`, () => {
        it(`will call PUT on the milestone's milestones
            and add the new sub milestones`, async () => {
            mockBackend
                .expectPUT("/api/v1/milestones/26/milestones", {
                    id: 26,
                    ids: [77, 81],
                })
                .respond(200);

            const promise = MilestoneService.putSubMilestones(26, [77, 81]);
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });

    describe(`patchSubMilestones`, () => {
        it(`will call PATCH on the milestone's milestones
            and add the new sub milestones`, async () => {
            mockBackend
                .expectPATCH("/api/v1/milestones/26/milestones", {
                    add: [{ id: 77 }, { id: 81 }],
                })
                .respond(200);

            const promise = MilestoneService.patchSubMilestones(26, [77, 81]);
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });

    describe(`updateInitialEffort`, () => {
        it(`Sets milestone's initial effort as the sum of its backlog items' initial effort`, () => {
            const milestone = {
                initialEffort: 0,
                content: [{ initial_effort: 3 }, { initial_effort: 5 }],
            };
            MilestoneService.updateInitialEffort(milestone);
            expect(milestone.initialEffort).toEqual(8);
        });
    });

    describe(`reorderBacklog`, () => {
        it(`will call PATCH on the milestone's backlog
            and reorder items`, async () => {
            mockBackend
                .expectPATCH("/api/v1/milestones/26/backlog", {
                    order: {
                        ids: [99, 187],
                        direction: "before",
                        compared_to: 265,
                    },
                })
                .respond(200);

            const promise = MilestoneService.reorderBacklog(26, [99, 187], {
                direction: "before",
                item_id: 265,
            });
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });

    describe(`removeAddReorderToBacklog`, () => {
        it(`will call PATCH on the milestone's backlog
            and reorder items while moving them from another milestone`, async () => {
            mockBackend
                .expectPATCH("/api/v1/milestones/26/backlog", {
                    order: {
                        ids: [99, 187],
                        direction: "after",
                        compared_to: 265,
                    },
                    add: [
                        { id: 99, remove_from: 77 },
                        { id: 187, remove_from: 77 },
                    ],
                })
                .respond(200);

            const promise = MilestoneService.removeAddReorderToBacklog(77, 26, [99, 187], {
                direction: "after",
                item_id: 265,
            });
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });

    describe(`removeAddToBacklog`, () => {
        it(`will call PATCH on the milestone's backlog
            and move items from another milestone`, async () => {
            mockBackend
                .expectPATCH("/api/v1/milestones/26/backlog", {
                    add: [
                        { id: 99, remove_from: 77 },
                        { id: 187, remove_from: 77 },
                    ],
                })
                .respond(200);

            const promise = MilestoneService.removeAddToBacklog(77, 26, [99, 187]);
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });

    describe(`reorderContent`, () => {
        it(`will call PATCH on the milestone's content
            and reorder items`, async () => {
            mockBackend
                .expectPATCH("/api/v1/milestones/26/content", {
                    order: {
                        ids: [99, 187],
                        direction: "before",
                        compared_to: 265,
                    },
                })
                .respond(200);

            const promise = MilestoneService.reorderContent(26, [99, 187], {
                direction: "before",
                item_id: 265,
            });
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });

    describe(`addReorderToContent`, () => {
        it(`will call PATCH on the milestone's content
            and add new items reordered`, async () => {
            mockBackend
                .expectPATCH("/api/v1/milestones/26/content", {
                    order: {
                        ids: [99, 187],
                        direction: "after",
                        compared_to: 265,
                    },
                    add: [{ id: 99 }, { id: 187 }],
                })
                .respond(200);

            const promise = MilestoneService.addReorderToContent(26, [99, 187], {
                direction: "after",
                item_id: 265,
            });
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });

    describe(`addToContent`, () => {
        it(`will call PATCH on the milestone's content
            and add new items`, async () => {
            mockBackend
                .expectPATCH("/api/v1/milestones/26/content", {
                    add: [{ id: 99 }, { id: 187 }],
                })
                .respond(200);

            const promise = MilestoneService.addToContent(26, [99, 187]);
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });

    describe(`removeAddReorderToContent`, () => {
        it(`will call PATCH on the milestone's content
            and reorder items while moving them from another milestone`, async () => {
            mockBackend
                .expectPATCH("/api/v1/milestones/26/content", {
                    order: {
                        ids: [99, 187],
                        direction: "after",
                        compared_to: 265,
                    },
                    add: [
                        { id: 99, remove_from: 77 },
                        { id: 187, remove_from: 77 },
                    ],
                })
                .respond(200);

            const promise = MilestoneService.removeAddReorderToContent(77, 26, [99, 187], {
                direction: "after",
                item_id: 265,
            });
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });

    describe(`removeAddToContent`, () => {
        it(`will call PATCH on the milestone's content
            and move items from another milestone`, async () => {
            mockBackend
                .expectPATCH("/api/v1/milestones/26/content", {
                    add: [
                        { id: 99, remove_from: 77 },
                        { id: 187, remove_from: 77 },
                    ],
                })
                .respond(200);

            const promise = MilestoneService.removeAddToContent(77, 26, [99, 187]);
            mockBackend.flush();

            expect(await wrapPromise(promise)).toBeTruthy();
        });
    });
});
