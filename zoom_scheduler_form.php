<?php
//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class zoom_scheduler_form extends moodleform {

	public function definition() {
        global $CFG;
        
		$mform = $this->_form;
		
		$mform->addElement('hidden', 'id', 1);
		
		$mform->addElement('static', 'description', get_string('description', 'block_zoom_scheduler'), '');
		
		$days = array('Monday'=>'Monday','Tuesday'=>'Tuesday','Wednesday'=>'Wednesday',
			'Thursday'=>'Thursday','Friday'=>'Friday','Saturday'=>'Saturday','Sunday'=>'Sunday');
		$mform->addElement('select', 'weekday', get_string('weekday', 'block_zoom_scheduler'), $days);
		
		$mform->addElement('date_time_selector', 'timestart', get_string('timestart', 'block_zoom_scheduler'),
			array('step'=>1) );
		$mform->addHelpButton('timestart', 'timestart', 'block_zoom_scheduler');
		
		$mform->addElement('duration', 'duration', get_string('duration', 'block_zoom_scheduler'));
		
		$mform->addElement('text', 'prefix', get_string('prefix', 'block_zoom_scheduler'));
		$mform->addHelpButton('prefix', 'prefix', 'block_zoom_scheduler');
		
		//$mform->addElement('select', 'action', get_string('action', 'block_zoom_scheduler'),
		//	array('','Create','Update'));
		//$mform->addHelpButton('action', 'action', 'block_zoom_scheduler');
		
		$this->add_action_buttons(false, 'Submit');
	}

}
