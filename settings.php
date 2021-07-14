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
 * Defines the form for editing activity results block instances.
 *
 * @package    block_zoom_scheduler
 * @copyright  2021 Nick Stefanski <nmstefanski@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/blocks/zoom_scheduler/lib.php');

if ($ADMIN->fulltree) {
	
	$days = get_list_of_weekdays();
	$settings->add(new admin_setting_configselect('block_zoom_scheduler/defaultweekday',
        get_string('defaultweekday', 'block_zoom_scheduler'), 
        get_string('defaultweekday_desc', 'block_zoom_scheduler'), 'monday', $days));
	
	$settings->add(new admin_setting_configduration('block_zoom_scheduler/defaultduration',
        get_string('defaultduration', 'block_zoom_scheduler'), 
        get_string('defaultduration_desc', 'block_zoom_scheduler'), 2700, 3600));
}
