<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Authentication class for apoa is defined here.
 *
 * @package     auth_apoa
 * @copyright   2022 Matthew<you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/message/lib.php');
require_once($CFG->dirroot. '/user/profile/lib.php');

define('MEMBERSHIP_APPROVED', 2);
define('MEMBERSHIP_DENIED', 0);
define('MEMBERSHIP_AMENDED', 1);

define('FEDERATION_ACTIVE', 0);
define('FEDERATION_LAPSED', 1);
define('FEDERATION_INACTIVE', 2);
use core\event\notification_sent;
use core_course\task\content_notification_task;
use \core_message\api as api;
use core_message\output\preferences\notification_list;
use \moodle_url as moodle_url;
use \core_user as core_user;
use PHP_CodeSniffer\Reports\Notifysend;

function is_federation_pending(){
    global $USER;

    $cache = \cache::make('auth_apoa', 'is_federation_pending_cache');
    
    $cachekey = "u_$USER->id";
    if ($data = $cache->get($cachekey)){
        return $data['federation_pending'];
    }
    else{
        if($profile = profile_user_record($USER->id)){
            $federationpending = $profile->federation_pending == 1 ? True : False;
            if($federationpending){
                $cache->set($cachekey, array('federation_pending' => $federationpending));
            }
            return $federationpending;
        }
        
    }
}

function is_membership_category_approved(){
    global $USER;

    $cache = \cache::make('auth_apoa', 'membership_category_approved_cache');

    $cachekey = "u_$USER->id";
    if ($data = $cache->get($cachekey)){
        return $data;
    }
    else{
        if($profile = profile_user_record($USER->id)){
            $membership_category_approved = $profile->membership_category_approved;
            $membership_category = $profile->membership_category;
            $federation = $profile->federation;
            $membershipfields = array('membership_category_approved' => $membership_category_approved,
                    'membership_category' => $membership_category,
                    'federation' => $federation);
            $cache->set($cachekey, $membershipfields);
            return $membershipfields;
        }
        
    }
}

function country_to_federation($country){
    $mapping = array('australia' => 'Australia',
        'bangladesh' => 'Bangladesh',
        'brunei' => 'Brunei',
        'cambodia' => 'Cambodia',
        'china' => 'China',
        'hongkong' => 'HongKong',
        'india' => 'India',
        'indonesia' => 'Indonesia',
        'japan' => 'Japan',
        'korea' => 'Korea',
        'malaysia' => 'Malaysia',
        'myanmar' => 'Myanmar',
        'nepal' => 'Nepal',
        'oman' => 'Oman',
        'pakistan' => 'Pakistan',
        'philippines' => 'Philippines',
        'saudiarabia' => 'SaudiArabia',
        'singapore' => 'Singapore',
        'srilanka' => 'SriLanka',
        'taiwan' => 'Taiwan',
        'thailand' => 'Thailand',
        'turkey' => 'Turkey',
        'uae' => 'UAE',
        'vietnam' => 'Vietnam');

    return $mapping[$country];
}

function send_welcome_message($to){
    global $CFG;

    $supportuser = core_user::get_support_user();
    $supportlink = new moodle_url($CFG->wwwroot . '/user/contactsitesupport.php');
    $message = get_string('welcomemessage', 'auth_apoa', ['firstname' => $to->firstname, 'supportlink' => $supportlink->out()]);
    api::add_contact($to->id, $supportuser->id);
    $conversation = api::create_conversation(
        api::MESSAGE_CONVERSATION_TYPE_INDIVIDUAL,
        [
            $supportuser->id,
            $to->id
        ]
    );

    api::set_favourite_conversation($conversation->id, $to->id);
    message_post_message($supportuser, $to, $message, FORMAT_HTML);
}


function auth_apoa_user_created($event){
    return;
    $data = $event->get_data();
    $userid = $data['relateduserid'];
    $user = core_user::get_user($userid);
    send_welcome_message($user);
}

function validate_existing_email($email, $path){
    global $DB;

    if($path == 1){
        if($authrecord =  $DB->get_record('auth_apoa', array('email' => $email))){
                if($authrecord->membership_category == 'Federation' || $authrecord->membership_category == 'Federation Fellow'){
                    if(country_to_federation($authrecord->country)){
                        $authrecord->membership_category = 'Federation Fellow';
                    }
                    else{
                        $authrecord->membership_category = 'Affiliate Federation Fellow';
                    }
                }
                if($authrecord->membership_category == 'Paramedical / Affiliate Member'){
                    $authrecord->membership_category = 'Affiliate Member';
                }
                return $authrecord;
            };
    }
    if($path == 2) {
        if($authrecord =  $DB->get_record('auth_apoa_federation_members', array('email' => $email))){
            $authrecord->membership_category = 'Federation Fellow';
            $authrecord->country = $authrecord->federation;
            $authrecord->id = 0;
            return $authrecord;
        };
    }
    return false;
}

function membership_category_class($membership_category){

    $cleancategory = str_replace(' ', '', strtolower($membership_category));

    $classname = "auth_apoa\\membershipcategory\\$cleancategory";

    if(class_exists($classname)){
        return new $classname();
    }else{
        return new \auth_apoa\membershipcategory\noapprovalrequired($membership_category);
    }

    return false;
}


/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 * @return bool
 */
function auth_apoa_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $USER;

    if($USER->id != $user->id){
        return;
    }
    $params = [
        'userid' => $user->id
    ];
    if ($course) {
        $params['course'] = $course->id;
    }
    $url = new moodle_url('/auth/apoa/updatemembershipcategory.php', $params);
    $node = new core_user\output\myprofile\node('contact', 'changemembershipcategory',
        get_string('changemembershipcategory', 'auth_apoa'), null, $url);
    $tree->add_node($node);
}

function update_membership_category($formdata){
    profile_save_data($formdata);
}

function get_active_approval_requests($userid){
    global $DB;

    return $DB->get_records('auth_apoa_membershipchanges', array('userid' => $userid, 'approved' => null), 'timecreated DESC', '*');
}

function get_approval_requests($userid){
    global $DB;

    return $DB->get_records('auth_apoa_membershipchanges', array('userid' => $userid), 'approved DESC, timecreated DESC', '*');
}

function approve_membership_category($requestid, $secret){
    global $DB;

    if($requestrecord = $DB->get_record('auth_apoa_membershipchanges', array('id' => $requestid, 'secret' => $secret, 'approved' => null))){
        $user = \core_user::get_user($requestrecord->userid);
        $user->profile_field_membership_category_approved = 1;
        profile_save_data($user);   
        $requestrecord->approved = 1;
        $DB->update_record('auth_apoa_membershipchanges', $requestrecord);
        auth_apoa_notify_membership_category_approved($user->id);
        return $user->id;
    }
    return 0;
}

/**
 * Send a message from one user to another. Will be delivered according to the message recipients messaging preferences
 *
 * @param object $userfrom the message sender
 * @param object $userto the message recipient
 * @param string $message the message
 * @param int $format message format such as FORMAT_PLAIN or FORMAT_HTML
 * @return int|false the ID of the new message or false
 */
function auth_apoa_notify_membership_category_processed($userto, $resultdata, $result) {
    global $PAGE;

    $eventdata = new \core\message\message();
    $eventdata->courseid         = 1;
    $eventdata->component        = 'auth_apoa';
    $eventdata->name             = 'membership_change_notification';
    $eventdata->userto           = $userto;
    $eventdata->userfrom         = core_user::get_support_user();
    $message = '';

    $resulttostring = (object)$resultdata;
    
    switch($result){
        case MEMBERSHIP_DENIED:
            $eventdata->subject = get_string('membershipcategorydeniedsubject', 'auth_apoa');
            $message = get_string('membershipcategoryapproved', 'auth_apoa', $resulttostring);
            break;
        case MEMBERSHIP_APPROVED:
            $eventdata->subject = get_string('membershipcategoryapprovedsubject', 'auth_apoa');
            $message = get_string('membershipcategoryapproved', 'auth_apoa', $resulttostring);
            break;
        case MEMBERSHIP_AMENDED:
            $eventdata->subject = get_string('membershipcategoryamendedsubject', 'auth_apoa');
            $message = get_string('membershipcategoryamended', 'auth_apoa', $resulttostring);
            break;
        default:
            throw new \moodle_exception('invalidcategoryprocess', 'auth_apoa');
    }

    $format = FORMAT_HTML;
    if ($format == FORMAT_HTML) {
        $eventdata->fullmessagehtml  = $message;
        //some message processors may revert to sending plain text even if html is supplied
        //so we keep both plain and html versions if we're intending to send html
        $eventdata->fullmessage = html_to_text($eventdata->fullmessagehtml);
    } else {
        $eventdata->fullmessage      = $message;
        $eventdata->fullmessagehtml  = '';
    }

    $eventdata->fullmessageformat = $format;
    $eventdata->smallmessage     = $message;//store the message unfiltered. Clean up on output.
    $eventdata->timecreated     = time();
    $eventdata->notification    = 1;
    // User image.e.
    $eventdata->customdata = [
        'actionbuttons' => [
            'send' => get_string_manager()->get_string('send', 'message', null, $eventdata->userto->lang),
        ],
    ];
    return message_send($eventdata);
}





function send_accept_trigger(){


    global $CFG;
    $email = optional_param('email', '', PARAM_EMAIL);
    $method = optional_param('method', 'decline', PARAM_ALPHA);
    $category = optional_param('category', 'all', PARAM_ALPHA);
    $fromsender = optional_param('fromsender', TRUE, PARAM_BOOL);


    if(!$email) {
        return;
    }

    if($method == "accept"){
        $accpetedevent =  auth_apoa\event\auth_apoa_invite_accepted::create(array(
            'context' => \context_system::instance(),
            'other' => array('email' => $email, 'category' => $category, 'fromsender' => $fromsender)
        ));

        $accpetedevent->trigger();
    }

    if($fromsender){
        return;
    }

    $apiUrl = 'https://script.google.com/macros/s/AKfycbwC_N2685Gq3tSAczXNfl-CavOajaLiDZPHJmaj9dMAVVZemlNY44KbejSP6ybZZsVUGw/exec?token=AKfycbwjvoPMS7phLJxkqWs5E7IWIMa71nL3Mv1g3F7tb_v58SqPRsJLxVVWdtt8yfpXAz0UZw&method=' . $method;

    $token = "AKfycbwjvoPMS7phLJxkqWs5E7IWIMa71nL3Mv1g3F7tb_v58SqPRsJLxVVWdtt8yfpXAz0UZw";    

    $data = array('method' => $method, 'email' => $email, 'token' => $token);

    $json = json_encode($data);

    $apiHeaders = [
        'Accept: application/json',
    ];

        $options = [
            'http' => [
                'header' => implode("\r\n", $apiHeaders),
                'method' => 'POST',
                'content' => $json
            ],
        ];

        $context = stream_context_create($options);

        $response = file_get_contents($apiUrl, false, $context);

        if ($response === false) {
            // Handle error, e.g., connection error or invalid response
            return false;
        } else {
            // Process the API response
            $response = json_decode($response, true);
            return $response['data'];
        }
    
}

function process_subscriptions_form($formdata) {

    global $USER;

    foreach($formdata as $key => $value) {
        if(is_numeric($key) && $value){
            local_subscriptions_add_subscription_to_cart($value, $USER->id);
        }
        
    }
}


function auth_apoa_post_forgot_password_requests($data){
    global $DB;

    if(!$email = $data->email){
        return;
    }

    $sql = 'SELECT a.id
            FROM {auth_apoa} a LEFT JOIN {user} u ON a.email = u.email
            WHERE a.email = :email AND u.email IS NULL';
            
    $params = array(
        'email' => $email
    );

    if($DB->record_exists_sql($sql, $params)) {
        if($DB->record_exists('auth_apoa', array('email' => $email))){
            $userauth = get_auth_plugin('apoa');
            if (!$userauth->can_reset_password() or !is_enabled_auth('apoa')){
                throw new \moodle_exception('cannotmailconfirm');
            }
            redirect(new moodle_url('/login/signup.php', array('path' => 1, 'email'=> $email)), get_string('forgottenpasswordemailexists', 'auth_apoa'));
        }
    }
}

function auth_apoa_update_federation_statuses($setting) {
    global $DB; 
    
    $federation = str_replace('s_auth_apoa_federationstatus', '', $setting);
    $status = get_config('auth_apoa', 'federationstatus' . $federation);
    $federation = country_to_federation($federation);

    if(!$federation) {
        return;
    }

    $lastruleorder = $DB->get_record_sql("SELECT MAX(sortorder) as total FROM {local_profilecohort}");
    $totalrules = $lastruleorder->total;

    $federationfield = profile_get_custom_field_data_by_shortname('federation');
    $categoryfield =   profile_get_custom_field_data_by_shortname('membership_category');
    $approvedfield =   profile_get_custom_field_data_by_shortname('membership_category_approved');

    $matchvalue = $DB->sql_compare_text('matchvalue');
    $matchvalueplaceholder = $DB->sql_compare_text(':matchvalue');

    $rulessql = "SELECT * FROM {local_profilecohort} WHERE {$matchvalue} = {$matchvalueplaceholder} AND 
            fieldid = :fieldid";
    $rulesparams = [
        'matchvalue' => $federation,
        'fieldid' => $federationfield->id
    ];

    if($rule = $DB->get_record_sql($rulessql, $rulesparams)){
        if($status == FEDERATION_INACTIVE) {
            $getnextrules = "SELECT * FROM {local_profilecohort} lcp
                            WHERE (lcp.sortorder = :sortone AND lcp.fieldid = :categoryid AND lcp.andnextrule =1) OR
                            (lcp.sortorder = :sorttwo AND lcp.fieldid = :approvedid AND lcp.andnextrule = 0)
                            ORDER BY lcp.sortorder";
            $params = [
                'sortone' => $rule->sortorder+1,
                'categoryid' => $categoryfield->id,
                'sorttwo' => $rule->sortorder+2,
                'approvedid' => $approvedfield->id,
            ];
            if($nextrules = $DB->get_records_sql($getnextrules, $params)){
                if(count($nextrules) == 2 && $status = FEDERATION_INACTIVE){
                    $ids = array_merge([$rule->id], array_keys($nextrules));
                    $DB->delete_records_list('local_profilecohort', 'id', $ids);
                }
            }
        }
    }else{
        if($status == FEDERATION_ACTIVE || $status == FEDERATION_LAPSED){
                
            $ruleone = new stdClass();
            $ruleone->fieldid = $federationfield->id;
            $ruleone->matchtype = NULL;
            $ruleone->matchvalue = $federation;
            $ruleone->value = 22;
            $ruleone->sortorder = $totalrules+1;    
            $ruleone->andnextrule = 1;

            $ruletwo = new stdClass();
            $ruletwo->fieldid = $categoryfield->id;
            $ruletwo->matchtype = NULL;
            $ruletwo->matchvalue = "Federation Fellow";
            $ruletwo->value = 22;
            $ruletwo->sortorder = $totalrules+2;
            $ruletwo->andnextrule = 1;

            $rulethree = new stdClass();
            $rulethree->fieldid = $approvedfield->id;
            $rulethree->matchtype = NULL;
            $rulethree->matchvalue = 1;
            $rulethree->value = 22;
            $rulethree->sortorder = $totalrules+3;
            $rulethree->andnextrule = 0;
            $DB->insert_records('local_profilecohort', [$ruleone, $ruletwo, $rulethree]);
        }
    }

}

function auth_apoa_clear_apoa_notification_preferences($cutoff) {
    global $DB;

    $select = 'name = ? AND value < ?';

    foreach(auth_apoa_user_preferences() as $preference => $config){
        $DB->delete_records_select("user_preferences", $select, [$preference, $cutoff]);
    }
}

function auth_apoa_display_notification_before_main(){
    $preference = 'auth_apoa_user_notapproved';
    $membershipfields = is_membership_category_approved();
    
    if($membershipfields['membership_category'] == "Federation Fellow"){
        $federation = strtolower(preg_replace('/[^A-Za-z]/', '', $membershipfields['federation']));
        if($status = get_config('auth_apoa', "federationstatus$federation")){
            $notification = format_text(get_config('auth_apoa', "federationnotification$federation"));
            switch ($status) {
                case FEDERATION_ACTIVE:
                    if($membershipfields['membership_category_approved']){
                        $message = get_string('federationpending', 'auth_apoa');
                        return [$message, $preference];
                    }
                    break;
                case FEDERATION_LAPSED:
                    if($membershipfields['membership_category_approved']){
                        $message = get_string('federationlapsed', 'auth_apoa', $notification);
                        return [$message, $preference];
                    }
                    break;
                case FEDERATION_INACTIVE:
                    $message = get_string('federationexpired', 'auth_apoa', $notification);
                    return [$message, $preference];
                default:
                    break;
            }
            
        }
    }

    if(!$membershipfields['membership_category_approved']){
        if($membershipfields['membership_category'] == "No Membership"){
            $message = get_string('nomembershippending', 'auth_apoa');
        }
        else{
            $message = get_string('membershipcategoryapprovalpending', 'auth_apoa', $membershipfields['membership_category']);
        }
    return [$message, $preference];
    }
    else{

    }
}

function auth_apoa_user_preferences(){
    return ['auth_apoa_user_notapproved'=> [
                'type' =>   PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => 0,
                'permissioncallback' => [core_user::class, 'is_current_user'],
        ]];
}