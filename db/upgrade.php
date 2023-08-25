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
 * This file keeps track of upgrades to the subscriptions block
 *
 * @since 3.8
 * @package auth_apoa
 * @copyright 2019 Jake Dallimore <jrhdallimore@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->dirroot}/my/lib.php");
require_once("{$CFG->libdir}/db/upgradelib.php");

/**
 * Upgrade code for the MyOverview block.
 *
 * @param int $oldversion
 */
function xmldb_auth_apoa_upgrade($oldversion) {
    global $DB, $CFG, $OUTPUT;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023062317) {

        // Define field membershipnumber to be added to auth_apoa.
        $table = new xmldb_table('auth_apoa');
        $field = new xmldb_field('membershipnumber', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'id');

        // Conditionally launch add field membershipnumber.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('membership_category', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'membershipnumber');

        // Conditionally launch add field membership_category.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $field = new xmldb_field('email', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'membership_category');

        // Conditionally launch add field email.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $key = new xmldb_key('email', XMLDB_KEY_UNIQUE, ['email']);

        // Launch add key email.
        $dbman->add_key($table, $key);

        $field = new xmldb_field('lifemembership', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'email');

        // Conditionally launch add field lifemembership.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('subscriptionends', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'lifemembership');

        // Conditionally launch add field subscriptionends.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('footandankle', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'subscriptionends');

        // Conditionally launch add field footandankle.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('handandupperlimb', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'footandankle');

        // Conditionally launch add field handandupperlimb.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('hip', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'handandupperlimb');

        // Conditionally launch add field hip.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('infection', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'hip');

        // Conditionally launch add field infection.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('knee', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'infection');

        // Conditionally launch add field knee.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('research', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'knee');

        // Conditionally launch add field research.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $field = new xmldb_field('osteoperosis', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'research');

        // Conditionally launch add field osteoperosis.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $field = new xmldb_field('paediatrics', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'osteoperosis');

        // Conditionally launch add field paediatrics.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('spine', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'paediatrics');

        // Conditionally launch add field spine.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('sports', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'spine');

        // Conditionally launch add field sports.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('trauma', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'sports');

        // Conditionally launch add field trauma.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('waves', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'trauma');

        // Conditionally launch add field waves.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        // Define field country to be added to auth_apoa.
        $table = new xmldb_table('auth_apoa');
        $field = new xmldb_field('country', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'email');

        // Conditionally launch add field country.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062317, 'auth', 'apoa');
    }


    return true;
}
