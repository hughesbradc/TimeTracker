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
* This form will allow the worker to sign a timesheet electronically. 
*
* @package    Block
* @subpackage TimeTracker
* @copyright  2011 Marty Gilbert & Brad Hughes
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
*/

require_once("$CFG->libdir/formslib.php");
require_once('lib.php');
require_once('timesheet_pdf.php');

class timetracker_workersig_form extends moodleform {
   function timetracker_workersig_form($courseid,$userid, $start, $end){
       $this->courseid = $courseid;
       $this->userid = $userid;
       $this->start = $start;
       $this->end = $end;
       parent::__construct();
   }

    function definition() {
        global $CFG, $DB, $COURSE, $USER, $OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('header','general',
            get_string('timesheet','block_timetracker'));

        $mform->addElement('hidden','userid',$this->userid);
        $mform->addElement('hidden','id',$this->courseid);
        $mform->addElement('hidden','start',$this->start);
        $mform->addElement('hidden','end',$this->end);

        $mform->addElement('html', get_string('workerstatement','block_timetracker'));
        $mform->addElement('checkbox','workersig',get_string('clicktosign','block_timetracker'));
       
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit',
            'signbutton',get_string('signbutton','block_timetracker'),
            array('onclick'=>'return confirm("Are you sure you wish to submit the work units during
                this range?  Doing so may split units in a way that cannot be undone.")'));
        $mform->addGroup($buttonarray, 'buttonar','',array(' '), false);
        
        $mform->disabledIf('buttonar','workersig');
        $mform->addElement('html','<br />');

        $mform->addElement('html','
            <style type="text/css">
            
            table{
                width: 80%;
                height: 80%;
            }
            .calendar{
                border-left: 1px solid black;
                border-bottom: 1px solid black;
                border-top: 1px solid black;
            }
            table,div,td,th,tr{
                font-weight: normal;
                font-size: 18px;
                font-family: helvetica;
            }
            
            table{
            padding: 0;
            spacing: 0;
            border: 1px solid black;
            border-collapse: separate;
            margin-left: auto;
            margin-right: auto;
            }
            
            span.thirteen{
                font-weight: bold;
                font-size: 23px;
                font-family: helvetica;
            }
            
            span.ten{
                font-weight: bold;
                font-size: 20px;
                font-family: helvetica;
            }
            
            span.eight{
                font-weight: bold;
                font-size: 18px;
                font-family: helvetica;
            }
            
            span.seven{
                font-weight: bold;
                font-size: 17px;
                font-family: helvetica;
            }
            
            </style>');
            
            $pages = generate_html($this->start, $this->end, $this->userid, $this->courseid,-1,0);
            
            foreach($pages as $page){
                $page = str_replace('<font size="13">', '<span class="thirteen">',$page);
                $page = str_replace('<font size="10">', '<span class="ten">',$page);
                $page = str_replace('<font size="8">', '<span class="eight">',$page);
                $page = str_replace('<font size="7">', '<span class="seven">',$page);
                $page = str_replace('</font>', '</span>',$page);
                $page = str_replace('<hr style="height: 1px" />','', $page);
                $mform->addElement('html',$page);
                $mform->addElement('html', "\n\n\n");
            }
    }

    function validation($data){
    }
}
?>
