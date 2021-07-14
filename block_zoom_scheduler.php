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
 * Configures and displays the block
 *
 * @package    block_zoom_scheduler
 * @copyright  2019 Nick Stefanski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

class block_zoom_scheduler extends block_base {

	function init() {
		$this->title = get_string('pluginname', 'block_zoom_scheduler');
	}

	/**
	 * Which page types this block may appear on.
	 *
	 * The information returned here is processed by the
	 * {@link blocks_name_allowed_in_format()} function.
	 *
	 * @return array page-type prefix => true/false.
	 */
	public function applicable_formats() {
		global $COURSE;
		
		/*
		 * Zoom Scheduler will only work probably if there are multiple weekly sections.
		 */
		if (course_format_uses_sections($COURSE->format)) {
			$allowed = true;
		} else {
			$allowed = false;
		}
		
		return array('course-view' => $allowed, 'mod' => false, 'tag' => false);
	}

	function instance_allow_multiple() {
		return false;
	}

	/**
	  * Allow the block to have a configuration page
	  *
	  * @return boolean
	  */
	public function has_config() {
		return true;
	}

  /**
   * Sets up the content of the block for display to the user.
   *
   * @return The HTML content of the block.
   */
	function get_content() {
		global $COURSE, $CFG /*, $PAGE, $USER, $PAGE, $OUTPUT, $DB*/;
		
		require_once('zoom_scheduler_form.php');
		require_once($CFG->dirroot.'/blocks/zoom_scheduler/lib.php');
		
		if ($this->content !== NULL) {
			return $this->content;
		}
		
		$context = context_course::instance($COURSE->id); //TK
		if (!has_capability('block/zoom_scheduler:viewinstance', $context)) {
			return $this->content;
		}
		
		$this->content = new stdClass();
		$this->content->text = '';
		
		$mform = new zoom_scheduler_form();
		
		if ($data = $mform->get_data()) {
			//$result = 
			process_zoom_form($data);
			$courseurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
			redirect($courseurl);//, $result, null, \core\output\notification::NOTIFY_WARNING);
		} else {
			//Set default data (if any)
			$mform->set_data(
				array(
					'id' => $COURSE->id,
					'weekday' => get_config('block_zoom_scheduler', 'defaultweekday'),
					'timestart' => 0,
					'duration' => get_config('block_zoom_scheduler', 'defaultduration')
				)
			);
			
			//displays the form
			$this->content->text = $mform->render();
		}
		return $this->content;
	}

  /**
   * Tests if this block has been implemented correctly.
   * Also, $errors isn't used right now
   *
   * @return boolean
   */
	public function _self_test() {
		return true;
	}

}
