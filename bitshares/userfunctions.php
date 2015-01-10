<?php

$path = getcwd();
chdir(ROOT.'..');
require 'includes/application_top.php';
chdir($path);
require 'config.php';
require 'remove_order.php';
function isOrderCompleteUser($memo, $order_id)
{
	global $accountName;
	global $hashSalt;
	global $db;
	$sql = "select orders_id, currency, order_total from " .TABLE_ORDERS. " where orders_status = '" . MODULE_PAYMENT_BITSHARES_PAID_STATUS_ID ."' and orders_id = '".$order_id."'";
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		while (!$result->EOF) {
			$total = $result->fields['order_total'];
			$total = number_format((float)$total,2);
			$asset = btsCurrencyToAsset($result->fields['currency']);
			$hash =  btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
			$memoSanity = btsCreateMemo($hash);		
			if($memoSanity === $memo)
			{	
				return TRUE;
			}
			$result->MoveNext();
		}	
		
	}
	return FALSE;	
}
function doesOrderExistUser($memo, $order_id)
{
	global $accountName;
	global $hashSalt;
	global $db;

	$sql = "select orders_id, currency, order_total from ". TABLE_ORDERS. " where orders_status = '" . MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID ."' and orders_id = '".$order_id."'";
	$result = $db->Execute($sql);

	if ($result->RecordCount() > 0) {
		while (!$result->EOF) {
			$total = $result->fields['order_total'];
			$total = number_format((float)$total,2);
			$asset = btsCurrencyToAsset($result->fields['currency']);
			$hash =  btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
			$memoSanity = btsCreateMemo($hash);			
			if($memoSanity === $memo)
			{	
				$order = array();
				$order['order_id'] = $order_id;
				$order['total'] = $total;
				$order['asset'] = $asset;
				$order['memo'] = $memo;	
				
				return $order;
			}
			$result->MoveNext();
		}
	}
	return FALSE;
}
function getOpenOrdersUser()
{
	global $db;
	$openOrderList = array();
	$sql = "select orders_id, currency, order_total from ". TABLE_ORDERS." where orders_status = '" . MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID ."'";
	$result = $db->Execute($sql);

	if ($result->RecordCount() > 0) {
	  while (!$result->EOF) {
		$newOrder = array();
		$total = $result->fields['order_total'];
		$total = number_format((float)$total,2);		
		$newOrder['total'] = $total;
		$newOrder['currency_code'] = $result->fields['currency'];
		$newOrder['order_id'] = $result->fields['orders_id'];
		$newOrder['date_added'] = 0;
		array_push($openOrderList,$newOrder);    
		$result->MoveNext();
	  }
	}
	return $openOrderList;
}

function completeOrderUser($order)
{
	global $baseURL;   
	$transid = $order['trx_id'];			
	$db->Execute("update " . TABLE_ORDERS . " set orders_status = '" . MODULE_PAYMENT_BITSHARES_PAID_STATUS_ID . "', last_modified = now() where orders_id = '" . $order['order_id'] . "'");
	$sql_data_array = array('orders_id' => $order['order_id'],
                        'orders_status_id' => MODULE_PAYMENT_BITSHARES_PAID_STATUS_ID,
                        'date_added' => 'now()',
                        'customer_notified' => '0',
                        'comments' => 'Order Processed! [Transaction ID: ' . $transid . ']');
	zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
	$ret['url'] = $baseURL.'index.php?main_page=checkout_success';
	return $ret;
}
function cancelOrderUser($order)
{
	global $baseURL;
	global $db;

  # update order status to reflect processed status:
  $db->Execute("update " . TABLE_ORDERS . " set orders_status = '" . MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID . "', last_modified = now() where orders_id = '" . $order['order_id'] . "'");
  # update order status history:
  $sql_data_array = array('orders_id' => $order['order_id'],
                            'orders_status_id' => MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID,
                            'date_added' => 'now()',
                            'customer_notified' => '0',
                            'comments' => 'Order cancelled by user');
  zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
    	
    	
  if(function_exists('zen_remove_order'))
  {
      zen_remove_order($order['order_id'], $restock = true);
  }
        
	$response['url'] = $baseURL;

	return $response;
}
function cronJobUser()
{
	return 'Success!';	
}
function createOrderUser()
{

	global $accountName;
	global $hashSalt;
	global $db;
	$order_id    = $_REQUEST['order_id'];
	$asset = btsCurrencyToAsset($_REQUEST['code']);
	$total = number_format((float)$_REQUEST['total'],2);
	
	$hash =  btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
	$memo = btsCreateMemo($hash);
	$ret = array(
		'accountName'     => $accountName,
		'order_id'     => $order_id,
		'memo'     => $memo
	);
	
	return $ret;	
}

?>