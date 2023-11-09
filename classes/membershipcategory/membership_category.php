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

use stdClass;

defined('MOODLE_INTERNAL') || die;

abstract class membership_category {

    protected string $name;

    protected string $category;

    public function __construct($category){
        $this->name = strtolower(str_replace(' ', '', $this->category));
    }

    abstract public function approve();

    abstract public function extend_update_form(\MoodleQuickForm $mform, $insertbefore);

    public function validation($data, $files){
        return;
    }

    public function add_approval_request($formdata, $user = null){
        global $DB, $USER;

        if(empty($user)){
            $user = $USER;
        }

        if($DB->get_record('auth_apoa_membershipchanges', array('userid' => $user->id))){
            $DB->delete_records('auth_apoa_membershipchanges', array('userid' => $user->id));
        }

        $newapproval = new stdClass();
        $newapproval->userid = $user->id;
        $newapproval->newcategory = $this->category;
        $newapproval->timecreated = time();
        $newapproval->timemodified = $newapproval->timecreated;
        $newapproval->approved = $this->approve();
        $newapproval->extradata = $formdata->extradata;
        $newapproval->previouscategory = $formdata->previouscategory ? $formdata->previouscategory : 'No Membership';
        $newapproval->previouslyapproved = $formdata->previouslyapproved;

        $DB->insert_record('auth_apoa_membershipchanges', $newapproval);

    }

    public function get_category(){
        return $this->category;
    }

    public function get_name(){
        return $this->name;
    }

    public function extra_options(\context $context){
        return [];
    }
}