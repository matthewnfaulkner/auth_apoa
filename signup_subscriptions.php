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

$iid = optional_param('iid', '', PARAM_INT);

require_login(null, false);

if (empty($iid)) {
    $mform1 = new signup_subscriptions_form1();

    if ($formdata = $mform1->get_data()) {
        process_subscriptions_form($formdata);

    } else {
        echo $OUTPUT->header();

        echo $OUTPUT->heading_with_help(get_string('uploadusers', 'tool_uploaduser'), 'uploadusers', 'tool_uploaduser');

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
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('uploaduserspreview', 'tool_uploaduser'));


$mform2->display();

echo $OUTPUT->footer();
die;
