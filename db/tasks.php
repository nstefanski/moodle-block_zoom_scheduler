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
 * Definition of Zoom Scheduler scheduled tasks.
 *
 * @package    block_zoom_scheduler
 * @author     Nick Stefanski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$tasks = array(
    array(
        'classname' => 'block_zoom_scheduler\task\update_example_courses',
        'blocking' => 0,
        'minute' => '1',
        'hour' => '0',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ),
);