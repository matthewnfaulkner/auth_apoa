
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
 * User sign-up form.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot.'/login/lib.php');
require_once($CFG->dirroot.'/login/signup_form.php');
require_once($CFG->dirroot.'/local/subscriptions/lib.php');
require_once($CFG->dirroot.'/auth/apoa/lib.php');

use \core_user as core_user;
use enrol_plugin;

class signup_subscriptions_form1 extends \moodleform {



    function definition() {
        global $DB, $USER;

        $mform = $this->_form;

        // Read the profile directly, the membership_category_approved_cache can be stale.
        $profile = profile_user_record($USER->id, false);
        if(auth_apoa_can_choose_category_preference($profile->membership_category ?? '',
                $profile->membership_category_approved ?? 0)) {
            $mform->addElement('header', 'header_category_preference', get_string('categorypreference', 'auth_apoa'));
            $mform->setExpanded('header_category_preference', true);

            $categoryarray = array();
            foreach(PREFERABLE_MEMBERSHIP_CATEGORIES as $key => $category) {
                $categoryarray[] = $mform->createElement('radio',
                            'category_preference',
                            '',
                            get_string('categorypreferenceoptionlabel', 'auth_apoa',
                                get_string('categorypreference_' . $key, 'auth_apoa')),
                            $category,
                            array('style' => 'width: 20px; height:20px'));
            }
            $mform->addGroup($categoryarray, 'radioarray_category_preference',
                get_string('categorypreference', 'auth_apoa'), array('<br>'), false);
            $mform->hideIf('radioarray_category_preference', 'alternative_membership', 'checked');
        }

        $mform->addElement('header', 'header_subscription', get_string('subscriptionheader', 'auth_apoa'));
        $mform->setExpanded('header_subscription', true);

        $mainsubscriptionid = local_subscriptions_get_main_subscription();
        $enrolmentoptions = enrol_get_instances($mainsubscriptionid, true);

        $radioarray = array();
        foreach($enrolmentoptions as $option) {
            $plugin = enrol_get_plugin($option->enrol);

            if($plugin->show_enrolme_link($option)) {
                $radioarray[] = $mform->createElement('radio', 
                            'chosen_subscription', 
                            '', 
                            get_string('subscriptionoptionlabel', 'auth_apoa', $option), 
                            $option->id,
                            array('style' => 'width: 20px; height:20px'));
            }

        }

        $mform->addGroup($radioarray, 'radioarray_chosen_subscription' , '', array(' '), false);


        $mform->addElement('header', 'header_alternative', get_string('alternative_membership_options', 'auth_apoa'));
        $mform->setExpanded('header_alternative', false);

        $mform->addElement('static', 'desc_alternative', get_string('alternative_membership_options_desc', 'auth_apoa'));
        $mform->disabledIf('chosen_subscription', 'alternative_membership', 'checked');

        $federationfield = $DB->get_record('user_info_field', array('shortname' => 'federation'));
        $federations = explode("\n", $federationfield->param1);
        $federationOptions = [0 => 'Select Federation'];
        foreach($federations as $federation){
            if(!$federation) {
                continue;
            }
            $formattedsetting = strtolower(preg_replace('/[^A-Za-z]/', '', $federation));

            if(!get_config('auth_apoa', 'federationstatus' . $formattedsetting)) {
                $federationOptions[$federation] = $federation;
            }
        }

        $assocationfield = $DB->get_record('user_info_field', array('shortname' => 'association'));
        $associations = explode("\n", $assocationfield->param1);
        $associationOptions = [0 => 'Select National Orthopaedic Association'];
        foreach($associations as $association){
            if(!$association || $association == 'None') {
                continue;
            }
            $associationOptions[$association] = $association;
            
        }

        $mform->addElement('checkbox', 'alternative_membership', get_string('alternative_membership_options_enable', 'auth_apoa'));
        

        $federationElements = [];
        $federationElements[]= $mform->createElement(
            'select', 
            'alternative_membership_option', 
            get_string('alternative_membership_options_enable', 'auth_apoa'), 
            [0 => 'Select Membership Type', 'federation' =>'Federation Member', 'affiliatefederation' => 'Affiliate Federation Member']
        );
        $federationElements[] = $mform->createElement(
            'select', 
            'alternative_membership_federation',
             get_string('alternative_membership_options_desc', 'auth_apoa'), 
             $federationOptions);
        $federationElements[] = $mform->createElement(
            'select', 'alternative_membership_associate', 
            get_string('alternative_membership_options_desc', 'auth_apoa'), 
            $associationOptions);
        $mform->addGroup($federationElements, 'alternative_membership_elements' , '', array(' '), false);

        $mform->hideIf('alternative_membership_elements', 'alternative_membership');

        $mform->hideIf('alternative_membership_federation', 'alternative_membership_option', 'neq', 'federation');
        
        $mform->hideIf('alternative_membership_associate', 'alternative_membership_option', 'neq', 'affiliatefederation');

        $this->set_display_vertical();

        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', 'Continue');
        $buttonarray[] = $mform->createElement('submit', 'skipbutton', 'Skip', null, null, ['customclassoverride' => 'btn-light']);
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

    }

    public function validation($data, $files)
    {       
        if($data['skipbutton']){
            return;
        }
        $errors = parent::validation($data, $files);

        if($data['alternative_membership']) {
            if($data['alternative_membership_option'] == 'federation'){
                if(!$data['alternative_membership_federation']) {
                    $errors['alternative_membership_elements'] = get_string('federationnotselected', 'auth_apoa');
                }
            }
            else if ($data['alternative_membership_option'] == 'affiliatefederation') {
                if(!$data['alternative_membership_associate']) {
                    $errors['alternative_membership_elements'] = get_string('associationnotselected', 'auth_apoa');
                }
            }
            else{
                $errors['alternative_membership_elements'] = get_string('noaltmembershipselected', 'auth_apoa');
            }
        }
        else {
            if(!$data['chosen_subscription']) {
                $errors['radioarray_chosen_subscription'] = get_string('nosubscriptionselected', 'auth_apoa');
            }
            if($this->_form->elementExists('radioarray_category_preference') && empty($data['category_preference'])) {
                $errors['radioarray_category_preference'] = get_string('nocategorypreferenceselected', 'auth_apoa');
            }
        }
        return $errors;
    }

}


class signup_subscriptions_form2 extends \moodleform {



    function definition() {
        $mform = $this->_form;

        $enrolledin = array_flip(explode(',', $this->_customdata['enrolledin']));


        $mainsubscriptionid = local_subscriptions_get_main_subscription();

        if($subscriptions = get_subscriptions()) {
            if(isset($subscriptions[$mainsubscriptionid])) {
                unset($subscriptions[$mainsubscriptionid]);
            }
            foreach($subscriptions as $subscription) {

                $mform->addElement('header', 'header_' . $subscription->id, $subscription->fullname);
                $mform->setExpanded('header_' . $subscription->id);

                $mform->addElement('static', 'desc_' . $subscription->id, format_text($subscription->summary, $subscription->summaryformat));
                
                if(array_key_exists($subscription->id, $enrolledin)) {
                    $mform->addElement('static', 'alreadysubbed_' . $subscription->id, get_string('alreadysubbed', 'auth_apoa'));
                }
                else{
                    $enrolmentoptions = enrol_get_instances($subscription->id, true);
                    
                    $radioarray=array();
                    $radioarray[] = $mform->createElement('radio', $subscription->id, '', get_string('dontjoinsection', 'auth_apoa'), 0);
                    foreach($enrolmentoptions as $option) {
                        
                        $plugin = enrol_get_plugin($option->enrol);
                        
                        if($plugin->show_enrolme_link($option)) {
                            $radioarray[] = $mform->createElement('radio', $subscription->id, '', get_string('subscriptionoptionlabel', 'auth_apoa', $option), $option->id);
                        }
                    }
                    $mform->addGroup($radioarray, 'radioarray_' . $subscription->id , '', array(' '), false);
                }
            }
        };

        $mform->addElement('hidden', 'skip');
        $mform->setType('skip', PARAM_INT);
        $mform->setDefault('skip', 1);

        $this->set_display_vertical();
        $this->add_action_buttons(true, get_string('continue'));

    }



}
