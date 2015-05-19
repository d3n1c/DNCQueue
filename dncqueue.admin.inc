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

function dncqueue_admin_params(array $form = array(), array $form_state = array()) {
  if (!empty($form_state['storage']['clearQueue'])) {
    $form['clearQueue'] = array(
      '#type' => 'value',
      '#value' => $form_state['storage']['clearQueue'],
    );
    return confirm_form(
            $form,
            t('Are you sure want to clear All queue ?!'),
            'admin/config/system/dncsystem/dncqueue',
            t('This action cannot be undone !'),
            t('Yes reset now!'),
            t('cancel')
          );
            
  }
  $form['tables'] = array(
    '#type' => 'fieldset',
    '#title' => t('Content Types'),
    '#description' => t('Content type table structures'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  
  $params = variable_get('dncqueue_section_table', array());
  $fields = !empty($params['nodetype']) ? dncmain_get_content_type_fields($params['nodetype']) : array();
  $form['tables']['section'] = array(
    '#type' => 'fieldset',
    '#title' => count($fields) > 0 ? node_type_get_name($params['nodetype']) : t('Section Table'),
    '#description' => t('Table of section'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['tables']['section']['section[nodetype]'] = array(
    '#type' => 'textfield',
    '#title' => t('Content Type Name'),
    '#description' => t('(nodetype) Name of content type that represented to section table'),
    '#size' => 20,
    '#default_value' => isset($params['nodetype']) ? $params['nodetype'] : 'section',
  );
  $form['tables']['section']['fields'] = array(
    '#type' => 'fieldset',
    '#title' => t('Fields'),
    '#description' => t('Fields of the content type'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['tables']['section']['fields']['section[title]'] = array(
    '#type' => 'textfield',
    '#title' => !empty($params['title']) ? (!empty($fields[$params['title']]['label']) ? $fields[$params['title']]['label'] : t('Title')) : t('Title'),
    '#description' => t('(title) Name of field of it\'s content type that represent to title of the section'),
    '#size' => 20,
    '#default_value' => !empty($params['title']) ? $params['title'] : 'field_title',
  );
  $form['tables']['section']['fields']['section[description]'] = array(
    '#type' => 'textfield',
    '#title' => !empty($params['description']) ? (!empty($fields[$params['description']]['label']) ? $fields[$params['description']]['label'] : t('Description')) : t('Description'),
    '#description' => t('(description) Name of field of it\'s content type that represent to the description of section'),
    '#size' => 20,
    '#default_value' => !empty($params['description']) ? $params['description'] : 'field_description',
  );
  $form['tables']['section']['fields']['section[nomenclature]'] = array(
    '#type' => 'textfield',
    '#title' => !empty($params['nomenclature']) ? (!empty($fields[$params['nomenclature']]['label']) ? $fields[$params['nomenclature']]['label'] : t('Nomenclature')) : t('Nomenclature'),
    '#description' => t('(nomenclature) Name of field of it\'s content type that represent to the nomenclature of section'),
    '#size' => 20,
    '#default_value' => !empty($params['nomenclature']) ? $params['nomenclature'] : 'field_nomenclature',
  );
  unset ($params, $fields);
  
  $form['tables']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  
  $sparams = variable_get('dncqueue_params', array());
  $form['config'] = array(
    '#type' => 'fieldset',
    '#title' => t('Configurations'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['config']['configs[saysection]'] = array(
    '#type' => 'checkbox',
    '#title' => t('Say section part at sound player?!'),
    '#default_value' => !empty($sparams['saysection']) ? $sparams['saysection'] : NULL,
  );
  $form['config']['saysubmit'] = array(
    '#type' => 'submit',
    '#value' => t('save'),
    '#submit' => array('dncqueue_admin_config_params_submit'),
  );
  
  $form['reset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Reset'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['reset']['clear'] = array(
    '#type' => 'submit',
    '#value' => t('Reset all queue!'),
    '#submit' => array('dncqueue_reset_all_queue'),
  );
  
  return $form;
}

function dncqueue_admin_params_submit(&$form, &$form_state) {
  form_state_values_clean($form_state);
//  echo '<pre>'; print_r($form_state['input']); echo '</pre>'; exit;
  if (empty($form_state['values']['clearQueue'])) {
    variable_set('dncqueue_section_table', $form_state['input']['section']);
    menu_rebuild();
    drupal_set_message(t('The configuration options have been saved'));
  }
  else {
    // flush variables
    variable_del('dncqueue_display');
    variable_del('dncqueue_say');
    drupal_set_message(t('Done! Queue has been reset'));
  }
  $form_state['redirect'] = drupal_get_path_alias('admin/config/system/dncsystem/dncqueue');
}

function dncqueue_reset_all_queue(&$form, &$form_state) {
  form_state_values_clean($form_state);
  $form_state['storage']['clearQueue'] = 1;
  $form_state['rebuild'] = TRUE;
}

function dncqueue_admin_config_params_submit(&$form, &$form_state) {
  form_state_values_clean($form_state);
//  echo '<pre>'; print_r($form_state['input']); echo '</pre>'; exit;
  $configs = !empty($form_state['input']['configs']) ? $form_state['input']['configs'] : array();
  variable_set('dncqueue_params', $configs);
  unset ($configs);
  drupal_set_message(t('The configuration options have been saved'));
  $form_state['redirect'] = current_path();
}