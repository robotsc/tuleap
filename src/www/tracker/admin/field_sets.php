<?php
// Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
//
// $$
//
//
//  Written for Codendi by Marc Nazarian

if (! user_isloggedin()) {
    exit_not_logged_in();
    return;
}

if (! $ath->userIsAdmin()) {
    exit_permission_denied();
    return;
}

// Check if this tracker is valid (not deleted)
if (! $ath->isValid()) {
    exit_error($Language->getText('global', 'error'), $Language->getText('tracker_add', 'invalid'));
}

$ath->adminHeader([
    'title' => $Language->getText('tracker_admin_fieldset', 'tracker_admin') . $Language->getText('tracker_admin_fieldset', 'fieldset_admin'),
]);

$hp = Codendi_HTMLPurifier::instance();
echo '<H2>' . $Language->getText('tracker_import_admin', 'tracker') . ' \'<a href="/tracker/admin/?group_id=' . (int) $group_id . '&atid=' . (int) $atid . '">' . $hp->purify(SimpleSanitizer::unsanitize($ath->getName()), CODENDI_PURIFIER_CONVERT_HTML) . '</a>\' ' . $Language->getText('tracker_admin_fieldset', 'fieldset_admin') . '</H2>';
$ath->displayFieldSetList();
$ath->displayFieldSetCreateForm();

$ath->footer([]);
