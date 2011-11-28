<?php

/**
 * Structure step to restore one choice activity
 */
class restore_timetracker_block_structure_step extends restore_structure_step {
 
    protected function define_structure() {
 
        $paths = array();
        $userinfo = $this->get_setting_value('users');

        $paths[] = new restore_path_element('block', '/block', true);
        $paths[] = new restore_path_element('timetracker', '/block/timetracker');
        $paths[] = new restore_path_element('term', '/block/timetracker/term');
        $paths[] = new restore_path_element('config', '/block/timetracker/config');
        if($userinfo){
            $paths[] = new restore_path_element('workerinfo', 
                '/block/timetracker/workerinfo');
            $paths[] = new restore_path_element('pending', 
                '/block/timetracker/workerinfo/pending');
            $paths[] = new restore_path_element('alertunits', 
                '/block/timetracker/workerinfo/alertunits');
            $paths[] = new restore_path_element('alert_com', 
                '/block/timetracker/workerinf/alertunits/alert_com');
            $paths[] = new restore_path_element('workunit', 
                '/block/timetracker/workerinfo/workunit');
        }
 
        // Return the paths wrapped into standard activity structure
        //return $this->prepare_activity_structure($paths);
        return $paths;
    }
 
    protected function process_block($data) {
        global $DB;
        //error_log('before getting userinfo'); 
        $userinfo = $this->get_setting_value('users');

        $data = (object)$data;

        //error_log('Before checking blockid');
        if(!$this->task->get_blockid()){
            return;
        }

        $terms = (object)$data->timetracker[0]['term'];
        //print_object($data);
        if($terms) {
            foreach($terms as $term){
                $term = (object)$term;
                unset($term->id);
                $term->courseid = $this->get_courseid();
                //error_log('Adding term '.$term->name);
                $DB->insert_record('block_timetracker_term',$term);
            }
        }

        $config = (object)$data->timetracker[0]['config'];
        foreach ($config as $citem){
            $citem = (object)$citem;
            unset($citem->id);
            $citem->courseid = $this->get_courseid();
            //error_log("adding config item $citem->name");
            $DB->insert_record('block_timetracker_config',$citem);
        }
        
        if($userinfo){
            //if no workers, 'workerinfo' is undefined. How do we check? isset? TODO
            $workerlist = $data->timetracker[0]['workerinfo'];
            if($workerlist){
                foreach($workerlist as $worker){
                    $workerinfo = (object) $worker;
                    $workerinfo->courseid = $this->get_courseid();
                    $oldid = $workerinfo->id;
                    unset($workerinfo->id);
                    //error_log("inserting worker 
                        //$worker->firstname $worker->lastname $worker->oldid");
                    $newinfoid = $DB->insert_record('block_timetracker_workerinfo',
                        $workerinfo);
    
                    if(!$newinfoid){
                        return;
                    }
    
                    if(isset($worker['workunit'])){
                        foreach($worker['workunit'] as $unit){
                            $unit = (object)$unit;
                            unset($unit->id);
                            $unit->userid = $newinfoid;
                            $unit->courseid = $this->get_courseid();
                            //print_object($unit);
                            $DB->insert_record('block_timetracker_workunit',$unit);
                        }
                    }
    
                    if(isset($worker['pending'])){
                        foreach($worker['pending'] as $pending){
                            $pending = (object)$pending;
                            unset($pending->id);
                            $pending->userid = $newinfoid;
                            $pending->courseid = $this->get_courseid();
                            $DB->insert_record('block_timetracker_pending',$pending);
                        }
                    }
    
                    /*
                    if(isset($worker['alertunits'])){
                        foreach($worker['alertunits'] as $aunit){
                            $aunit = (object) $aunit;
                            unset($aunit->id);
                            $aunit->userid = $newinfoid;
                            $aunit->courseid=$this->get_courseid();
                            $DB->insert_record('block_timetracker_alertunits',$aunit);
                    } 
                    }
                    */
    
                    /*
                    if(isset($worker['alertcom'])){
                        foreach($worker['alertcom'] as $com){
                            //question about mdluserid here -- not sure
                            //how to ensure these align! 
                            //Might be best to not back these up. TODO
                            $com = (object) $com;
                            unset($com->id);
                            $com->userid = $newinfoid;
                            //$DB->insert_record('block_timetracker_alert_com',$com);
                        } 
                    }
                    */

                }
            }
        }
    }
}
