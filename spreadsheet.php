<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once("$CFG->libdir/excellib.class.php");

global $CFG;

$workbook = new MoodleExcelWorkbook('-');
$workbook->send('hourlog.xls');


// Formatting
$format_bold =& $workbook->add_format();
$format_bold->set_bold();

$format_center =& $workbook->add_format();
$format_center->set_align('center');

$format_calendar_days =& $workbook->add_format();
$format_calendar_days->set_bold();
$format_calendar_days->set_align('center');
$format_calendar_days->set_size(8);
$format_calendar_days->set_fg_color(22);

$format_calendar_header =& $workbook->add_format();
$format_calendar_header->set_bold();
$format_calendar_header->set_align('center');
$format_calendar_header->set_bottom(1);
$format_calendar_header->set_size(8);

$format_calendar_inout =& $workbook->add_format();
$format_calendar_inout->set_align('right');
$format_calendar_inout->set_size(8);

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


$worksheet = array();
$headers = array('First Name','Last Name','Time In','Time Out','Elapsed Time');

$worksheet[1] =& $workbook->add_worksheet('First worksheet');

// Set column widths
$worksheet[1]->set_column(0,8,9.29);

// Write data to spreadsheet
$worksheet[1]->write_string(0,0,'Mars Hill College', $format_title);
$worksheet[1]->merge_cells(0, 0, 0, 7);
$worksheet[1]->write_string(1,0,'Timesheet', $format_timesheet_header);
$worksheet[1]->merge_cells(1, 0, 1, 7);

// Creates separator line under 'Timesheet'
foreach (range(1,7) as $i)
    $worksheet[1]->write_blank(1,$i, $format_timesheet_header);

// Header Data
$worksheet[1]->write_string(2,0,"$headers[1], $headers[0]", $format_bold);
$worksheet[1]->merge_cells(2,0,2,3);
$worksheet[1]->write_string(3,0,'Student ID#', $format_bold);
$worksheet[1]->merge_cells(3,0,3,3);
$worksheet[1]->write_string(4,0,'Address');
$worksheet[1]->merge_cells(4,0,4,3);
$worksheet[1]->write_string(5,0,'Month of: <<MONTH>>');
$worksheet[1]->merge_cells(5,0,5,3);
$worksheet[1]->write_string(2,4,'Supervisor');
$worksheet[1]->merge_cells(2,4,2,7);
$worksheet[1]->write_string(3,4,'Department');
$worksheet[1]->merge_cells(3,4,3,7);
$worksheet[1]->write_string(4,4,'Position');
$worksheet[1]->merge_cells(4,4,4,7);
$worksheet[1]->write_string(5,4,'Budget #');
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
$worksheet[1]->set_row(8,105);
$worksheet[1]->set_row(9,105);
$worksheet[1]->set_row(10,105);
$worksheet[1]->set_row(11,105);
$worksheet[1]->set_row(12,105);

foreach (range(0,7) as $i)
    $worksheet[1]->write_blank(12,$i, $format_timesheet_header);
    $worksheet[1]->write_blank(14,$i, $format_timesheet_header);
    $worksheet[1]->write_blank(16,$i, $format_timesheet_header);

// Footer
$worksheet[1]->write_string(13,0,'Pay Rate or Stipend Amount',$format_bold);
$worksheet[1]->merge_cells(13,0,13,3);
$worksheet[1]->write_string(13,4,'Total Hours for November:',$format_bold);
$worksheet[1]->merge_cells(13,4,13,7);
$worksheet[1]->write_string(15,0,'Supervisor Signature/Date',$format_bold);
$worksheet[1]->merge_cells(15,0,15,3);
$worksheet[1]->write_string(15,4,'Worker Signature/Date',$format_bold);
$worksheet[1]->merge_cells(15,4,15,7);
$worksheet[1]->set_row(13,15);
$worksheet[1]->set_row(14,15);
$worksheet[1]->set_row(15,15);
$worksheet[1]->set_row(16,15);



// Creating worksheets
/*
for ($wsnumber = 1; $wsnumber <= $nroPages; $wsnumber++) {
    $sheettitle = get_string('logs').' '.$wsnumber.'-'.$nroPages;
    $worksheet[$wsnumber] =& $workbook->add_worksheet($sheettitle);
    $worksheet[$wsnumber]->set_column(1, 1, 30);
    $worksheet[$wsnumber]->write_string(0, 0, get_string('savedat').
                                userdate(time(), $strftimedatetime));
    $col = 0;
    foreach ($headers as $item) {
        $worksheet[$wsnumber]->write(FIRSTUSEDEXCELROW-1,$col,$item,'');
        $col++;
    }
}
*/
/*
if (empty($logs['logs'])) {
    $workbook->close();
    return true;
}

$formatDate =& $workbook->add_format();
$formatDate->set_num_format(get_string('log_excel_date_format'));

$row = FIRSTUSEDEXCELROW;
$wsnumber = 1;
$myxls =& $worksheet[$wsnumber];
foreach ($logs['logs'] as $log) {
    if (isset($ldcache[$log->module][$log->action])) {
        $ld = $ldcache[$log->module][$log->action];
    } else {
        $ld = $DB->get_record('log_display', array('module'=>$log->module, 'action'=>$log->action));
        $ldcache[$log->module][$log->action] = $ld;
    }
    if ($ld && !empty($log->info)) {
        // ugly hack to make sure fullname is shown correctly
        if (($ld->mtable == 'user') and ($ld->field == $DB->sql_concat('firstname', "' '" , 'lastname'))) {
            $log->info = fullname($DB->get_record($ld->mtable, array('id'=>$log->info)), true);
        } else {
            $log->info = $DB->get_field($ld->mtable, $ld->field, array('id'=>$log->info));
        }
    }

    // Filter log->info
    $log->info = format_string($log->info);
    $log->info = strip_tags(urldecode($log->info));  // Some XSS protection

    if ($nroPages>1) {
        if ($row > EXCELROWS) {
            $wsnumber++;
            $myxls =& $worksheet[$wsnumber];
            $row = FIRSTUSEDEXCELROW;
        }
    }

    $myxls->write($row, 0, $courses[$log->course], '');
    $myxls->write_date($row, 1, $log->time, $formatDate); // write_date() does conversion/timezone support. MDL-14934
    $myxls->write($row, 2, $log->ip, '');
    $fullname = fullname($log, has_capability('moodle/site:viewfullnames', get_context_instance(CONTEXT_COURSE, $course->id)));
    $myxls->write($row, 3, $fullname, '');
    $myxls->write($row, 4, $log->module.' '.$log->action, '');
    $myxls->write($row, 5, $log->info, '');

    $row++;
}
*/

$workbook->close();
return true;
