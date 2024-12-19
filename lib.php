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
 * @package block_zoom_scheduler
 * @author  2019 Nick Stefanski <nmstefanski@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/modlib.php');
require_once($CFG->dirroot.'/mod/zoom/locallib.php');

/**
 * Use data from form or url get to create or update zoom meetings
 * @param object $data
 * @param int $data->id course id
 * @param string $data->weekday
 * @param int $data->timestart unix timestamp
 * @param int $data->duration in seconds
 * optional @param string $data->prefix
 * optional @param string $data->host zoom host_id
 * @return string $result
 */
function process_zoom_form($data) {
	global $DB, $USER;
	$context = context_course::instance($data->id);
	require_capability('mod/zoom:addinstance', $context);
	$config = get_config('zoom'); //TK
	
	$host_id = $data->host ?? null;
	$service = zoom_webservice();
	if (!$host_id) {
		$moodleusers = get_enrolled_users($context, 'mod/zoom:addinstance', 0, 'u.*');
		if ($moodleusers && !array_key_exists($USER->id, $moodleusers) ) {
			// current user is not enrolled, use the first moodle user
			$zoomapiidentifier = zoom_get_api_identifier(reset($moodleusers));
			$host_id = $service->get_user($zoomapiidentifier)->id;
		} else {
			$host_id = zoom_get_user_id();
		}
	}
	$settings = $service->get_user_settings($host_id);
	$pmi_password = null;
	if($settings->schedule_meeting->use_pmi_for_scheduled_meetings) {
		$pmi_password = $settings->schedule_meeting->pmi_password;
	}
	
	$moduleid = $DB->get_field('modules', 'id', array('name'=>'zoom'));
	$course = $DB->get_record('course', array('id'=>$data->id));
	$num_sections = $DB->count_records('course_sections', array('course'=>$course->id)) - 1; //exclude section 0
	
	//calculate interval to desired weekday (if any)
	$dt = new DateTime();
	$dt->setTimestamp($course->startdate);
	$wdt = new DateTime($data->weekday);
	$diff = (int)$wdt->format('N') - (int)$dt->format('N');
	while($diff < 0){
		$diff+= 7; //fix negative values
	}

	//convert $data->timestart to hour and minute
	$hour = date('H', $data->timestart);
	$minute = date('i', $data->timestart);

	//add trailing space to prefix
	if($data->prefix && substr($data->prefix, -1) !== " "){
		$data->prefix = $data->prefix . " ";
	}
	$prefix = $data->prefix;
	
	$zooms = $DB->get_records('zoom', array('course'=>$course->id));
	
	// Allow override of default action to "Create" even if zooms exist in course.
	if (!$zooms || $data->action == "Create") {
		//$action = "Create";
		$zooms = null;
	} else {
		//$action = "Update";
	}
	
	// Allow updating meeting other than first in section.
	$limitfrom = ($data->nth > 0) ? $data->nth - 1 : 0;
	
	$result = "";
	$add_ct = 0;
	$update_ct = 0;
	
	for ($i = 0; $i < $num_sections; $i++) {
		$section = $i+1;
		
		//calculate live session from course startdate
		$dt = new DateTime();
		$dt->setTimestamp($course->startdate);
		$dt->add(new DateInterval('P'.($diff+($i*7)).'D') ); //startdate plus weekday difference plus number of weeks
		$dt->setTime($hour, $minute);
		
		$timestring = $dt->format(get_string('dtformat', 'block_zoom_scheduler'));
		$topic = get_string('topic', 'block_zoom_scheduler',
			['prefix' => $prefix, 'section' => $section, 'dt' => $timestring]);
		$cmidnumber = get_string('cmidnumber', 'block_zoom_scheduler',
			['section' => sprintf("%02d", $section), 'count' => '01']);
		$start_time = $dt->getTimestamp();
		
		/*$comp_dt = null;
		if(get_config('block_zoom_scheduler', 'completionexpected')) {
			$comp_dt = $dt;
			$comp_dt->add(new DateInterval('PT'.(300).'S') );
			$comp_time = $comp_dt->getTimestamp();
		}*/
		//set completion expected to same as meeting start to avoid duplicate events on dashboard
		$comp_time = $start_time;
		
		$newzoom = null;
		
		if($zooms){ //try update
			$sectionid = $DB->get_field('course_sections', 'id', array('course'=>$course->id,'section'=>$section));
			$cms = $DB->get_records('course_modules', array('course'=>$course->id,'module'=>$moduleid,'section'=>$sectionid), 
			                       $sort='id ASC', $fields='*', $limitfrom, $limitnum=1);
			$cm = reset($cms);
			$cm->modname = 'zoom';
			
			$newzoom = $zooms[$cm->instance];
		}
		if($newzoom){ //confirm update
			$newzoom->coursemodule = $cm->id;
			$newzoom->instance = $cm->instance;
			$newzoom->update = $cm->id;
			
			$newzoom->visible = $cm->visible;
			$newzoom->availability = $cm->availability;
			$newzoom->completion = $cm->completion;
			$newzoom->completionview = $cm->completionview;
			$newzoom->introeditor = array('text' => $newzoom->intro, 'format' => $newzoom->introformat, 'itemid' => 0);
		} else { //add
			$newzoom = new stdClass();
			$newzoom->meeting_id = -1;
			$newzoom->course = $course->id;
			$newzoom->section = $section;
			$newzoom->add = 'zoom';
			$newzoom->update = 0;
			$newzoom->grade = 0;
			$newzoom->completionunlocked = 1;
			
			$newzoom->visible = 1;
			$newzoom->availabilityconditionsjson = '{"op":"&","c":[],"showc":[]}';
			$newzoom->completion = 2;
			$newzoom->completionview = 1;
			
			$newzoom->recurring = 0;
			$newzoom->introeditor = array('text' => null, 'format' => null, 'itemid' => 0);
		}
		
		$newzoom->modulename = 'zoom';
		$newzoom->host_id = $host_id;
		$newzoom->module = $moduleid;
		$newzoom->name = $topic;
		$newzoom->showdescription = 0;
		$newzoom->start_time = $start_time;
		$newzoom->duration = $data->duration;
		$newzoom->cmidnumber = $cmidnumber;
		if($comp_time) {
			$newzoom->completionexpected = $comp_time;
		}
		
		$newzoom->option_host_video = $config->defaulthostvideo;
		$newzoom->option_participants_video = $config->defaultparticipantsvideo;
		$newzoom->option_audio = $config->defaultaudiooption;
		$newzoom->option_jbh = $config->defaultjoinbeforehost;
		$newzoom->option_waiting_room = $config->defaultwaitingroomoption;
		$newzoom->option_mute_upon_entry = $config->defaultmuteuponentryoption;
		$newzoom->option_authenticated_users = $config->defaultauthusersoption;
		$newzoom->option_auto_recording = $config->recordingoption;
		$newzoom->requirepasscode = 1;
		$newzoom->meetingcode = $pmi_password ?? rand(100000,999999);
		
		try {
			if($newzoom->update){ //update
				$updateinfo = update_moduleinfo($cm, $newzoom, $course);
				$moduleinfo = $updateinfo[1];
				$moduleinfo->section = $section;
				$update_ct++;
			} else { //add
				$moduleinfo = add_moduleinfo($newzoom, $course);
				$add_ct++;
					
				//move before archive link
				$beforemod_idnumber = 'liveses-wk'.sprintf("%02d", $section).'u01';
				$beforemod = $DB->get_record('course_modules', array('course'=>$course->id,'idnumber'=>$beforemod_idnumber));
				
				if($moduleinfo->coursemodule && $beforemod){
					course_add_cm_to_section($moduleinfo->course, $moduleinfo->coursemodule, $moduleinfo->section, $beforemod->id);
					
					//indent module
					$DB->set_field('course_modules', 'indent', 1, array('id' => $moduleinfo->coursemodule));
				}
			}
			
		} catch(moodle_exception $e){
			//$result .= $e->getMessage()." $newzoom->coursemodule in section $section<br>";
			\core\notification::error($e->getMessage()." $newzoom->coursemodule in section $section");
		}
	}
	
	if($update_ct){
		//$result .= "Updated $update_ct meetings.<br>";
		\core\notification::info("Updated $update_ct meetings.");
	}
	if($add_ct){
		//$result .= "Created $add_ct meetings.<br>";
		\core\notification::info("Created $add_ct meetings.");
	}
	
	//return $result;
}

function get_list_of_weekdays() {
	$days = array(
		'monday' => get_string('monday', 'core_calendar'),
		'tuesday' => get_string('tuesday', 'core_calendar'),
		'wednesday' => get_string('wednesday', 'core_calendar'),
		'thursday' => get_string('thursday', 'core_calendar'),
		'friday' => get_string('friday', 'core_calendar'),
		'saturday' => get_string('saturday', 'core_calendar'),
		'sunday' => get_string('sunday', 'core_calendar')
	);
	return $days;
}
