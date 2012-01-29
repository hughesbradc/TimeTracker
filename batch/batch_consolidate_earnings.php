<?php


define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

global $CFG, $DB, $USER;

$files = array();

$files[]='oct_earnings.csv';
$files[]='nov_earnings.csv';
$files[]='dec_earnings.csv';

/**
 The purpose of this script is to find earnings/max earnings for this term
*/

$workers = array();

foreach ($files as $file){
    //echo "opening file $file \n";
    //iterate through second file
    if(($handle = fopen($file, "r")) !== FALSE){

        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

            $id = strtolower($data[0]); 
            $hours = $data[1];

            if(array_key_exists($id, $workers)){
                //if(isset($workers[$id])){
                //error_log("Duplicate record found: $id");
                error_log($id.' '.$hours .' '.$workers[$id]->hours);
                $workers[$id]->hours += $hours;
                error_log($id.' '.$workers[$id]->hours);
            } else {
                $this_worker = new  stdClass();
                $this_worker->studentid = $id;
                $this_worker->fileid = str_replace('s000','',$id);
                $this_worker->hours = $hours;
                $this_worker->firstname = $data[3];
                $this_worker->lastname = $data[2];
                $this_worker->department = $data[4];
    
                $workers[$id] = $this_worker;
            }
        }
    } else {
        error_log("Cannot open $file");
    }
}

//print out the consolidated numbers
foreach ($workers as $worker){
    //print_object($worker);
    echo '"'.$worker->fileid.'","'.$worker->hours.'","'.
        $worker->lastname.'","'.$worker->firstname.'","'.
        $worker->department.'"'. "\n";

}

