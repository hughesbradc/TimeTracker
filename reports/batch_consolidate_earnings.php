<?php

$FILE1='october_earnings.csv';
$FILE2='september_earnings.csv';

define('CLI_SCRIPT', true);
require_once('../../../config.php');
require_once('../lib.php');

/**
 The purpose of this script is to find earnings/max earnings for this term
*/
global $CFG, $DB, $USER;

$workers = array();

//iterate through first file
if(($handle = fopen($FILE1, "r")) !== FALSE){
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $id = strtolower($data[0]); 
        $hours = $data[1];

        $this_worker = new  stdClass();
        $this_worker->studentid = $id;
        $this_worker->hours = $hours;
        $this_worker->firstname = $data[3];
        $this_worker->lastname = $data[2];
        $this_worker->department = $data[4];

        $workers[$id] = $this_worker;
        //error_log("Adding $id to array");
    }
} else {
    error_log("Cannot open $FILE1");
}

//error_log("Done with file1...on to file2");

//iterate through second file
if(($handle = fopen($FILE2, "r")) !== FALSE){
    //error_log("about to process file2");
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
            $this_worker->hours = $hours;
            $this_worker->firstname = $data[3];
            $this_worker->lastname = $data[2];
            $this_worker->department = $data[4];

            $workers[$id] = $this_worker;
        }
    }
} else {
    error_log("Cannot open $FILE2");
}

//print out the consolidated numbers
foreach ($workers as $worker){
    //print_object($worker);
    echo '"'.$worker->studentid.'","'.$worker->hours.'","'.
        $worker->lastname.'","'.$worker->firstname.'","'.
        $worker->department.'"'. "\n";


}

