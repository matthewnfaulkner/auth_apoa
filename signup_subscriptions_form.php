
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

use \core_user as core_user;
use enrol_plugin;

class signup_subscriptions_form1 extends \moodleform {



    function definition() {
        $mform = $this->_form;

        $mainsubscriptionid = local_subscriptions_get_main_subscription();
        $enrolmentoptions = enrol_get_instances($mainsubscriptionid, true);

        $radioarray=array();
        foreach($enrolmentoptions as $option) {
            $plugin = enrol_get_plugin($option->enrol);

            if($plugin->show_enrolme_link($option)) {
                $radioarray[] = $mform->createElement('radio', $mainsubscriptionid, '', format_text($option->name), $option->id);
            }
            
        }
        
        $mform->addGroup($radioarray, 'radioarray_' . $mainsubscriptionid , '', array(' '), false);
        $mform->addRule('radioarray_' . $mainsubscriptionid, '', 'required', null, 'client');
        $this->set_display_vertical();
        $this->add_action_buttons(true, get_string('continue'));

    }



}


class signup_subscriptions_form2 extends \moodleform {



    function definition() {
        $mform = $this->_form;

        $mainsubscriptionid = local_subscriptions_get_main_subscription();

        if($subscriptions = get_subscriptions()) {
            if(isset($subscriptions[$mainsubscriptionid])) {
                unset($subscriptions[$mainsubscriptionid]);
            }
            foreach($subscriptions as $subscription) {
                $enrolmentoptions = enrol_get_instances($subscription->id, true);

                $mform->addElement('header', 'header' . $subscription->id, $subscription->fullname);
                
                $radioarray=array();
                $radioarray[] = $mform->createElement('radio', $subscription->id, '', get_string('none'), 0);
                foreach($enrolmentoptions as $option) {
                    
                    $plugin = enrol_get_plugin($option->enrol);
                    
                    if($plugin->show_enrolme_link($option)) {
                        $radioarray[] = $mform->createElement('radio', $subscription->id, '', format_text($option->name), $option->id);
                    }
                }
                $mform->addGroup($radioarray, 'radioarray_' . $subscription->id , '', array(' '), false);
                
            }
        };

        $mform->addElement('hidden', 'iid');
        $mform->setType('iid', PARAM_INT);
        $mform->setDefault('iid', 1);

        $this->set_display_vertical();
        $this->add_action_buttons(true, get_string('continue'));

    }



}
