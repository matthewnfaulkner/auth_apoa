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

    if ($oldversion < 2023062319) {

        // Rename field osteoporosis on table auth_apoa to NEWNAMEGOESHERE.
        $table = new xmldb_table('auth_apoa');
        $field = new xmldb_field('osteoperosis', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'research');

        // Launch rename field osteoporosis.
        $dbman->rename_field($table, $field, 'osteoporosis');

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062319, 'auth', 'apoa');
    }

    if ($oldversion < 2023062320) {

        // Define field apssnumber to be added to auth_apoa.
        $table = new xmldb_table('auth_apoa');
        $field = new xmldb_field('apssnumber', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'waves');

        // Conditionally launch add field apssnumber.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062320, 'auth', 'apoa');
    }


    if ($oldversion < 2023062321) {

        // Define field membershipnumberraw to be added to auth_apoa.
        $table = new xmldb_table('auth_apoa');
        $field = new xmldb_field('membershipnumberraw', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'apssnumber');

        // Conditionally launch add field membershipnumberraw.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062321, 'auth', 'apoa');
    }

    if ($oldversion < 2023062322) {

        // Define field status to be added to auth_apoa.
        $table = new xmldb_table('auth_apoa');
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'membershipnumberraw');

        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062322, 'auth', 'apoa');
    }

    if ($oldversion < 2023062323) {

        // Define field title to be added to auth_apoa.
        $table = new xmldb_table('auth_apoa');
        $field = new xmldb_field('title', XMLDB_TYPE_CHAR, '10', null, null, null, null, 'status');

        // Conditionally launch add field title.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('firstname', XMLDB_TYPE_CHAR, '45', null, null, null, null, 'title');

        // Conditionally launch add field firstname.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $field = new xmldb_field('lastname', XMLDB_TYPE_CHAR, '45', null, null, null, null, 'firstname');

        // Conditionally launch add field lastname.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('address', XMLDB_TYPE_CHAR, '512', null, null, null, null, 'lastname');

        // Conditionally launch add field address.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('state', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'address');

        // Conditionally launch add field state.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('postcode', XMLDB_TYPE_CHAR, '45', null, null, null, null, 'state');

        // Conditionally launch add field postcode.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('phone', XMLDB_TYPE_CHAR, '45', null, null, null, null, 'postcode');

        // Conditionally launch add field phone.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $field = new xmldb_field('fax', XMLDB_TYPE_CHAR, '45', null, null, null, null, 'phone');

        // Conditionally launch add field fax.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('institution', XMLDB_TYPE_CHAR, '256', null, null, null, null, 'fax');

        // Conditionally launch add field institution.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062323, 'auth', 'apoa');
    }

    if ($oldversion < 2023062329) {

        // Define table auth_apoa_membershipchanges to be created.
        $table = new xmldb_table('auth_apoa_membershipchanges');

        // Adding fields to table auth_apoa_membershipchanges.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('newcategory', XMLDB_TYPE_CHAR, '55', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('approved', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('extradata', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table auth_apoa_membershipchanges.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for auth_apoa_membershipchanges.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062329, 'auth', 'apoa');
    }


    if ($oldversion < 2023062330) {

        // Define field previouscategory to be added to auth_apoa_membershipchanges.
        $table = new xmldb_table('auth_apoa_membershipchanges');
        $field = new xmldb_field('previouscategory', XMLDB_TYPE_CHAR, '55', null, XMLDB_NOTNULL, null, '', 'extradata');

        // Conditionally launch add field previouscategory.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('previouslyapproved', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'previouscategory');

        // Conditionally launch add field previouslyapproved.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062330, 'auth', 'apoa');
    }


    if ($oldversion < 2023062331) {

        // Define field secret to be added to auth_apoa_membershipchanges.
        $table = new xmldb_table('auth_apoa_membershipchanges');
        $field = new xmldb_field('secret', XMLDB_TYPE_CHAR, '15', null, null, null, null, 'previouslyapproved');

        // Conditionally launch add field secret.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062331, 'auth', 'apoa');
    }

    if ($oldversion < 2023062332) {

        // Changing nullability of field approved on table auth_apoa_membershipchanges to null.
        $table = new xmldb_table('auth_apoa_membershipchanges');
        $field = new xmldb_field('approved', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'timecreated');

        // Launch change of nullability for field approved.
        $dbman->change_field_notnull($table, $field);

        $field = new xmldb_field('amendmentneeded', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'secret');

        // Conditionally launch add field amendmentneeded.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'amendmentneeded');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timeapproved', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');

        // Conditionally launch add field timeapproved.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('amendmentcomments', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timeapproved');

        // Conditionally launch add field amendmentcomments.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062332, 'auth', 'apoa');
    }

    if ($oldversion < 2023062335) {

        // Changing nullability of field previouscategory on table auth_apoa_membershipchanges to null.
        $table = new xmldb_table('auth_apoa_membershipchanges');
        $field = new xmldb_field('previouscategory', XMLDB_TYPE_CHAR, '55', null, null, null, 'No Membership', 'extradata');

        // Launch change of nullability for field previouscategory.
        $dbman->change_field_notnull($table, $field);

        $field = new xmldb_field('previouslyapproved', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'previouscategory');

        // Launch change of nullability for field previouslyapproved.
        $dbman->change_field_notnull($table, $field);

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062335, 'auth', 'apoa');
    }

    if ($oldversion < 2023062336) {

        // Changing nullability of field previouscategory on table auth_apoa_membershipchanges to null.
        $table = new xmldb_table('auth_apoa_membershipchanges');
        $field = new xmldb_field('previouscategory', XMLDB_TYPE_CHAR, '55', null, XMLDB_NOTNULL, null, 'No Membership', 'extradata');

        // Launch change of nullability for field previouscategory.
        $dbman->change_field_notnull($table, $field);

        $field = new xmldb_field('previouslyapproved', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'previouscategory');

        // Launch change of nullability for field previouslyapproved.
        $dbman->change_field_notnull($table, $field);

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062336, 'auth', 'apoa');
    }

    if ($oldversion < 2023062337) {

        // Define field spinesubscriptionends to be added to auth_apoa.
        $table = new xmldb_table('auth_apoa');
        $field = new xmldb_field('spinesubscriptionends', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'institution');

        // Conditionally launch add field spinesubscriptionends.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Apoa savepoint reached.
        upgrade_plugin_savepoint(true, 2023062337, 'auth', 'apoa');
    }


    return true;
}
