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

require_once($CFG->dirroot . '/blocks/vcl/locallib.php');
require_once($CFG->dirroot . '/blocks/vcl/vcl.class.php');

class block_vcl extends block_base {
    /**
     * block initializations
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_vcl');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        if(!has_capability('block/vcl:reservation', $this->context)){
            return ''; // Block isn't useful unless the user can make reservations
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $jsmodule = array(
            "name" => "block_vcl",
            "fullpath" => "/blocks/vcl/vcl.js",
            "requires" => array('io', 'node', 'yui2-calendar',
                                'yui2-slider', 'json-parse', 'transition'));       
        $this->page->requires->js('/blocks/vcl/vcl.js', true);
        $this->page->requires->js_init_call('M.block_vcl.init', NULL, false, $jsmodule);
        
        $this->content->text .= "<div id=\"vcl_block\">\n";
        $this->content->text .= "  <div id=\"vclCurrentReservations\">\n";
        $this->content->text .= "    <h3>" . get_string('currentreservations', 'block_vcl') . "</h3>\n";
        $this->content->text .= "    <div id=\"vclCurrent\">\n";
        $this->content->text .= "      <img src=\"{$CFG->wwwroot}/blocks/vcl/images/throbber.gif\" class=\"throbber\" />\n";
        $this->content->text .= "    </div>\n";
        $this->content->text .= "  </div>\n"; 
        $this->content->text .= "  <div id=\"vclScheduleReservation\">\n";
        $this->content->text .= "    <h3>" . get_string('newreservation', 'block_vcl') . "</h3>\n";
        $this->content->text .= "    <img src=\"{$CFG->wwwroot}/blocks/vcl/images/throbber.gif\" class=\"throbber\" />\n";
        $this->content->text .= "    <div id=\"vclNew\">\n";
        $this->content->text .= "      <form id=\"vclReservationForm\" method=\"post\" action=\"{$CFG->wwwroot}/blocks/vcl/ajax.php\">\n";
        $this->content->text .= "        <input type=\"hidden\" name=\"action\" value=\"newReservation\" />\n";
        $this->content->text .= "        <p>\n";
        $this->content->text .= "          <select id=\"vclImageList\" title=\"" . get_string('selectimage', 'block_vcl') . "\" name=\"image\">\n";
        $this->content->text .= "          </select>\n";
        $this->content->text .= "          <select id=\"vclWhen\" name=\"when\">\n";
        $this->content->text .= "            <option selected=\"selected\">" . get_string('now', 'block_vcl') . "</option>\n";
        $this->content->text .= "            <option>" . get_string('later', 'block_vcl') . "</option>\n";
        if(has_capability('block/vcl:blockallocation', $this->context)){
            // stub for block allocation option
        }
        $this->content->text .= "          </select>\n";
        $this->content->text .= "        </p>\n";
        $this->content->text .= "        <div id=\"vclDateSelector\">\n";
        $this->content->text .= "          " . get_string('date', 'block_vcl') . ":<br />\n";
        $this->content->text .= "          <input type=\"text\" name=\"date\" id=\"vclDate\" value=\"\" />\n";
        $this->content->text .= "          <div id=\"vclCalContainer\"></div>\n";
        $this->content->text .= "          <br />\n";
        $this->content->text .= "          " . get_string('time', 'block_vcl') . ":<br />\n";
        $this->content->text .= "          <input type=\"text\" name=\"time\" id=\"vclTime\" value=\"\" />\n";
        $this->content->text .= "          <div id=\"vclTimeContainer\">\n";
        $this->content->text .= "            <table>\n";
        $this->content->text .= "              <tr>\n";
        $this->content->text .= "                <td>" . get_string('hour', 'block_vcl') . "</td>\n";
        $this->content->text .= "                <td>\n";
        $this->content->text .= "                  <div id=\"vclTimeContainerHour\">\n";
        $this->content->text .= "                    <div id=\"slider-thumb-hour\"><img src=\"{$CFG->wwwroot}/blocks/vcl/images/slider.gif\"></div>\n";
        $this->content->text .= "                  </div>\n";
        $this->content->text .= "                </td>\n";
        $this->content->text .= "              </tr>\n";
        $this->content->text .= "              <tr>\n";
        $this->content->text .= "                <td>" . get_string('minute', 'block_vcl') . "</td>\n";
        $this->content->text .= "                <td>\n";
        $this->content->text .= "                  <div id=\"vclTimeContainerMinute\">\n";
        $this->content->text .= "                    <div id=\"slider-thumb-minute\"><img src=\"{$CFG->wwwroot}/blocks/vcl/images/slider.gif\"></div>\n";
        $this->content->text .= "                  </div>\n";
        $this->content->text .= "                </td>\n";
        $this->content->text .= "              </tr>\n";
        $this->content->text .= "            </table>\n";
        $this->content->text .= "          </div>\n";
        $this->content->text .= "        </div>\n";
        $this->content->text .= "        <p>\n";
        $this->content->text .= "          <input type=\"button\" id=\"vclButton\" value=\"". get_string('createreservation', 'block_vcl') . "\" />\n";
        $this->content->text .= "        </p>\n";
        $this->content->text .= "      </form>\n";
        $this->content->text .= "    </div>\n";
        $this->content->text .= "  </div>\n";
        if(get_config('block_vcl', 'helpurl')){
            $this->content->text .= "  <div id=\"vclHelp\">\n";
            $this->content->text .= "    <a href=\"" . get_config('block_vcl', 'helpurl') . "\">" . get_string('setup', 'block_vcl') . "</a>";
            $this->content->text .= "  </div>\n";
        }
        $this->content->text .= "</div>\n";

        return $this->content;
    }

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * allow more than one instance of the block on a page
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        //allow more than one instance on a page
        return false;
    }

    /**
     * allow instances to have their own configuration
     *
     * @return boolean
     */
    function instance_allow_config() {
        //allow instances to have their own configuration
        return true;
    }

    /**
     * displays instance configuration form
     *
     * @return boolean
     */
    function instance_config_print() {
        return false;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        // only display in the context of a course
        return array('course-view'=>true);
    }

    /**
     * set up the groups and enrollment in the VCL
     * when a new block instance is created
     *
     * @return bool
     */
    public function instance_create(){
        if(get_config('block_vcl', 'enablecoursegroup') &&
                get_config('block_vcl', 'api')){
            if(get_config('block_vcl', 'moodleuser') && 
                    get_config('block_vcl', 'moodlepass') &&
                    get_config('block_vcl', 'moodleaffil') &&
                    get_config('block_vcl', 'moodlegroup') &&
                    get_config('block_vcl', 'vclprivnode')){
                $vcl = new VCL(
                    get_config('block_vcl', 'api'),
                    get_config('block_vcl', 'moodleuser'),
                    get_config('block_vcl', 'moodlepass')
                );
                if($nodes = $vcl->getNodes()){
                    if($parentNode = vcl_node_find($nodes, get_config('block_vcl', 'vclprivnode'))){
                        $courseNode = 0;
                        $courseName = $this->vclUserGroup();
                        
                        foreach(vcl_node_children($nodes, $parentNode) as $id => $path){
                            if($path == get_config('block_vcl', 'vclprivnode') . "/" . $courseName){
                                $courseNode = $id;
                                break;
                            }
                        }
                        if(!$courseNode){
                            if(!$courseNode = $vcl->addNode($courseName, $parentNode)){
                                vcl_error("Could not add course node $courseName at $parentNode: {$vcl->errmsg}");
                            }
                        }
                        if($courseNode){
                            if(!$vcl->addUserGroup(
                                $courseName,
                                get_config('block_vcl', 'moodleaffil'),
                                get_config('block_vcl', 'moodleuser'),
                                get_config('block_vcl', 'moodlegroup'),
                                min(240, get_config('block_vcl', 'reservationmax') * 60),
                                get_config('block_vcl', 'reservationmax') * 60,
                                get_config('block_vcl', 'reservationextension'),
                                2
                            )){
                                vcl_error("Could not add user group: {$courseName}: {$vcl->errmsg}");
                            }
                            if(!$vcl->addResourceGroup(
                                $this->vclImageGroup(),
                                get_config('block_vcl', 'moodlegroup'),
                                "image"
                            )){
                                vcl_error("Could not add resource group: {$vcl->errmsg}");
                            }
                            if(!$vcl->addUserGroupPriv(
                                $courseName,
                                get_config('block_vcl', 'moodleaffil'),
                                $courseNode,
                                array("imageCheckOut")
                            )){
                                vcl_error("Error setting user group privileges: {$vcl->errmsg}");
                            } 
                            if(!$vcl->addImageGroupToComputerGroup(
                                $this->vclImageGroup(),
                                get_config('block_vcl', 'moodlecomputers')
                            )){
                                vcl_error("Error mapping image to computer group: {$vcl->errmsg}");
                            }
                            if(!$vcl->addResourceGroupPriv(
                                $this->vclImageGroup(),
                                "image",
                                $courseNode,
                                array("available")
                            )){
                                vcl_error("Error adding resource group privilege: {$vcl->errmsg}");
                            }
                            $users = array();
                            foreach(get_enrolled_users($this->context) as $user){
                                if($vcluser = vcl_get_username($user)){
                                    array_push($users, $vcluser);
                                }
                            }
                            if(count($users)){
                                if(!$vcl->addUsersToGroup(
                                    $courseName,
                                    get_config('block_vcl', 'moodleaffil'),
                                    $users
                                )){
                                    vcl_error("Error adding users to group: {$vcl->errmsg}, {$vcl->message}");
                                }
                            }
                        }
                    } else {
                        vcl_error("Could not find privilege node: " . get_config('block_vcl', 'vclprivnode'));
                    }
                } else {
                    vcl_error("Could not retrieve node: " . $vcl->errmsg);
                }
            } else {
                vcl_error("Not all required configuration values present");
            }
        }
        return true;
    } 

    /**
     *  clean up VCL groups when an instance is deleted
     *
     *  @return bool
     */
    public function instance_delete(){
        if(get_config('block_vcl', 'enablecoursegroup') && 
                get_config('block_vcl', 'api')){
            if(get_config('block_vcl', 'moodleuser') &&
                    get_config('block_vcl', 'moodlepass') &&
                    get_config('block_vcl', 'vclprivnode') &&
                    get_config('block_vcl', 'moodleaffil')){
                $vcl = new VCL(
                    get_config('block_vcl', 'api'),
                    get_config('block_vcl', 'moodleuser'),
                    get_config('block_vcl', 'moodlepass')
                );
                if($nodes = $vcl->getNodes()){
                    $courseName = $this->vclUserGroup();
                    if($parentNode = vcl_node_find($nodes, get_config('block_vcl', 'vclprivnode'))){
                        $courseNode = 0;
                        foreach(vcl_node_children($nodes, $parentNode) as $id => $path){
                            if($path == get_config('block_vcl', 'vclprivnode') . "/" . $courseName){
                                $courseNode = $id;
                                break;
                            }
                        }
                        if($courseNode){
                            if(!$vcl->removeNode($courseNode)){
                                vcl_error("Could not remove privilege node: {$vcl->errmsg}");
                            }
                        }
                    } else {
                        vcl_error("Could not find privilege node: " . get_config('block_vcl', 'vclprivnode'));
                    }
                    if(!$vcl->removeUserGroup(
                        $courseName,
                        get_config('block_vcl', 'moodleaffil')
                    )){
                        vcl_error("Could not remove user group: {$vcl->errmsg}");
                    }
                    if(!$vcl->removeResourceGroup(
                        $this->vclImageGroup(),
                        "image"
                    )){
                        vcl_error("Could not remove resource group: {$vcl->errmsg}");
                    }
                } else {
                    vcl_error("Could not retrieve privilege nodes: {$vcl->errmsg}");
                }
            } else {
                vcl_error("Not all required configuration values present");
            }
        }
        return true;
    }

    /**
     *  Run some custom functions when an instance configuration is saved.
     *  This is particularly useful for immediately adding a course roster
     *  to a selected VCL group.
     */
    public function instance_config_save($data) {
        if(get_config('block_vcl', 'api') &&
               get_config('block_vcl', 'enablecoursegroup')){
           if(get_config('block_vcl', 'moodleuser') &&
                    get_config('block_vcl', 'moodlepass')){
                $vcl = new VCL(
                    get_config('block_vcl', 'api'),
                    get_config('block_vcl', 'moodleuser'),
                    get_config('block_vcl', 'moodlepass')
                );

                $userGroup = $this->vclUserGroup();
                $imageGroup = $this->vclImageGroup();

                $existing_images = array();
                if($images = $vcl->getGroupImages($imageGroup)){
                    foreach($images as $image){
                        $existing_images[$image['id']] = $image['name'];
                    }
                } else {
                    vcl_error("Error retrieving images from $imageGroup: {$vcl->errmsg}");
                }
                foreach($data as $id => $value){
                    if(preg_match("/^image_(\d+)$/", $id, $matches)){
                        $id = $matches[1];
                        if(!$value && array_key_exists($id, $existing_images)){
                            if(!$vcl->removeImageFromGroup($imageGroup, $id)){
                                vcl_error("Error removing image from group: $imageGroup: {$vcl->errmsg}");
                            }
                        } else if($value && !array_key_exists($id, $existing_images)){
                            if(!$vcl->addImageToGroup($imageGroup, $id)){
                                vcl_error("Error adding image to group: $imageGroup: {$vcl->errmsg}");
                            }
                        }
                    }
                }
            } else {
                vcl_error("Not all required configuration values present");
            }
        }
        return parent::instance_config_save($data);
    }

    /**
     *  run a cron script
     *
     *  @return bool
     */
    public function cron(){
        global $DB;

        if(get_config('block_vcl', 'api') &&
                get_config('block_vcl', 'enablecoursegroup')){
            if(get_config('block_vcl', 'moodleuser') &&
                    get_config('block_vcl', 'moodlepass') &&
                    get_config('block_vcl', 'moodleaffil')){
                $instances = $DB->get_records('block_instances',
                                          array('blockname' => 'vcl'));

                foreach($instances as $instance){
                    $block = block_instance('vcl', $instance);

                    $vcl = new VCL(
                        get_config('block_vcl', 'api'),
                        get_config('block_vcl', 'moodleuser'),
                        get_config('block_vcl', 'moodlepass')
                    );

                    $vclusers = array();
                    if($vclmembers = $vcl->getUserGroupMembers($group, $affiliation)){
                        foreach($vclmembers as $member){
                            array_push($vclusers, $member);
                        }
                    } else {
                        vcl_error("Could not retrieve members from {$group}@{$affiliation}: {$vcl->errmsg}");
                    }

                    $moodleusers = array();
                    foreach(get_enrolled_users($block->context) as $user){
                        if($vcluser = vcl_get_username($user)){
                            array_push($moodleusers, $vcluser);
                        }
                    }

                    $add = array_diff($moodleusers, $vclusers);
                    if(count($add)){
                        if(!$vcl->addUsersToGroup(
                            $block->vclUserGroup(),
                            get_config('block_vcl', 'moodleaffil'),
                            $add
                        )){
                            vcl_error("Error adding users to group: " . $block->vclUserGroup() . ": {$vcl->errmsg}");
                        }
                    }
                    
                    $remove = array_diff($vclusers, $moodleusers);
                    if(count($remove)){
                        if(!$vcl->removeUsersFromGroup(
                            $block->vclUserGroup(),
                            get_config('block_vcl', 'moodleaffil'),
                            $remove
                        )){
                            vcl_error("Error removing users from group: " . $block->vclUserGroup() . ": {$vcl->errmsg}");
                        }
                    }
                }
            } else {
                vcl_error("Not all required configuration values present");
            }
        }
        return true;
    }

    /**
     *  Retrieve the name of the VCL group corresponding to this block instance.
     */
    public function vclUserGroup(){
        $name = $this->context->get_course_context()->get_context_name(false, true);
        if(strlen($name) > 30){
            $name = preg_replace("/^((?:\w+\s+){2}).+\s([^\s]+)$/", "\\1\\2", $name);
        }
        if(strlen($name) > 30){
            $name = preg_replace("/^(\w+\s+).+\s([^\s]+)$/", "\\1\\2", $name);
        }
        if(strlen($name) > 30){
            $name = preg_replace("/\s([^\s]+)$/", "\\1", $name);
        }
        return vcl_node_clean($name);
    }

    /**
     *  Retrieve the name of the VCL image group corresponding to this 
     *  block instance
     */
    public function vclImageGroup(){
        return $this->vclUserGroup() . " Images";
    }
}
