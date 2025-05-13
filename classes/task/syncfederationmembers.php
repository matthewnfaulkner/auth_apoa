<?php

namespace auth_apoa\task;

require_once($CFG->dirroot. '/user/profile/lib.php');
/**
 * An example of a scheduled task.
 */
class  syncfederationmembers extends \core\task\scheduled_task {

    use \core\task\logging_trait;
    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('syncfederationmembers', 'auth_apoa');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        // Call your own api
        global $DB;
        $sql = "SELECT u.id, af.federation, af.expires FROM {user} u 
                INNER JOIN {user_info_data} ud ON u.id = ud.userid
                INNER JOIN {user_info_field} uif on uif.id = ud.fieldid
                INNER JOIN {auth_apoa_federation_members} af ON u.email = af.email
                INNER JOIN {user_info_data} ud2 ON u.id = ud2.userid
                INNER JOIN {user_info_field} uif2 on uif2.id = ud2.fieldid
                WHERE uif.shortname = :membershipfield AND ud.data = :federationcat
                AND uif2.shortname = :federationfield AND ud2.data = af.federation";

        $params = [
            'membershipfield' => 'membership_category',
            'federationcat' => 'Federation Fellow',
            'federationfield' => 'federation'
        ];

        $rs = $DB->get_recordset_sql($sql, $params);

        foreach($rs as $r){
            if($r->expires == 0 || $r->expires > time()){
                $r->profile_field_membership_category_approved = 1;
                profile_save_data($r);
            }else{
                $r->profile_field_membership_category_approved = 0;
                profile_save_data($r);
            }
        }

    }

}