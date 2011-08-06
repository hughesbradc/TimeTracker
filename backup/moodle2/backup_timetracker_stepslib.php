<?php
 
/**
* Define all the backup steps that will be used by the backup_timetracker_block 
*/
class backup_timetracker_block_structure_step extends backup_block_structure_step {

    protected function define_structure() {
        error_log("in define_structure()");
        global $DB;
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Get the block
        $block = $DB->get_record('block_instances', array('id' => $this->task->get_blockid()));

        // Extract configdata
        $config = unserialize(base64_decode($block->configdata));

        // Define each element separated
        $workerinfo = new backup_nested_element('workerinfo', array('id'), array(
            'active', 'address', 'budget', 'comments', 'currpayrate',
            'dept', 'email', 'firstname', 'idnum', 'institution', 'lastname',
            'maxtermearnings', 'phonenumber', 'supervisor', 
            'timetrackermethod')); //annotate mdluserid

        $terms = new backup_nested_element('term', array('id'), array(
            'name', 'month', 'day')); 
        
        $config = new backup_nested_element('config',array('id'), array(
            'name','value')); 

        $alertcom = new backup_nested_element('alert_com', array('id'), array(
            'alertid')); //annotate mdluserid

        $alertunits = new backup_nested_element('alerunits', array('id'), array(
            'timein', 'lasteditedby', 'lastedited', 'message',
            'origtimein','origtimeout','payrate','todelete','alerttime')); 

        $pending = new backup_nested_element('pending', array('id'), array(
            'timein')); //annotate userid
        
        $workunits = new backup_nested_element('workunit', array('id'), array(
            'timein', 'timeout', 'lastedited', 'lasteditedby', 
            'payrate')); //annotate userid


        // Build the tree -- isn't this all of the dependencies?
        $workerinfo->add_child($alertunits);
        $workerinfo->add_child($pending);
        $workerinfo->add_child($workunits);
        $workerinfo->add_child($terms);
        $workerinfo->add_child($config);
        $workerinfo->add_child($alertcom);

        // Define sources
        $terms->set_source_table('terms', array('courseid' => backup::VAR_COURSEID));
        $config->set_source_table('config', array('courseid' => backup::VAR_COURSEID));
        if($userinfo){
            $alertcom->set_source_table('alert_com', array('courseid' => backup::VAR_COURSEID));
            $workerinfo->set_source_table('workerinfo', array('courseid' => backup::VAR_COURSEID));
            $alertunits->set_source_table('alertunits', array('courseid' => backup::VAR_COURSEID,
                'userid'=>'../id'));
            $pending->set_source_table('pending', array('courseid' => backup::VAR_COURSEID,
                'userid'=>'../id'));
            $workunits->set_source_table('pending', array('courseid' => backup::VAR_COURSEID,
                'userid'=>'../id'));
            $alertcom->annotate_ids('user', 'mdluserid');
            $workerinfo->annotate_ids('user', 'mdluserid');
        }


        // Annotations (none)

        // Return the root element (timetracker), wrapped into standard block structure
        return $this->prepare_block_structure($workerinfo);
    }
}
