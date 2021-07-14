<?php
//moodleform is defined in formslib.php
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/zoom_scheduler/lib.php');

class zoom_scheduler_form extends moodleform {

	public function definition() {
		
		$mform = $this->_form;
		
		$mform->addElement('hidden', 'id', 1);
		$mform->setType('id', PARAM_INT);
		
		$mform->addElement('static', 'description', get_string('description', 'block_zoom_scheduler'), '');
		
		$days = get_list_of_weekdays();
		$mform->addElement('select', 'weekday', get_string('weekday', 'block_zoom_scheduler'), $days);
		
		$mform->addElement('date_time_selector', 'timestart', get_string('timestart', 'block_zoom_scheduler'),
			array('step'=>1) );
		$mform->addHelpButton('timestart', 'timestart', 'block_zoom_scheduler');
		
		$mform->addElement('duration', 'duration', get_string('duration', 'block_zoom_scheduler'));
		
		$mform->addElement('text', 'prefix', get_string('prefix', 'block_zoom_scheduler'));
		$mform->setType('prefix', PARAM_NOTAGS);
		$mform->addHelpButton('prefix', 'prefix', 'block_zoom_scheduler');
		
		// Additional hidden elements for backend use, may be added to UI later.
		$mform->addElement('hidden', 'action', '');
		$mform->setType('action', PARAM_ACTION);
		$mform->addElement('hidden', 'nth', 0);
		$mform->setType('nth', PARAM_INT);
		
		//$mform->addElement('select', 'action', get_string('action', 'block_zoom_scheduler'),
		//	array('','Create','Update'));
		//$mform->addHelpButton('action', 'action', 'block_zoom_scheduler');
		
		$this->add_action_buttons(false, 'Submit');
	}

}
