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
 * Bulk user upload forms
 *
 * @package    tool
 * @subpackage uploaduser
 * @copyright  2007 Dan Poltawski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/local/subscriptions/lib.php');
/**
 * Upload a file CVS file with user information.
 *
 * @copyright  2007 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_apoa_adduser_form extends moodleform {
    function definition () {
        $mform = $this->_form;

        $mform->addElement('header', 'adduserheader', get_string('adduserheader', 'auth_apoa'));

        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
        $mform->setType('email', core_user::get_property_type('email'));
        $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('email');
                

        profile_signup_fields($mform);

        $mform->addElement('text', 'membershipnumber', get_string('membershipnumber','auth_apoa'), PARAM_TEXT);
        $mform->addRule('membershipnumber', '', 'required');

        $maxapoa = get_max_apoa_number('membershipnumberraw');
        $maxsubscription = get_max_subscriptions_users_number(get_config('auth_apoa', 'subscriptionapoa'));

        $max = max($maxapoa->maxval, $maxsubscription->membership_number);

        $max = $maxsubscription->prefix . $max;

        $mform->addElement('static', 'maxmembershipnumber', get_string('maxmembershipnumber', 'auth_apoa'), $max);
        $mform->addElement('date_time_selector', 'subscriptionends', get_string('subscriptionend', 'auth_apoa'), array('optional' => true));
        $mform->addHelpButton('subscriptionends', 'subscriptionend');

        
        $options =[
            'footandankle' => 'Foot & Ankle',
            'handandupperlimb' => 'Hand & Upper Limb',
            'hip' => 'Hip',
            'infection' => 'Infection',
            'knee' => 'Knee',
            'research' => 'Orthopaedic Research',
            'osteoporosis' => 'Osteoporosis',
            'paediatrics' => 'Paediatrics',
            'spine' => 'Spine',
            'sports' => 'Sports Injury',
            'trauma' => 'Trauma',
            'waves' => 'Waves'
        ];
        $mform->addElement('select', 'subsections', get_string('subsections', 'auth_apoa'), $options, array('multiple' => true));

        $mform->addElement('text', 'spinemembershipnumber', get_string('spinemembershipnumber','auth_apoa'), PARAM_TEXT);

        $maxapoaspine = get_max_apoa_number('apssnumber');
        $maxspinesubscription = get_max_subscriptions_users_number(get_config('auth_apoa', 'subscriptionspine'));

        $max = max($maxapoaspine->maxval, $maxspinesubscription->membership_number);

        $max = $maxspinesubscription->prefix . $max;

        $mform->addElement('static', 'maxmembershipnumber', get_string('maxspinemembershipnumber', 'auth_apoa'), $max);

        $this->add_action_buttons(false, get_string('adduser', 'auth_apoa'));
    }


    function validation($data, $files)
    {
        global $DB;

        $errors = parent::validation($data, $files);

        if($DB->record_exists('auth_apoa', array('email' => $data['email']))){
            $errors['email'] = "User with this email already exists";
        }
        $membershipnumber  = preg_replace("/[^0-9]/", "", $data['membershipnumber']);
        if($DB->record_exists('auth_apoa', array('membershipnumber' => $membershipnumber))){
            $errors['membershipnumber'] = "User with this membership number already exists";
        }
        
        if($DB->record_exists('auth_apoa', array('membershipnumber' => $data['membershipnumber']))){
            $errors['membershipnumber'] = "User with this membership number already exists";
        }
        if(in_array('spine', $data['subsections'])){
            $membershipnumber  = preg_replace("/[^0-9]/", "", $data['apssnumber']);
            if($DB->record_exists('auth_apoa', array('apssnumber' => $membershipnumber))){
                $errors['spinemembershipnumber'] = "User with this APSS membership number already exists";
            }
        }

        return $errors;
    }
}



