<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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
 * Task: update_meetings
 *
 * @package    mod_zoom
 * @author     Nick Stefanski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_zoom_scheduler\task;
use \Datetime;
use \Dateinterval;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/zoom/locallib.php');
require_once($CFG->dirroot.'/mod/zoom/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

/**
 * Scheduled task to sychronize meeting data.
 */
class update_example_courses extends \core\task\scheduled_task {

	/**
	* Returns name of task.
	*
	* @return string
	*/
	public function get_name() {
		return get_string('updateexamplecourses', 'block_zoom_scheduler');
	}

	/**
	* Updates example courses.
	*
	* @return boolean
	*/
	public function execute() {
		global $CFG, $DB;
		
		$zoommod = $DB->get_field('modules', 'id', array('name' => 'zoom'));
		
		// get list of courses in config
		mtrace('... getting courses ...');
		$configlist = get_config('block_zoom_scheduler', 'examplecourses');
		if ($configlist) {
			$courselist = explode(',',$configlist);
			$now = new DateTime();
			foreach ($courselist as $courseid) {
				$courseid = (int) $courseid;
				mtrace('... working on course '.$courseid);
				try {
					$course = $DB->get_record('course', array('id' => $courseid));
					// if startdate is past, increment start and end dates by one week
					$startdate = new DateTime();
					$startdate->setTimestamp($course->startdate);
					if ($now >= $startdate) {
						$startdate->add(new DateInterval('P7D') );
						$data = (object) array('id' => $courseid,
												  'startdate' => $startdate->getTimestamp() );
						if ($course->enddate) {
							$enddate = new DateTime();
							$enddate->setTimestamp($course->enddate);
							$data->enddate = $startdate->getTimestamp();
						}
						$DB->update_record('course', $data);
						mtrace('... incremented start date');
					}
					
					// delete all zoom meeting activities in course
					$cms = $DB->get_records('course_modules', array('course' => $courseid, 'module' => $zoommod));
					foreach ($cms as $cm) {
						course_delete_module($cm->id, true);
					}
					mtrace('... deleted zoom meetings');
				} catch(moodle_exception $e) {
					
				}
			}
			mtrace('... all courses processed');
		} else {
			mtrace('... no courses found ...');
		}
		
		return true;
	}
}
