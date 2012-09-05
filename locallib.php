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


/**
 *  Retrieve a VCL username based on the block configuration
 */
function vcl_get_username($user=NULL){
    global $USER;
    if(!$user){
        $user = $USER;
    }
    $username = preg_replace("/@.+$/", "", $user->username);
    $affiliation = '';
    switch(get_config('block_vcl', 'affiliation')){
        case "username":
            $data = explode('@', $user->username);
            if(count($data) > 1){
                $affiliation = array_pop($data);
            }
            break;

        case "email":
            $data = explode('@', $user->email);
            if(count($data) > 1){
                $affiliation = array_pop($data);
            }
            break;

        case "idnumber":
            $data = explode('@', $user->idnumber);
            if(count($data) > 1){
                $affiliation = array_pop($data);
            }
            break;

        case "institution":
            $affiliation = $user->institution;
            break;

        case "custom":
            $affiliation = get_config('block_vcl', 'customaffil');
            break;
    }
    
    if(!$affiliation){
        $affiliation = get_config('block_vcl', 'customaffil');
    }

    if(get_config('block_vcl', 'affilstriptld')){
        $affiliation = preg_replace("/\.\w{2,4}$/", "", $affiliation);
    }
    
    if(get_config('block_vcl', 'affilupper')){
        $affiliation = strtoupper($affiliation);
    }

    if($username && $affiliation){
        return $username . "@" . $affiliation;
    }
}

/** 
 *  Find a node path in the privilege tree, returning the ID
 */
function vcl_node_find($nodes, $nodePath){
    foreach($nodes as $id => $node){
        $path = array($node['name']);
        $item = $node;
        while(isset($nodes[$item['parent']])){
            $item = $nodes[$item['parent']];
            array_unshift($path, $item['name']);
        }
        if(implode("/", $path) == $nodePath){
            return $id;
        }
    }   
}

/**
 *  Retrieve a list of node children
 */
function vcl_node_children($nodes, $parentid){
    $children = array();
    foreach($nodes as $id => $node){
        if($parentid == $node['parent']){
            $path = array($node['name']);
            $item = $node;
            while(isset($nodes[$item['parent']])){
                $item = $nodes[$item['parent']];
                array_unshift($path, $item['name']);
            }   
            $children[$id] = implode("/", $path);
        }   
    }   
    return $children;
}

/**
 *  Remove any invalid characters from a node name
 */
function vcl_node_clean($text){
    $text = preg_replace("/[^-A-Za-z0-9_. ]/", "", $text);
    $text = substr($text, 0, 40);
    return $text; 
}

