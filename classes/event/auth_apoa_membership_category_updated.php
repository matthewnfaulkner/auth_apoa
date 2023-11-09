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
 * Event to be triggered when a new course module is created.
 *
 * @package    core
 * @copyright  2013 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace auth_apoa\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Class course_module_created
 *
 * Class for event to be triggered when a new course module is created.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string modulename: name of module created.
 *      - string name: title of module.
 *      - string instanceid: id of module instance.
 * }
 *
 * @package    core
 * @since      Moodle 2.6
 * @copyright  2013 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class auth_apoa_membership_category_updated extends \core\event\base {

    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'auth_apoa_membershipchanges';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventuserchangedmembershipcategory', 'auth_apoa');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' changed their membership category to $this->other";
    }

    /**
     * Returns relevant URL.
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/auth_apoa/' . $this->other['modulename'] . '/view.php', array('id' => $this->objectid));
    }

    /**
     * Custom validation.
     *
     * @throw \coding_exception
     */
    protected function validate_data() {
        parent::validate_data();
    }

    public static function get_objectid_mapping() {
        return array('db' => 'auth_apoa_membershipchanges', 'restore' => 'auth_apoa_membershipchanges');
    }

}

