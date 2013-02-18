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

$string['pluginname'] = 'Virtual Computing';

$string['autoconnect'] = "Auto Login";
$string['autoenroll'] = "VCL user group";
$string['autoenroll_help'] = "Add members of this course to the selected VCL group.";
$string['cancel'] = 'Cancel';
$string['cancelreservation'] = "Cancel this reservation";
$string['config_auth'] = "API authentication token";
$string['config_affil'] = "Default affiliation";
$string['connect'] = 'Connect!';
$string['createreservation'] = 'Create Reservation';
$string['currentreservations'] = 'Current Reservations';
$string['date'] = 'Date';
$string['descauthentication'] = "An authentication token to use with the VCL API. This is only necessary when using delegated authentication (i.e. Shibboleth).";
$string['descaffiliation'] = "This specifies how to identify the VCL affiliation value. For \$USER fields, this would be the value following a '@' character.";
$string['descaffiltld'] = "Select this option to remove the Top Level Domain (e.g. .edu, .org, .com) from the affiliation value";
$string['descaffilupper'] = "Select this option to convert the affiliation value to ALL CAPS.";
$string['descauthmethod'] = "Select the authentication method used by the VCL.";
$string['descenableautoconnect'] = "Present users with a one-click auto-connect option.";
$string['descautoenroll'] = "For each VCL block in Moodle, automatically add enrolled users to a selected VCL group."; 
$string['descconfig'] = "The Virtual Computing Lab block allows moodle users to make and control VCL reservations directory from within Moodle."; 
$string['descconfigimages'] = "Select the image(s) that enrolled members of this course should be allowed to access.";
$string['desccoursegroup'] = "This setting enables course-based group creation in the VCL";
$string['desccustomaffiliation'] = "An affiliation value to use for all users or in cases where the affiliation cannot otherwise be found";
$string['descgroup'] = "These settings allow Moodle to create and manage course-based groups in the VCL. For each Moodle course with this block enabled, a group in the VCL will be created. Each block instance can be configured to allow access to particular VCL environments, and each user enrolled in the course will then automatically be given access to the environment.";
$string['descheaderaffil'] = "Define how to construct a VCL Affiliation value for users of the VCL block.";
$string['descheaderreserv'] = "Control the default settings for user reservations.";
$string['deschelpurl'] = "A link with setup or usage instructions related to the VCL.";
$string['deschost'] = "The URL of the VCL web application";
$string['descmoodleaffil'] = "The affiliation for newly created user groups";
$string['descmoodlecomputers'] = "The VCL computer group to which new image groups will be mapped";
$string['descmoodlegroup'] = "The managing group for newly created resource (image) groups";
$string['descmoodlepass'] = "A password for the user listed above";
$string['descmoodleuser'] = "A VCL user with the ability to create and assign resource groups as well as manage nodes in the privilege table. It is recommended that this be a Local user (please not admin@Local!) with full control over the node defined below.";
$string['descvclprivnode'] = "The path ('/'-delimited) to the parent node in the privilege tree under which course nodes will be created. For example, this might be 'VCL/My Affiliation/Courses'";
$string['descreservationmax'] = "The maximum total length of time for a reservation (in hours)";
$string['descreservationextension'] = "The length of time by which a reservation can be extended (in minutes)";
$string['descreservationduration'] = "The default length of a reservation (in minutes)";
$string['downloadrdp'] = "Download an RDP file";
$string['end'] = 'End';
$string['endreservation'] = "End this reservation";
$string['enterrdp'] = "Enter the following into an RDP client";
$string['extend'] = 'Extend';
$string['extendby'] = "Extend this reservation by";
$string['fullscreen'] = "Full Screen";
$string['getrdp'] = "Get RDP file";
$string['headerconfig'] = "Configure VCL blocks";
$string['headerconfigimages'] = "VCL Computing Environments";
$string['headeraffil'] = "Configure the affiliation settings";
$string['headergroup'] = "Allow Moodle to create course-based groups in the VCL";
$string['headerreserv'] = "Configure reservation settings";
$string['hour'] = 'Hour';
$string['invaliddate'] = "You provided an invalid date format.";
$string['ipaddress'] = "Host";
$string['labelaffiliation'] = "Affiliation value";
$string['labelaffiltld'] = "Remove TLD";
$string['labelaffilupper'] = "Convert to ALL CAPS";
$string['labelauthentication'] = "Authentication token";
$string['labelauthmethod'] = "Authentication method";
$string['labelautoenroll'] = "Add courselist to VCL groups";
$string['labelenableautoconnect'] = "Enable auto-connect";
$string['labelcoursegroup'] = "Enable group management";
$string['labelcustomaffiliation'] = "Default affiliation";
$string['labelhelpurl'] = "Help URL";
$string['labelhost'] = "VCL host";
$string['labelmoodleaffil'] = "User group affiliation";
$string['labelmoodlecomputers'] = "VCL computer group";
$string['labelmoodlegroup'] = "VCL managing group";
$string['labelmoodlepass'] = "VCL user password";
$string['labelmoodleuser'] = "VCL user account";
$string['labelreservationextension'] = "Reservation extension";
$string['labelreservationmax'] = "Maximum extension time";
$string['labelreservationduration'] = "Reservation duration";
$string['labelvclprivnode'] = "VCL node for courses";
$string['later'] = 'Later';
$string['minute'] = 'Minute';
$string['minutes'] = "minutes";
$string['minutesremaining'] = "minutes remaining";
$string['newreservation'] = 'New Reservations';
$string['noimages'] = "In order to setup your account, please first login to the";
$string['noreservations'] = "You have no current reservations.";
$string['now'] = 'Now';
$string['otherconnections'] = "Other ways to connect";
$string['password'] = "Password";
$string['pastreservations'] = "Cannot make reservation in the past.";
$string['ready'] = 'Your reservation is ready!';
$string['reservationfailed'] = "We are sorry, but you reservation has failed.";
$string['reservationloading'] = "Your reservation is loading and will be ready in approximately";
$string['reservationtimedout'] = "Your reservation has timed out.";
$string['screen'] = "Screen";
$string['selectimage'] = 'Select an image to reserve';
$string['setup'] = 'VCL Help';
$string['time'] = 'Time';
$string['use_value_below'] = 'Use the custom affiliation value below';
$string['username'] = "Username";
$string['vcl'] = "Virtual Computing Lab";
