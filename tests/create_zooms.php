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
 * Test Create Zooms
 * 
 * This test can be run to create zoom meetings in a course 
 * as a different user (must still have capability to create zooms)
 * using url params instead of the block form.
 *
 * @param int id
 * @param string weekday
 * @param int hour
 * @param int minute
 * @param int length (minutes)
 * optional @param string prefix
 * optional @param string email (of zoom host)
 */

//defined('MOODLE_INTERNAL') || die();
require_once('../../../config.php');

require_login();

global $CFG, $DB;
require_once($CFG->dirroot.'/blocks/zoom_scheduler/lib.php');
require_once($CFG->dirroot.'/mod/zoom/locallib.php');

$data = new stdClass();

$data->id = $_GET["id"]; //22;
if(!$data->id) {
	echo "You must set a course id (id=) in the url parameters.";
	die();
}

$data->weekday = $_GET["day"];
$hour = $_GET["hour"];
$minute = $_GET["minute"];
if(!$data->weekday || !isset($hour) || !isset($minute)) {
	echo "You MUST set meeting day of week (day=), hour (hour=), and minute (minute=) in the url parameters.";
	die();
}
$dt = new DateTime();
$dt->setTime($hour, $minute);
$data->timestart = $dt->getTimestamp();

$length_mins = $_GET["length"];
if(!$length_mins) {
	echo "You must set meeting length in minutes (length=) in the url parameters.";
	die();
}
$data->duration = $length_mins*60;

//prefix used to add additional info at beginning of meeting topic
$data->prefix = $_GET["prefix"] ? $_GET["prefix"] : "";

$email = $_GET["email"] ? $_GET["email"] : "";

if($email){
	$service = new mod_zoom_webservice();
	try {
		$zoomuser = $service->get_user($email);
		if ($zoomuser !== false) {
			$data->host = $zoomuser->id;
		} else {
			echo "Could not find host id related to $email";
		}
	} catch (moodle_exception $error) {
		echo "Could not complete ws call with $email";
	}
}

$result = process_zoom_form($data);/**/
print_R($result);