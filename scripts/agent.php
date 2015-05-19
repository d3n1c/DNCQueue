<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/*
 * Change parameters here
 */
function wsAgentGetMyVars() {
  return array(
    'secret' => 'kosong123',
    'url' => 'http://queue.in/squeue',
    'keyID' => '1505136206',
    'requestIDs' => array(
      'getData' => '1505131141',
    ),
  );
//  return array(
//    'secret' => 'kosong123',
//    'url' => 'http://test.halalanthoyyiban.com/squeue',
//    'keyID' => '1505143825',
//    'requestIDs' => array(
//      'getData' => '1505142532',
//    ),
//  );
}

function wsAgentGetServerTime() {
  $vars = wsAgentGetMyVars();
  $agent = new jsonRPCClient($vars['url']);
  $result = $agent->__call('dncsystem.time', array());
  unset ($agent, $vars);
  return $result;
}

function wsAgentGetHash($time) {
  $vars = wsAgentGetMyVars();
  return md5($vars['secret'] . $vars['keyID'] . $time);
}

function wsAgentGetMyIdentity() {
  $time = wsAgentGetServerTime();
  $time = !empty($time['result']) ? $time['result'] : date('Y-m-d H:i:s', time());
  $vars = wsAgentGetMyVars();
  return array(
    'hash' => wsAgentGetHash($time),
    'timestamp' => $time,
  );
}

function wsAgentSendServiceRequest($method, array $request = array()) {
  $request = array('requests' => $request);
  $request['identity'] = wsAgentGetMyIdentity();
  $data = serialize($request);
  
  // if you want to encrypt, do in this line first before it compressed and converting to base64 formated
  
  $data = gzcompress($data);
  $data = base64_encode($data);
  
  $vars = wsAgentGetMyVars();
  $params = array(
    'agent' => $vars['keyID'],
    'request' => $data,
  );
  unset ($data);
  $agent = new jsonRPCClient($vars['url']);
  $result = $agent->__call($method, $params);
  unset ($agent, $params, $vars);
  return $result;
}

function wsAgentGetFunctionServices() {
  $result = wsAgentSendServiceRequest('dncsystem.listFunctions');
  return $result;
}

function wsAgentSendServiceOrder($requestID, array $arguments = array()) {
  $request = array();
  $request[0][$requestID] = array('arguments' => $arguments);
  $result = wsAgentSendServiceRequest('dncsystem.order', $request);
  unset($request);
  if (empty($result[0][$requestID]) || count($result[0][$requestID]) < 1) {
    return NULL;
  }
  
  return $result[0][$requestID];
}

/*
 * Example for retrieve remote list of tables
 */
function wsAgentShowTables($full = TRUE) {
  $vars = wsAgentGetMyVars();
  $requestID = $vars['requestIDs']['showTables'];
  $arguments = array($full);
  $result = wsAgentSendServiceOrder($requestID, $arguments);
  unset ($requestID, $arguments, $vars);
  if (!empty($result['error'])) {
    return 'error = ' . $result['message'];
  }
  return $result;
}

/*
 * Example for retrieve remote list of table's fields
 */
function wsAgentGetTableFields($tablename, $full = TRUE) {
  $vars = wsAgentGetMyVars();
  $requestID = $vars['requestIDs']['tableFields'];
  $arguments = array($tablename, $full);
  $result = wsAgentSendServiceOrder($requestID, $arguments);
  unset ($requestID, $arguments, $vars);
  if (!empty($result['error'])) {
    return 'error = ' . $result['message'];
  }
  return $result;
}

/*
 * Example for searching data
 */
function wsAgentGetData($tablename, array $condition = array(), array $order = array(), array $range = array()) {
  $vars = wsAgentGetMyVars();
  $requestID = $vars['requestIDs']['searchData'];
  
  $data = array();
  $data[0]['nodetype'] = $tablename;
  $data[0]['data'] = array();
  if (count($condition) > 0) {
    $data[0]['data'][0]['condition'] = $condition;
  }
  if (count($order) > 0) {
    $data[0]['data'][0]['order'] = $order;
  }
  if (count($range) > 0) {
    $data[0]['data'][0]['range'] = $range;
  }
  
  // create arguments
  $arguments = array($data);
  unset ($data);
  
  $result = wsAgentSendServiceOrder($requestID, $arguments);
  unset ($requestID, $arguments, $vars);
  if (!empty($result['error'])) {
    return 'error = ' . $result['message'];
  }
  
  if (count($result[0]['data']) < 1 || empty($result[0]['data'][0]['result']) || empty($result[0]['data'][0]['count'])) {
    unset ($result);
    return NULL;
  }
  
  return $result[0]['data'][0]['result'];
}

/*
 * Example for retrieve sound data queue
 */
function wsAgentSoundData() {
  $vars = wsAgentGetMyVars();
  $requestID = $vars['requestIDs']['getData'];
  $arguments = array();
  $result = wsAgentSendServiceOrder($requestID, $arguments);
  unset ($requestID, $arguments, $vars);
  if (!empty($result['error'])) {
    return 'error = ' . $result['message'];
  }
  return $result;
}

