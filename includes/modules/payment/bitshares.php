<?php

/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2011-2014 Bitshares
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


/**
 * @package paymentMethod
 */
class bitshares {
  var $code, $title, $description, $enabled, $payment;
  
  function log($contents){
    error_log($contents);
  }
  
  // class constructor
  function bitshares() {
    global $order;
    $this->code = 'bitshares';
    $this->title = MODULE_PAYMENT_BITSHARES_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_BITSHARES_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_PAYMENT_BITSHARES_SORT_ORDER;
    $this->enabled = ((MODULE_PAYMENT_BITSHARES_STATUS == 'True') ? true : false);

    if ((int)MODULE_PAYMENT_BITSHARES_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_BITSHARES_ORDER_STATUS_ID;
      $payment='bitshares';
    } else if ($payment=='bitshares') {
      $payment='';
    }

    if (is_object($order))
      $this->update_status();

    $this->email_footer = MODULE_PAYMENT_BITSHARES_TEXT_EMAIL_FOOTER;
  }

  // class methods
  function update_status() {
    global $db;
    global $order;

    // check zone
    if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_BITSHARES_ZONE > 0) ) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . intval(MODULE_PAYMENT_BITSHARES_ZONE) . "' and zone_country_id = '" . intval($order->billing['country']['id']) . "' order by zone_id");
      while (!$check->EOF) {
        if ($check->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
        $check->MoveNext();
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
      
  }

  function javascript_validation() {
    return false;
  }

  function selection() {
    return array('id' => $this->code,
                 'module' => $this->title);
  }

  function pre_confirmation_check() {
    return false;
  }

  // called upon requesting step 3
  function confirmation() {
    return false;
  }
  
  // called upon requesting step 3 (after confirmation above)
  function process_button() {    
    return false;
  }

  // called upon clicking confirm
  function before_process() {
    global $insert_id, $order, $db;
 
    // change order status to value selected by merchant
    $db->Execute("update ". TABLE_ORDERS. " set orders_status = " . intval(MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID) . " where orders_id = ". intval($insert_id));
    return false; 
  }

  // called upon clicking confirm (after before_process and after the order is created)
  function after_process() {
    global $insert_id, $order;  
    $url = 'bitshares/redirect2bitshares.php?order_id='.$insert_id.'&code='.$order->info['currency'].'&total='.$order->info['total']; 
	$_SESSION['cart']->reset(true);
	zen_redirect($url);
    return false;
  }

  function get_error() {
    return false;
  }

  function check() {
    global $db;

    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_BITSHARES_STATUS'");
      $this->_check = $check_query->RecordCount();
    }

    return $this->_check;
  }

  function install() {
    global $db, $messageStack;

    if (defined('MODULE_PAYMENT_BITSHARES_STATUS')) {
      $messageStack->add_session('Bitshares module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=bitshares', 'NONSSL'));
      return 'failed';
    }

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) "
    ."values ('Enable Bitshares Module', 'MODULE_PAYMENT_BITSHARES_STATUS', 'True', 'Do you want to accept bitcoin payments via bitshares.com?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) "
    ."values ('Unpaid Order Status', 'MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID', '" . intval(DEFAULT_ORDERS_STATUS_ID) .  "', 'Automatically set the status of unpaid orders to this value.', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) "
    ."values ('Paid Order Status', 'MODULE_PAYMENT_BITSHARES_PAID_STATUS_ID', '2', 'Automatically set the status of paid orders to this value.', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) "
    ."values ('Payment Zone', 'MODULE_PAYMENT_BITSHARES_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) "
    ."values ('Sort order of display.', 'MODULE_PAYMENT_BITSHARES_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '2', now())");
  }

  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }

  function keys() {
    return array(
                 'MODULE_PAYMENT_BITSHARES_STATUS', 
                 'MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID',
                 'MODULE_PAYMENT_BITSHARES_PAID_STATUS_ID',
                 'MODULE_PAYMENT_BITSHARES_SORT_ORDER',
                 'MODULE_PAYMENT_BITSHARES_ZONE'
                );
  }
}
