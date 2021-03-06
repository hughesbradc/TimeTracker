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


function generate_pdf_from_timesheetid($timesheetid, $userid, 
    $courseid, $method = 'I', $base=''){

    global $DB, $CFG;
    $units = $DB->get_records('block_timetracker_workunit', array('userid'=>$userid,
        'timesheetid'=>$timesheetid), 'timein ASC');

    if($units){

        $start = reset($units);
        $startinfo = get_month_info(userdate($start->timein, "%m"),
            userdate($start->timein, "%Y"));
        $end = end($units);
        $endinfo = get_month_info(userdate($end->timeout, "%m"),
            userdate($end->timeout, "%Y"));

        return generate_pdf($startinfo['firstdaytimestamp'], $endinfo['lastdaytimestamp'], 
            $userid, $courseid, $method, $base, $timesheetid);

    } else {
        print_error('invalidtimesheetid', 'block_timetracker', 
            $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$courseid);
    }

}

function generate_pdf($start, $end, $userid, $courseid, $method = 'I', 
    $base='', $timesheetid=-1, $unsignedonly=false){
    global $CFG,$DB;

    $htmlpages = generate_html($start, $end, $userid, $courseid, $timesheetid,
        $unsignedonly);
    // Collect Data
    $conf = get_timetracker_config($courseid);

    $month = userdate($start, "%m");
    $year = userdate($start, "%Y");

    $workerrecord = $DB->get_record('block_timetracker_workerinfo', 
        array('id'=>$userid));

    if(!$workerrecord){
        print_error('usernotexist', 'block_timetracker',
            $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$courseid);
    }

    // ********** BEGIN PDF ********** //
    // Create new PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $fn = $year.'_'.($month<10?'0'.$month:$month).'Timesheet_'.
        substr($workerrecord->firstname,0,1).
        $workerrecord->lastname. '_'.$workerrecord->mdluserid;
    
    // Set Document Data
    $pdf->setCreator(PDF_CREATOR);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetCellPadding(0);
    $pdf->SetTitle($fn);
    $pdf->SetAuthor('TimeTracker');
    $pdf->SetSubject(' ');
    $pdf->SetKeywords(' ');
    
    // Remove Default Header/Footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    foreach($htmlpages as $page){
        $pdf->AddPage();
        $pdf->writeHTML($page);
    }
    
    //create the filename
    $fn .= '.pdf';


    //Close and Output PDF document
    //change the $method from 'I' to $method -- allow more than just a single file
    //to be created
    $pdf->Output($base.'/'.$fn, $method);
    return $fn;    
}

/**
    @return an array of HTML pages used for printing - one page per array item
*/
function generate_html($start, $end, $userid, $courseid, $timesheetid=-1,
    $unsignedonly=false){
    global $CFG,$DB;

    $pages = array();

    $startstring = userdate($start, "%m%Y");
    $endstring = userdate($end, "%m%Y");
    $samemonth = ($startstring == $endstring);

    $workerrecord = $DB->get_record('block_timetracker_workerinfo', 
        array('id'=>$userid));

    if(!$workerrecord){
        print_error('usernotexist', 'block_timetracker',
            $CFG->wwwroot.'/blocks/timetracker/index.php?id='.$courseid);
    }

    // Collect Data
    $conf = get_timetracker_config($courseid);

    $firstmonth= userdate($start, "%m");
    $firstyear = userdate($start, "%Y");
    $firstmonthinfo = make_timestamp($firstyear, $firstmonth, 1);


    //$curr = $start;
    $curr = $firstmonthinfo;


    $overallhoursum = 0;
    $overalldollarsum = 0;

    while ($curr <= $end) {
        $month = userdate($curr, "%m");
        $year = userdate($curr, "%Y");

        $monthinfo = get_month_info($month, $year);
        $mid = $monthinfo['firstdaytimestamp'];
        $eod = strtotime('+ 1 day', $mid);
        $eod -= 1;
        $monthhoursum = 0;
        $monthdollarsum = 0;

        $units = get_split_month_work_units($workerrecord->id, $courseid, $month, $year,
            $timesheetid, $unsignedonly);
    
        // Add Page

        /**DING DING DING**/
        //$pdf->AddPage();
    
        // ********** HEADER ********** //
        $htmldoc = '
            <table style="margin-left: auto; margin-right: auto" cellspacing="0"'. 
            'cellpadding="0" width="540px">
            <tr>
                <td align="center"><font size="10"><b>'.
                $conf['institution'].'</b></font></td>
            </tr>
            <tr>
                <td align="center"><font size="10"><b>Timesheet - '.
                $monthinfo['monthname'].', '.
                $year.'</b></font>
                </td>
            </tr>
        </table>
        <hr style="height: 1px" />';
    
        //$pdf->writeHTML($htmldoc, true, false, false, false, '');
    
        // ********** WORKER AND SUPERVISOR DATA ********** //
        $htmldoc .= '
            <table style="margin-left: auto: margin-right: auto" cellspacing="0"'.
            'cellpadding="0" width="540px">
            <tr>
                <td><font size="8.5"><b>WORKER: '.strtoupper($workerrecord->lastname).', '
                    .strtoupper($workerrecord->firstname).'<br />'
                .'ID: '.$workerrecord->idnum.'<br />'
                .'ADDRESS: '.$workerrecord->address.'<br />
                YTD Earnings: $ '.get_earnings_this_year($userid, $courseid).
                '</b></font></td>
                <td><font size="8.5"><b>SUPERVISOR: '.$conf['supname'].'<br />'
                .'DEPARTMENT: '.$conf['department'].'<br />'
                .'POSITION: '.$conf['position'].'<br />'
                .'BUDGET: '.$conf['budget'].'</b></font></td>
            </tr>
        </table>
        <br />';
    
        //$pdf->writeHTML($htmldoc, true, false, false, false, '');
    
        // ********** CALENDAR DAYS HEADER (Sun - Sat) ********** //
        // ********** CALENDAR DATES AND DATA ********** //
    
        //Arrays for dates and vals;
        $days = array();
        $vals = array();
    
        $date = 1;
        $dayofweek = $monthinfo['dayofweek']; 
        
        $weeksum = 0;
        $monthhoursum = 0;

        $htmldoc .= '
    
            <table border="1" cellpadding="2px" width="540px" '.
            'style="margin-right: auto; margin-left: auto">
            <tr bgcolor="#C0C0C0">
                <td class="calendar" align="center"><font size="8"><b>Monday</b></font></td>
                <td class="calendar" align="center"><font size="8"><b>Tuesday</b></font></td>
                <td class="calendar" align="center"><font size="8"><b>Wednesday</b></font></td>
                <td class="calendar" align="center"><font size="8"><b>Thursday</b></font></td>
                <td class="calendar" align="center"><font size="8"><b>Friday</b></font></td>
                <td class="calendar" align="center"><font size="8"><b>Saturday</b></font></td>
                <td class="calendar" align="center"><font size="8"><b>Sunday</b></font></td>
                <td class="calendar" align="center"><font size="8"><b>Total Hours</b>'.
                '</font></td>
            </tr>
        ';
        
        // ********** START THE TABLE AND DATA ********** //
        //write blank cells to catch up to the first day of the month
        $counter = 1;
        while($counter != $dayofweek){
            $counter++; 
            $days[] = '<td class="calendar" style="height: 10px">&nbsp;</td>';
            $vals[] = '<td class="calendar" style="height: 70px">&nbsp;</td>';
            $counter %= 7;
        }
        
        //a "week" - a row in the table
        for($row=0; $row < 6; $row++){

            $dayofweek = $dayofweek % 7;

            do {
                $days[] = '<td class="calendar" style="height: 10px" align="center"><b>'.
                    $date.'</b></td>';

                //begin of print work units
                
                // Print the data in the correct date blocks
                $wustr = "";

                if($units){
                    foreach($units as $unit) {
                        if($unit->timein < $eod && 
                            $unit->timein >= $mid && 
                            $unit->timein >= $start && 
                            $unit->timeout <= $end){

                            $in = userdate($unit->timein,
                                get_string('timeformat','block_timetracker'));
                            $out = userdate($unit->timeout,
                                get_string('timeformat','block_timetracker'));

                            //FIXMEFIXME!
                            if(array_key_exists('round', $conf) && $conf['round'] > 0)
                                $factor = ($conf['round']/2)-1;
                            else
                                $factor = 0;

                            if(($unit->timeout - $unit->timein) > $factor){ 
                                //WHAT IF NOT ROUNDED?
                                $wustr .= "In: $in<br />Out: $out<br />";

                                $hours = get_hours($unit->timeout - $unit->timein,
                                    $unit->courseid);

                                //overtime or regular?
                                if(($hours + $weeksum) > 40){

                                    $ovthours = $reghours = 0;
                                    if($weeksum > 40){ //already over 40
                                        //no reghours, just ovthours
                                        $ovthours = $hours;
                                    } else { //not already over 40
                                        $reghours = 40 - $weeksum;
                                        $ovthours = $hours - $reghours;
                                    }
                                   
                                    $amt = $reghours * $unit->payrate;
                                    $ovtamt = $ovthours * ($workerrecord->currpayrate * 1.5);
                                    $amt += $ovtamt;

                                } else {
                                    $amt = $hours * $unit->payrate;
                                }

                                $monthdollarsum += $amt;
                                $overalldollarsum += $amt;
                                $weeksum += $hours;
                                $overallhoursum += $hours;

                            }
                        }
                    }
                }
                
                $vals[] = '<td class="calendar"  style="height: 70px"><font size="7">'.
                    $wustr.'</font></td>';
                
                //if day of week = 0 (Sunday), copy value over and reset weekly sum to 0. 
                // Calculate total hours
                if($dayofweek == 0){
                    //Add week sum to monthly sum
                    //Print value in weekly totals column 
                    //clear weekly sum
                    $monthhoursum += $weeksum;
                    $days[] = '<td class="calendar" style="height: 10px">&nbsp;</td>';
                    if($weeksum == 0) $weeksum = '&nbsp;';
                    $vals[] = 
                        '<td class="calendar" style="height: 70px" align="center">'.
                        '<font size="10"><b><br /><br />'.
                        $weeksum.'</b><br /></font></td>';
                    $weeksum = 0;
                } else if ($date == $monthinfo['lastday']){
                    //what about when we reach the end of the month? 
                    //Still need to put totals!!!
                    $counter = $dayofweek;
                    while($counter != 0){ //pad to the rightmost column
                        $days[] = '<td class="calendar" style="height: 10px">&nbsp;</td>';
                        $vals[] = '<td class="calendar" style="height: 70px">&nbsp;</td>';
                        $counter++;
                        $counter %= 7;
                    }
                    $monthhoursum += $weeksum;
                    $days[] = '<td class="calendar" style="height: 10px">&nbsp;</td>';
                    if($weeksum == 0) $weeksum = '&nbsp;';
                    $vals[] = 
                        '<td class="calendar" style="height: 70px" align="center">'.
                        '<font size="10"><b><br /><br />'.
                        $weeksum.'</b><br /></font></td>';
                    $weeksum = 0;
    
                }

                $mid = strtotime('+ 1 day', $mid); //midnight
                $eod = strtotime('+ 1 day', $eod); //23:59:59
                
                $dayofweek++; 
                $date++;
                $curr = strtotime('+1 day', $curr);
            } while ($date <= $monthinfo['lastday'] && $dayofweek != 7);
            if($date >= $monthinfo['lastday']) break; 
        }//this is a single "row" or "week"
        
        for($i = 0; $i < 6; $i++){
            $htmldoc.="\n<tr>\n";
            for($j=0; $j<8; $j++){
                $spot = $j + (8 * $i);
                if(isset($days[$spot]))
                    $htmldoc .= "\t".$days[$spot]."\n";    
                else
                    $htmldoc .= "\t".
                        '<td class="calendar" style="height: 10px">&nbsp;</td>'."\n";
            }
            $htmldoc.="\n</tr>\n";
        
            $htmldoc.="\n<tr>\n";
            for($j=0; $j<8; $j++){
                $spot = $j + (8 * $i);
                if(isset($vals[$spot]))
                    $htmldoc .= "\t".$vals[$spot]."\n";    
                else
                    $htmldoc .="\t".
                        '<td class="calendar" style="height: 70px">&nbsp;</td>'."\n";
            }
            $htmldoc.="\n</tr>\n";
        }
    
        $htmldoc .= '</table><br />';
    
        //$pdf->writeHTML($htmldoc, true, false, false, false, '');
    
    
        // ********** FOOTER TOTALS ********** //
        $htmldoc .= '
            <table border="1" cellpadding="5px" width="540px" '.
            'style="margin-left: auto; margin-right: auto">
        <tr>
            <td style="height: 25px"><font size="13"><b>Base Pay Rate</b></font>
            <br />
                <font size="10">$'.round($workerrecord->currpayrate, 2).'</font></td>
            <td style="height: 20px"><font size="13"><b>Total Hours/Earnings for '.
	 	        $monthinfo['monthname'].', '.$year.'</b></font><br /><font size="10">'.
                round($monthhoursum, 3).' / $'.
		        round($monthdollarsum, 2) .'</font></td>
        </tr></table><br />';
        //$pdf->writeHTML($htmldoc, true, false, false, false, '');

        //here is a new page!!!
        $pages[] = $htmldoc;
    }

    end($pages);
    $key = key($pages);
    $htmldoc = $pages[$key];

    // ********** OVERALL TOTALS AND SIGNATURES********** //
    if($timesheetid != -1){
        $ts = $DB->get_record('block_timetracker_timesheet', 
            array('id'=>$timesheetid));
    }

    $htmldoc .= '
        <table border="1" cellpadding="5px" width="540px" '.
        'style="margin-left: auto; margin-right: auto">';
    if(!$samemonth){
        $desc = '';
        if($timesheetid == -1){
            $desc = 
	            userdate($start, get_string('dateformat', 'block_timetracker')).
                ' to '.
	            userdate($end, get_string('dateformat', 'block_timetracker'));
        }
        

        $htmldoc .='
        <tr>
        <td colspan="2" style="height: 35px"><font size="13"><b>Total Hours/Earnings  '.
            $desc.
            '</b></font><br /><font size="10">'.
            round($overallhoursum, 3).' / $'.
	        round($overalldollarsum, 2) .'</font></td>
        </tr>';
    }

    if($timesheetid != -1){
        $datestr = get_string('datetimeformat', 'block_timetracker');
        $htmldoc .='
        <tr>
            <td style="height: 45px"><font size="13"><b>Worker Signature/Date</b></font>'.
            '<br />'.
            '<font size="8">Signed by '.$workerrecord->firstname.' '.
            $workerrecord->lastname.'<br />'.
            userdate($ts->workersignature, $datestr).
            '</font></td>'.
            '<td style="height: 45px"><font size="13"><b>Supervisor Signature/Date</b>'.
            '</font><br />'.
            '<font size="8">';
        if($ts->supervisorsignature != 0){
            $super = $DB->get_record('user', array('id'=>$ts->supermdlid));
            if(!$super) print_error('Supervisor does not exist');
            $htmldoc .= 'Signed by '.$super->firstname.' '.$super->lastname.'<br />'.
                userdate($ts->supervisorsignature, $datestr);
        } else {
            $htmldoc .= 'Awaiting supervisor signature';
        }

        $htmldoc .='
            </font></td>
        </tr>
        </table><br />';

    } else {
        /*
        $htmldoc .='
        
        <tr>
            <td style="height: 45px"><font size="13"><b>Worker Signature/Date</b></font></td>
            <td style="height: 45px"><font size="13"><b>Supervisor Signature/Date</b></font></td>
        </tr>
        */
        $htmldoc .=' </table><br />';
    }

    $pages[$key] = $htmldoc;

    return $pages;
}


?>
