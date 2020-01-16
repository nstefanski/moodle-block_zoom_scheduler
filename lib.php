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
	global $DB;
	$context = context_course::instance($data->id);
	require_capability('mod/zoom:addinstance', $context);
	$config = get_config('mod_zoom');
	
	$host_id = $data->host ? $data->host : zoom_get_user_id();
	$moduleid = $DB->get_field('modules', 'id', array('name'=>'zoom'));
	$course = $DB->get_record('course', array('id'=>$data->id));
	$num_sections = $DB->count_records('course_sections', array('course'=>$course->id)) - 1; //exclude section 0
	
	//need to get a grade category "ungraded" and return id
	$gradecat = $DB->get_record('grade_categories', array('courseid'=>$course->id,'fullname'=>'Ungraded'));
	
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
	$action = $zooms ? "Update" : "Create";
	$result = "";
	$ct = 0;
	
	for ($i = 0; $i < $num_sections; $i++) {
		$section = $i+1;
		
		//calculate live session from course startdate
		$dt = new DateTime();
		$dt->setTimestamp($course->startdate);
		$dt->add(new DateInterval('P'.($diff+($i*7)).'D') ); //startdate plus weekday difference plus number of weeks
		$dt->setTime($hour, $minute);
		
		//$topic = $prefix . "Week $section Live Session - " . $dt->format('l, g:i A T');
		$timestring = $dt->format(get_string('dtformat', 'block_zoom_scheduler'));
		$topic = get_string('topic', 'block_zoom_scheduler',
			['prefix' => $prefix, 'section' => $section, 'dt' => $timestring]);
		//$cmidnumber = 'liveses-wk'.sprintf("%02d", $section).'z01';//.sprintf("%04d", $length_mins);
		$cmidnumber = get_string('cmidnumber', 'block_zoom_scheduler',
			['section' => sprintf("%02d", $section), 'count' => '01']);
		$start_time = $dt->getTimestamp();
		
		if($zooms){ //update
			$sectionid = $DB->get_field('course_sections', 'id', array('course'=>$course->id,'section'=>$section));
			$cm = $DB->get_record('course_modules', array('course'=>$course->id,'module'=>$moduleid,'section'=>$sectionid));
			$cm->modname = 'zoom';
			
			$newzoom = $zooms[$cm->instance];
			$newzoom->coursemodule = $cm->id;
			$newzoom->instance = $cm->instance;
			$newzoom->name = $cm->name;
			$newzoom->update = $cm->id;
			
			$newzoom->visible = $cm->visible;
			$newzoom->availability = $cm->availability;
			$newzoom->completion = $cm->completion;
			$newzoom->completionusegrade = $cm->completionusegrade;
		} else { //add
			$newzoom = new stdClass();
			$newzoom->meeting_id = -1;
			$newzoom->course = $course->id;
			$newzoom->section = $section;
			$newzoom->add = 'zoom';
			$newzoom->update = 0;
			$newzoom->grade = 1;
			$newzoom->gradecat = $gradecat->id;
			$newzoom->completionunlocked = 1;
			
			$newzoom->visible = 1;
			$newzoom->availabilityconditionsjson = '{"op":"&","c":[],"showc":[]}';
			$newzoom->completion = 2;
			$newzoom->completionusegrade = 1;
		}
		
		$newzoom->modulename = 'zoom';
		$newzoom->host_id = $host_id;
		$newzoom->module = $moduleid;
		$newzoom->name = $topic;
		$newzoom->showdescription = 0;
		$newzoom->start_time = $start_time;
		$newzoom->duration = $data->duration;
		$newzoom->cmidnumber = $cmidnumber;
		
		$newzoom->option_host_video = $config->defaulthostvideo;
		$newzoom->option_participants_video = $config->defaultparticipantsvideo;
		$newzoom->option_audio = $config->defaultaudiooption;
		$newzoom->option_jbh = $config->defaultjoinbeforehost;
		
		try {
			if($zooms){ //update
				$updateinfo = update_moduleinfo($cm, $newzoom, $course);
				$moduleinfo = $updateinfo[1];
				$moduleinfo->section = $section;
			} else { //add
				$moduleinfo = add_moduleinfo($newzoom, $course);
					
				//move before archive link
				$beforemod_idnumber = 'liveses-wk'.sprintf("%02d", $section).'u01';
				$beforemod = $DB->get_record('course_modules', array('course'=>$course->id,'idnumber'=>$beforemod_idnumber));
				
				if($moduleinfo->coursemodule && $beforemod->id){
					course_add_cm_to_section($moduleinfo->course, $moduleinfo->coursemodule, $moduleinfo->section, $beforemod->id);
					
					//indent module
					$DB->set_field('course_modules', 'indent', 1, array('id' => $moduleinfo->coursemodule));
				}
			}
			$ct++;
		} catch(moodle_exception $e){
			$result .= $e->getMessage()."<br>";
		}
	}
	
	//$result = array($data, $ct);
	$result .= $action."d $ct meetings.";
	return $result;
}