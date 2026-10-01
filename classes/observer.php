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
 * Event observers for auth_apoa.
 *
 * @package    auth_apoa
 * @copyright  2022 Matthew<you@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_apoa;

defined('MOODLE_INTERNAL') || die();

class observer {

    /**
     * Set membership category when a user is enrolled in, or their enrolment changes in, the main subscription.
     *
     * @param \core\event\base $event user_enrolment_created or user_enrolment_updated
     * @return void
     */
    public static function user_enrolment_changed(\core\event\base $event) {
        global $CFG;
        require_once($CFG->dirroot . '/auth/apoa/lib.php');

        auth_apoa_user_enrolment_changed($event);
    }
}
