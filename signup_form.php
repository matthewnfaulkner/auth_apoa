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

class signup_form extends \login_signup_form {

    private int $path;

    private int $emailexists = 0;

    function definition() {
        global $USER, $CFG;
        
        $this->path =  optional_param('path', 0, PARAM_INT);


        if($this->_customdata['path']){
            $this->path = $this->_customdata['path'];
        };

        if($this->_customdata['makenewuser']){
            $this->path = 0;
        };

        $mform = $this->_form;

        $mform->addElement('hidden', 'path', $this->path);
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        if(!$this->path){

            $mform->addElement('static', 'usernamepolicyinfo', '', get_string('usernamepolicy', 'auth_apoa'));

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
        
            $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
            $mform->setType('email', core_user::get_property_type('email'));
            $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
            $mform->setForceLtr('email');

            $mform->addElement('text', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
            $mform->setType('email2', core_user::get_property_type('email'));
            $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
            $mform->setForceLtr('email2');

            $namefields = useredit_get_required_name_fields();
            foreach ($namefields as $field) {
                $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
                $mform->setType($field, core_user::get_property_type('firstname'));
                $stringid = 'missing' . $field;
                if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                    $stringid = 'required';
                }
                $mform->addRule($field, get_string($stringid), 'required', null, 'client');
            }

            $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="20"');
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
                $mform->setDefault('country', '');
            }

            profile_signup_fields($mform);
            $mform->addHelpButton('profile_field_membership_category', 'membership_category', 'auth_apoa', 'What is this?');
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
            $this->set_display_vertical();
            $this->add_action_buttons(true, get_string('createaccount'));
        }
        else if($this->path == 1){
            \MoodleQuickForm::registerElementType('checkabletext',
                                         "$CFG->dirroot/$CFG->admin/tool/checkabletext/classes/checkabletext.php",
                                         'tool_checkabletext\MoodleQuickForm_checkabletext');

            $mform->addElement('checkabletext', 'email', get_string('email'), 'maxlength="100" size="25"');
            $mform->setType('email', core_user::get_property_type('email'));
            $mform->addRule('email', get_string('missingemail'), 'required', null, 'server');
            $mform->setForceLtr('email');

            
            // Hook for plugins to extend form definition.
            core_login_extend_signup_form($mform);

            $buttonarray=array();   
            $buttonarray[] = $mform->createElement('submit', 'existingemail', get_string('checkexistingemail', 'auth_apoa'));
            $buttonarray[] = $mform->createElement('cancel');
            $mform->addGroup($buttonarray, 'buttonemail', '', array(' '), false);
            $mform->closeHeaderBefore('buttonemail');
            
            if($this->_customdata['emailexists']){
                $this->existing_extra_form_elements();
            }
        }

    }

    function definition_after_data(){
        global $CFG;

        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
        
    }


    public function existing_extra_form_elements(){
        global $CFG, $OUTPUT;
        $mform = $this->_form;

        if($mform->elementExists("buttonemail")){
            $mform->removeElement("buttonemail");
        }

        $mform->addElement('hidden', 'emailexists', 1);
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('emailexists', 1);

        $element = $mform->getElement("email");
        $element->updateAttributes(array('readonly' => 'readonly', 'valid' => get_string('emailexists', 'auth_apoa')));

        $mform->addElement('static', 'usernamepolicyinfo', '', get_string('usernamepolicy', 'auth_apoa'));

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
        

        $mform->addElement('hidden', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
        $mform->setType('email2', core_user::get_property_type('email'));
        $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');

        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
        }

        $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="20"');
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
            $mform->setDefault('country', '');
        }

        $this->profile_signup_fields_existing($mform, []);
        
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
        $this->set_display_vertical();
        $this->add_action_buttons(true, get_string('createaccount'));


    }

    public function add_default_signupelements($data){
        global $CFG, $OUTPUT;
        $mform = $this->_form;

        if($mform->elementExists("buttonemail")){
            $mform->removeElement("buttonemail");
        }


        $mform->addElement('hidden', 'emailexists', 1);
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('emailexists', 1);

        $element = $mform->getElement("email");
        $mform->setDefault('email', $data['email']);
        $element->updateAttributes(array('readonly' => 'readonly', 'valid' => get_string('emailexists', 'auth_apoa')));

        $mform->addElement('static', 'usernamepolicyinfo', '', get_string('usernamepolicy', 'auth_apoa'));
        
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
        

        $mform->addElement('hidden', 'email2', get_string('emailagain'), 'maxlength="100" size="25"');
        $mform->setType('email2', core_user::get_property_type('email'));
        $mform->addRule('email2', get_string('missingemail'), 'required', null, 'client');
        $mform->setDefault('email2', $data['email']);

        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
        }

        $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="20"');
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
            $mform->setDefault('country', '');
        }

        $this->profile_signup_fields_existing($mform, $data);


        
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
        $this->set_display_vertical();
        $this->add_action_buttons(true, get_string('createaccount'));
    
    }

    function profile_signup_fields_existing(\MoodleQuickForm $mform, $data): void {
        if ($fields = $this->profile_get_signup_fields_existing()) {
            foreach ($fields as $field) {
                // Check if we change the categories.
                if (!isset($currentcat) || $currentcat != $field->categoryid) {
                     $currentcat = $field->categoryid;
                     //$mform->addElement('header', 'category_'.$field->categoryid, format_string($field->categoryname));
                }
                if($field->categoryname == 'Membership'){
                    $mform->addElement('hidden', $field->object->inputname);
                    $mform->setDefault($field->object->inputname, $data[$field->object->inputname]);
                }
                else{
                    $field->object->edit_field($mform);
                }
            }
        }
    }

        /**
     * Retrieves a list of profile fields that must be displayed in the sign-up form.
     *
     * @return array list of profile fields info
     * @since Moodle 3.2
     */
    function profile_get_signup_fields_existing(): array {
        $profilefields = array();
        $fieldobjects = profile_get_user_fields_with_data(0);
        foreach ($fieldobjects as $fieldobject) {
            $field = (object)$fieldobject->get_field_config_for_external();
            if ($fieldobject->get_category_name() !== null) {
                $profilefields[] = (object) array(
                    'categoryid' => $field->categoryid,
                    'categoryname' => $fieldobject->get_category_name(),
                    'fieldid' => $field->id,
                    'datatype' => $field->datatype,
                    'object' => $fieldobject
                );
            }
        }
        return $profilefields;
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

        if($this->path){
            $errors = [];
            if (!validate_email($data['email'])) {
                $errors['email'] = get_string('invalidemail');
            }
            $errors += signup_validate_data($data, $files);
            if(empty($errors['email'] && !$data['emailexists'])){
                    if($user = $this->validate_existing_email($data['email'])){
                        $data['profile_field_membership_category'] = $user->membership_category;
                        $data['profile_field_federation'] = country_to_federation($user->country);
                        $data['profile_field_membershipnumber'] = $user->id;
                        if(!$this->_customdata['emailexists']){
                            if(empty($errors['email'])){
                                $this->add_default_signupelements($data);
                            }
                        }
                    }else{
                        $errors['email'] = get_string('emaildoesnotexist' , 'auth_apoa');
                        $this->_form->addElement('submit', 'makenewuser', get_string('makenewaccount', 'auth_apoa'));
                    }
            }
            if(!$errors){
                $errors += parent::validation($data, $files);
            }

        }
        else{
            $errors = parent::validation($data, $files);

            // Extend validation for any form extensions from plugins.
            $errors = array_merge($errors, core_login_validate_extend_signup_form($data));

            

            $errors += signup_validate_data($data, $files);
        }
    

        return $errors;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $title = $this->path ? get_string('exisitinguserheader', 'auth_apoa') : get_string('newaccount');
        $context = [
            'formhtml' => $formhtml,
            'formtitle' => $title
        ];
        return $context;
    }

    public function validate_existing_email($email){
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

    
}
