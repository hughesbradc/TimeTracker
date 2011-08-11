<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify it under the terms of the GNU
// General Public License as published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
// the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
// Public License for more details.
//
// You should have received a copy of the GNU General Public License along with Moodle.  If not, see
// <http://www.gnu.org/licenses/>.

/** Strings for component 'block_timetracker', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    Block @subpackage TimeTracker @copyright  2011 Marty Gilbert & Brad Hughes @license
 * http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['datetimeformat']='%m/%d/%y, %I:%M %p';
$string['timeformat']='%I:%M%p';

$string['couldnotclockout'] = 'Error adding clock out'; 
$string['trackermethod'] = 'Which timetracking method would you like to use?';
$string['pluginname'] = 'TimeTracker';
$string['timetracker:manageworkers'] = 'Manage Workers';
$string['timetracker:activateworkers'] = 'Activate Workers';
$string['notactiveerror'] = 'You are not authorized to work.  Please contact your supervisor.';
$string['defaultworkerconfig'] = 'Default Worker Configuration';


//Strings for Block Settings
$string['workerdisplaysettings'] = 'Worker Display Settings';
$string['showtotalhours'] = 'Display total hours worked';
$string['showtermhours'] = 'Show hours this term';
$string['showmonthhours'] = 'Show month hours';
$string['showytdhours'] = 'Show year to date hours';
$string['showmonthearnings'] = 'Show earnings this month';
$string['showtermearnings'] = 'Show earnings this term';
$string['showytdearnings'] = 'Show year to date earnings';
$string['showtotalearnings'] = 'Show total earnings';
$string['department'] = 'Department';
$string['position'] = 'Position';
$string['budget'] = 'Budget Number';
$string['institution'] = 'Institution Name';
$string['supname'] = 'Supervisor(s) Name(s)';


// Strings for Page Tabs
$string['home'] = 'Home';
$string['hourlog'] = 'Hourlog';
$string['reports'] = 'Reports';
$string['settings'] = 'Settings';


// Global Strings
$string['datefrom'] = 'Date from ';
$string['dateto'] = ' to ';
$string['submitbutton'] = 'Submit';
$string['savebutton'] = 'Save';
$string['generatebutton'] = 'Generate';


// Strings for Block
$string['blocktitle'] = 'TimeTracker';
$string['hourstitle'] = 'Hours';
$string['earningstitle'] = 'Earnings';
$string['totalmonth'] = 'This month: ';
$string['totalterm'] = 'This term: ';
$string['totalytd'] = 'Year to date: ';
$string['total'] = 'Total: ';
$string['manage'] = 'Manage';
$string['registerinfo'] = 'Register Work Study Information';


// Strings for Employee Home Page (also includes strings from the 'block' section above)
$string['welcome'] = 'Welcome';
$string['currentstats'] = 'Current Statistics for {$a}';
$string['contact'] = 'Contact: ';
$string['supervisor'] = 'Supervisor';
$string['finaid'] = 'Financial Aid Work Study Coordinator';


// Strings for Employee Hourlog Page
$string['hourlogtitle'] = 'Hourlog for {$a}';
$string['addentry'] = 'Add Entry';
$string['date'] = 'Date';
$string['timein'] = 'Time In';
$string['timeout'] = 'Time Out'; // For context-sensitive help
$string['duration'] = 'Duration: ';
$string['hours'] = 'hours';
$string['minutes'] = 'minutes';
$string['previousentries'] = 'Previous Entries';
$string['noprevious'] = 'You have no previous entries.';

$string['timeclocktitle'] = 'Timeclock';
$string['clockinouttitle'] = 'Clock in/out';
$string['clockedout'] = 'You are not currently clocked in.';
$string['clockedin'] = 'You are currently clocked in.';
$string['clockinlink'] = 'Clock In';
$string['clockoutlink'] = 'Clock Out';
$string['pendingtimestamp'] = 'You have a pending timestamp: ';


// Strings for Employee Reports Page
$string['reportstitle'] = 'Reports';
$string['timeperiod'] = 'Time Period';
$string['cumulative'] = 'Cumulative';


// String for Supervisor Home Page
$string['homedefinition'] = 'Here is a summary of the last 10 employee activities: ';


// Strings for Supervisor Hourlog Page
$string['hourlogdefinition'] = 'Please choose an employee to view/edit hourlog.';
$string['headerdate'] = 'Date';
$string['headertimein'] = 'Time In';
$string['headertimeout'] = 'Time Out';
$string['headeredit'] = 'Edit';
$string['headerdelete'] = 'Delete';
$string['editunittitle'] = 'Editing workunit for {$a}';


// Strings for Supervisor Reports Page
$string['hourlogheader'] = 'Hourlog';
$string['iemployee'] = 'Individual Employee';
$string['selemployee'] = 'Select Employee';
$string['aemployees'] = 'All Employees';


// Strings for 'Update Worker Information' Page
$string['emplinfo'] = 'Employee Information';
$string['eidirection'] = 'Select an employee to edit';
$string['sitesettings'] = 'Site Settings';
$string['positions'] = 'Positions';
$string['addposition'] = 'Add Position';
$string['export'] = 'Export';
$string['positiontitle'] = 'Position Title';
$string['budget'] = 'Budget Number';
$string['firstname'] = 'First Name';
$string['lastname'] = 'Last Name';
$string['idnum'] = 'ID Number';
$string['address'] = 'Address';
$string['phone'] = 'Phone';
$string['position'] = 'Position';
$string['currpayrate'] = 'Current Pay Rate';
$string['trackermethod'] = 'Tracker Method';
$string['maxtermearnings'] = 'Maximum Earnings Per Term';
$string['comments'] = 'Comments';
$string['manageworkers'] = 'Worker Information';
$string['active'] = 'Active';
$string['firstname'] = 'First name';
$string['lastname'] = 'Last name';
$string['email'] = 'Email address';
$string['noworkers'] = 'No workers listed';
$string['updateformheadertitle'] = 'Update Worker Information';
$string['manageworkertitle'] = 'Manage Workers';


// Strings for 'Error Alert'
$string['errortitle'] = 'Error Alert for {$a}';
$string['sendbutton'] = 'Send';
$string['to'] = 'To:  '; 
$string['subject'] = 'Subject:  '; 
$string['subjecttext'] = 'TimeTracker Work Unit Error Alert for {$a}'; 
$string['data'] = 'Change Work Unit Data to:  '; 
$string['date'] = 'Date:  '; 
$string['timeinerror'] = 'Time In:  '; 
$string['timeouterror'] = 'Time Out:  '; 
$string['deleteunit'] = 'Delete this work unit'; 
$string['messageforerror'] = 'Message:  '; 
$string['selectallnone'] = 'Select all or none';
$string['existingunit'] = '<b>Existing Work Unit Data:</b>';
$string['existingtimein'] = '<b>Existing Time In:</b>  {$a}';
$string['existingtimeout'] = '<b>Existing Time Out:</b>  {$a}';
$string['existingduration'] = '<b>Duration:</b>  {$a}';
$string['approvedsuccess'] = 'The error approval has been processed successfully!  Please wait to be
    redirected.'; 


//Manage Unit Alerts
$string['previous'] = 'Current Work Unit';
$string['proposed'] = 'Proposed Work Unit';
$string['noalerts'] = 'No Alerts at this Time';
$string['managealerts'] = 'Manage Alert Work Units';
$string['managealerts_help'] = 'This page allows the supervisor or administrator to manage alerted work units
    Typically, this indicates that the worker has issued a dispute with the recorded time, and
    has notified his/her supervisor of the error. The three options afforded are:
    <ul>
    <li><b>Approve</b> - Accept the workers propsed changes to the work unit.</li>
    <li><b>Change</b> - Modify the work unit</li>
    <li><b>Deny</b> - Deny the request and leave the original work unit intact.</li>
    </ul>';
$string['changealert'] = 'Change Work Unit Error Alert for {$a}';
$string['changeto'] = 'Change To:';

// Error Alert Email
$string['emessage1'] = 'Please review the following TimeTracker error alert data:';
$string['emessage2'] = '<b>Existing Data:</b>';
$string['emessage3'] = 'Time In:  {$a}';
$string['emessage4'] = 'Time Out:  {$a}';
$string['emessageduration'] = 'Duration:  {$a}';
$string['emessage5'] = '<b>Proposed Data:</b>';
$string['emessagedelete'] = 'Delete this work unit';
$string['emessage6'] = 'Message: {$a}';
$string['br1'] = '<br />';
$string['br2'] = '<br /><br />';
$string['hr'] = '<hr>';
$string['emessageavailable'] = '<b>Available Actions:</b>';
$string['emessagedisclaimer'] = '(Please note that you will be prompted to sign into Moodle in order to 
    complete the request).';
$string['emessageapprove'] = 'Approve';
$string['emessagechange'] = 'Change';
$string['emessagedeny'] = 'Deny';

$string['approvedsubject'] = 'Error Alert Approved for {$a}';
$string['amessage1'] = 'Sent on behalf of {$a}:';
$string['amessage2'] = 'The following work unit has been approved:';
$string['approveddata'] = '<b>Approved Data:</b>';
$string['unitdeleted'] = 'This work unit has been deleted.';

$string['denysubject'] = 'Error Alert Denied for {$a}';
$string['dmessage1'] = 'The following alert unit data was denied:';

$string['emessagesent'] = 'Your notification has been sent to the selected supervisor(s)!';
$string['approvesuccess'] = 'You have successfully approved the error alert request.  The worker and any
    other supervisor(s) will be notified.';
$string['denysuccess'] = 'You have successfully denied the error alert request.  The worker and any
    other supervisor(s) will be notified.';

$string['changeapproved'] = 'This work unit has been approved with the following change:';
$string['deletewarning'] = 'Warning: You are about to delete this work unit.  Are you sure?';


//Error Codes
$string['notpermissible'] = 'You do not have permissions to perform this action';
$string['errordeleting'] = 'Invalid session key or user id';
$string['usernotexist'] = 'User does not exist';
$string['alreadyapproved'] = 'This work unit has already been approved by {$a}';

$string['timesheettitle'] = 'Timesheet Report';

//Context-Sensitive Help
//Configuration Page
$string['maxtermearnings_help'] = 'The maximum hours the worker is allowed to work per term. <br /> 
    Enter decimal hours (example: 750.00) or zero (0) for unlimited.';
$string['currpayrate_help'] = 'The hourly rate of pay the worker will receive. <br /> Enter decimal currency 
    (Example: 7.50).';
$string['showtotalhours_help'] = 'Shows the worker the overall total number of hours worked. <br />
    Yes = Enabled <br /> No = Disabled';
$string['showtermhours_help'] = 'Shows the worker the total number of hours worked for the current term. 
    <br /> Yes = Enabled <br /> No = Disabled';
$string['showmonthhours_help'] = 'Shows the worker the total number of hours worked for the current month. 
    <br /> Yes = Enabled <br /> No = Disabled';
$string['showytdhours_help'] = 'Shows the worker the total number of hours worked year to date. <br /> 
    Yes = Enabled <br /> No = Disabled';
$string['showmonthearnings_help'] = 'Shows the worker the total amount earned for the current month. <br /> 
    Yes = Enabled <br /> No = Disabled';
$string['showtermearnings_help'] = 'Shows the worker the total amount earned for the current term. <br />
    Yes = Enabled <br /> No = Disabled';
$string['showytdearnings_help'] = 'Shows the worker the total amount earned year to date. <br /> Yes = Enabled
    <br /> No = Disabled';
$string['showtotalearnings_help'] = 'Shows the worker the overall total amount earned. <br /> Yes = Enabled <
    br /> No = Disabled';
$string['idnum_help'] = 'The worker\'s identification number';
$string['department_help'] = 'The name of the department that employs the worker.';
$string['position_help'] = 'The worker\'s position title.';
$string['budget_help'] = 'The budget number that the worker\'s paycheck will be drafted from.';
$string['institution_help'] = 'The name of the institution the worker is employed by.';
$string['supname_help'] = 'The name(s) of the worker\'s supervisor(s).';
//Hourlog
$string['timein_help'] = 'Choose the date and time in which your shift began.';
$string['timeout_help'] = 'Choose the date and time in which your shift ended.';
//Manage Workers
$string['manageworkers_help'] = 'This page allows the supervisor or administrator to manage workers.
<br /><br /> Active allows the supervisor and/or administrator to allow or deny the worker to log
hours. <br /> For instance, if a worker hasn\'t completed all of the necessary paperwork to begin
work, the worker isn\'t marked active and isn\'t allowed to log hours.  Once the worker has
completed all necessary paperwork, the administrator sets the worker as \'active\' and the worker
can begin to log hours. <br /><br /> Checked = active <br /> Unchecked = inactive<hr>
First Name: The worker\'s first name <hr>
Last Name:  The worker\'s last name <hr>
Email Address:  The worker\'s email address <hr>
Last Work Unit:  The worker\'s last time in, time out (if worker is currently not on the clock), and duration of the shift. <hr>
Action Icons (from left to right):  Update Worker Information, Reports, Delete.  <br /><br /> Update Worker Information allows the administrator or supervisor to edit worker data. <br /><br /> Reports displays various worker summary data. <br /><br /> Delete will delete the worker and all data generated by the worker from the database.';
//Reports
$string['startreport'] = 'Report start date';
$string['startreport_help'] = 'Select the beginning date to generate data for the desired reporting period.';
$string['endreport'] = 'Report end date';
$string['endreport_help'] = 'Select the end date to generate data for the desired reporting period.';
//Update Worker Information Help (pulled from configuration page: department, budget, position, insitution, and supervisor name)
$string['firstname_help'] = 'The worker\'s first name.';
$string['lastname_help'] = 'The worker\'s last name.';
$string['email_help'] = 'The worker\'s email address.';
$string['address_help'] = 'The worker\'s address.';
$string['phone_help'] = 'The worker\'s phone number';
$string['currpayrate_help'] = 'The worker\'s current rate of pay. <br />Enter $7.75 as 7.75';
$string['trackermethod_help'] = 'The method the worker will use to track hours. <br /><br /> TimeClock has the worker clock in at the beginning of his or her shift and clock out at the conclusion of his or her shift. <br /> Hourlog has the worker choose the date and time he or she began the work shift and ended the work shift. <br /><br />This method is set automatically from the TimeTracker configuration page.  The Tracking Method can be changed for individual workers.';
$string['messageforerror_help'] = 'Allows the worker to send a message regarding the work unit error
to his or her supervisor.';
$string['deleteunit_help'] = 'Request that your supervisor delete this work unit.';

$string['workerid'] = 'Worker(s)';
$string['workerid_help'] = 'Select one or more workers that you wish to generate the report
    for.  <br /><br />
    HINT: To select multiple workers, hold the \'Control\' (CTRL) button and click the desired users.';
$string['month'] = 'Month';
$string['month_help'] = 'Select the month that you wish to generate the report for.';
$string['year'] = 'Year';
$string['year_help'] = 'Select the year that you wish to generate the report for.';
$string['fileformat'] = 'File Format';
$string['fileformat_help'] = 'Select the file format in which you wish to generate the report.'; 

//Terms
$string['terms_title'] = 'Edit Terms';
