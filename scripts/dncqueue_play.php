<?php

/* 
 * Copyright (C) 2015 denic
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
//date_default_timezone_set('Asia/Makassar');

define ('DRUPAL_ROOT', '/home/apache/fikridrup');
define ('SOUND_PLAYER', '/usr/bin/play');
define ('SLEEP', '/usr/bin/sleep');
chdir(DRUPAL_ROOT);
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
$_SERVER['HTTP_HOST'] = 'antrian.ku';
$_SERVER['SCRIPT_NAME'] = '/' . basename(__FILE__);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_SOFTWARE'] = 'php';

drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

function playsound($number, $section = NULL, $suffix = NULL) {
  $command = array();
  
  $soundpath = '/home/denic/Project/bisnis/software antrian/Voice/Indonesia';
  $command[] = SOUND_PLAYER . ' ' . preg_replace('/\s+/', '\ ', $soundpath) . '/start.wav';
  
  $configs = variable_get('dncqueue_params', array());
  if (!empty($section) && !empty($configs['saysection'])) {
    // the old style ;)
//    $dump = str_split($section);
//    foreach ($dump as $value) {
//      $file = $soundpath . '/' . $value . '.wav';
//      clearstatcache();
//      if (is_file($file)) {
//        $command[] = SOUND_PLAYER . ' ' . preg_replace('/\s+/', '\ ', $file);
//      }
//      unset ($file);
//    }
//    unset ($dump);
    settype($section, 'int');
    $file = $soundpath . '/' . $section . '.wav';
    clearstatcache();
    if (is_file($file)) {
      $command[] = SOUND_PLAYER . ' ' . preg_replace('/\s+/', '\ ', $file);
    }
    unset ($file);
//    $command[] = SLEEP . ' 1';
  }
  
  $numb = dncConvertNumber($number);
  if (count($numb) > 0) {
    foreach ($numb as $value) {
      $file = $soundpath . '/' . $value . '.wav';
      clearstatcache();
      if (is_file($file)) {
        $command[] = SOUND_PLAYER . ' ' . preg_replace('/\s+/', '\ ', $file);
      }
      unset ($file);
    }
  }
  
  if (!empty($suffix)) {
    $file = $soundpath . '/' . $suffix . '.wav';
    clearstatcache();
    if (is_file($file)) {
      $command[] = SOUND_PLAYER . ' ' . preg_replace('/\s+/', '\ ', $soundpath . '/mohon ke loket.wav');
      $command[] = SOUND_PLAYER . ' ' . preg_replace('/\s+/', '\ ', $file);
    }
    unset ($file);
  }
  unset ($soundpath);
  
  if (count($command) > 0) {
//    print_r($command);
    foreach ($command as $value) {
      shell_exec($value);
    }
  }
  unset ($command);
}

function dncConvertNumber($number) {
  $return = array();
  if ($number >= 1000) {
    $dump = floor($number / 1000);
    $return[] = $dump * 1000;
    $number -= $dump;
    unset ($dump);
  }
  if ($number >= 100) {
    $dump = floor($number / 100);
    $return[] = $dump * 100;
    $number -= $dump;
    unset ($dump);
  }
  $return[] = $number;
  return $return;
}

function dncGetData() {
  $data = variable_get('dncqueue_say', array());
  if (is_array($data) && count($data) > 0) {
    ksort($data);
    foreach ($data as $key => $value) {
      $return = $value;
      $return['key'] = $key;
      return $return;
    }
  }
  return NULL;
}

function dncRemoveQueue($thekey) {
  $data = variable_get('dncqueue_say', array());
  if (!empty($data[$thekey])) {
    unset ($data[$thekey]);
  }
  variable_set('dncqueue_say', $data);
}

function dncPlaySound() {
  $data = dncGetData();
//  print_r($data);
  if (isset($data['number']) && isset($data['key']) && isset($data['snid'])) {
    $suffix = !empty($data['suffix']) ? $data['suffix'] : NULL;
    dncqueue_set_current_display($data['snid'], $data['number'], $suffix);
    dncRemoveQueue($data['key']);
    playsound($data['number'], $data['section'], $suffix);
    unset ($suffix);
  }
  unset ($data);
}

dncPlaySound();
