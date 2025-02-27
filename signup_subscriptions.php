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
require('signup_subscriptions_form.php');

$skip = optional_param('skip', '', PARAM_INT);

require_login(null, false);

if (empty($skip)) {
    $mform1 = new signup_subscriptions_form1();

    if ($formdata = $mform1->get_data()) {
        process_subscriptions_form($formdata);

    } else {

        $choosemaintitle = get_string('choosemaintitle', 'auth_apoa');
        $choosemaindescription = get_string('choosemaintitle_desc', 'auth_apoa');

        $PAGE->set_title($choosemaintitle);

        $PAGE->set_heading($SITE->fullname);

        echo $OUTPUT->header();

        echo $OUTPUT->heading($choosemaintitle, 2, 'text-primary');

        echo \html_writer::tag('p', $choosemaindescription, array('class' => 'text-primary'));
        //echo $OUTPUT->paragraph($choosemaindescription, 'text-primary');

        $mform1->display();

        echo $OUTPUT->footer();
        die;
    }
} 

$returnurl = new moodle_url('/user/profile.php');
$checkouturl = new moodle_url('/local/shopping_cart/checkout.php');
$mform2 = new signup_subscriptions_form2();

// If a file has been uploaded, then process it.
if ($formdata = $mform2->is_cancelled()) {
    redirect($returnurl);

} else if ($formdata = $mform2->get_data()) {

    process_subscriptions_form($formdata);
    redirect($checkouturl);

}

// Print the header.

$choosesectionstitle = get_string('choosesectionstitle', 'auth_apoa');
$choosesectionsdescription = get_string('choosesectionstitle_desc', 'auth_apoa');

$PAGE->set_title($choosesectionstitle);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

echo $OUTPUT->heading($choosesectionstitle, 2, 'text-primary');
echo \html_writer::tag('p', $choosesectionsdescription, array('class' => 'text-primary'));

$mform2->display();

echo $OUTPUT->footer();
die;
