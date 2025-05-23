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

  use moodleform;

  defined('MOODLE_INTERNAL') || die;

  class federationfellow extends \auth_apoa\membershipcategory\membership_category {


    public function __construct(){
      $this->category = 'Federation Fellow';
      parent::__construct($this->category);
    }
    
    public function extend_update_form(\MoodleQuickForm $mform, $insertbefore)
    {
      $profilefederation = profile_get_custom_field_data_by_shortname('federation');

      $federations = explode("\n", $profilefederation->param1);

      $withkeys = array_combine($federations, $federations);

      $elements = [];

      $elements[] = $mform->createElement('header', 'federationheading', get_string('categoryextensionheading', 'auth_apoa', $this->category));
      $elements[] = $mform->createElement('select', 'profile_field_federation', $profilefederation->name, $withkeys);

      foreach($elements as &$element){
          $mform->insertElementBefore($element, $insertbefore);
      }

      
      $mform->addRule('profile_field_federation', 'Please Select a Country', 'required');

      $mform->addElement('static', 'federationapprovaldescription', get_string('federationapprovaldescription', 'auth_apoa'));

      $mform->addElement('submit', 'submitbutton', get_string('updatemembershipcategory', 'auth_apoa'));
      $mform->closeHeaderBefore('submitbutton');

    }

    public function validation($data, $files){
      $errors = [];

      if($data['profile_field_federation'] == 0){
        $errors['profile_field_federation'] = get_string('selectfederation', 'auth_apoa');
      }
      
      return $errors;
    }

    public function add_approval_request($formdata, $user = null){
        global $DB, $USER;

        if(empty($user)){
          $user = $USER;
        }

        if($DB->get_record('auth_apoa_membershipchanges', array('userid' => $user->id))){
            $DB->delete_records('auth_apoa_membershipchanges', array('userid' => $user->id));
        }

        $secret = random_string(15);
        $newapproval = new \stdClass();
        $newapproval->userid = $user->id;
        $newapproval->newcategory = $this->category;
        $newapproval->timecreated = time();
        $newapproval->approved = $this->approve();
        $newapproval->extradata = $formdata->profile_field_federation;
        $newapproval->amendmentcomments = get_string('membershipchangednotapproved', 'auth_apoa');
        if(isset($formdata->previouscategory)){
          $newapproval->previouscategory = $formdata->previouscategory;
        }
        if(isset($formdata->previouslyapproved)){
          $newapproval->previouslyapproved = $formdata->previouslyapproved;
        } 
        $newapproval->secret = $secret;
        if($inserted = $DB->insert_record('auth_apoa_membershipchanges', $newapproval)){
            $newapproval->id = $inserted;
            if(!$this->approve()){

              if($formdata->profile_field_federation){
                if($feduser = $DB->get_record('auth_apoa_federation_members', array('email' => $user->email))) {
                    if($formdata->profile_field_federation == $feduser->federation) {
                      if($feduser && ($feduser->expires > time() || $feduser->expires ==0)){
                        $newapproval->approved = true;
                        $newapproval->amendmentcomments = get_string('membershipchangedapproved', 'auth_apoa');
                        $formattedfederation = strtolower(preg_replace('/[^A-Za-z]/', '', $formdata->profile_field_federation));
                        $DB->update_record('auth_apoa_membershipchanges', $newapproval);
                        return 1;
                      }
                      else{
                        $newapproval->amendmentcomments = get_string('federationmembershipexpired', 'auth_apoa');
                        $DB->update_record('auth_apoa_membershipchanges', $newapproval);
                        return 0;
                      }
                    }
                }
              }
              $authplugin = get_auth_plugin('apoa');
              $formattedfederation = strtolower(preg_replace('/[^A-Za-z]/', '', $formdata->profile_field_federation));

              if(get_config('auth_apoa', 'federationemail'.$formattedfederation)){
                $authplugin->membership_category_send_confirmation_email_to_federation($user, $formattedfederation, null, $inserted, $secret);
                $newapproval->approved = null;
                $newapproval->amendmentcomments = get_string('pendingfederationapproval', 'auth_apoa');
                $DB->update_record('auth_apoa_membershipchanges', $newapproval);
                return 0;
              }
             
            }
        }
        
        return 0;
    }
  }