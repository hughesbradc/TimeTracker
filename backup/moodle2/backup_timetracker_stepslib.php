<?php
 
/**
* Define all the backup steps that will be used by the backup_timetracker_block 
*/
class backup_timetracker_block_structure_step extends backup_block_structure_step {

    protected function define_structure() {
        //error_log("in define_structure()");
        global $DB,$CFG;

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('users');

        // Get the block
        $block = $DB->get_record('block_instances', array('id' => $this->task->get_blockid()));

        // Extract configdata
        $config = unserialize(base64_decode($block->configdata));
        //$pre=$CFG->prefix.'block_timetracker_';
        $pre='block_timetracker_';
        //error_log($pre); 

        $timetracker_main = new backup_nested_element('timetracker', array('id'), null);

        // Define each element separated
        $workerinfo = new backup_nested_element('workerinfo', array('id'), array(
            'active', 'address', 'budget', 'comments', 'currpayrate',
            'dept', 'email', 'firstname', 'idnum', 'institution', 'lastname',
            'maxtermearnings', 'phonenumber', 'supervisor', 
            'timetrackermethod','mdluserid')); //annotate mdluserid

        $terms = new backup_nested_element('term', array('id'), array(
            'name', 'month', 'day')); 
        
        $config = new backup_nested_element('config',array('id'), array(
            'name','value')); 

        $alertunits = new backup_nested_element('alerunits', array('id'), array(
            'timein', 'lasteditedby', 'lastedited', 'message',
            'origtimein','origtimeout','payrate','todelete','alerttime')); 

        $alertcom = new backup_nested_element('alert_com', array('id'), array('mdluserid')); 

        $pending = new backup_nested_element('pending', array('id'), array(
            'timein')); 
        
        $workunits = new backup_nested_element('workunit', array('id'), array(
            'timein', 'timeout', 'lastedited', 'lasteditedby', 
            'payrate'));


        // Build the tree -- isn't this all of the dependencies?
        $timetracker_main->add_child($workerinfo);
        $timetracker_main->add_child($terms);
        $timetracker_main->add_child($config);
        $workerinfo->add_child($alertunits);
        $workerinfo->add_child($pending);
        $alertunits->add_child($alertcom);
        $workerinfo->add_child($workunits);

        // Define sources
        $timetracker_main->set_source_array(array((object)array('id'=>$this->task->get_blockid())));
        $terms->set_source_table($pre.'term', array('courseid' => backup::VAR_COURSEID));
        $config->set_source_table($pre.'config', array('courseid' => backup::VAR_COURSEID));
        if($userinfo){
            $workerinfo->set_source_table($pre.'workerinfo', 
                array('courseid' => backup::VAR_COURSEID));
            $alertunits->set_source_table($pre.'alertunits', 
                array('courseid' => backup::VAR_COURSEID,
                'userid'=>'../id'));
            /*
            //need to fix all of this
            $alertcom->set_source_table($pre.'alert_com', 
                array('courseid' => backup::VAR_COURSEID,
                'alertid'=>'../id'));
            */
            $pending->set_source_table($pre.'pending', 
                array('courseid' => backup::VAR_COURSEID,
                'userid'=>'../id'));
            $workunits->set_source_table($pre.'workunit', 
                array('courseid' => backup::VAR_COURSEID,
                'userid'=>'../id'));
        }


        // Annotations (none)
        $alertcom->annotate_ids('user', 'mdluserid');
        $workerinfo->annotate_ids('user', 'mdluserid');

        // Return the root element (timetracker), wrapped into standard block structure
        return $this->prepare_block_structure($timetracker_main);
    }
}
