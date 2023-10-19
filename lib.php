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

use \core_message\api as api;
use \moodle_url as moodle_url;
use \core_user as core_user;

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
            $membershipfields = array('membership_category_approved' => $membership_category_approved,
                    'membership_category' => $membership_category);
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
        'hongkong' => 'Hong Kong',
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
        'saudiarabia' => 'Saudi Arabia',
        'singapore' => 'Singapore',
        'srilanka' => 'Sri Lanka',
        'taiwan' => 'Taiwan',
        'thailand' => 'Thailand',
        'turkey' => 'Turkey',
        'unitedarabemirates' => 'United Arab Emirates',
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

function validate_existing_email($email){
    global $DB;
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
    return false;
}



