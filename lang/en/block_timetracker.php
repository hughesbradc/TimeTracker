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

$string['datetimeformat']='%m/%d/%y %I:%M %p';
$string['timeformat']='%I:%M %p';
$string['dateformat']='%b %e, %Y';

$string['couldnotclockout'] = 'Error adding clock out'; 
$string['trackermethod'] = 'Which timetracking method would you like to use?';
$string['pluginname'] = 'TimeTracker';
$string['timetracker:manageworkers'] = 'Manage workers';
$string['timetracker:activateworkers'] = 'Activate workers';
$string['timetracker:manageoldunits'] = 'Manage units from a previous month';
$string['timetracker:managepayrate'] = 'Manage the pay rate for work units';
$string['notactiveerror'] = 'This worker is not authorized to work.';
$string['defaultworkerconfig'] = 'Default Worker Configuration';
$string['workername'] = 'Worker Name';

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
$string['remaining'] = 'Remaining: ';
$string['totalytd'] = 'Year to date: ';
$string['total'] = 'Total: ';
$string['manage'] = 'Manage';
$string['registerinfo'] = 'Register Work-Study Information';


// Strings for Employee Home Page (also includes strings from the 'block' section above)
$string['welcome'] = 'Welcome';
$string['currentstats'] = 'Current Statistics for {$a}';
$string['contact'] = 'Contact: ';
$string['supervisor'] = 'Supervisor';
$string['finaid'] = 'Financial Aid Work-Study Coordinator';


// Strings for Employee Hourlog Page
$string['hourlogtitle'] = 'Hourlog for {$a}';
$string['addentry'] = 'Add work unit';
$string['date'] = 'Date';
$string['timein'] = 'Time in';
$string['timeout'] = 'Time out'; // For context-sensitive help
$string['duration'] = 'Duration: ';
$string['hours'] = 'hours';
$string['minutes'] = 'minutes';
$string['previousentries'] = 'Previous entries';
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
$string['timeperiod'] = 'Time period';
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
$string['editunittitle'] = 'Editing work unit for {$a}';


// Strings for Supervisor Reports Page
$string['hourlogheader'] = 'Hourlog';
$string['iemployee'] = 'Individual Employee';
$string['selemployee'] = 'Select Employee';
$string['aemployees'] = 'All Employees';


//Add Unit Page
$string['addunittitle'] = 'Add Work Unit for {$a}';


// Strings for 'Update Worker Information' Page
$string['emplinfo'] = 'Employee Information';
$string['eidirection'] = 'Select an employee to edit';
$string['sitesettings'] = 'Site Settings';
$string['positions'] = 'Positions';
$string['addposition'] = 'Add Position';
$string['export'] = 'Export';
$string['positiontitle'] = 'Position Title';
$string['budget'] = 'Budget Number';
$string['idnum'] = 'ID Number';
$string['address'] = 'Address';
$string['phone'] = 'Phone';
$string['position'] = 'Position';
$string['currpayrate'] = 'Current Pay Rate';
$string['trackermethod'] = 'Tracker Method';
$string['maxtermearnings'] = 'Maximum Term Earnings';
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
$string['deleteunit'] = 'Delete this work unit?'; 
$string['messageforerror'] = 'Message:  '; 
$string['selectallnone'] = 'Select all or none';
$string['existingunit'] = '<b>Original Work Unit Data:</b>';
$string['existingtimein'] = '<b>Original Time In:</b>  {$a}';
$string['existingtimeout'] = '<b>Original Time Out:</b>  {$a}';
$string['existingduration'] = '<b>Duration:</b>  {$a}';
$string['approvedsuccess'] = 'The error approval has been processed successfully!  
Please wait to be redirected.'; 

//Manage Unit Alerts
$string['previous'] = 'Original Work Unit';
$string['proposed'] = 'Proposed Work Unit';
$string['message'] = 'Message';
$string['noalerts'] = 'There are no alerts at this time.';
$string['managealerts'] = 'Manage Alerts&nbsp; ';
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
$string['emessage2'] = '<b>Original Data:</b>';
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
$string['emessagedelete'] = 'Delete';
$string['emessagechange'] = 'Change';
$string['emessagedeny'] = 'Deny';

$string['approvedsubject'] = 'Error Alert Approved for {$a}';
$string['amessage1'] = 'Sent on behalf of {$a}:';
$string['amessage2'] = 'The following work unit has been approved:';
$string['approveddata'] = '<b>Approved Data:</b>';
$string['unitdeleted'] = 'This work unit has been deleted.';

/**  ALERT DELETED MESSAGES **/
$string['deletedsubject'] = 'Error Alert Deleted for {$a}';
$string['deletemessage1'] = 'The following alert unit data was deleted:';
$string['alertdeletesuccess'] = 'Error alert deleted for {$a}';
$string['alertdeletefailure'] = 'Error removing the alert for {$a}';
$string['deletemessage1'] = 'The following alert unit data was removed:';

/** END ALERT DELETED MESSAGES **/


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
$string['invalidtimesheetid'] = 'Timesheet ID does not exist for this user';
$string['timesheettitle'] = 'Timesheet Report';

//Context-Sensitive Help
//Configuration Page
$string['maxtermearnings_help'] = 'The maximum hours the worker is allowed to work per term. <br /> 
    Enter decimal hours (example: 750.00) or zero (0) for unlimited.';
$string['currpayrate_help'] = 'The hourly rate of pay the worker will receive. <br /> Enter decimal currency 
    (Example: 7.50).';
$string['round'] = 'Round to the nearest N seconds (default: 900)';
$string['round_help'] = 'The number of seconds used for rounding work units. The default
is to round to the nearest quarter hour (15 minutes), which is 900 seconds. A value of
\'0\' here means that there will be no rounding.';
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
$string['payrate'] = 'Pay rate';
$string['payrate_help'] = 'Input the hourly wage for this work unit';

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
$string['startreport'] = 'Starts'; 
$string['startreport_help'] = 'Select the beginning date to generate data for the desired reporting period. Begin time is 12:00am';
$string['endreport'] = 'Ends before';
$string['endreport_help'] = 'Select the end date to generate data for the desired reporting period. Only work units that end before this date will be displayed';
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

//Electronic Signatures
$string['timesheet'] = 'Timesheet';
$string['signbutton'] = 'Sign timesheet';
$string['signbuttonsup'] = 'Sign selected timesheet(s)';
$string['signature'] = 'Signature';
$string['signheader'] = 'Sign Timesheets';
$string['editwarning'] = 'Are you sure you wish to edit this timesheet?  Doing so will remove the student\'s signature and require him or her to re-sign before the timesheet can be approved.';
$string['viewofficial'] = 'View Official Timesheets';
$string['workerstatement'] = 'I certify that the hours reported on this timesheet are true, correct, and are within the student\'s allotted maximum earnings.  I understand that if my timesheet is not signed by the due date, I will not be paid for these hours until the next pay period.';
$string['supervisorstatement'] = 'I certify that the hours reported on the selected timesheet(s) are true, correct, and are within each student\'s allotted maximum earnings. I understand that if the timesheets are not signed by the due date, the students will not be paid for these hours until the next pay period.';
$string['clicktosign'] = 'Click here to sign:';
$string['supsignerror'] = '<B>ERROR: A supervisor cannot sign a student\'s timesheet.</B>';
$string['notstosign'] = '<b><center>There are no timesheets requiring your signature
    at this time.</center></b>';
$string['signtsheading'] = 'Sign Timesheet';
$string['rejectts'] = 'Reject Timesheet';
$string['nocourseserror'] = 'There are no courses in this category.';
$string['noworkerserror'] = 'There are no workers enrolled in this course.';
$string['nounits'] = 'ERROR: There are no units to be signed.'; 


//Deny Official Timesheet Strings
$string['rejecttstitle'] = 'Reject Timesheet';
$string['tssubject'] = 'TimeTracker Timesheet Rejected';
$string['reject'] = 'Reject';
$string['headername'] = '<b>Reject Timesheet for {$a}</b>';
$string['headertimestamp'] = ' <b>signed on {$a}</b>';
$string['remessage1'] = 'Your timesheet signed on {$a}';
$string['remessagesup'] = ' was rejected by your supervisor for the following reason(s):';
$string['remessageadmin'] = ' was rejected by an administrator for the following reason(s):';
$string['rejectreason'] = 'Reason(s):';
$string['instruction'] = 'Please contact your supervisor for more information and to re-sign the
    timesheet.';
$string['remessagesent'] = 'The student has been notified that his or her timesheet has been edited
    and requires a new signature.';
