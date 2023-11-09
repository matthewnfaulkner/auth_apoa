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

use core\plugininfo\profilefield;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/auth/apoa/lib.php');
/**
 * Upload a file CVS file with user information.
 *
 * @copyright  2007 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_apoa_updatemembershipcategory_form extends moodleform {

    protected auth_apoa\membershipcategory\membership_category $approvalclass;

    protected string $category;

    protected int $approved;
        
    function definition () {
        global $PAGE;

        $mform = $this->_form;

        $PAGE->requires->js_call_amd('auth_apoa/categorychooser', 'init');
        
        $this->category = $this->_customdata['membership_category'];
        $this->approved = $this->_customdata['membership_category_approved'];
        $userid = $this->_customdata['userid'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $userid);
        
        $profilefieldata = profile_get_custom_field_data_by_shortname('membership_category');
        
        $options = $profilefieldata->param1;

        $membershipcategories = explode("\n", $options);
        
        $withkeys = array_combine($membershipcategories, $membershipcategories);

        $default = $this->category;

        $mform->addElement('select', 
                            'profile_field_membership_category', 
                            get_string('membershipcategory', 'auth_apoa'), 
                            $withkeys,
                            ['data-categorychooser-field' => 'selector']);
        $mform->setType('profile_field_membership_category', PARAM_TEXT);
        $mform->setDefault('profile_field_membership_category', $default);
        // Button to update format-specific options on format change (will be hidden by JavaScript).

        $mform->registerNoSubmitButton('updatemembershipcategory');
        $mform->addElement('submit', 'updatemembershipcategory', get_string('updatemembershipcategory', 'auth_apoa'), [
            'data-categorychooser-field' => 'updateButton',
            'class' => 'd-none',
        ]);
        
        // Just a placeholder for the course format options.
        $mform->addElement('hidden', 'addmembershipcategorieshere');
        $mform->setType('addmembershipcategorieshere', PARAM_BOOL);

    }

    public function definition_after_data()
    {
        $mform = $this->_form;

        // add course format options
        $categoryvalue = $mform->getElementValue('profile_field_membership_category');
        if (is_array($categoryvalue) && !empty($categoryvalue)) {
    

            if($categoryclass = membership_category_class(reset($categoryvalue))){
                    $this->approvalclass = $categoryclass;
                    if($this->category == $categoryclass->get_category() && $this->approved){
                        $notify = $mform->createElement('static', 'approved', get_string('membershipalreadyapproved', 'auth_apoa'));
                        $mform->insertElementBefore($notify, 'addmembershipcategorieshere');
                        return;
                    }
                    
                    $categoryclass->extend_update_form($mform, 'addmembershipcategorieshere');
            }
        }
    }

    public function validation($data, $files)
    {       
        $errors = parent::validation($data, $files);

        if(isset($this->approvalclass)){
            $errors = array_merge($this->approvalclass->validation($data, $files), $errors);
        }

        return $errors;
    }
}



