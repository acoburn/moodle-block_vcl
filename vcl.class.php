<?php

//   Licensed to the Apache Software Foundation (ASF) under one or more
//   contributor license agreements.  See the NOTICE file distributed with
//   this work for additional information regarding copyright ownership.
//   The ASF licenses this file to You under the Apache License, Version 2.0
//   (the "License"); you may not use this file except in compliance with
//   the License.  You may obtain a copy of the License at
//
//   http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.

/**
 *  Class VCL
 *
 *  A class for interacting with the XML-RPC interface of the
 *  Virtual Computing Lab.
 */
class VCL {

    /**
     * @var int contains an error code for any problems with the last RPC operation.
     */
    public $errcode = 0;
    /**
     * @var string contains an error message for a recent RPC failure.
     */
    public $errmsg = "";
    /**
     * @var string contains any general information about the most recent command.
     */
    public $message = "";

    private $api = "";
    private $username = "";
    private $password = "";


/**
 *  Constructor
 *
 *  Create a connection to the VCL XML-RPC API for a particular user. The username
 *  should contain the appropriate affiliation.
 *
 *  @param string $api
 *      The URL of the VCL API
 *  @param string $username
 *      In username@AFFILIATION format
 *  @param string $password
 */
    public function __construct($api, $username, $password){
        $this->api = $api;
        $this->username = $username;
        $this->password = $password;
    }


/**
 *  Function getImages
 *
 *  Retreive a list of images to which the user has access.
 *
 *  @returns array
 */
    public function getImages(){
        if($images = $this->rpc('XMLRPCgetImages', array()))
            if(count($images))
                return $images;
    }


/**
 *  Function addReservation
 *
 *  Create a new VCL reservation.
 *
 *  @param int $imageid
 *      the image ID
 *  @param int $time
 *      a timestamp for the start of the reservation
 *  @param int $duration
 *      the length of the reservation (in 15 minute intervals)
 *
 *  @returns bool
 *      Returns true on success
 */
    public function addReservation($imageid, $time, $duration){
        if($rc = $this->rpc('XMLRPCaddRequest', array($imageid, $time, $duration))){
            $this->message = "Successfully added reservation.";
            return 1;
        }
    }

/**
 *  Function getUserGroupMembers
 *
 *  Retrieve a list of group members for the given group.
 *
 *  @param string $name
 *      The group name
 *  @param string $affiliation
 *      The group affiliation
 *
 *  @returns array
 *      Returns a list of usernames
 */
    public function getUserGroupMembers($name, $affiliation){
        if($response = $this->rpc('XMLRPCgetUserGroupMembers', array($name, $affiliation))){
            if($response["status"] == "success"){
                $this->message = "User group members retrieved.";
                return $response["members"];
            }
        }
    }

    public function getNodes(){
        if($response = $this->rpc("XMLRPCgetNodes", array())){
            if($response['status'] == 'success'){
                $nodes = array();
                foreach($response['nodes'] as $node){
                    $id = $node['id'];
                    unset($node['id']);
                    $nodes[$id] = $node; 
                }
                return $nodes;
            }
        }
    }

    public function getResourceGroupPrivs($name, $type, $nodeid){
        if($response = $this->rpc("XMLRPCgetResourceGroupPrivs", array($name, $type, $nodeid))){
            if($response['status'] == 'success'){
                return $response['privileges'];
            }
        }
    }

    public function getResourceGroups($type){
        if($response = $this->rpc("XMLRPCgetResourceGroups", array($type))){
            if($response['status'] == 'success'){
                return $response['groups'];
            }
        }
    }

    public function removeUserGroup($name, $affiliation){
        if($response = $this->rpc("XMLRPCremoveUserGroup", array($name, $affiliation))){
            if($response['status'] = 'success'){
                return $response;
            }
        }
    }

    public function removeResourceGroup($name, $type){
        if($response = $this->rpc("XMLRPCremoveResourceGroup", array($name, $type))){
            if($response['status'] = 'success'){
                return $response;
            }
        }
    }

    public function getUserGroupPrivs($name, $affiliation, $nodeid){
        if($response = $this->rpc("XMLRPCgetUserGroupPrivs", array($name, $affiliation, $nodeid))){
            if($response['status'] == 'success'){
                return $response['privileges'];
            }
        }
    }

    public function addNode($name, $parentNode){
        if($response = $this->rpc("XMLRPCaddNode", array($name, $parentNode))){
            if($response['status'] == 'success'){
                return $response['nodeid'];
            }
        }
    }

    public function removeNode($nodeid){
        if($response = $this->rpc("XMLRPCremoveNode", array($nodeid))){
            if($response['status'] == 'success'){
                return 1;
            }
        }
    }

    public function addResourceGroup($name, $managingGroup, $type){
        if($response = $this->rpc("XMLRPCaddResourceGroup", array($name, $managingGroup, $type))){
            if($response['status'] == 'success'){
                return 1;
            } else {
                print_r($response);
            }
        }
    }

    public function removeImageGroupFromComputerGroup($imageGroup, $computerGroup){
        if($response = $this->rpc("XMLRPCremoveImageGroupFromComputerGroup", array($imageGroup, $computerGroup))){
            if($response['status'] == 'success'){
                return 1;
            }   
        }   
    }   

    public function addImageGroupToComputerGroup($imageGroup, $computerGroup){
        if($response = $this->rpc("XMLRPCaddImageGroupToComputerGroup", array($imageGroup, $computerGroup))){
            if($response['status'] == 'success'){
                return 1;
            }   
        }   
    }  

    public function removeImageFromGroup($imageGroup, $imageid){
        if($response = $this->rpc("XMLRPCremoveImageFromGroup", array($imageGroup, $imageid))){
            if($response['status'] == 'success'){
                return 1;
            }
        }
    }

    public function getGroupImages($imageGroup){
        if($response = $this->rpc("XMLRPCgetGroupImages", array($imageGroup))){
            if($response['status'] == 'success'){
                return $response['images'];
            }
        }
    }

    public function addImageToGroup($imageGroup, $imageid){
        if($response = $this->rpc("XMLRPCaddImageToGroup", array($imageGroup, $imageid))){
            if($response['status'] == 'success'){
                return 1;
            }
        }
    }

    public function addResourceGroupPriv($name, $type, $nodeid, $priv){
        $privileges = implode(":", $priv);
        if($response = $this->rpc("XMLRPCaddResourceGroupPriv", array($name, $type, $nodeid, $privileges))){
            if($response['status'] == 'success'){
                return 1;
            }
        }
    }

    public function addUserGroup($name, $affiliation, $owner, $managingGroup, $initialMaxTime, $totalMaxTime, $maxExtendTime, $custom=1){
        if($response = $this->rpc("XMLRPCaddUserGroup", array($name, $affiliation, $owner, $managingGroup, $initialMaxTime, $totalMaxTime, $maxExtendTime, $custom))){
            if($response['status'] == 'success'){
                return 1;
            }
        }
    }

    public function addUserGroupPriv($name, $affiliation, $nodeid, $priv){
        $privileges = implode(":", $priv);
        if($response = $this->rpc("XMLRPCaddUserGroupPriv", array($name, $affiliation, $nodeid, $privileges))){
            if($response['status'] == 'success'){
                return 1;
            }
        }
    }

    public function nodeExists($name, $parentNode){
        if($response = $this->rpc("XMLRPCnodeExists", array($name, $parentNode))){
            if($response['status'] == 'success'){
                return $response['exists'];
            }
        }
    }

/**
 *  Function getUserGroups
 *
 *  Retrieve a list of accessible user groups
 *
 *  @returns array
 *      Returns an array of group data structures
 */
    public function getUserGroups(){
        if($response = $this->rpc("XMLRPCgetUserGroups", array())){
            if($response["status"] == "success"){
                $this->message = "User groups retrieved."; 
                return $response["groups"];
            }
        }
    }

/**
 *  Function addUsersToGroup
 *
 *  Add user accounts to an existing user group
 *
 *  @param string $group
 *      The name of the group to which users will be added
 *  @param string $affiliation
 *      The group's affiliation
 *  @param array $users
 *      An array of usernames to add
 *
 *  @returns bool
 *      Returns true on success
 */ 
    public function addUsersToGroup($group, $affiliation, $users){
        if($response = $this->rpc("XMLRPCaddUsersToGroup", array($group, $affiliation, $users))){
            if($response["status"] == "success"){
                return 1;
            } else if($response["status"] == "warning"){
                $this->message = "The following users were not added: " . implode(', ', $response['failedusers']);
                $this->errcode = $response['warningcode'];
                $this->errmsg = $response['warningmsg'];
            }
        }
    }

/**
 *  Function extendReservation
 *
 *  Extend an existing reservation.
 *
 *  @param int $id
 *      the reservation id
 *  @param int $duration
 *      the number of minutes by which to extend the reservation.
 *  
 *  @returns bool
 *      Returns true on success
 */
    public function extendReservation($id, $duration){
        if($rc = $this->rpc("XMLRPCextendRequest", array($id, $duration))){
            $this->message = "Reservation successfully extended.";
            return 1;
        }
    }


/**
 *  Function deleteReservation
 *
 *  Delete an existing reservation.
 *
 *  @param int $id
 *      The reservation id
 *
 *  @returns bool
 *      Returns true on success
 */
    public function deleteReservation($id){
        if($rc = $this->rpc("XMLRPCendRequest", array($id))){ 
            $this->message = "Reservation successfully deleted.";
            return 1;
        }
    }


/**
 *  Function getRequestStatus
 *
 *  Get the status of an existing request.
 *
 *  @param int $id
 *      The request id
 *
 *  @returns array
 */
    public function getRequestStatus($id){
        if($rc = $this->rpc("XMLRPCgetRequestStatus", array($id)))
            return $rc;
    }


/**
 *  Function getConnectData
 *
 *  Get the user, password and serverIP for a prepared request.
 *
 *  @param int $requestid
 *      The id for this request.
 *  @param string $remote_addr
 *      The IP address of the user's computer. 
 *
 *  @returns array
 */
    public function getConnectData($requestid, $remote_addr){
        if($rc = $this->rpc("XMLRPCgetRequestConnectData", array($requestid,
            $remote_addr))){
            if($rc["status"] == "ready"){
                unset($rc['status']);
                return $rc;
            } else {
                $this->message = "The connection is not yet ready.";
            }
        }
    }


/**
 *  Function affiliations
 *
 *  Get a list of affiliations
 *
 *  @returns array
 */
    public function affiliations(){
        if($response = $this->rpc("XMLRPCaffiliations", array())){
            if(count($response)){
                return $response;
            }
        }
    }


/**
 *  Function getReservations
 *
 *  Get a list of current reservations along with associated metadata.
 *
 *  @returns array
 */
    public function getReservations(){
        if($response = $this->rpc('XMLRPCgetRequestIds', array()))
            if(count($response["requests"]))
                return $response["requests"];
    }


    private function rpc($method, $args) {
        $this->errcode = 0;
        $this->errmsg = "";
        $this->message = "";

        $request = xmlrpc_encode_request($method, $args);
        $header  = "Content-Type: text/xml\r\n";
        $header .= "X-User: " . $this->username . "\r\n";
        $header .= "X-Pass: " . $this->password . "\r\n";
        $header .= "X-APIVERSION: 2";
        $context = stream_context_create(
                array(
                        'http' => array(
                                'method' => "POST",
                                'header' => $header,
                                'content' => $request
                        )
                )
        );
        $location = "?mode=" . ($method == "XMLRPCaffiliations" ? "xmlrpcaffiliations" :
                                                                  "xmlrpccall"); 
        $file = file_get_contents($this->api . $location, false, $context);
        $response = xmlrpc_decode($file);
        if(isset($response['status']) && $response['status'] == 'error'){
            $this->errcode = $response['errorcode'];
            $this->errmsg = $response['errormsg'];
            return;
        } else {
            return $response;
        }
    }
} // class VCL
