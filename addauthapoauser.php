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
 * Bulk user registration script from a comma separated file
 *
 * @package    tool
 * @subpackage uploaduser
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__.'/adduser_form.php');


admin_externalpage_setup('auth_addauthapoauser');


$mform = new auth_apoa_adduser_form();

echo $OUTPUT->header();

if($formdata = $mform->get_data()){
    if(addauthapoauser($formdata)){
        echo $OUTPUT->heading(get_string('uploadusersresult', 'tool_uploaduser'));
        echo $OUTPUT->notify_success(get_string('useraddedsuccessfully', 'auth_apoa'));
        echo $OUTPUT->single_button("$CFG->wwwroot/auth/apoa/addauthapoauser.php", 'Back');
        echo $OUTPUT->footer();

        die;
    };

}

echo $OUTPUT->heading(get_string('uploaduserspreview', 'tool_uploaduser'));

echo $mform->display();

echo $OUTPUT->footer();
die;
