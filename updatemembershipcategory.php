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
require_once(__DIR__.'/updatemembershipcategory_form.php');
require_once(__DIR__.'/amendmembershipcategory_form.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

$delete = optional_param('delete', 0, PARAM_INT);
$requestid = optional_param('requestid', 0, PARAM_INT);
$confirm      = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
$amend = optional_param('amend', 0, PARAM_INT); 


if(isloggedin() && !isguestuser()){
    $user = $USER;
}else{
    throw new \moodle_exception('requirelogin');
}

$context = \context_system::instance();

$return = new moodle_url('/auth/apoa/updatemembershipcategory.php');

$redirect = new moodle_url('/user/profile.php');

echo $OUTPUT->header();

if($amend and confirm_sesskey()){
    $requestrecord = $DB->get_record('auth_apoa_membershipchanges', array('id' => $amend), '*', MUST_EXIST);

    $optionsyes = array('amend'=>$amend, 'sesskey'=>sesskey());
    $amendurl = new moodle_url('/auth/apoa/updatemembershipcategory.php', $optionsyes);

    $amendform = new auth_apoa_amendmembershipcategory_form($amendurl, array(
                                                                        'requestid' => $amend,
                                                                        'membership_category' => $requestrecord->newcategory,
                                                                         ));

    if($amendform->is_cancelled()){
        redirect($return);
    }
    if($amenddata = $amendform->get_data()){
        $DB->update_record('auth_apoa_membershipchanges', $amenddata);
        echo $OUTPUT->notification(get_string('amendmentsubmitted', 'auth_apoa'), 'success');
    }else{
        $amendform->display();
        $OUTPUT->footer();
        die;
    }
}
if ($delete and confirm_sesskey() and $requestid) {              // Delete a selected user, after confirmation


    if ($confirm != md5($delete)) {
        $fullname = fullname($user, true);
        echo $OUTPUT->heading(get_string('deleteapprovalrequest', 'auth_apoa'));

        $optionsyes = array('delete'=>$delete, 'confirm'=>md5($delete), 'sesskey'=>sesskey(), 'requestid' => $requestid);
        $deleteurl = new moodle_url('/auth/apoa/updatemembershipcategory.php', $optionsyes);
        $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

        echo $OUTPUT->confirm(get_string('deleteapprovalrequestconfirm', 'auth_apoa'), $deletebutton, $deleteurl);
        echo $OUTPUT->footer();
        die;
    } else if (data_submitted()) {
        if($requestrecord = $DB->get_record('auth_apoa_membershipchanges', array('id' => $requestid))){
            $usercontext = \context_user::instance($user->id);
            if($user->id != $requestrecord->userid){
                require_capability('auth/apoa:cancelrequest', $usercontext);
            }
            $requestuser = core_user::get_user($requestrecord->userid);
            profile_load_data($requestuser);
            if($requestrecord->previouscategory !== null){
                $requestuser->profile_field_membership_category = $requestrecord->previouscategory;
            }
            $requestuser->profile_field_membership_category_approved = $requestrecord->previouslyapproved;
            profile_save_data($requestuser);
            $DB->delete_records('auth_apoa_membershipchanges', array('id' => $requestid));
        }
        } else {
            echo $OUTPUT->notification(get_string('approvalnotdeleted', 'auth_apoa'), 'error');
        }
    }


$profilefields = profile_user_record($user->id);
$mform = new auth_apoa_updatemembershipcategory_form(null, array('userid' => $user->id,
                                                                'membership_category' => $profilefields->membership_category,
                                                                'membership_category_approved' => $profilefields->membership_category_approved,
                                                                ));

$activerequests = get_active_approval_requests($USER->id);


if($mform->is_cancelled()){
    redirect($redirect);
}
if($formdata = $mform->get_data()){
    if(empty($activerequests)){
        if($categoryclass = membership_category_class($formdata->profile_field_membership_category)){
            $formdata->previouscategory = $profilefields->membership_category;
            $formdata->previouslyapproved = $profilefields->membership_category_approved;
            $formdata->profile_field_membership_category_approved = $categoryclass->add_approval_request($formdata);
            
            profile_save_data($formdata);
            $cache = \cache::make('auth_apoa', 'membership_category_approved_cache');
            $cachekey = "u_$user->id";
            $cache->delete($cachekey);
            \core\event\user_updated::create_from_userid($user->id)->trigger();
        }
    }
}

echo $OUTPUT->heading(get_string('changemembershipcategory', 'auth_apoa'));

echo html_writer::tag('p', format_text(get_string('changemembershipdescription', 'auth_apoa'), FORMAT_HTML, array('filter' => true, 'context' => $context)));

$requests = get_approval_requests($USER->id);

$table = new html_table();
$table->head = array ();
$table->colclasses = array();
$table->attributes['class'] = 'admintable generaltable table-sm';
$table->head[] = get_string('timecreated');
$table->head[] = get_string('membershipcategory', 'auth_apoa');
$table->head[] = get_string('membershipchangeapproved', 'auth_apoa');
$table->head[] = get_string('supportingdata', 'auth_apoa');
$table->head[] = get_string('membershipchangecomments', 'auth_apoa');
$table->head[] = get_string('options', 'auth_apoa');
$table->colclasses[] = 'centeralign';

$table->id = "membershipchanges";


$row = array();
$unapproved = false;
$daysremaining = 0;
if($requests){
    $mostrecentrequest = reset($requests);

    $timebetweenchanges = get_config('auth_apoa', 'membershipcategoryrefresh') * 1000;

    $timehaspassed = time() - $mostrecentrequest->timecreated > $timebetweenchanges;

    if(!$timehaspassed){
        $unapproved = true;
        $daysremaining =  floor(($mostrecentrequest->timecreated + $timebetweenchanges - time()) / 86400000);
    }

    foreach($requests as $request){
        
        $requestclass = membership_category_class($request->newcategory);

        $row = array();
        $row[] = date('H:i:s d/m/Y', $request->timecreated) ;

        $row[] = $request->newcategory;
        if($request->approved === null){
            $row[] = 'Pending';
        }else{
            $row[] = $request->approved ? 'Approved' : 'Denied';
        }
        $row[] = $request->extradata;

        $row[] = $request->amendmentcomments;

        $buttons = array();

        if($request->approved === null){

            // prevent editing of admins by non-admins
            $url = new moodle_url('/auth/apoa/updatemembershipcategory.php', array('delete'=>1, 'requestid' => $request->id, 'sesskey'=> sesskey()));
            $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', get_string('cancelapprovalrequest', 'auth_apoa')));
            

            if($request->amendmentneeded){
                $url = new moodle_url('/auth/apoa/updatemembershipcategory.php', array('amend'=>$request->id, 'sesskey'=> sesskey()));
                $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/more', get_string('amendapprovalrequest', 'auth_apoa')));
            }

            $extrabuttons = $requestclass->extra_options($context);

            $buttons = array_merge($buttons, $extrabuttons);
        }
        $row[] = implode(' ', $buttons);
        $table->data[] = $row;

    }
}else{
    $table->data[] = [get_string('nomembershipchanges', 'auth_apoa')];
}

if(!$unapproved || is_siteadmin()){
    echo $mform->display();
}else{
    echo $OUTPUT->notification(get_string('canonlychangeeverymonth', 
        'auth_apoa', 
        array('timebetweenchanges' => floor($timebetweenchanges/86400000), 'daysremaining' => $daysremaining)), 
        'error');
}

if (!empty($table)) {
    echo html_writer::start_tag('div', array('class'=>'no-overflow'));
    echo $OUTPUT->heading(get_string('activemembershipchanges', 'auth_apoa'));
    echo html_writer::table($table);
    echo html_writer::end_tag('div');
}

echo $OUTPUT->footer();
die;
