
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
namespace auth_apoa;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot.'/login/lib.php');
require_once($CFG->dirroot.'/login/signup_form.php');

use \core_user as core_user;

class signupexisting_form extends \login_signup_form {



    function definition() {
        global $USER, $CFG;
        $mform = $this->_form;

        $mform->addElement('text', 'username', get_string('username'), 'maxlength="100" size="12" autocapitalize="none"');
        $mform->setType('username', PARAM_RAW);
        $mform->addRule('username', get_string('missingusername'), 'required', null, 'client');

        if (!empty($CFG->passwordpolicy)){
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }
        $mform->addElement('password', 'password', get_string('password'), [
            'maxlength' => 32,
            'size' => 12,
            'autocomplete' => 'new-password'
        ]);
        $mform->setType('password', core_user::get_property_type('password'));
        $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');
        
        $mform->addElement('password', 'password2', get_string('passwordagain'), [
            'maxlength' => 32,
            'size' => 12,
            'autocomplete' => 'new-password'
        ]);

        $mform->setType('password2', core_user::get_property_type('password'));
        $mform->addRule('password2', get_string('missingpassword'), 'required', null, 'client');
    
        $mform->addElement('text', 'email', get_string('email'), array("value" => $this->_customdata['email']), 'maxlength="100" size="25"');
        $mform->setType('email', core_user::get_property_type('email'));
        $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('email');

        $mform->addElement('text', 'email2', get_string('emailagain'), array("value" => $this->_customdata['email']), 'maxlength="100" size="25"');
        $mform->setType('email2', core_user::get_property_type('email'));
        $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('email2');

        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), array("value" => $this->_customdata[$field]),  'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
        }

        $mform->addElement('text', 'city', get_string('city'), array("value" => $this->_customdata['city']), 'maxlength="120" size="20"');
        $mform->setType('city', core_user::get_property_type('city'));
        if (!empty($CFG->defaultcity)) {
            $mform->setDefault('city', $CFG->defaultcity);
        }

        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country = array_merge($default_country, $country);
        $mform->addElement('select', 'country', get_string('country'), $country);

        if( !empty($CFG->country) ){
            $mform->setDefault('country', $CFG->country);
        }else{
            $mform->setDefault('country', $this->_customdata['country']);
        }

        profile_signup_fields($mform);

        if (signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }

        // Hook for plugins to extend form definition.
        core_login_extend_signup_form($mform);

        // Add "Agree to sitepolicy" controls. By default it is a link to the policy text and a checkbox but
        // it can be implemented differently in custom sitepolicy handlers.
        $manager = new \core_privacy\local\sitepolicy\manager();
        $manager->signup_form($mform);

        // buttons
        //
        if($this->_customdata['fromauth']){
            $this->set_display_vertical();
        }

        $this->add_action_buttons(true, get_string('createaccount'));

    }

    function definition_after_data(){
        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
    }


    public function existing_extra_form_elements($data){
        global $CFG;
        $mform = $this->_form;
        $mform->addElement('hidden', 'username', $data['username']);
        $mform->setType('username', PARAM_RAW);
        $mform->addRule('username', get_string('missingusername'), 'required', null, 'client');


        $mform->addElement('hidden', 'email2', $data['email']);
        $mform->setType('email2', core_user::get_property_type('email'));
        $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');

        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('hidden', $field, $data[$field]);
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
        }

        $mform->addElement('hidden', 'city', $data['city']);
        $mform->setType('city', core_user::get_property_type('city'));
        if (!empty($CFG->defaultcity)) {
            $mform->setDefault('city', $CFG->defaultcity);
        }

        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country = array_merge($default_country, $country);
        $mform->addElement('hidden', 'country', $data['country']);

        if( !empty($CFG->country) ){
            $mform->setDefault('country', $CFG->country);
        }else{
            $mform->setDefault('country', '');
        }

        if ($fields = profile_get_signup_fields()) {
            foreach ($fields as $field) {
                // Check if we change the categories.
                if (!isset($currentcat) || $currentcat != $field->categoryid) {
                     $currentcat = $field->categoryid;
                     $mform->addElement('header', 'category_'.$field->categoryid, format_string($field->categoryname));
                };
                $mform->addElement('hidden', $field->object->inputname, $data[$field->object->inputname]);
            }
        }


    }

    /**
     * Validate user supplied data on the signup form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {

        // Extend validation for any form extensions from plugins.
        $errors = core_login_validate_extend_signup_form($data);

        
        if ($data['password2'] != $data['password']) {
            $errors['password2'] = get_string('passwordsdontmatch');
        }

        $errors += $this->signup_existing_validate_data($data, $files);
        
        
        if (signup_captcha_enabled()) {
            $recaptchaelement = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptchaelement->verify($response)) {
                    $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }

        return $errors;
    }

    public function validate_existing_email(){
        return null;
        $user = array('username' => 'abbie123',
            'firstname' => 'Abbie',
            'lastname' => 'Jones');
        return $user;
    }

        /**
     * Validates the standard sign-up data (except recaptcha that is validated by the form element).
     *
     * @param  array $data  the sign-up data
     * @param  array $files files among the data
     * @return array list of errors, being the key the data element name and the value the error itself
     * @since Moodle 3.2
     */
    public function signup_existing_validate_data($data, $files) {
        global $CFG, $DB;

        $errors = array();
        $authplugin = get_auth_plugin($CFG->registerauth);

        if ($DB->record_exists('user', array('username' => $data['username'], 'mnethostid' => $CFG->mnet_localhost_id))) {
            $errors['username'] = get_string('usernameexists');
        } else {
            // Check allowed characters.
            if ($data['username'] !== \core_text::strtolower($data['username'])) {
                $errors['username'] = get_string('usernamelowercase');
            } else {
                if ($data['username'] !== \core_user::clean_field($data['username'], 'username')) {
                    $errors['username'] = get_string('invalidusername');
                }

            }
        }

        // Check if user exists in external db.
        // TODO: maybe we should check all enabled plugins instead.
        if ($authplugin->user_exists($data['username'])) {
            $errors['username'] = get_string('usernameexists');
        }

        if (! validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');
        }
        if (empty($data['email2'])) {
            $errors['email2'] = get_string('missingemail');

        } else if (\core_text::strtolower($data['email2']) != \core_text::strtolower($data['email'])) {
            $errors['email2'] = get_string('invalidemail');
        }
        if (!isset($errors['email'])) {
            if ($err = email_is_not_allowed($data['email'])) {
                $errors['email'] = $err;
            }
        }

        // Construct fake user object to check password policy against required information.
        $tempuser = new \stdClass();
        $tempuser->id = 1;
        $tempuser->username = $data['username'];
        $tempuser->firstname = $data['firstname'];
        $tempuser->lastname = $data['lastname'];
        $tempuser->email = $data['email'];

        $errmsg = '';
        if (!check_password_policy($data['password'], $errmsg, $tempuser)) {
            $errors['password'] = $errmsg;
        }

        // Validate customisable profile fields. (profile_validation expects an object as the parameter with userid set).
        $dataobject = (object)$data;
        $dataobject->id = 0;
        $errors += profile_validation($dataobject, $files);

        return $errors;
    }

}
