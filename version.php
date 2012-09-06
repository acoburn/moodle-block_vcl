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

/*
 * This Moodle block can be used to access the Virtual Computing Lab.
 * For more information about the VCL, visit http://vcl.apache.org
 * 
 * @package    blocks
 * @subpackage vcl
 * @author     Aaron Coburn <acoburn@amherst.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @copyright  (C) 2012 Amherst College
 *
 * This Moodle plugin provides access to a Virtual Computing Lab
 * infrastructure. It allows users to make and manage reservations
 * to remote computing environments.
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2012090600;
$plugin->cron = 1800; // every 30 minutes
