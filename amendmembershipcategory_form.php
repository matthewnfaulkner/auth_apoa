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
class auth_apoa_amendmembershipcategory_form extends moodleform {

    protected auth_apoa\membershipcategory\membership_category $approvalclass;

    protected string $category;

        
    function definition () {
        global $PAGE;

        $mform = $this->_form;
        
        $this->category = $this->_customdata['membership_category'];

        $requestid = $this->_customdata['requestid'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $requestid);

        $mform->addElement('hidden', 'amendmentneeded');
        $mform->setType('amendmentneeded', PARAM_INT);
        $mform->setDefault('amendmentneeded', 0);

        // Just a placeholder for the course format options.
        $mform->addElement('hidden', 'addmembershipcategorieshere');
        $mform->setType('addmembershipcategorieshere', PARAM_BOOL);

        $categoryclass = membership_category_class($this->category);

        $categoryclass->extend_update_form($mform, 'addmembershipcategorieshere');
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



