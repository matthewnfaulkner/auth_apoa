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
 * Auth e-mail external API
 *
 * @package    auth_apoa
 * @category   external
 * @copyright  2016 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.2
 */

 namespace auth_apoa\membershipcategory;

defined('MOODLE_INTERNAL') || die;

class noapprovalrequired extends \auth_apoa\membershipcategory\membership_category {

    
    public function __construct($category){
        $this->category = $category;
      }

    public function approve(){
        return 1;
    }

    public function approval_hook(){
    return;
    }

    public function extend_update_form(\MoodleQuickForm $mform, $insertbefore)
    { 

        $mform->addElement('static', 'automaticallyapproved', get_string('automaticallyapproved', 'auth_apoa'));
        $btnstring = get_string('updatemembershipcategory', 'auth_apoa');

        $mform->addElement('submit', 'submitbutton', $btnstring);
        $mform->closeHeaderBefore('submitbutton');

    }
    
    public function validation($data, $files){
        $errors = [];
        return $errors;
      }
    
}