<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Confirm self registered user.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require(__DIR__ . '/../../config.php');
 require($CFG->dirroot . '/login/lib.php');
 require_once($CFG->libdir . '/authlib.php');
 require($CFG->dirroot . '/auth/apoa/lib.php');
 

$data = optional_param('data', '', PARAM_RAW);  // Formatted as:  secret/username

$redirect = optional_param('redirect', '', PARAM_LOCALURL);    // Where to redirect the browser once the user has been confirmed.

$PAGE->set_url('/auth/apoa/federation_confirm.php');
$PAGE->set_context(context_system::instance());

if (!$authplugin = signup_get_user_confirmation_authplugin()) {
    throw new moodle_exception('confirmationnotenabled');
}

if (!empty($data) || (!empty($p) && !empty($s))) {

    if (!empty($data)) {
        $dataelements = explode('/', $data, 2); // Stop after 1st slash. Rest is username. MDL-7647
        $requestsecret = $dataelements[0];
        $requestid   = $dataelements[1];
    }

    $confirmed = approve_membership_category($requestid, $requestsecret);

    if ($confirmed) {
        $PAGE->navbar->add(get_string("confirmed"));
        $PAGE->set_title(get_string("confirmed"));
        $PAGE->set_heading($COURSE->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
        echo "<p>".get_string("confirmed")."</p>\n";
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        exit;

    }else {
        $PAGE->navbar->add(get_string("cantconfirm"));
        $PAGE->set_title(get_string("cantconfirm"));
        $PAGE->set_heading($COURSE->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
        echo "<p>".get_string("cantconfirm")."</p>\n";
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        exit;
    }
} else {
    throw new \moodle_exception("errorwhenconfirming");
}

redirect("$CFG->wwwroot/");
