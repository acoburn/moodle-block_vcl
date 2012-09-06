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

// Capability definitions for the vcl block.
//
// The capabilities are loaded into the database table when the block is
// installed or updated. Whenever the capability definitions are updated,
// the module version number should be bumped up.
//
// The system has four possible values for a capability:
// CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
//
//
// CAPABILITY NAMING CONVENTION
//
// It is important that capability names are unique. The naming convention
// for capabilities that are specific to modules and blocks is as follows:
//   [mod/block]/<plugin_name>:<capabilityname>
//
// component_name should be the same as the directory name of the mod or block.
//
// Core moodle capabilities are defined thus:
//    moodle/<capabilityclass>:<capabilityname>
//
// Examples: mod/forum:viewpost
//           block/recent_activity:view
//           moodle/site:deleteuser
//
// The variable name for the capability definitions array is $capabilities

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'block/vcl:reservation' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'user' => CAP_ALLOW,
            'guest' => CAP_PREVENT
        )
    ),

    'block/vcl:blockallocation' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_PREVENT,
            'guest' => CAP_PREVENT
        )
    )
);
