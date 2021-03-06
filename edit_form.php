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

require_once($CFG->dirroot . '/blocks/vcl/vcl.class.php');

class block_vcl_edit_form extends block_edit_form {

    protected function specific_definition($mform){
        global $USER;

        if(get_config('block_vcl', 'enablecoursegroup')){
            
            $vcl = new VCL(
                get_config('block_vcl', 'api'),
                get_config('block_vcl', 'moodleuser'),
                get_config('block_vcl', 'moodlepass')
            );

            $images = $vcl->getImages();
            if(count($images)){
                $mform->addElement('header', 'vclimages', get_string("headerconfigimages", 'block_vcl'));
                $mform->addElement('html', '<p>' . get_string("descconfigimages", 'block_vcl') . "</p>");
                foreach($images as $image){
                    $description = trim($image['description']);
                    $mform->addElement('advcheckbox', 'config_image_' . $image['id'], "<strong>" . $image['name'] . "</strong>", " " . $description);
                }
            }
        }
    }
}
