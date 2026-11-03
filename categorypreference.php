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
 * Lets the current user update their membership category preference ("Which best describes you?").
 *
 * @package    auth_apoa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/signup_subscriptions_form.php');

require_login(null, false);
if (isguestuser()) {
    throw new \moodle_exception('noguest');
}

$PAGE->set_context(\context_user::instance($USER->id));
$PAGE->set_url(new moodle_url('/auth/apoa/categorypreference.php'));
$PAGE->set_pagelayout('admin');

$returnurl = new moodle_url('/user/preferences.php');
$title = get_string('categorypreference', 'auth_apoa');

$form = new signup_category_preference_form($PAGE->url, ['submitlabel' => get_string('savechanges')]);
$form->set_data(['category_preference' => get_user_preferences('auth_apoa_category_preference', null)]);

if ($form->is_cancelled()) {
    redirect($returnurl);
}
if ($formdata = $form->get_data()) {
    set_user_preference('auth_apoa_category_preference', $formdata->category_preference);
    redirect($returnurl, get_string('categorypreferencesaved', 'auth_apoa'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->set_title($title);
$PAGE->set_heading(fullname($USER));
$PAGE->navbar->add(get_string('preferences'), $returnurl);
$PAGE->navbar->add($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo html_writer::tag('p', get_string('categorypreference_desc', 'auth_apoa'));
$form->display();
echo $OUTPUT->footer();
