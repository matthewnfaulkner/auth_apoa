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
 * Plugin strings are defined here.
 *
 * @package     auth_apoa
 * @category    string
 * @copyright   2022 Matthew<you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();



$string['pluginname'] = 'APOA Authentication';

$string['auth_emailnofederationemail'] = 'No email address for this federaion';
$string['pathnewtitle'] = 'New Member';
$string['passwordagain'] = 'Re-enter password';
$string['pathexistingtitle'] = 'Exisiting Member';
$string['pathnewdesc'] = 'Select if you have never had an APOA membership or are a member through your national federation';
$string['pathexistingdesc'] = 'Select if you have an existing APOA membership.';
$string['checkexistingemail'] = 'Check for exisiting membership';
$string['emailexists'] = 'Email exists';
$string['emaildoesnotexist'] = 'We have no record of that email address. Have another email? If not you can proceed to make a new account. Once created you can message support to get any active subscriptions updated.';
$string['exisitinguserheader'] = 'Sign Up Existing Member';
$string['makenewaccount'] = "Make new account";
$string['subscriptionmapping'] ='Subscription Mapping';
$string['subscriptionmapping_desc'] ='Determines the mapping from subscription information contained within the existing user table with actual subscription courses.';

$string['federationemailsheader'] = 'Federation Emails';
$string['federationemails'] = 'Email addresses for each federation. This address is where federation confirms are sent after a user signs up as a federation member.';


$string['federationemailsent'] = "Your Federation has been notified of your registration.
\nFor now you only have a temporary and limited access.
\nOnce your federation confirms your membership you will be granted full access.";
$string['federationemailalreadysent'] = "Your federation has already been notified, 
please allow them time to confirm your membership.";
$string['federationconfirmed'] = "{$a} has been approved as a federation member of the APOA.";
$string['federationemailconfirm'] = 'Confirm your account';
$string['federationemailconfirmation'] = 'Hi,

A new account has been requested at \'{$a->sitename}\'
for {$a->fullname}. They have done so as a member of your federation.

Can you please confirm this user is a member of your organisation and then please go to this web address:

<a href="{$a->link}"> Confirm Federation Member</a>

This will confirm his membership of the Asia Pacific Orthopeadic Association as a Federation member.

In most mail programs, this should appear as a blue link
which you can just click on.  If that doesn\'t work,
then cut and paste the address into the address
line at the top of your web browser window.

If you need help, please contact the site administrator,
{$a->admin}';
$string['federationemailconfirmationresend'] = 'Resend federation confirmation email';
$string['federationemailconfirmationsubject'] = '{$a}: federation memebership confirmation';
$string['federationemailconfirmsent'] = '<p>An email should have been sent to your federations email at <b>{$a}</b></p>
   <p>It contains easy instructions for them to confirm your federation membership.</p>
   <p>If you continue to have difficulty, contact the site administrator.</p>';
$string['federationemailconfirmsentfailure'] = 'Federation confirmation email failed to send';
$string['federationemailconfirmsentsuccess'] = 'Federation confirmation email sent successfully';
$string['passwordagain'] = "Reenter password.";

$string['emailconfirmationfederationtousersubject'] = 'Federation Membership Confirmation';
$string['emailconfirmationfederationtouser'] = 'Hi {$a->username}, 

your membership has been confirmed by your federation.

As a member you now have access to more features.

<a href="{$a->link}">Learn More</a>

Thanks,
{$a->admin}';
$string['emailconfirmation'] = 'Hi,

A new account has been requested at \'{$a->sitename}\'
using your email address.

To confirm your new account, please click the link below.

<a href="{$a->link}">Confirm Email</a>


If you need help, please contact the site administrator,
{$a->admin}';

$string['welcomemessage'] = 'Hi {$a->firstname}, welcome to APOA online. I\'m site support if you have any issues feel free to ask them here. Or fill in this <a href="{$a->supportlink}" style="text-decoration: underline;">support form</a>';
$string['membership_category_help'] =
   "
   <b>Honorary Fellow</b>, being a person of distinction approved by
   the Council in recognition of his
   <br>
   <b>Senior Fellow</b>, being a Fellow who has retired from active
   practice.
   <br>
   <b>Life Fellow</b>, being a fully trained orthopaedic surgeon in active
   practice for at least 5 years and who is a member of the
   Recognised Organisation of a country, territory or area and
   who has been nominated by two existing Life fellows and has
   paid the subscription set by the Executive Committee. Life
   Fellow will enjoy special recognition and may be granted
   special privileges set by the Executive Committee.
   <br>
   <b>Fellow</b>, being a fully trained orthopaedic surgeon in active
   practice, who is a member of an organisation recognised by the
   Council, and who has paid the subscription set by the
   Executive Committee.
   <br>
   <b>Associate Fellow</b>, being a doctor of a medical specialty other
   than orthopaedics and being a member of an organisation
   recognised by the Council, and who has paid the subscription
   set by the Executive Committee.
   <br>
   <b>Affiliate Fellow</b>, being a member of a paramedical profession,
   a scientist, or an organisation recognised by the Council, and
   who has paid the subscription set by the Executive Committee.
   <br>
   <b>Trainee Fellow</b>, being a medical practitioner undergoing
   structured training in orthopaedic surgery and who has paid the
   subscription set by the Executive Committee.
   <br>
   <b>Federation Member</b> being an organisation or a recognised
   National Orthopaedic Association of a country, territory, or
   area, within the Asia Pacific region, admitted by the Council as
   an Orthopaedic Association.
   <br>
   <b>Affiliate Federation Member</b>, being an organisation of a
   country, territory or area, outside the Asia Pacific region
   admitted by the Council as an Affiliate National Orthopaedic
   Association member. Affiliate NOA member has restricted
   rights as set by the Executive Committee.
   <br>
   ";
$string['noemailmode'] = "No Email Mode";
$string['noemailmode_desc'] = "Confirmation Emails will no longer be sent but their responses will be simulated. Used for development";
$string['usernamepolicy'] = "Usernames must be all lowercase with no special characters.";


$string['adduserheader'] = 'User Details';
$string['adduser'] = 'Add User';
$string['subscriptionend'] = 'Date of end of subscription';
$string['subscriptionend_help'] = 'Enter the date when the subscription is due to run out. Leave disabled if subscription is for lifetime';
$string['subsections'] = 'Subsection subscriptions';
$string['spinemembershipnumber'] = 'Membership Number to Spine Section, if applicable';
$string['membershipnumber'] = 'APOA Membership Number';
$string['maxspinemembershipnumber'] = 'Current Highest APSS Membership Number';
$string['maxmembershipnumber'] = 'Current Highest APOA Membership Number';


$string['footandankle'] = "Foot & Ankle";
$string['handandupperlimb'] = 'Hand & Upper Limb';
$string['hip'] = 'Hip';
$string['infection'] = 'Infection Section';
$string['knee'] = 'Knee';
$string['research'] = 'Orthopaedic Research';
$string['osteoporosis'] = 'Osteoporosis';
$string['paediatrics'] = 'Paediatrics';
$string['spine'] = 'Spine';
$string['sports'] = 'Sports Injury';
$string['trauma'] = 'Trauma';
$string['waves'] = 'WAVES';
$string['apoa'] = 'APOA';