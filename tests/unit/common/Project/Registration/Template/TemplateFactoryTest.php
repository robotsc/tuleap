<?php
/**
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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

namespace Tuleap\Project\Registration\Template;

use org\bovigo\vfs\vfsStream;
use Tuleap\ForgeConfigSandbox;
use Tuleap\Glyph\GlyphFinder;
use Tuleap\Project\XML\ConsistencyChecker;
use Tuleap\XML\ProjectXMLMerger;

class TemplateFactoryTest extends \Tuleap\Test\PHPUnit\TestCase
{
    use ForgeConfigSandbox;

    private TemplateFactory $factory;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&ConsistencyChecker
     */
    private $consistency_checker;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\ProjectManager
     */
    private $project_manager;

    protected function setUp(): void
    {
        \ForgeConfig::set('codendi_cache_dir', vfsStream::setup('TemplateFactoryTest')->url());

        $this->consistency_checker = $this->createMock(ConsistencyChecker::class);
        $this->project_manager     = $this->createMock(\ProjectManager::class);

        $this->factory = new TemplateFactory(
            new GlyphFinder(new \EventManager()),
            new ProjectXMLMerger(),
            $this->consistency_checker,
            $this->createMock(TemplateDao::class),
            $this->project_manager
        );
    }

    public function testItReturnsTemplates(): void
    {
        $this->consistency_checker->method('areAllServicesAvailable')->willReturn(true);

        $templates = $this->factory->getValidTemplates();
        self::assertCount(4, $templates);
        self::assertInstanceOf(AgileALMTemplate::class, $templates[0]);
        self::assertInstanceOf(ScrumTemplate::class, $templates[1]);
        self::assertInstanceOf(KanbanTemplate::class, $templates[2]);
        self::assertInstanceOf(IssuesTemplate::class, $templates[3]);
    }

    public function testItReturnsScrumTemplate(): void
    {
        $this->consistency_checker->method('areAllServicesAvailable')->willReturn(true);

        $template = $this->factory->getTemplate(ScrumTemplate::NAME);
        self::assertInstanceOf(ScrumTemplate::class, $template);
    }

    public function testItReturnsEmptyTemplate(): void
    {
        $this->consistency_checker->method('areAllServicesAvailable')->willReturn(true);

        $template = $this->factory->getTemplate(EmptyTemplate::NAME);
        self::assertInstanceOf(EmptyTemplate::class, $template);
    }

    public function testItReturnsScrumTemplateXML(): void
    {
        $this->consistency_checker->method('areAllServicesAvailable')->willReturn(true);

        $template = $this->factory->getTemplate(ScrumTemplate::NAME);
        $xml      = simplexml_load_string(file_get_contents($template->getXMLPath()));
        self::assertNotEmpty($xml->services);
        self::assertNotEmpty($xml->agiledashboard);
        self::assertNotEmpty($xml->trackers);
    }

    public function testItThrowsAnExceptionWhenTemplateDoesntExist(): void
    {
        $this->consistency_checker->method('areAllServicesAvailable')->willReturn(true);

        $this->expectException(InvalidXMLTemplateNameException::class);

        $this->factory->getTemplate('stuff');
    }

    public function testItDoesntReturnEmptyTemplateWhenNoTemplatesAreAvailable(): void
    {
        $this->consistency_checker->method('areAllServicesAvailable')->willReturn(false);

        $glyph_finder   = new GlyphFinder($this->createMock(\EventManager::class));
        $empty_template = new EmptyTemplate($glyph_finder);

        $available_templates = $this->factory->getValidTemplates();

        self::assertEquals($empty_template->getId(), $available_templates[0]->getId());
        self::assertEquals($empty_template->getTitle(), $available_templates[0]->getTitle());
    }

    public function testItDoesntReturnTheTemplateThatIsNotAvailable(): void
    {
        $this->consistency_checker->method('areAllServicesAvailable')->willReturn(false);

        $this->expectException(InvalidXMLTemplateNameException::class);

        $this->factory->getTemplate(ScrumTemplate::NAME);
    }

    public function testItReturnsCompanyTemplateWhenTheTemplateIdIsNot100(): void
    {
        $this->consistency_checker->method('areAllServicesAvailable')->willReturn(true);

        $template100 = $this->createMock(\Project::class);
        $template100->expects(self::once())->method('getGroupId')->willReturn("100");
        $template100->expects(self::never())->method('getUnixNameLowerCase');
        $template100->expects(self::never())->method('getDescription');
        $template100->expects(self::never())->method('getPublicName');

        $template110 = $this->createMock(\Project::class);
        $template110->expects(self::atLeast(2))->method('getGroupId')->willReturn("110");
        $template110->method('getUnixNameLowerCase')->willReturn("hustler-company");
        $template110->method('getDescription')->willReturn("New Jack City");
        $template110->method('getPublicName')->willReturn("Hustler Company");

        $template120 = $this->createMock(\Project::class);
        $template120->expects(self::atLeast(2))->method('getGroupId')->willReturn("120");
        $template120->method('getUnixNameLowerCase')->willReturn("lyudi-invalidy-company");
        $template120->method('getDescription')->willReturn("All about us");
        $template120->method('getPublicName')->willReturn("Lyudi Invalidy Company");

        $site_templates = [$template100, $template110, $template120];
        $this->project_manager->method('getSiteTemplates')->willReturn($site_templates);

        $glyph_finder      = new GlyphFinder($this->createMock(\EventManager::class));
        $hustler_template  = new CompanyTemplate($template110, $glyph_finder);
        $invalidy_template = new CompanyTemplate($template120, $glyph_finder);

        $expected_company_templates = [$hustler_template, $invalidy_template];

        self::assertEquals($expected_company_templates, $this->factory->getCompanyTemplateList());
    }
}
