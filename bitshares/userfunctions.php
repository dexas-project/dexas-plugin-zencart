<?php

$path = getcwd();
chdir(ROOT.'..');
require 'includes/application_top.php';
chdir($path);
require 'config.php';
require 'remove_order.php';
function isOrderCompleteUser($memo, $order_id)
{

	global $db;
	$sql = "select orders_id, currency, order_total from " .TABLE_ORDERS. " where orders_status = '" . MODULE_PAYMENT_BITSHARES_PAID_STATUS_ID ."' and orders_id = '".$order_id."'";
	$result = $db->Execute($sql);
	if ($result->RecordCount() > 0) {
		while (!$result->EOF) {
			$total = $result->fields['order_total'];
			$total = number_format((float)$total,2);
			$asset = btsCurrencyToAsset($result->fields['currency']);
			$hash =  btsCreateEHASH(accountName,$order_id, $total, $asset, hashSalt);
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

	global $db;

	$sql = "select orders_id, currency, order_total, orders_date_finished from ". TABLE_ORDERS. " where orders_status = '" . MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID ."' and orders_id = '".$order_id."'";
	$result = $db->Execute($sql);

	if ($result->RecordCount() > 0) {
		while (!$result->EOF) {
			$total = $result->fields['order_total'];
			$total = number_format((float)$total,2);
			$asset = btsCurrencyToAsset($result->fields['currency']);
			$hash =  btsCreateEHASH(accountName,$order_id, $total, $asset, hashSalt);
			$memoSanity = btsCreateMemo($hash);			
			if($memoSanity === $memo)
			{	
				$order = array();
				$order['order_id'] = $order_id;
				$order['total'] = $total;
				$order['asset'] = $asset;
				$order['memo'] = $memo;	
				$order['date_added'] = $result->fields['orders_date_finished'];  
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
	$sql = "select orders_id, currency, order_total, orders_date_finished from ". TABLE_ORDERS." where orders_status = '" . MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID ."'";
	$result = $db->Execute($sql);

	if ($result->RecordCount() > 0) {
	  while (!$result->EOF) {
		$newOrder = array();
    $id = $order['cart_id'];
		$total = $result->fields['order_total'];
		$total = number_format((float)$total,2);	
    $asset = btsCurrencyToAsset($result->fields['currency']);
  	$hash =  btsCreateEHASH(accountName,$id, $total, $asset, hashSalt);
		$memo = btsCreateMemo($hash); 
    
		$newOrder['total'] = $total;
		$newOrder['asset'] = $asset;
		$newOrder['order_id'] = $id;
		$newOrder['date_added'] = $result->fields['orders_date_finished'];
		array_push($openOrderList,$newOrder);    
		$result->MoveNext();
	  }
	}
	return $openOrderList;
}

function completeOrderUser($order)
{
	
	
	global $db;
	$response = array();
	
	$transid = $order['trx_id'];			
	$res = $db->Execute("update " . TABLE_ORDERS . " set orders_status = '" . MODULE_PAYMENT_BITSHARES_PAID_STATUS_ID . "', last_modified = now() where orders_id = '" . $order['order_id'] . "'");
	if(!$res)
	{
		$response['error'] = 'Could not update order status to complete';
		return $response;
	}
	$sql_data_array = array('orders_id' => $order['order_id'],
                        'orders_status_id' => MODULE_PAYMENT_BITSHARES_PAID_STATUS_ID,
                        'date_added' => 'now()',
                        'customer_notified' => '0',
                        'comments' => 'Order Processed! [Transaction ID: ' . $transid . ']');
	zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
	
	$response['url'] = baseURL.'index.php?main_page=checkout_success';
	return $response;
}
function cancelOrderUser($order)
{
	
	global $db;
	$response = array();
  # update order status to reflect processed status:
  $res = $db->Execute("update " . TABLE_ORDERS . " set orders_status = '" . MODULE_PAYMENT_BITSHARES_UNPAID_STATUS_ID . "', last_modified = now() where orders_id = '" . $order['order_id'] . "'");
	if(!$res)
	{
		$response['error'] = 'Could not update order status to cancelled';
		return $response;
	}  
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
        
	$response['url'] = baseURL;
	return $response;
}
function cronJobUser()
{
	return 'Success!';	
}
function createOrderUser()
{

	global $db;
	$order_id    = $_REQUEST['order_id'];
	$asset = btsCurrencyToAsset($_REQUEST['code']);
	$total = number_format((float)$_REQUEST['total'],2);
	
	$hash =  btsCreateEHASH(accountName,$order_id, $total, $asset, hashSalt);
	$memo = btsCreateMemo($hash);
	$ret = array(
		'accountName'     => accountName,
		'order_id'     => $order_id,
		'memo'     => $memo
	);
	
	return $ret;	
}

?>