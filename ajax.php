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

require('../../config.php');
require_once($CFG->dirroot . '/blocks/vcl/locallib.php');
require_once($CFG->dirroot . '/blocks/vcl/vcl.class.php');

require_login();

$vcl = new VCL(
    get_config('block_vcl', 'api'),
    vcl_get_username(),
    get_config('block_vcl', 'authentication'));

if (!isset($_REQUEST["action"])){
    exit(0);
}

switch($_REQUEST["action"]){
    /* ******************************************************** *
     *  Generate the RDP file for connecting to the machine.
     * ******************************************************** */
    case "connect":
        if($rc = $vcl->getConnectData($_REQUEST["id"], $_SERVER["REMOTE_ADDR"])){
            if ($reservations = $vcl->getReservations()){
                $name = "Connect";
                foreach ($reservations as $r){
                    if ($r['requestid'] == $_REQUEST['id']){
                        $name = $r['imagename'];
                        break;
                    }
                }
                $ipaddress = $rc['serverIP'];
                $username = $rc['user'];
                $password = $rc['password'];

                header("Content-type: application/rdp");
                header("Content-Disposition: inline; filename=\"{$name}.rdp\"");
                print "screen mode id:i:1\r\n";
                print "desktopwidth:i:1024\r\n";
                print "desktopheight:i:768\r\n";
                print "session bpp:i:16\r\n";
                print "winposstr:s:0,1,382,71,1182,671\r\n";
                print "full address:s:$ipaddress\r\n";
                print "compression:i:1\r\n";
                print "keyboardhook:i:2\r\n";
                print "audiomode:i:0\r\n";
                print "redirectdrives:i:1\r\n";
                print "redirectprinters:i:1\r\n";
                print "redirectcomports:i:1\r\n";
                print "redirectsmartcards:i:1\r\n";
                print "redirectclipboard:i:1\r\n";
                print "displayconnectionbar:i:1\r\n";
                print "auto connect:i:1\r\n";
                print "autoreconnection enabled:i:1\r\n";
                print "username:s:$username\r\n";
                print "clear password:s:$password\r\n";
                print "authentication level:i:2\r\n";
                print "prompt for credentials:i:0\r\n";
                print "promptcredentialonce:i:1\r\n";
                print "alternate shell:s:\r\n";
                print "shell working directory:s:\r\n";
                print "disable wallpaper:i:0\r\n";
                print "disable full window drag:i:1\r\n";
                print "disable menu anims:i:1\r\n";
                print "disable themes:i:0\r\n";
                print "disable cursor setting:i:0\r\n";
                print "bitmapcachepersistenable:i:1\r\n";
                print "use multimon:i:0\r\n";
                print "audiocapturemode:i:0\r\n";
                print "videoplaybackmode:i:1\r\n";
                print "connection type:i:2\r\n";
                print "allow font smoothing:i:0\r\n";
                print "allow desktop composition:i:0\r\n";
                print "redirectposdevices:i:0\r\n";
                print "redirectdirectx:i:1\r\n";
                print "negotiate security layer:i:1\r\n";
                print "remoteapplicationmode:i:0\r\n";
                print "gatewayhostname:s:\r\n";
                print "gatewayusagemethod:i:4\r\n";
                print "gatewaycredentialsource:i:4\r\n";
                print "gatewayprofileusagemethod:i:0\r\n";
                print "use redirection server name:i:0\r\n";
                exit(0);
            }
        } else if($vcl->errcode){
            header("Content-type: text/plain");
            print $vcl->errmsg;
        }
        break;

    /* ******************************************************** *
     *  Extend a reservation by a specified amount of time.
     * ******************************************************** */
    case "extendReservation":
        header("Content-type: text/plain");
        if($vcl->extendReservation($_REQUEST['id'], get_config('block_vcl', 'reservationextension'))){
            // print $vcl->message;
        } else if ($vcl->errcode) {
            print json_encode(array("error" => $vcl->errmsg));
        }
        break;

    /* ******************************************************** *
     *  Generate some HTML for the reservation list.
     * ******************************************************** */
    case "getReservations":
        header("Content-type: text/plain");
        $json = array();
        if($reservations = $vcl->getReservations()){
            $i = 0;
            foreach($reservations as $r){
                $html = "<div id=\"vclReservation{$r['requestid']}\">\n";
                if($i > 0)
                    $html .= "<hr />\n";
                $html .= "<b>{$r['imagename']}</b> ";
                $html .= "<div id=\"vclStatus{$r['requestid']}\">\n";
                if($status = $vcl->getRequestStatus($r['requestid'])){
                    $json[$r['requestid']] = array("status" => $status['status']);
                    switch($status['status']){
                        case "ready":
                            $html .= "<p>" . get_string('ready', 'block_vcl') . "<br /> " . floor(($r['end'] - time()) / 60) . " " . get_string('minutesremaining', 'block_vcl') . ".</p>";
                            $html .= "<p style=\"text-align:center\">";
                            $rc = $vcl->getConnectData($r['requestid'], $_SERVER["REMOTE_ADDR"]);
                            $isRdp = false;
                            $isSsh = false;
                            foreach($rc['connectMethods'] as $cm){
                                if ($cm['connectport'] == 3389) {
                                    $isRdp = true;
                                }
                                if ($cm['connectport'] == 22) {
                                    $isSsh = true;
                                }
                            }
                            if ($isRdp && get_config('block_vcl', 'enableautoconnect')){
                                $params = "forwardDisks=yes&forwardPrinters=yes&" .
                                          "forwardSerial=yes&forwardAudio=0&" .
                                          "drawDesktop=yes&title={$r['imagename']}";
                                $html .= "<input type=\"button\" title=\"" .
                                         get_string('autoconnect', 'block_vcl') . "\" value=\"" .
                                         get_string('connect', 'block_vcl') .
                                         "\" onclick=\"M.block_vcl.connect('{$rc['user']}', '{$rc['password']}', '{$rc['serverIP']}', {$r['requestid']}, '{$params}')\" />\n";

                                // Disable extensions beyond 8 hours.
                                $disabled = "";
                                if(($r['end'] - $r['start']) / 60 / 60 >= get_config('block_vcl', 'reservationmax'))
                                    $disabled = "disabled=\"disabled\"";
                                $html .= "<input type=\"button\" $disabled title=\"" .
                                         get_string('extendby', 'block_vcl') . " " .
                                         get_config('block_vcl', 'reservationextension') . " " .
                                         get_string('minutes', 'block_vcl') .
                                         "\" onclick=\"M.block_vcl.extend('{$r['requestid']}')\" value=\"" .
                                         get_string('extend', 'block_vcl') . "\" />\n";
                                $html .= "<input type=\"button\" title=\"" .
                                         get_string('endreservation', 'block_vcl') .
                                         "\" onclick=\"M.block_vcl.remove('{$r['requestid']}')\" value=\"" .
                                         get_string('end', 'block_vcl') . "\" />\n";

                                $html .= "<br />\n";
                                $html .= get_string('screen', 'block_vcl') . ": ";
                                $html .= "<select id=\"vcl_block_screen_{$r['requestid']}\">";
                                $html .= "<option value=\"fullscreen\">".get_string('fullscreen', 'block_vcl')."</option>";
                                $html .= "<option value=\"1280x1024\">1280x1024</option>";
                                $html .= "<option selected=\"selected\" value=\"1024x768\">1024x768</option>";
                                $html .= "<option value=\"800x600\">800x600</option>";
                                $html .= "<option value=\"640x480\">640x480</option>";
                                $html .= "</select>";

                                $html .= "</p>";
                                $html .= "<br />";
                                $html .= "<p><a href=\"#\" id=\"connAlt_{$r['requestid']}\" class=\"inactive\" onclick=\"M.block_vcl.show_alternatives({$r['requestid']});return false;\">".get_string('otherconnections', 'block_vcl') . "</a></p>";
                                $html .= "<div class=\"alt_connection\">";
                            } else {
                                // Disable extensions beyond 8 hours.
                                $disabled = "";
                                if(($r['end'] - $r['start']) / 60 / 60 >= get_config('block_vcl', 'reservationmax'))
                                    $disabled = "disabled=\"disabled\"";
                                $html .= "<input type=\"button\" $disabled title=\"" .
                                         get_string('extendby', 'block_vcl') . " " .
                                         get_config('block_vcl', 'reservationextension') . " " .
                                         get_string('minutes', 'block_vcl') .
                                         "\" onclick=\"M.block_vcl.extend('{$r['requestid']}')\" value=\"" .
                                         get_string('extend', 'block_vcl') . "\" />\n";
                                $html .= "<input type=\"button\" title=\"" .
                                         get_string('endreservation', 'block_vcl') .
                                         "\" onclick=\"M.block_vcl.remove('{$r['requestid']}')\" value=\"" .
                                         get_string('end', 'block_vcl') . "\" />\n";

                                $html .= "</p>";
                            }

                            if ($isRdp) {
                                $html .= "<p style=\"text-align:center\">".get_string('downloadrdp', 'block_vcl').":<br />";
                                $html .= "<input type=\"button\" title=\"" . get_string('downloadrdp', 'block_vcl') . "\" onclick=\"window.location='" . $CFG->wwwroot . $_SERVER['PHP_SELF'] ."?action=connect&amp;id={$r['requestid']}'\" value=\"" . get_string('getrdp', 'block_vcl') . "\" />\n";
                                $html .= "</p>";
                            }
                            
                            $html .= "<p style=\"text-align:center\">";
                            if ($isRdp) {
                                $html .= get_string('enterrdp', 'block_vcl').":<br />";
                                $html .= "<b>" . get_string('ipaddress', 'block_vcl') . "</b>: ".$rc["serverIP"]."<br />\n";
                                $html .= "<b>" . get_string('username', 'block_vcl') . "</b>: ".$rc["user"]."<br />\n";
                                $html .= "<b>" . get_string('password', 'block_vcl') . "</b>: ".$rc["password"]."<br />\n";
                            }
                            if ($isSsh) {
                                $html .= get_string('enterssh', 'block_vcl'). ": <br/>";
                                $html .= "<br>";
                                $html .= "<input onclick=\"this.select();return false;\" readonly=\"readonly\" type=\"text\" title=\"SSH Command\" value=\"ssh " . $rc['serverIP'] . " -l " . $rc['user'] . " -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null\" style=\"display: block;width: 90%; margin: 0 auto; padding: 3px;\">";
                                $html .= "<br>";
                                $html .= "<b>" . get_string('password', 'block_vcl') . "</b>: ".$rc["password"]."<br />\n";
                            }
                            $html .= "</p>\n";
                            if(get_config('block_vcl', 'enableautoconnect')){
                                $html .= "</div>";
                            }
                            break;

                        case "loading":
                            $html .= "<p>" . get_string('reservationloading', 'block_vcl');
                            $html .= " {$status['time']} " . strtolower(get_string(($status['time'] != 1 ? 'minutes' : 'minute'), 'block_vcl')) . ".</p>\n";
                            $html .= "<input type=\"button\" title=\"" . get_string('cancelreservation', 'block_vcl') . "\" onclick=\"M.block_vcl.remove('{$r['requestid']}')\" value=\"" . get_string('cancel', 'block_vcl') . "\" />\n";
                            break;

                        case "future":
                            $html .= strftime("<p>%b %e at %l:%M %p</p>", $r['start']);       
                            $html .= "<p><input type=\"button\" title=\"" . get_string('cancelreservation', 'block_vcl') . "\" onclick=\"M.block_vcl.remove('{$r['requestid']}')\" value=\"" . get_string('cancel', 'block_vcl') . "\" /></p>\n";
                            break;

                        case "failed":
                            $html .= strftime("<p>%b %e at %l:%M %p</p>", $r['start']);
                            $html .= "<p>" . get_string('reservationfailed', 'block_vcl') . "</p>";
                            $html .= "<p><input type=\"button\" title=\"" . get_string('cancelreservation', 'block_vcl') . "\" onclick=\"M.block_vcl.remove('{$r['requestid']}')\" value=\"" . get_string('cancel', 'block_vcl') . "\" /></p>\n";
                            break;

                        case "timedout":
                            $html .= strftime("<p>%b %e at %l:%M %p</p>", $r['start']);       
                            $html .= "<p>" . get_string('reservationtimedout', 'block_vcl') . "</p>"; 
                            $html .= "<p><input type=\"button\" title=\"" . get_string('cancelreservation', 'block_vcl') . "\" onclick=\"M.block_vcl.remove('{$r['requestid']}')\" value=\"" . get_string('cancel', 'block_vcl') . "\" /></p>\n";
                            break;
                    }
                }
                $html .= "</div>\n";
                $html .= "</div>\n";
                $json[$r['requestid']]["html"] = $html;
                $json[$r['requestid']]["start"] = $r['start'];
                $i++;
            }
        } else if($vcl->errcode){
            $json["error"] = "ERROR: " . $vcl->errmsg;
        } else {
            $json["error"] = get_string('noreservations', 'block_vcl');
        }
        print json_encode($json);
        break;

    /* ******************************************************** *
     *  Get the images to which a user has access.
     * ******************************************************** */
    case "getImages":
        header("Content-type: text/plain");
        if ($images = $vcl->getImages()){
            print json_encode($images);
        } else {
            print json_encode(array("error" => get_string('noimages', 'block_vcl') . ' <a href="' . get_config('block_vcl', 'api') . '">"' . get_string('vcl', 'block_vcl') . '</a>'));
        }
        break;
        
    /* ******************************************************** *
     *  Create a new reservation.
     * ******************************************************** */
    case "newReservation":
        header("Content-type: text/plain");
        $time = $message = "";
        if($_POST['when'] == get_string('later', 'block_vcl')){
            list($month, $day, $year) = preg_split("/\//", stripslashes($_POST['date']));
            list($hour, $minute, $ampm) = preg_split("/[:\s]/", $_POST['time']);

            if(strtolower($ampm) == "pm" && $hour <= 12){ $hour += 12; }
            if($hour == 24){ $hour = 0; }
            
            if($timestamp = mktime($hour, $minute, 0, $month, $day, $year)){
                if(abs($timestamp - time()) > 60 * 15){
                    if($timestamp < time()){
                        $message = get_string('pastreservations', 'block_vcl');
                    } else {
                        $time = $timestamp;
                    }
                } else {
                    $time = "now";
                }
            } else {
                $message = get_string('invaliddate', 'block_vcl');
            }
        } else {
            $time = "now";
        }
        if($time){
            $vcl->addReservation($_POST['image'], $time, get_config('block_vcl', 'reservationduration'));
            if ($vcl->errcode){
                print json_encode(array("error" => $vcl->errmsg));
            } else {
                print json_encode(array("error" => ""));
            }
        } else {
            print json_encode(array("error" => $message));
        }
        break;
        
    /* ******************************************************** *
     *  Delete an existing reservation.
     * ******************************************************** */
    case "deleteReservation":
        header("Content-type: text/plain");
        if($vcl->deleteReservation($_REQUEST["id"])){
          //  print $vcl->message;
        } else if ($vcl->errcode > 1)
            print json_encode(array("error" => $vcl->errmsg));
        break;
}
