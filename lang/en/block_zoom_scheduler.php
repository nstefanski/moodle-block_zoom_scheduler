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
 * @package   block_zoom_scheduler
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['zoom_scheduler:addinstance'] = 'Add a new Zoom Scheduler block';
$string['zoom_scheduler:viewinstance'] = 'View Zoom Scheduler block';
$string['pluginname'] = 'Zoom Scheduler';
$string['topic'] = '{$a->prefix}Week {$a->section} Live Session - {$a->dt}';
$string['dtformat'] = 'l, g:i A T';
$string['cmidnumber'] = 'liveses-wk{$a->section}z{$a->count}';
$string['description'] = 'Create/update a Zoom meeting for each week.';
$string['weekday'] = 'Weekday';
$string['timestart'] = 'Meeting start time';
$string['timestart_help'] = 'Only hour and minute are necessary';
$string['duration'] = 'Meeting length';
$string['prefix'] = 'Meeting name prefix (optional)';
$string['prefix_help'] = 'Meeting name will be formatted like: <br />'
	.'"PREFIX Week X Live Session - Weekday, X:XX PM TZ"';
$string['action'] = 'Action';
$string['action_help'] = '1. Create a new meeting in each section of the course'
	.'<br />2. Update all existing meetings in the course to standard settings';

$string['defaultweekday'] = 'Default weekday';
$string['defaultweekday_desc'] = 'The default day of week for scheduling zoom meetings in the block form';
$string['defaultduration'] = 'Default meeting length';
$string['defaultduration_desc'] = 'The default duration for scheduling zoom meetings in the block form';
$string['examplecourses'] = 'Example courses';
$string['examplecourses_desc'] = 'Comma separated list of course ids to be used for training purposes; the course start dates will be regularly updated and the zoom meetings purged';
$string['updateexamplecourses'] = 'Update Example Courses';

$string['msg_count'] = 'There are <strong>{$a}</strong> meetings currently scheduled in this course.';
$string['msg_not_enrolled'] = '<br><br>You are not enrolled in the course. New meetings will be scheduled for {$a}.';
$string['msg_scheduled_user'] = '<br><br>Some of the meetings in this course were scheduled by a different user. When the meetings are updated, they will remain the host.';
