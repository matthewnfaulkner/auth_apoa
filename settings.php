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
 * Admin settings and defaults.
 *
 * @package auth_apoa
 * @copyright  2017 Stephen Bourget
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot .'/local/subscriptions/lib.php');

if ($ADMIN->fulltree) {

    $ADMIN->add('user', new admin_settingpage('tool_apoausers', get_string('newusers', 'tool_apoausers'), "$CFG->wwwroot/$CFG->admin/tool/apoausers/users.php", 'tool/apoausers:view'));

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_apoa/pluginname', '',
        new lang_string('auth_apoadescription', 'auth_apoa')));

    $options = array(
        new lang_string('no'),
        new lang_string('yes'),
    );

    $settings->add(new admin_setting_configcheckbox('auth_apoa/noemailmode', new lang_string('noemailmode', 'auth_apoa'),
        new lang_string('noemailmode_desc', 'auth_apoa'), 0));

    $settings->add(new admin_setting_configduration('auth_apoa/membershipcategoryrefresh', new lang_string('membershipcategoryrefresh', 'auth_apoa'),
        new lang_string('membershipcategoryrefresh_desc', 'auth_apoa'), 0));

    $authplugin = get_auth_plugin('apoa');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
            get_string('auth_fieldlocks_help', 'auth'), false, false);

    $settings->add(new admin_setting_heading('auth_apoa/subscriptionmapping',  new lang_string('subscriptionmapping', 'auth_apoa'),
            new lang_string('subscriptionmapping_desc', 'auth_apoa')));
    
    $columns = $authplugin->get_subscription_headers();
    $subscriptions = get_subscription_courses();
    $options = [];
    foreach($subscriptions as $subscription){
        $options[$subscription->id] = $subscription->shortname; 
    }
    $context = context_system::instance();
    $settings->add(new admin_setting_configselect('auth_apoa/subscriptionapoa', 'APOA', "", "", $options));
    foreach($columns as $column){

        $settings->add(new admin_setting_configselect('auth_apoa/subscription'. $column, $column, "", "", $options));

    }

    $federationfield = $DB->get_record('user_info_field', array('shortname' => 'federation'));
    $federations = explode("\n", $federationfield->param1);
    foreach($federations as $federation){
        if(!$federation) {
            continue;
        }
        $formattedsetting = strtolower(preg_replace('/[^A-Za-z]/', '', $federation));

        $settings->add(new admin_setting_heading('auth_apoa/federationdiv' . $formattedsetting, $federation, $federation));

        $settings->add(new admin_setting_configtext('auth_apoa/federationemail' . $formattedsetting,
            $federation . " Email Contact",
            new lang_string('federationemail_desc', 'auth_apoa'),
            '',
            PARAM_EMAIL));

        $statuses = [FEDERATION_ACTIVE => 'Active', 
                     FEDERATION_LAPSED => 'Lapsed', 
                     FEDERATION_INACTIVE => 'Inactive'];

        $setting =  new admin_setting_configselect('auth_apoa/federationstatus' . $formattedsetting, 
                $federation . ' Status',
                new lang_string('federationstatus_desc', 'auth_apoa'),
                2,
                $statuses
        );
        $setting->set_updatedcallback('auth_apoa_update_federation_statuses');          
        $settings->add($setting);

        $settings->add(new admin_setting_confightmleditor('auth_apoa/federationnotification' . $formattedsetting,
            $federation . " Notification",
            new lang_string('federationnotification_desc', 'auth_apoa'),
            '',
            PARAM_RAW));
        
    }

    $settings->add(new admin_setting_heading('auth_apoa/membershipcategoryapprovals',  new lang_string('membershipcategoryapprovalsheader', 'auth_apoa'),
        new lang_string('membershipcategoryapprovals', 'auth_apoa')));
    

    $membershipcategoryfield = $DB->get_record('user_info_field', array('shortname' => 'membership_category'));
    $membershipcategories = explode("\n", $membershipcategoryfield->param1);
    
    foreach($membershipcategories as $category){
        $formattedsetting = strtolower(preg_replace('/[^A-Za-z]/', '', $category));
        $settings->add(new admin_setting_configcheckbox('auth_apoa/membershipcategoryapproval' . $formattedsetting,
            $category,
            '',
            1));
    }
}
