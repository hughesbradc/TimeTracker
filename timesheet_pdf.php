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
 * This block will display a summary of hours and earnings for the worker.
 *
 * @package    Block
 * @subpackage TimeTracker
 * @copyright  2011 Marty Gilbert & Brad Hughes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');
require_once('../../lib/tcpdf/tcpdf.php');

global $CFG;

$month = required_param('month', PARAM_INTEGER);
$year = required_param('year', PARAM_INTEGER);
$userid = required_param('userid', PARAM_INTEGER);
$courseid = required_param('id', PARAM_INTEGER);

$monthinfo = get_month_info($month, $year);


$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$PAGE->set_course($course);
$context = $PAGE->context;

$workerrecord = $DB->get_record('block_timetracker_workerinfo', 
    array('id'=>$userid,'courseid'=>$courseid));

if(!$workerrecord){
    print_error('usernotexist', 'block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$courseid);
}

// Collect Data
$mdluser= $DB->get_record('user', array('id'=>$workerrecord->mdluserid));
$conf = get_timetracker_config($courseid);

$canmanage = false;
if (has_capability('block/timetracker:manageworkers', $context)) { //supervisor
    $canmanage = true;
}

if(!$canmanage && $USER->id != $workerrecord->mdluserid){
    print_error('notpermissible', 'block_timetracker',
        $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$courseid);
}

$sql = 'SELECT * from ' .$CFG->prefix.'block_timetracker_workunit '. 'WHERE userid='.$userid.
    ' AND courseid='.$courseid.' AND timein BETWEEN '. $monthinfo['firstdaytimestamp'] .' AND '.
    $monthinfo['lastdaytimestamp']. ' ORDER BY timein';

$units = $DB->get_recordset_sql($sql);

// ********** BEGIN PDF ********** //
// Create new PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set Document Data
$pdf->setCreator(PDF_CREATOR);
$pdf->SetFont('helvetica', '', 8);
$pdf->SetCellPadding(0);
$pdf->SetTitle('Timesheet_'.$monthinfo['monthname'].'_'.$year);
$pdf->SetAuthor('TimeTracker');
$pdf->SetSubject(' ');
$pdf->SetKeywords(' ');

// Remove Default Header/Footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add Page
$pdf->AddPage();

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);//set some language-dependent strings

// ********** HEADER ********** //
$htmldoc = '
<table cellspacing="0" cellpadding="0">
    <tr>
        <td align="center"><font size="10"><b>'.$conf['institution'].'</b></font></td>
    </tr>
    <tr>
        <td align="center"><font size="10"><b>Timesheet - '.$monthinfo['monthname'].', '.$year.'</b></font>
        </td>
    </tr>
</table>
<hr style="height: 1px" />
';

$pdf->writeHTML($htmldoc, true, false, false, false, '');

// ********** WORKER AND SUPERVISOR DATA ********** //
$htmldoc = '

<table cellspacing="0" cellpadding="0">
    <tr>
        <td><font size="8"><b>'.$workerrecord->lastname.', '.$workerrecord->firstname.'<br />'.
        $workerrecord->idnum.'<br />'.
        $workerrecord->address.'<br />
        YTD Earnings: $ '.get_earnings_this_year($userid,$courseid).'</b></font></td>
        <td><font size="8"><b>'.$conf['supname'].'<br />'.
        $conf['department'].'<br />'.
        $conf['position'].'<br />'.
        $conf['budget'].'</b></font></td>
    </tr>
</table>

';

$pdf->writeHTML($htmldoc, true, false, false, false, '');

// ********** CALENDAR DAYS HEADER (Sun - Sat) ********** //
// ********** CALENDAR DATES AND DATA ********** //

//Arrays for dates and vals;
$days = array();
$vals = array();

$date = 1;
$dayofweek = $monthinfo['dayofweek']; 

$weeksum = 0;
$monthsum = 0;

$htmldoc = '

<table border="1" cellpadding="2px">
    <tr bgcolor="#C0C0C0">
        <td align="center"><font size="8"><b>Sunday</b></font></td>
        <td align="center"><font size="8"><b>Monday</b></font></td>
        <td align="center"><font size="8"><b>Tuesday</b></font></td>
        <td align="center"><font size="8"><b>Wednesday</b></font></td>
        <td align="center"><font size="8"><b>Thursday</b></font></td>
        <td align="center"><font size="8"><b>Friday</b></font></td>
        <td align="center"><font size="8"><b>Saturday</b></font></td>
        <td align="center"><font size="8"><b>Total Hours</b></font></td>
    </tr>
';

// ********** START THE TABLE AND DATA ********** //


for($row=0; $row < 6; $row++){
    $dayofweek = $dayofweek % 7;

    $counter = 0;
    //write blank cells to catch up to the first day of the month
    while($counter != $dayofweek){
        $counter++; 
        $days[] = '<td style="height: 10px">&nbsp;</td>';
        $vals[] = '<td style="height: 75px">&nbsp;</td>';
    }

    do {
        $days[] = '<td style="height: 10px" align="center"><b>'.$date.'</b></td>';


        //begin of print work units
        

        // Print the data in the correct date blocks
        $wustr = "";
        $mid = (86400 * ($date -1)) + $monthinfo['firstdaytimestamp'];
        $eod = (86400 * ($date -1)) + ($monthinfo['firstdaytimestamp'] + 86399);

        foreach($units as $unit){
            if($unit->timein < $eod && $unit->timein > $mid){
                $in = userdate($unit->timein,get_string('timeformat','block_timetracker'));
                $out = userdate($unit->timeout,get_string('timeformat','block_timetracker'));
                if(($unit->timeout - $unit->timein) >449){
                    $wustr .= "In: $in<br />Out: $out<br />";
                    $weeksum += get_hours(($unit->timeout - $unit->timein));
                }
            } else {
                break;
            }
        }
        
        //$worksheet[1]->write_string($currentrow +1, $dayofweek, $wustr, $format_cal_block);
        $vals[] = '<td style="height: 75px"><font size="7">'.$wustr.'</font></td>';
        //end of print work units
        
        //if day of week = 7, copy value over and reset weekly sum to 0.        
      
        // Calculate total hours

        if($dayofweek == 6){
            //Add week sum to monthly sum
            //Print value in weekly totals column 
            //clear weekly sum
            $monthsum = $monthsum + $weeksum;
            //$worksheet[1]->write_string($currentrow +1, 7, $weeksum, $format_cal_total);
            $days[] = '<td style="height: 10px">&nbsp;</td>';
            $vals[] = '<td style="height: 75px" align="center"><font size="11"><b><br /><br />'.
                $weeksum.'</b><br /></font></td>';
            $weeksum = 0;
        }
        
        $dayofweek ++; $date++;
    } while ($date <= $monthinfo['lastday'] && $dayofweek % 7 != 0);
    if($date >= $monthinfo['lastday']) break; 
}

for($i = 0; $i < 6; $i++){
    $htmldoc.="\n<tr>\n";
    for($j=0; $j<8; $j++){
        $spot = $j + (8 * $i);
        if(isset($days[$spot]))
            $htmldoc .= "\t".$days[$spot]."\n";    
        else
            $htmldoc .= "\t".'<td style="height: 10px">&nbsp;</td>'."\n";
    }
    $htmldoc.="\n</tr>\n";

    $htmldoc.="\n<tr>\n";
    for($j=0; $j<8; $j++){
        $spot = $j + (8 * $i);
        if(isset($vals[$spot]))
            $htmldoc .= "\t".$vals[$spot]."\n";    
        else
            $htmldoc .="\t".'<td style="height: 75px">&nbsp;</td>'."\n";
    }
    $htmldoc.="\n</tr>\n";
}

$htmldoc .= '</table>';

$pdf->writeHTML($htmldoc, true, false, false, false, '');


// ********** FOOTER TOTALS AND SIGNATURES ********** //
$htmldoc = '

<table border="1" cellpadding="5px">
<tr>
    <td style="height: 35px"><font size="13"><b>Payrate or Stipend Amout</font><br />
        <font size="12">$'.$workerrecord->currpayrate.'</b></font></td>
    <td style="height: 35px"><font size="13"><b>Total Hours for '.$monthinfo['monthname'].', '.$year.'
        </font><br /><font size="12">'.$monthsum.'</b></font></td>
</tr>
<tr>
    <td style="height: 50px"><font size="13"><b>Worker Signature/Date</b></font></td>
    <td style="height: 50px"><font size="13"><b>Supervisor Signature/Date</b></font></td>
</tr>
</table>
';

$pdf->writeHTML($htmldoc, true, false, false, false, '');


//Close and Output PDF document
$pdf->Output('Timesheet_2011_August.pdf', 'I');


?>