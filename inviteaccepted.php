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


$which = optional_param('reason', "inviteaccepted", PARAM_TEXT);

$PAGE->set_url('/auth/apoa/invitedeclined.php');
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter text-primary');
switch ($which) {
    case "inviteaccepted":
        echo "<p>".get_string("inviteaccepted",'auth_apoa')."</p>\n";
        break;
    case "inviteacceptedspecial":
        echo "<p>".get_string("inviteacceptedspecial",'auth_apoa')."</p>\n";
        break;
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
