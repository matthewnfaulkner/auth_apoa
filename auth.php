<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Authentication class for apoa is defined here.
 *
 * @package     auth_apoa
 * @copyright   2022 Matthew<you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot. '/auth/email/auth.php');
require_once(__DIR__. '/signup_form.php');
require_once(__DIR__. '/signupexisting_form.php');

use auth_apoa\signup_form as signup_form;
use auth_apoa\signupexisting_form as signupexisting_form;
// For further information about authentication plugins please read
// https://docs.moodle.org/dev/Authentication_plugins.
//
// The base class auth_plugin_base is located at /lib/authlib.php.
// Override functions as needed.

/**
 * Authentication class for apoa.
 */
class auth_plugin_apoa extends auth_plugin_email {

    public bool $multipath;

    private array $paths;

    private int $noemailmode;
    /**
     * Set the properties of the instance.
     */
    public function __construct() {
        $this->authtype = 'apoa';
        $this->multipath = True;
        $this->noemailmode = get_config('auth_apoa', 'noemailmode');
        $this->paths = array(
                'new' => array(
                    'path' => 0,
                    'title' => get_string('pathnewtitle', 'auth_apoa'), 
                    'desc' => get_string('pathnewdesc', 'auth_apoa')),
                'existing' => array(
                    'path' => 1, 
                    'title' => get_string('pathexistingtitle', 'auth_apoa'), 
                    'desc' => get_string('pathexistingdesc', 'auth_apoa')
            ));
    }

     /**
     * Return a form to capture user details for account creation.
     * This is used in /login/signup.php.
     * @return moodle_form A form which edits a record from the user table.
     */
    function signup_form() {
        global $CFG;
        $this->get_subscription_headers();
        $params = array('path' => $_POST['path'], 
        'emailexists' =>$_POST['emailexists'], 
        'createuser' => $_POST['submitbutton'],
        'makenewuser' => $_POST['makenewuser'],);
        return new signup_form(null, $params, 'post', '', array('autocomplete'=>'on'));
    }

     /**
     * Return a form to capture user details for account creation.
     * This is used in /login/signup.php.
     * @return moodle_form A form which edits a record from the user table.
     */
    function signup_form_from_user($user, $url) {
        global $CFG;

        $params = [];
        foreach($user as $key => $value){
            $params[$key] = $value;
        }
        
        return new signup_form($url, $params, 'post', '', array('autocomplete'=>'on'));
    }
    
    public function get_paths(){
        return $this->paths;
    }
    
    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        global $CFG, $DB;

        // Validate the login by using the Moodle user table.
        // Remove if a different authentication method is desired.
        $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));

        // User does not exist.
        if (!$user) {
            return false;
        }

        return validate_internal_user_password($user, $password);
    }

        /**
     * Sign up a new user ready for confirmation.
     *
     * Password is passed in plaintext.
     * A custom confirmationurl could be used.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     * @param string $confirmationurl user confirmation URL
     * @return boolean true if everything well ok and $notify is set to true
     * @throws moodle_exception
     * @since Moodle 3.2
     */
    public function user_signup_with_confirmation($user, $notify=true, $confirmationurl = null) {
        global $CFG, $DB, $SESSION;
        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');
        $plainpassword = $user->password;
        $user->password = hash_internal_user_password($plainpassword);
        if (empty($user->calendartype)) {
            $user->calendartype = $CFG->calendartype;
        }
        $federationemail = '';
        $membershipcategory = $user->profile_field_membership_category;
        if($membershipcategory == "Federation Fellow"){
            if($federation = $user->profile_field_federation){
                $formattedfederation = strtolower(preg_replace('/[^A-Za-z]/', '', $federation));
                if(!$federationemail = get_config('auth_apoa', 'federationemail'.$formattedfederation)){
                    throw new \moodle_exception('auth_emailnofederationemail', 'auth_apoa');
                }
            }
        }

        $user->profile_field_membership_category_approved = 0;
        $user->profile_field_hasactivesubscription = 0;
        $user->id = user_create_user($user, false, false);

        user_add_password_history($user->id, $plainpassword);

        // Save any custom profile field information.
        profile_save_data($user);
        
        // Save wantsurl against user's profile, so we can return them there upon confirmation.
        if (!empty($SESSION->wantsurl)) {
            if($SESSION->wantsurl == $CFG->wwwroot . '/'){
                $redirect = $redirect =  new moodle_url('/local/landingpage/index.php');
            }
            set_user_preference('auth_email_wantsurl', $SESSION->wantsurl, $user);
        }
        
        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();

        if($federationemail){
            $confirmationurl = new moodle_url('/auth/apoa/user_confirm.php', array('data' => "$user->secret/$user->username", 'federation' => $formattedfederation));

        }
        else{
            $confirmationurl = new moodle_url('/auth/apoa/confirm.php', array('data' => "$user->secret/$user->username", 'redirect' => $redirect));
        }
        
        if($this->noemailmode){
            redirect($confirmationurl);
        }
        if (! $this->send_confirmation_email($user, $confirmationurl)) {
            throw new \moodle_exception('auth_emailnoemail', 'auth_email');
        }

        if ($notify) {
            global $CFG, $PAGE, $OUTPUT;
            $emailconfirm = get_string('emailconfirm');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $PAGE->set_heading($PAGE->course->fullname);
            echo $OUTPUT->header();
            notice(get_string('emailconfirmsent', '', $user->email), "$CFG->wwwroot/index.php");
        } else {
            return true;
        }
    }

        /**
     * Confirm the new user as registered.
     *
     * @param string $username
     * @param string $confirmsecret
     */
    function federation_confirm($username, $confirmsecret) {
        global $DB, $SESSION;
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->auth != $this->authtype) {
                return AUTH_CONFIRM_ERROR;

            } else if ($user->secret === $confirmsecret && $user->profile['membership_category_approved']) {
                return AUTH_CONFIRM_ALREADY;

            } else if ($user->secret === $confirmsecret) {   // They have provided the secret key to get in
                $user->profile['membership_category_approved'] = 1;
                $sql = "SELECT uip.id 
                FROM  {user_info_field} uip 
                WHERE uip.shortname = :fieldname";
                $params = array('fieldname' => 'membership_category_approved');
                
                $field = $DB->get_record_sql($sql, $params);
                $DB->set_field("user_info_data", "data", 0, array("userid"=>$user->id, "fieldid" => $field->id));
                
                $cache = \cache::make('auth_apoa', 'membership_category_approved_cache');

                $cachekey = "u_$user->id";

                $cache->delete($cachekey);

                if ($wantsurl = get_user_preferences('auth_email_wantsurl', false, $user)) {
                    // Ensure user gets returned to page they were trying to access before signing up.
                    $SESSION->wantsurl = $wantsurl;
                    unset_user_preference('auth_email_wantsurl', $user);
                }
                
                return AUTH_CONFIRM_OK;
            }
        } else {
            return AUTH_CONFIRM_ERROR;
        }
    }
    /**
 * Send email to specified user with confirmation text and activation link.
 *
 * @param stdClass $user A {@link $USER} object
 * @param string $confirmationurl user confirmation URL
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function send_confirmation_email($user, $confirmationurl = null) {
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new stdClass();
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $subject = get_string('emailconfirmationsubject', '', format_string($site->fullname));

    if (empty($confirmationurl)) {
        $confirmationurl = '/login/confirm.php';
    }

    $confirmationurl = new moodle_url($confirmationurl);
    // Remove data parameter just in case it was included in the confirmation so we can add it manually later.
    $confirmationurl->remove_params('data');
    $confirmationpath = $confirmationurl->out(false);

    // We need to custom encode the username to include trailing dots in the link.
    // Because of this custom encoding we can't use moodle_url directly.
    // Determine if a query string is present in the confirmation url.
    $hasquerystring = strpos($confirmationpath, '?') !== false;
    // Perform normal url encoding of the username first.
    $username = urlencode($user->username);
    // Prevent problems with trailing dots not being included as part of link in some mail clients.
    $username = str_replace('.', '%2E', $username);

    $data->link = $confirmationpath . ( $hasquerystring ? '&' : '?') . 'data='. $user->secret .'/'. $username;

    $message     = get_string('emailconfirmation', 'auth_apoa', $data);
    $messagehtml = text_to_html(get_string('emailconfirmation', 'auth_apoa', $data), false, false, true);

    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user($user, $supportuser, $subject, $message, $messagehtml);
}

    /**
 * Send email to specified user with confirmation text and activation link.
 *
 * @param stdClass $user A {@link $USER} object
 * @param string $confirmationurl user confirmation URL
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function send_federation_confirm_to_user($user) {
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new stdClass();
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

    $subject = get_string('emailconfirmationfederationtousersubject', 'auth_apoa', format_string($site->fullname));

    $confirmationurl = new moodle_url('/local/landingpage/index.php');
    // Remove data parameter just in case it was included in the confirmation so we can add it manually later.
    $confirmationurl->remove_params('data');
    $confirmationpath = $confirmationurl->out(false);

    $hasquerystring = strpos($confirmationpath, '?') !== false;

    // Perform normal url encoding of the username first.
    $username = urlencode($user->username);
    // Prevent problems with trailing dots not being included as part of link in some mail clients.
    $username = str_replace('.', '%2E', $username);

    $data->link = $confirmationpath;
    $data->username = $username;

    $message     = get_string('emailconfirmationfederationtouser', 'auth_apoa', $data);
    $messagehtml = text_to_html(get_string('emailconfirmationfederationtouser', 'auth_apoa', $data), false, false, true);

    // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
    return email_to_user($user, $supportuser, $subject, $message, $messagehtml);
}
    

        /**
     * Send email to specified user with confirmation text and activation link.
     *
     * @param stdClass $user A {@link $USER} object
     * @param string $confirmationurl user confirmation URL
     * @return bool Returns true if mail was sent OK and false if there was an error.
     */
    function send_confirmation_email_to_federation($user, $federation, $confirmationurl = null) {
        global $CFG;

        $site = get_site();
        $supportuser = core_user::get_support_user();

        $data = new stdClass();
        $data->sitename  = format_string($site->fullname);
        $data->admin     = generate_email_signoff();
        $data->fullname  = fullname($user);
        $subject = get_string('federationemailconfirmationsubject', 'auth_apoa', format_string($site->fullname));

        if(!$federationemail = get_config('auth_apoa', 'federationemail'.$federation)){
            throw new \moodle_exception('auth_emailnofederationemail', 'auth_apoa');
        }

        if (empty($confirmationurl)) {
            $confirmationurl = '/auth/apoa/federation_confirm.php';
        }

        $confirmationurl = new moodle_url($confirmationurl);
        // Remove data parameter just in case it was included in the confirmation so we can add it manually later.
        $confirmationurl->remove_params('data');
        $confirmationpath = $confirmationurl->out(false);

        // We need to custom encode the username to include trailing dots in the link.
        // Because of this custom encoding we can't use moodle_url directly.
        // Determine if a query string is present in the confirmation url.
        $hasquerystring = strpos($confirmationpath, '?') !== false;
        // Perform normal url encoding of the username first.
        $username = urlencode($user->username);
        // Prevent problems with trailing dots not being included as part of link in some mail clients.
        $username = str_replace('.', '%2E', $username);

        $data->link = $confirmationpath . ( $hasquerystring ? '&' : '?') . 'data='. $user->secret .'/'. $username;

        $message     = get_string('federationemailconfirmation', '', $data);
        $messagehtml = text_to_html(get_string('federationemailconfirmation', 'auth_apoa', $data), false, false, true);

        // Directly email rather than using the messaging system to ensure its not routed to a popup or jabber.
        return $this->email_to_federation($user, $federationemail, $supportuser, $subject, $message, $messagehtml);
    }

    /**
 * Send an email to a specified user
 *
 * @param stdClass $user  A {@link $USER} object
 * @param stdClass $from A {@link $USER} object
 * @param string $subject plain text subject line of the email
 * @param string $messagetext plain text version of the message
 * @param string $messagehtml complete html version of the message (optional)
 * @param string $attachment a file on the filesystem, either relative to $CFG->dataroot or a full path to a file in one of
 *          the following directories: $CFG->cachedir, $CFG->dataroot, $CFG->dirroot, $CFG->localcachedir, $CFG->tempdir
 * @param string $attachname the name of the file (extension indicates MIME)
 * @param bool $usetrueaddress determines whether $from email address should
 *          be sent out. Will be overruled by user profile setting for maildisplay
 * @param string $replyto Email address to reply to
 * @param string $replytoname Name of reply to recipient
 * @param int $wordwrapwidth custom word wrap width, default 79
 * @return bool Returns true if mail was sent OK and false if there was an error.
 */
function email_to_federation($user, $to,  $from, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '',
                       $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79) {

    global $CFG, $PAGE, $SITE;

    if (empty($user) or empty($user->id)) {
        debugging('Can not send email to null user', DEBUG_DEVELOPER);
        return false;
    }

    if (empty($to)) {
        debugging('Can not send email to federation without email: '.$user->id, DEBUG_DEVELOPER);
        return false;
    }

    if (!empty($user->deleted)) {
        debugging('Can not send email to deleted user: '.$user->id, DEBUG_DEVELOPER);
        return false;
    }

    if (defined('BEHAT_SITE_RUNNING')) {
        // Fake email sending in behat.
        return true;
    }

    if (!empty($CFG->noemailever)) {
        // Hidden setting for development sites, set in config.php if needed.
        debugging('Not sending email due to $CFG->noemailever config setting', DEBUG_NORMAL);
        return true;
    }

    if (email_should_be_diverted($to)) {
        $subject = "[DIVERTED {$to}] $subject";
        $to = $CFG->divertallemailsto;
    }

    // Skip mail to suspended users.
    if ((isset($user->auth) && $user->auth=='nologin') or (isset($user->suspended) && $user->suspended)) {
        return true;
    }

    if (!validate_email($to)) {
        // We can not send emails to invalid addresses - it might create security issue or confuse the mailer.
        debugging("email_to_federation: Federation $user->id (".fullname($user).") email ($to) is invalid! Not sending.");
        return false;
    }

    if (over_bounce_threshold($user)) {
        debugging("email_to_federation: User $user->id (".fullname($user).") is over bounce threshold! Not sending.");
        return false;
    }

    // TLD .invalid  is specifically reserved for invalid domain names.
    // For More information, see {@link http://tools.ietf.org/html/rfc2606#section-2}.
    if (substr($to, -8) == '.invalid') {
        debugging("email_to_user: User $user->id (".fullname($user).") email domain ($to) is invalid! Not sending.");
        return true; // This is not an error.
    }

    // If the user is a remote mnet user, parse the email text for URL to the
    // wwwroot and modify the url to direct the user's browser to login at their
    // home site (identity provider - idp) before hitting the link itself.
    if (is_mnet_remote_user($user)) {
        require_once($CFG->dirroot.'/mnet/lib.php');

        $jumpurl = mnet_get_idp_jump_url($user);
        $callback = partial('mnet_sso_apply_indirection', $jumpurl);

        $messagetext = preg_replace_callback("%($CFG->wwwroot[^[:space:]]*)%",
                $callback,
                $messagetext);
        $messagehtml = preg_replace_callback("%href=[\"'`]($CFG->wwwroot[\w_:\?=#&@/;.~-]*)[\"'`]%",
                $callback,
                $messagehtml);
    }
    $mail = get_mailer();

    if (!empty($mail->SMTPDebug)) {
        echo '<pre>' . "\n";
    }

    $temprecipients = array();
    $tempreplyto = array();

    // Make sure that we fall back onto some reasonable no-reply address.
    $noreplyaddressdefault = 'noreply@' . get_host_from_url($CFG->wwwroot);
    $noreplyaddress = empty($CFG->noreplyaddress) ? $noreplyaddressdefault : $CFG->noreplyaddress;

    if (!validate_email($noreplyaddress)) {
        debugging('email_to_user: Invalid noreply-email '.s($noreplyaddress));
        $noreplyaddress = $noreplyaddressdefault;
    }

    // Make up an email address for handling bounces.
    if (!empty($CFG->handlebounces)) {
        $modargs = 'B'.base64_encode(pack('V', $user->id)).substr(md5($to), 0, 16);
        $mail->Sender = generate_email_processing_address(0, $modargs);
    } else {
        $mail->Sender = $noreplyaddress;
    }

    // Make sure that the explicit replyto is valid, fall back to the implicit one.
    if (!empty($replyto) && !validate_email($replyto)) {
        debugging('email_to_federation: Invalid replyto-email '.s($replyto));
        $replyto = $noreplyaddress;
    }

    if (is_string($from)) { // So we can pass whatever we want if there is need.
        $mail->From     = $noreplyaddress;
        $mail->FromName = $from;
    // Check if using the true address is true, and the email is in the list of allowed domains for sending email,
    // and that the senders email setting is either displayed to everyone, or display to only other users that are enrolled
    // in a course with the sender.
    } else if ($usetrueaddress && can_send_from_real_email_address($from, $user)) {
        if (!validate_email($from->email)) {
            debugging('email_to_user: Invalid from-email '.s($from->email).' - not sending');
            // Better not to use $noreplyaddress in this case.
            return false;
        }
        $mail->From = $from->email;
        $fromdetails = new stdClass();
        $fromdetails->name = fullname($from);
        $fromdetails->url = preg_replace('#^https?://#', '', $CFG->wwwroot);
        $fromdetails->siteshortname = format_string($SITE->shortname);
        $fromstring = $fromdetails->name;
        if ($CFG->emailfromvia == EMAIL_VIA_ALWAYS) {
            $fromstring = get_string('emailvia', 'core', $fromdetails);
        }
        $mail->FromName = $fromstring;
        if (empty($replyto)) {
            $tempreplyto[] = array($from->email, fullname($from));
        }
    } else {
        $mail->From = $noreplyaddress;
        $fromdetails = new stdClass();
        $fromdetails->name = fullname($from);
        $fromdetails->url = preg_replace('#^https?://#', '', $CFG->wwwroot);
        $fromdetails->siteshortname = format_string($SITE->shortname);
        $fromstring = $fromdetails->name;
        if ($CFG->emailfromvia != EMAIL_VIA_NEVER) {
            $fromstring = get_string('emailvia', 'core', $fromdetails);
        }
        $mail->FromName = $fromstring;
        if (empty($replyto)) {
            $tempreplyto[] = array($noreplyaddress, get_string('noreplyname'));
        }
    }

    if (!empty($replyto)) {
        $tempreplyto[] = array($replyto, $replytoname);
    }

    $temprecipients[] = array($to, fullname($user));

    // Set word wrap.
    $mail->WordWrap = $wordwrapwidth;

    if (!empty($from->customheaders)) {
        // Add custom headers.
        if (is_array($from->customheaders)) {
            foreach ($from->customheaders as $customheader) {
                $mail->addCustomHeader($customheader);
            }
        } else {
            $mail->addCustomHeader($from->customheaders);
        }
    }

    // If the X-PHP-Originating-Script email header is on then also add an additional
    // header with details of where exactly in moodle the email was triggered from,
    // either a call to message_send() or to email_to_user().
    if (ini_get('mail.add_x_header')) {

        $stack = debug_backtrace(false);
        $origin = $stack[0];

        foreach ($stack as $depth => $call) {
            if ($call['function'] == 'message_send') {
                $origin = $call;
            }
        }

        $originheader = $CFG->wwwroot . ' => ' . gethostname() . ':'
             . str_replace($CFG->dirroot . '/', '', $origin['file']) . ':' . $origin['line'];
        $mail->addCustomHeader('X-Moodle-Originating-Script: ' . $originheader);
    }

    if (!empty($CFG->emailheaders)) {
        $headers = array_map('trim', explode("\n", $CFG->emailheaders));
        foreach ($headers as $header) {
            if (!empty($header)) {
                $mail->addCustomHeader($header);
            }
        }
    }

    if (!empty($from->priority)) {
        $mail->Priority = $from->priority;
    }

    $renderer = $PAGE->get_renderer('core');
    $context = array(
        'sitefullname' => $SITE->fullname,
        'siteshortname' => $SITE->shortname,
        'sitewwwroot' => $CFG->wwwroot,
        'subject' => $subject,
        'prefix' => $CFG->emailsubjectprefix,
        'to' => $to,
        'toname' => fullname($user),
        'from' => $mail->From,
        'fromname' => $mail->FromName,
    );
    if (!empty($tempreplyto[0])) {
        $context['replyto'] = $tempreplyto[0][0];
        $context['replytoname'] = $tempreplyto[0][1];
    }
    if ($user->id > 0) {
        $context['touserid'] = $user->id;
        $context['tousername'] = $user->username;
    }

    if (!empty($user->mailformat) && $user->mailformat == 1) {
        // Only process html templates if the user preferences allow html email.

        if (!$messagehtml) {
            // If no html has been given, BUT there is an html wrapping template then
            // auto convert the text to html and then wrap it.
            $messagehtml = trim(text_to_html($messagetext));
        }
        $context['body'] = $messagehtml;
        $messagehtml = $renderer->render_from_template('core/email_html', $context);
    }

    $context['body'] = html_to_text(nl2br($messagetext));
    $mail->Subject = $renderer->render_from_template('core/email_subject', $context);
    $mail->FromName = $renderer->render_from_template('core/email_fromname', $context);
    $messagetext = $renderer->render_from_template('core/email_text', $context);

    // Autogenerate a MessageID if it's missing.
    if (empty($mail->MessageID)) {
        $mail->MessageID = generate_email_messageid();
    }

    if ($messagehtml && !empty($user->mailformat) && $user->mailformat == 1) {
        // Don't ever send HTML to users who don't want it.
        $mail->isHTML(true);
        $mail->Encoding = 'quoted-printable';
        $mail->Body    =  $messagehtml;
        $mail->AltBody =  "\n$messagetext\n";
    } else {
        $mail->IsHTML(false);
        $mail->Body =  "\n$messagetext\n";
    }

    if ($attachment && $attachname) {
        if (preg_match( "~\\.\\.~" , $attachment )) {
            // Security check for ".." in dir path.
            $supportuser = core_user::get_support_user();
            $temprecipients[] = array($supportuser->email, fullname($supportuser, true));
            $mail->addStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
        } else {
            require_once($CFG->libdir.'/filelib.php');
            $mimetype = mimeinfo('type', $attachname);

            // Before doing the comparison, make sure that the paths are correct (Windows uses slashes in the other direction).
            // The absolute (real) path is also fetched to ensure that comparisons to allowed paths are compared equally.
            $attachpath = str_replace('\\', '/', realpath($attachment));

            // Build an array of all filepaths from which attachments can be added (normalised slashes, absolute/real path).
            $allowedpaths = array_map(function(string $path): string {
                return str_replace('\\', '/', realpath($path));
            }, [
                $CFG->cachedir,
                $CFG->dataroot,
                $CFG->dirroot,
                $CFG->localcachedir,
                $CFG->tempdir,
                $CFG->localrequestdir,
            ]);

            // Set addpath to true.
            $addpath = true;

            // Check if attachment includes one of the allowed paths.
            foreach (array_filter($allowedpaths) as $allowedpath) {
                // Set addpath to false if the attachment includes one of the allowed paths.
                if (strpos($attachpath, $allowedpath) === 0) {
                    $addpath = false;
                    break;
                }
            }

            // If the attachment is a full path to a file in the multiple allowed paths, use it as is,
            // otherwise assume it is a relative path from the dataroot (for backwards compatibility reasons).
            if ($addpath == true) {
                $attachment = $CFG->dataroot . '/' . $attachment;
            }

            $mail->addAttachment($attachment, $attachname, 'base64', $mimetype);
        }
    }

    // Check if the email should be sent in an other charset then the default UTF-8.
    if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {

        // Use the defined site mail charset or eventually the one preferred by the recipient.
        $charset = $CFG->sitemailcharset;
        if (!empty($CFG->allowusermailcharset)) {
            if ($useremailcharset = get_user_preferences('mailcharset', '0', $user->id)) {
                $charset = $useremailcharset;
            }
        }

        // Convert all the necessary strings if the charset is supported.
        $charsets = get_list_of_charsets();
        unset($charsets['UTF-8']);
        if (in_array($charset, $charsets)) {
            $mail->CharSet  = $charset;
            $mail->FromName = core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
            $mail->Subject  = core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
            $mail->Body     = core_text::convert($mail->Body, 'utf-8', strtolower($charset));
            $mail->AltBody  = core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));

            foreach ($temprecipients as $key => $values) {
                $temprecipients[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
            foreach ($tempreplyto as $key => $values) {
                $tempreplyto[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
            }
        }
    }

    foreach ($temprecipients as $values) {
        $mail->addAddress($values[0], $values[1]);
    }
    foreach ($tempreplyto as $values) {
        $mail->addReplyTo($values[0], $values[1]);
    }

    if (!empty($CFG->emaildkimselector)) {
        $domain = substr(strrchr($mail->From, "@"), 1);
        $pempath = "{$CFG->dataroot}/dkim/{$domain}/{$CFG->emaildkimselector}.private";
        if (file_exists($pempath)) {
            $mail->DKIM_domain      = $domain;
            $mail->DKIM_private     = $pempath;
            $mail->DKIM_selector    = $CFG->emaildkimselector;
            $mail->DKIM_identity    = $mail->From;
        } else {
            debugging("Email DKIM selector chosen due to {$mail->From} but no certificate found at $pempath", DEBUG_DEVELOPER);
        }
    }

    if ($mail->send()) {
        set_send_count($user);
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return true;
    } else {
        // Trigger event for failing to send email.
        $event = \core\event\email_failed::create(array(
            'context' => context_system::instance(),
            'userid' => $from->id,
            'relateduserid' => $user->id,
            'other' => array(
                'subject' => $subject,
                'message' => $messagetext,
                'errorinfo' => $mail->ErrorInfo
            )
        ));
        $event->trigger();
        if (CLI_SCRIPT) {
            mtrace('Error: lib/moodlelib.php email_to_federation(): '.$mail->ErrorInfo);
        }
        if (!empty($mail->SMTPDebug)) {
            echo '</pre>';
        }
        return false;
        }
    }

    public function get_subscription_headers(){
        global $DB;

        $columns = $DB->get_columns('auth_apoa');
        $unwantedcolumns = ['id', 'membershipnumber', 'email', 'membership_category', 'subscriptionends', 'lifemembership', 'country'];
        foreach($unwantedcolumns as $unwanted){
            unset($columns[$unwanted]);
        };
        return array_keys($columns);

    }

    public function enrol_existing_member($user){
        global $DB;
        $toenrolin = [];
        if($authrecord = $DB->get_record('auth_apoa', array('email' => $user->email))){
           $lifemember = $authrecord->lifemembership;
           $membershipcategory = $authrecord->membership_category;
           $subscriptionends = $authrecord->subscriptionends;
           $apoasubscription = get_config('auth_apoa', 'subscriptionapoa');

           if($lifemember){
                $toenrolin[$apoasubscription]  = 0;
           }
           else if ($subscriptionends > time()){
                $toenrolin[$apoasubscription] = $subscriptionends;
           }
            foreach($authrecord as $field => $value){
                $subscription = get_config('auth_apoa', 'subscription' . $field);
                if($subscription !== false){
                    if($value){
                        $toenrolin[$subscription] = 0;
                    }
                }  
            }
            if($toenrolin){
                if($membershipcategory != 'Federation Fellow'){
                    $user->profile_field_membership_category_approved = 1;
                }
                $user->profile_field_hasactivesubscription = 1;
                profile_save_data($user);
            }
             foreach($toenrolin as $courseid => $enddate){
                $plugin = enrol_get_plugin('manual');
                $instances = enrol_get_instances($courseid, true);
                foreach($instances as $instance){
                    if($instance->enrol == 'manual'){
                        break;
                    }
                }
                if($instance){
                    $plugin->enrol_user($instance, $user->id, 5, 0, $enddate);
                }
            }
        }
        
        return false;
    }
}
