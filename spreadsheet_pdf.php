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
require_once($CFG->libdir.'/pdflib.php');
require_once('lib.php');
//require_once('../../lib/moodlelib.php');

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


// Create PDF Document
//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
$pdf = new pdf();

// Set PDF Document Information
//$pdf->setCreator("TimeTracker Module");
//$pdf->setAuthor($mdluser->username);
//$pdf->setTitle('Timesheet_'.$year.'_'.$monthinfo['monthname']);
//$pdf->Cell(0,0, 'Moodle PDF Library Test', 0, 1, 'C', 1);

// Add Page to PDF
$pdf->AddPage();

// Header/Title Information
$htmldoc ='
<HTML>
<HEAD>
   <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN"> 
    <META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
    <TITLE></TITLE> 
    <STYLE>
        <!-- 
        BODY,DIV,TABLE,THEAD,TBODY,TFOOT,TR,TH,TD,P { font-family:"Arial"; font-size:x-small }
         -->
    </STYLE>
    
</HEAD>

<BODY TEXT="#000000">
<TABLE FRAME=VOID CELLSPACING=0 COLS=8 RULES=NONE BORDER=0>
    <COLGROUP><COL WIDTH=84><COL WIDTH=84><COL WIDTH=84><COL WIDTH=84><COL WIDTH=84><COL
WIDTH=84><COL WIDTH=84><COL WIDTH=84></COLGROUP>
    <TBODY>
        <TR>
            <TD COLSPAN=8 WIDTH=668 HEIGHT=19 ALIGN=CENTER><B><FONT
SIZE=3>' .$conf['institution'].'</FONT></B></TD>
            </TR>
        <TR>
            <TD STYLE="border-bottom: 1px solid #000000" COLSPAN=8 HEIGHT=19 ALIGN=CENTER><B><FONT
SIZE=3>Timesheet - '.$monthinfo['monthname'].', '.$year .'</FONT></B></TD>
            </TR>
        <TR>
';

// Worker and Supervisor Information
$htmldoc .='
            <TD COLSPAN=4 HEIGHT=16 ALIGN=LEFT><B>'. $workerrecord->lastname .', '.
            $workerrecord->firstname.'</B></TD>
            <TD COLSPAN=4 ALIGN=LEFT><B>'.$conf['supname'] .'</B></TD>
            </TR>
        <TR>
            <TD COLSPAN=4 HEIGHT=16 ALIGN=LEFT><B>'.$workerrecord->idnum.'</B></TD>
            <TD COLSPAN=4 ALIGN=LEFT><B>' .$conf['department'].'</B></TD>
            </TR>
        <TR>
            <TD COLSPAN=4 HEIGHT=16 ALIGN=LEFT><B>'.$workerrecord->address.'</B></TD>
            <TD COLSPAN=4 ALIGN=LEFT><B>'.$conf['position'].'</B></TD>
            </TR>
        <TR>
            <TD COLSPAN=4 HEIGHT=16 ALIGN=LEFT><B>YTD Earnings: $ '
                .get_earnings_this_year($userid,$courseid).'</B></TD>
            <TD COLSPAN=4 ALIGN=LEFT><B>'.$conf['budget'].'</B></TD>
            </TR>
        <TR>
            <TD HEIGHT=16 ALIGN=LEFT><BR /></TD>
            <TD ALIGN=LEFT><BR />&nbsp;</TD>
            <TD ALIGN=LEFT><BR />&nbsp;</TD>
            <TD ALIGN=LEFT><BR />&nbsp;</TD>
            <TD ALIGN=LEFT><BR />&nbsp;</TD>
            <TD ALIGN=LEFT><BR />&nbsp;</TD>
            <TD ALIGN=LEFT><BR />&nbsp;</TD>
            <TD ALIGN=LEFT><BR />&nbsp;</TD>
        </TR>
';
// Days of the Week & Total Hours Headers
$htmldoc .='
        <TR>
            <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left:
1px solid #000000; border-right: 1px solid #000000" HEIGHT=16 ALIGN=CENTER
BGCOLOR="#C0C0C0"><B><FONT SIZE=1>Sunday</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left:
1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER BGCOLOR="#C0C0C0"><B><FONT
SIZE=1>Monday</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left:
1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER BGCOLOR="#C0C0C0"><B><FONT
SIZE=1>Tuesday</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left:
1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER BGCOLOR="#C0C0C0"><B><FONT
SIZE=1>Wednesday</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left:
1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER BGCOLOR="#C0C0C0"><B><FONT
SIZE=1>Thursday</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left:
1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER BGCOLOR="#C0C0C0"><B><FONT
SIZE=1>Friday</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left:
1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER BGCOLOR="#C0C0C0"><B><FONT
SIZE=1>Saturday</FONT></B></TD>
            <TD STYLE="border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left:
1px solid #000000; border-right: 1px solid #000000" ALIGN=CENTER BGCOLOR="#C0C0C0"><B><FONT
SIZE=1>Total Hours</FONT></B></TD>
        </TR>
';

//Arrays for dates and vals;
$days = array();
$vals = array();

$date = 1;
$dayofweek = $monthinfo['dayofweek']; 

$weeksum = 0;
$monthsum = 0;

for($row=0; $row < 12; $row++){
    $dayofweek = $dayofweek % 7;

    $counter = 0;
    //write blank cells to catch up to the first day of the month
    while($counter != $dayofweek){
        $counter++; 
        $days[] = '<TD STYLE="border-left: 1px solid#000000; border-right: 1px'. 
            ' solid #000000" HEIGHT=15>&nbsp;</TD>';

        $vals[] = '<TD STYLE="border-bottom: 1px solid#000000; border-left: 1px'. 
            ' solid#000000; border-right: 1px solid #000000" HEIGHT=92>&nbsp;</TD>';
    }

    do {

        //$worksheet[1]->write_string($currentrow, $dayofweek, $date, $format_calendar_dates);
        $days[]='<TD STYLE="border-left: 1px solid#000000; border-right: 1px'.
            ' solid #000000" HEIGHT=15 ALIGN=CENTER><B><FONT SIZE=1>'.$date.'</FONT></B></TD>';
        
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
                    //unset($units[$key]);
                    $weeksum += get_hours(($unit->timeout - $unit->timein));
                    //error_log($weeksum);
                }
            } else {
                break;
            }
        }
        
        //$worksheet[1]->write_string($currentrow +1, $dayofweek, $wustr, $format_cal_block);
        $vals[] = '<TD STYLE="border-bottom: 1px solid#000000; border-left: 1px'. 
            ' solid#000000; border-right: 1px solid #000000" HEIGHT=92>'.$wustr.'</TD>';
        
        //end of print work units
        
        //if day of week = 7, copy value over and reset weekly sum to 0.        
      
        // Calculate total hours

        if($dayofweek == 6){
            //Add week sum to monthly sum
            //Print value in weekly totals column 
            //clear weekly sum
            $monthsum = $monthsum + $weeksum;
            //$worksheet[1]->write_string($currentrow +1, 7, $weeksum, $format_cal_total);
            $days[] = '<TD STYLE="border-left: 1px solid#000000; border-right: 1px'. 
                ' solid #000000" HEIGHT=15>&nbsp;</TD>';

            $vals[] = '<TD STYLE="border-bottom: 1px solid#000000; border-left: 1px'. 
                ' solid#000000; border-right: 1px solid #000000" HEIGHT=92><B><FONT SIZE=3>'.
                $weeksum.'</FONT></B></TD>'; 

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
            $htmldoc .= "\t".'<TD STYLE="border-left: 1px solid#000000; border-right: 1px'. 
                ' solid #000000" HEIGHT=15>&nbsp;</TD>'."\n";
    }
    $htmldoc.="\n</tr>\n";

    $htmldoc.="\n<tr>\n";
    for($j=0; $j<8; $j++){
        $spot = $j + (8 * $i);
        if(isset($vals[$spot]))
            $htmldoc .= "\t".$vals[$spot]."\n";    
        else
            $htmldoc .="\t".'<TD STYLE="border-bottom: 1px solid#000000; border-left: 1px'. 
            ' solid#000000; border-right: 1px solid #000000" HEIGHT=92>&nbsp;</TD>'."\n";
    }
    $htmldoc.="\n</tr>\n";
}

//Footer code
$htmldoc .='
        <TR>
            <TD STYLE="border-bottom: 1px solid #000000; border-left: 1px solid #000000;
border-right: 1px solid #000000" COLSPAN=4 HEIGHT=40 ALIGN=LEFT VALIGN=TOP><B>Pay Rate or Stipend
Amount<BR />'.$workerrecord->currpayrate.'</B></TD>
            <TD STYLE="border-bottom: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=4
ALIGN=LEFT VALIGN=TOP><B>Total Hours for '.$monthinfo['monthname'] .', ' .$year.'<BR />'
            .$monthsum.'</B></TD>
            </TR>
        <TR>
            <TD STYLE="border-bottom: 1px solid #000000; border-left: 1px solid #000000;
border-right: 1px solid #000000" COLSPAN=4 HEIGHT=50 ALIGN=LEFT VALIGN=TOP><B>Supervisor
Signature/Date</B></TD>
            <TD STYLE="border-bottom: 1px solid #000000; border-right: 1px solid #000000" COLSPAN=4
ALIGN=LEFT VALIGN=TOP><B>Worker Signature/Date</B></TD>
            </TR>
    </TBODY>
</TABLE>
<!-- ************************************************************************** -->
</BODY>
';

//write it to a file for TESTING ONLY
//$filename = '/tmp/test.html';
//$fp = fopen($filename, 'w');
//fwrite($fp,$htmldoc);
//fclose($fp);
/*
$filename = '/tmp/test2.html';
$fh = fopen($filename, 'r');
$htmldoc = fread($fh,filesize($filename));
fclose($fh);
print ($htmldoc);
*/

// Create the PDF from the HTML
//$pdf->writeHTML($htmldoc, true, false, true, false, '');
$pdf->writeHTML($htmldoc);
//$pdf->lastPage();
$pdf->Output('Timesheet_'.$year.'_'.$monthinfo['monthname'].'.pdf', 'I');

return true;
?>
