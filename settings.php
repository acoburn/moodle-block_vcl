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

defined('MOODLE_INTERNAL') || die;

//////////////////////////////////////////////////////////////
//
//      VCL API settings
//
//////////////////////////////////////////////////////////////
$settings->add(
    new admin_setting_heading(
        'block_vcl/headerconfig', 
        get_string('headerconfig', 'block_vcl'), 
        get_string('descconfig', 'block_vcl')
    ));

// Setting for VCL API URL
$DEFAULT_HOST = preg_replace(
                    "/^(?:.*\.)?(\w+\.\w{2,3})$/",
                    "\\1",
                    $_SERVER['SERVER_NAME']
                );

$settings->add(
    new admin_setting_configtext(
        'block_vcl/api',
        get_string('labelhost', 'block_vcl'),
        get_string('deschost', 'block_vcl'),
        "https://vcl." . $DEFAULT_HOST,
        PARAM_URL,
        40
    ));

$settings->add(
    new admin_setting_configselect(
        'block_vcl/authmethod',
        get_string('labelauthmethod', 'block_vcl'),
        get_string('descauthmethod', 'block_vcl'), '',
        array(
            "delegated" => "Shibboleth (i.e. delegated authentication)",
            "internal" => "LDAP"
        )
    ));

// Setting for VCL API password
$settings->add(
    new admin_setting_configpasswordunmask(
        'block_vcl/authentication',
        get_string('labelauthentication', 'block_vcl'),
        get_string('descauthentication', 'block_vcl'),
        ''
    ));

$settings->add(
    new admin_setting_configtext(
        'block_vcl/helpurl',
        get_string('labelhelpurl', 'block_vcl'),
        get_string('deschelpurl', 'block_vcl'),
        '',
        PARAM_URL
    ));

//////////////////////////////////////////////////////////////
//
//      Affiliation configuration
//
//////////////////////////////////////////////////////////////
$settings->add(
    new admin_setting_heading(
        'block_vcl/affilheader', 
        get_string('headeraffil', 'block_vcl'), 
        get_string('descheaderaffil', 'block_vcl')
    ));

// Populate the available options depending on the availability
// of certain fields and whether the field contains '@domain'.
$options = array();
foreach(array('username', 'email', 'idnumber') as $key){
    if(property_exists($USER, $key)){
        $data = explode('@', $USER->$key);
        if(count($data) > 1){
            $options[$key] = '$USER->' . $key . ' (' . array_pop($data) . ')';
        }    
    }
}

if($USER->institution){
    $options["institution"] = '$USER->institution (' . $USER->institution . ')';
}

$options["custom"] = get_string('use_value_below', 'block_vcl');

$settings->add(
    new admin_setting_configselect(
        'block_vcl/affiliation',
        get_string('labelaffiliation', 'block_vcl'),
        get_string('descaffiliation', 'block_vcl'), '',
        $options
    ));

// Configure a custom affiliation value
$settings->add(
    new admin_setting_configtext(
        'block_vcl/customaffil',
        get_string('labelcustomaffiliation', 'block_vcl'),
        get_string('desccustomaffiliation', 'block_vcl'),
        '',
        PARAM_NOTAGS
    ));

// Remove any TLD suffix from an affiliation value
$settings->add(
    new admin_setting_configcheckbox(
        'block_vcl/affilstriptld',
        get_string('labelaffiltld', 'block_vcl'),
        get_string('descaffiltld', 'block_vcl'),
        0
    ));

// Convert the affiliation value to ALL CAPS
$settings->add(
    new admin_setting_configcheckbox(
        'block_vcl/affilupper',
        get_string('labelaffilupper', 'block_vcl'),
        get_string('descaffilupper', 'block_vcl'),
        0
    ));


//////////////////////////////////////////////////////////////
//
//      Reservation settings
//
//////////////////////////////////////////////////////////////
$settings->add(
    new admin_setting_heading(
        'block_vcl/reservationheader', 
        get_string('headerreserv', 'block_vcl'), 
        get_string('descheaderreserv', 'block_vcl')
    ));

$settings->add(
    new admin_setting_configtext(
        'block_vcl/reservationduration',
        get_string('labelreservationduration', 'block_vcl'),
        get_string('descreservationduration', 'block_vcl'),
        60,
        PARAM_INT
    ));

$settings->add(
    new admin_setting_configtext(
        'block_vcl/reservationmax',
        get_string('labelreservationmax', 'block_vcl'),
        get_string('descreservationmax', 'block_vcl'),
        6,
        PARAM_INT
    ));

$settings->add(
    new admin_setting_configtext(
        'block_vcl/reservationextension',
        get_string('labelreservationextension', 'block_vcl'),
        get_string('descreservationextension', 'block_vcl'),
        30,
        PARAM_INT
    ));

//////////////////////////////////////////////////////////////
//
//      Course group management settings
//
//////////////////////////////////////////////////////////////
$settings->add(
    new admin_setting_heading(
        'block_vcl/groupconfig',
        get_string('headergroup', 'block_vcl'),
        get_string('descgroup', 'block_vcl')
    ));

$settings->add(
    new admin_setting_configcheckbox(
        'block_vcl/enablecoursegroup',
        get_string('labelcoursegroup', 'block_vcl'),
        get_string('desccoursegroup', 'block_vcl'),
        0
    ));

$settings->add(
    new admin_setting_configtext(
        'block_vcl/moodleuser',
        get_string('labelmoodleuser', 'block_vcl'),
        get_string('descmoodleuser', 'block_vcl'),
        ''
    ));

$settings->add(
    new admin_setting_configpasswordunmask(
        'block_vcl/moodlepass',
        get_string('labelmoodlepass', 'block_vcl'),
        get_string('descmoodlepass', 'block_vcl'),
        ''
    ));

$settings->add(
    new admin_setting_configtext(
        'block_vcl/moodlegroup',
        get_string('labelmoodlegroup', 'block_vcl'),
        get_string('descmoodlegroup', 'block_vcl'),
        ''
    ));

$settings->add(
    new admin_setting_configtext(
        'block_vcl/moodleaffil',
        get_string('labelmoodleaffil', 'block_vcl'),
        get_string('descmoodleaffil', 'block_vcl'),
        ''
    ));

$settings->add(
    new admin_setting_configtext(
        'block_vcl/moodlecomputers',
        get_string('labelmoodlecomputers', 'block_vcl'),
        get_string('descmoodlecomputers', 'block_vcl'),
        ''
    ));

$settings->add(
    new admin_setting_configtext(
        'block_vcl/vclprivnode',
        get_string('labelvclprivnode', 'block_vcl'),
        get_string('descvclprivnode', 'block_vcl'),
        ''
    ));




