<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once("$CFG->libdir/excellib.class.php");
require_once('lib.php');
require_once('../../lib/moodlelib.php');

global $CFG;

$month = 3;
$year = 2011;
$userid = 1;
$courseid = 2;

$monthinfo = get_month_info($month, $year);

$workbook = new MoodleExcelWorkbook('-');
$workbook->send('timesheet'.$year.'_'.$month.'.xls');


// Formatting
$format_bold =& $workbook->add_format();
$format_bold->set_bold();

$format_cal_block =& $workbook->add_format();
$format_cal_block->set_left(1);
$format_cal_block->set_right(1);
$format_cal_block->set_bottom(1);
$format_cal_block->set_text_wrap();
$format_cal_block->set_v_align('Top');
$format_cal_block->set_size(8);

$format_calendar_dates =& $workbook->add_format();
$format_calendar_dates->set_bold();
$format_calendar_dates->set_align('center');
$format_calendar_dates->set_size(8);
$format_calendar_dates->set_left(1);
$format_calendar_dates->set_right(1);

$format_calendar_days =& $workbook->add_format();
$format_calendar_days->set_bold();
$format_calendar_days->set_align('center');
$format_calendar_days->set_size(8);
$format_calendar_days->set_fg_color(22);
$format_calendar_days->set_border(1);

$format_calendar_header =& $workbook->add_format();
$format_calendar_header->set_bold();
$format_calendar_header->set_align('center');
$format_calendar_header->set_bottom(1);
$format_calendar_header->set_size(8);

$format_center =& $workbook->add_format();
$format_center->set_align('center');

$format_footer =& $workbook->add_format();
$format_footer->set_bold();
$format_footer->set_bottom(1);
$format_footer->set_v_align('Top');
$format_footer->set_text_wrap();

$format_footer_block =& $workbook->add_format();
$format_footer_block->set_bottom(1);
$format_footer_block->set_top(1);
$format_footer_block->set_left(1);
$format_footer_block->set_right(1);

$format_timesheet_header =& $workbook->add_format();
$format_timesheet_header->set_bold();
$format_timesheet_header->set_align('center');
$format_timesheet_header->set_size(12);
$format_timesheet_header->set_bottom(1);

$format_title =& $workbook->add_format();
$format_title->set_bold();
$format_title->set_align('center');
$format_title->set_size(12);

$format_week_header =& $workbook->add_format();
$format_week_header->set_bold();
$format_week_header->set_align('center');
$format_week_header->set_size(8);

// Collect Data
$workerrecord = $DB->get_record('block_timetracker_workerinfo', array('id'=>$userid,'courseid'=>$courseid));
$mdluser= $DB->get_record('user', array('id'=>$workerrecord->mdluserid));
$conf = get_timetracker_config($courseid);

$worksheet = array();

$worksheet[1] =& $workbook->add_worksheet('First worksheet');

// Set column widths
$worksheet[1]->set_column(0,8,10.57);

// Write data to spreadsheet
$worksheet[1]->write_string(0,0,'Mars Hill College', $format_title);
$worksheet[1]->merge_cells(0, 0, 0, 7);
$worksheet[1]->write_string(1,0,'Timesheet - '.$monthinfo['monthname'].', '.$year, $format_timesheet_header);
$worksheet[1]->merge_cells(1, 0, 1, 7);

// Creates separator line under 'Timesheet'
foreach (range(1,7) as $i){
    $worksheet[1]->write_blank(1,$i, $format_timesheet_header);
}

// Header Data
$worksheet[1]->write_string(2,0,"$workerrecord->lastname, $workerrecord->firstname", $format_bold);
$worksheet[1]->merge_cells(2,0,2,3);
$worksheet[1]->write_string(3,0,"$mdluser->username", $format_bold);
$worksheet[1]->merge_cells(3,0,3,3);
$worksheet[1]->write_string(4,0,"$workerrecord->address", $format_bold);
$worksheet[1]->merge_cells(4,0,4,3);
$worksheet[1]->write_string(5,0,'YTD Earnings: $'.get_earnings_this_year($userid,$courseid), $format_bold);
$worksheet[1]->merge_cells(5,0,5,3);
$worksheet[1]->write_string(2,4,$conf['supname'], $format_bold);
$worksheet[1]->merge_cells(2,4,2,7);
$worksheet[1]->write_string(3,4,$conf['department'], $format_bold);
$worksheet[1]->merge_cells(3,4,3,7);
$worksheet[1]->write_string(4,4,$conf['position'], $format_bold);
$worksheet[1]->merge_cells(4,4,4,7);
$worksheet[1]->write_string(5,4,$conf['budget'], $format_bold);
$worksheet[1]->merge_cells(5,4,5,7);


// Calendar Data
$worksheet[1]->write_string(7,0,'Sunday',$format_calendar_days);
$worksheet[1]->write_string(7,1,'Monday',$format_calendar_days);
$worksheet[1]->write_string(7,2,'Tuesday',$format_calendar_days);
$worksheet[1]->write_string(7,3,'Wednesday',$format_calendar_days);
$worksheet[1]->write_string(7,4,'Thursday',$format_calendar_days);
$worksheet[1]->write_string(7,5,'Friday',$format_calendar_days);
$worksheet[1]->write_string(7,6,'Saturday',$format_calendar_days);
$worksheet[1]->write_string(7,7,'Total Hours',$format_calendar_days);
$worksheet[1]->set_row(9,69);
$worksheet[1]->set_row(11,69);
$worksheet[1]->set_row(13,69);
$worksheet[1]->set_row(15,69);
$worksheet[1]->set_row(17,69);
$worksheet[1]->set_row(19,69);
$worksheet[1]->set_row(8,11.25);
$worksheet[1]->set_row(10,11.25);
$worksheet[1]->set_row(12,11.25);
$worksheet[1]->set_row(14,11.25);
$worksheet[1]->set_row(16,11.25);
$worksheet[1]->set_row(18,11.25);

foreach (range(0,7) as $i){
    $worksheet[1]->write_blank(8,$i, $format_calendar_dates);
    $worksheet[1]->write_blank(9,$i, $format_cal_block);
    $worksheet[1]->write_blank(10,$i, $format_calendar_dates);
    $worksheet[1]->write_blank(11,$i, $format_cal_block);
    $worksheet[1]->write_blank(12,$i, $format_calendar_dates);
    $worksheet[1]->write_blank(13,$i, $format_cal_block);
    $worksheet[1]->write_blank(14,$i, $format_calendar_dates);
    $worksheet[1]->write_blank(15,$i, $format_cal_block);
    $worksheet[1]->write_blank(16,$i, $format_calendar_dates);
    $worksheet[1]->write_blank(17,$i, $format_cal_block);
    $worksheet[1]->write_blank(18,$i, $format_calendar_dates);
    $worksheet[1]->write_blank(19,$i, $format_cal_block);
}

// Footer
foreach (range(0,7) as $i){
    $worksheet[1]->write_blank(20,$i, $format_footer_block);
    $worksheet[1]->write_blank(21,$i, $format_footer_block);
}

$worksheet[1]->write_string(20,0,"Pay Rate or Stipend Amount\n\r" .'$'.$workerrecord->currpayrate,$format_footer);
$worksheet[1]->merge_cells(20,0,20,3);
$worksheet[1]->write_string(20,4,'Total Hours for '.$monthinfo['monthname'].', '.$year.':',$format_footer);
$worksheet[1]->merge_cells(20,4,20,7);
$worksheet[1]->write_string(21,0,'Supervisor Signature/Date',$format_footer);
$worksheet[1]->merge_cells(21,0,21,3);
$worksheet[1]->write_string(21,4,'Worker Signature/Date',$format_footer);
$worksheet[1]->merge_cells(21,4,21,7);
$worksheet[1]->set_row(20,30);
$worksheet[1]->set_row(21,30);

// Number the Days and add data

$sql = 'SELECT * from ' .$CFG->prefix.'block_timetracker_workunit '. 'WHERE userid='.$userid.
    ' AND courseid='.$courseid.' AND timein BETWEEN '. $monthinfo['firstdaytimestamp'] .' AND '.
    $monthinfo['lastdaytimestamp']. ' ORDER BY timein';

$units = $DB->get_recordset_sql($sql);

$date = 1;
$dayofweek = $monthinfo['dayofweek']; 
for($currentrow = 8; $currentrow < 20; $currentrow += 2){
    //echo "inside for loop <br />";
    $dayofweek = $dayofweek % 7;
    do{
        $worksheet[1]->write_string($currentrow, $dayofweek, $date, $format_calendar_dates);
        
        
        //begin of print work units
        
        // Print the data in the correct date blocks
        $wustr = "";
        $mid = (86400 * ($date -1)) + $monthinfo['firstdaytimestamp'];
        $eod = (86400 * ($date -1)) + ($monthinfo['firstdaytimestamp'] + 86399);

        foreach($units as $unit){
            if($unit->timein < $eod && $unit->timein > $mid){
                $in = userdate($unit->timein,get_string('timeformat','block_timetracker'));
                $out = userdate($unit->timeout,get_string('timeformat','block_timetracker'));
                $wustr .= "In: $in\n\rOut: $out\n\r";
                //unset($units[$key]);
            } else {
                break;
            }
        }
        
        $worksheet[1]->write_string($currentrow +1, $dayofweek, $wustr, $format_cal_block);
        //end of print work units
        
    //if day of week = 7, copy value over and reset weekly sum to 0.        
        
        
        $dayofweek ++; $date++;
    } while ($date <= $monthinfo['lastday'] && $dayofweek % 7 != 0);
    if($date >= $monthinfo['lastday']) break; 
    
}

// Print monthly total 

$workbook->close();
return true;
