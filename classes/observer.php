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
     * When a user is enrolled in the main subscription, set their membership category and approve it.
     * Category is their answer to "Which best describes you?", their current choosable category, or Fellow.
     * Trainee, Honorary and Life Fellows keep theirs.
     *
     * @param \core\event\base $event user_enrolment_created or user_enrolment_updated
     * @return void
     */
    public static function user_enrolment_changed(\core\event\base $event) {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        if ($event->courseid != get_config('local_subscriptions', 'mainsubscription') || $event->other['enrol'] == 'cohort') {
            return;
        }

        $userid = $event->relateduserid;
        $current = profile_user_record($userid, false)->membership_category ?? '';

        if (in_array($current, ['Trainee Fellow', 'Honorary Fellow', 'Life Fellow'])) {
            $category = $current;
        } else {
            $default = in_array($current, ['Fellow', 'Senior Fellow', 'Associate Fellow', 'Affiliate Fellow']) ? $current : 'Fellow';
            $category = get_user_preferences('auth_apoa_category_preference', $default, $userid);
        }

        profile_save_custom_fields($userid, [
            'membership_category' => $category,
            'membership_category_approved' => 1,
        ]);

        \cache::make('auth_apoa', 'membership_category_approved_cache')->delete("u_$userid");
    }
}
