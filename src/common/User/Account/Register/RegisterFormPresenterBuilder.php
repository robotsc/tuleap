<?php
/**
 * Copyright (c) Enalean, 2023 - Present. All Rights Reserved.
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

namespace Tuleap\User\Account\Register;

use Psr\EventDispatcher\EventDispatcherInterface;
use Tuleap\Layout\BaseLayout;

final class RegisterFormPresenterBuilder
{
    public function __construct(
        private EventDispatcherInterface $event_dispatcher,
        private \TemplateRendererFactory $renderer_factory,
        private \Account_TimezonesCollection $timezones_collection,
    ) {
    }

    /**
     * @return \Closure(): void
     */
    public function getPresenterClosureForFirstDisplay(
        \HTTPRequest $request,
        BaseLayout $layout,
        bool $is_admin,
        bool $is_password_needed,
    ): \Closure {
        return $this->getPresenterClosure($request, $layout, $is_admin, $is_password_needed, null);
    }

    /**
     * @return \Closure(): void
     */
    public function getPresenterClosure(
        \HTTPRequest $request,
        BaseLayout $layout,
        bool $is_admin,
        bool $is_password_needed,
        ?RegisterFormValidationIssue $form_validation_issue,
    ): \Closure {
        $form_loginname       = $request->exist('form_loginname') ? $request->get('form_loginname') : '';
        $form_loginname_error = $this->getFieldError('form_loginname', $form_validation_issue);

        $form_realname       = $request->exist('form_realname') ? $request->get('form_realname') : '';
        $form_realname_error = $this->getFieldError('form_realname', $form_validation_issue);

        $form_email       = $request->exist('form_email') ? $request->get('form_email') : '';
        $form_email_error = $this->getFieldError('form_email', $form_validation_issue);

        $form_pw       = '';
        $form_pw_error = $this->getFieldError('form_pw', $form_validation_issue);

        $form_mail_site       = ! $request->exist('form_mail_site') || $request->get('form_mail_site') == 1;
        $form_mail_site_error = $this->getFieldError('form_mail_site', $form_validation_issue);

        $form_restricted       = \ForgeConfig::areRestrictedUsersAllowed() && (! $request->exist('form_restricted') || $request->get('form_restricted') == 1);
        $form_restricted_error = $this->getFieldError('form_restricted', $form_validation_issue);

        $form_send_email       = $request->get('form_send_email') == 1;
        $form_send_email_error = $this->getFieldError('form_send_email', $form_validation_issue);

        if ($request->exist('timezone') && $this->timezones_collection->isValidTimezone($request->get('timezone'))) {
            $timezone = $request->get('timezone');
        } else {
            $timezone = false;
        }
        $timezone_error = $this->getFieldError('timezone', $form_validation_issue);

        $form_register_purpose       = $request->exist('form_register_purpose') ? $request->get('form_register_purpose') : '';
        $form_register_purpose_error = $this->getFieldError('form_register_purpose', $form_validation_issue);

        $extra_plugin_field = $this->event_dispatcher
            ->dispatch(new AddAdditionalFieldUserRegistration($layout, $request))
            ->getAdditionalFieldsInHtml();


        if ($is_admin) {
            $prefill   = new \Account_RegisterAdminPrefillValuesPresenter(
                new \Account_RegisterField($form_loginname, $form_loginname_error),
                new \Account_RegisterField($form_email, $form_email_error),
                new \Account_RegisterField($form_pw, $form_pw_error),
                new \Account_RegisterField($form_realname, $form_realname_error),
                new \Account_RegisterField($form_register_purpose, $form_register_purpose_error),
                new \Account_RegisterField($form_mail_site, $form_mail_site_error),
                new \Account_RegisterField($timezone, $timezone_error),
                new \Account_RegisterField($form_restricted, $form_restricted_error),
                new \Account_RegisterField($form_send_email, $form_send_email_error),
                \ForgeConfig::areRestrictedUsersAllowed()
            );
            $presenter = new \Account_RegisterByAdminPresenter($prefill, $extra_plugin_field);
            $template  = 'register-admin';
        } else {
            $password_field = null;
            if ($is_password_needed) {
                $password_field = new \Account_RegisterField($form_pw, $form_pw_error);
            }

            $prefill   = new \Account_RegisterPrefillValuesPresenter(
                new \Account_RegisterField($form_loginname, $form_loginname_error),
                new \Account_RegisterField($form_email, $form_email_error),
                $password_field,
                new \Account_RegisterField($form_realname, $form_realname_error),
                new \Account_RegisterField($form_register_purpose, $form_register_purpose_error),
                new \Account_RegisterField($form_mail_site, $form_mail_site_error),
                new \Account_RegisterField($timezone, $timezone_error)
            );
            $presenter = new \Account_RegisterByUserPresenter($prefill, $extra_plugin_field);
            $template  = 'register-user';
        }
        $renderer = $this->renderer_factory->getRenderer(\ForgeConfig::get('codendi_dir') . '/src/templates/account/');

        return static function () use ($renderer, $template, $presenter): void {
            $renderer->renderToPage($template, $presenter);
        };
    }

    private function getFieldError(string $field_key, ?RegisterFormValidationIssue $form_validation_issue): ?string
    {
        return $form_validation_issue ? $form_validation_issue->getFieldError($field_key) : null;
    }
}
