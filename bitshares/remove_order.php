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
 * @param string  $order_id
 * @param boolean $restock
 */
function zen_remove_order($order_id, $restock = false)
{
	global $db;
    if ($restock == 'on')
    {
        $result = $db->Execute("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");

		if ($result->RecordCount() > 0) {
		  while (!$result->EOF) {
            $db->Execute("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $result->fields['products_quantity'] . ", products_ordered = products_ordered - " . $result->fields['products_quantity'] . " where products_id = '" . (int)$result->fields['products_id'] . "'");
			$result->MoveNext();
			}
        }

    }

    $db->Execute("delete from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int)$order_id . "'");
    $db->Execute("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order_id . "'");
    $db->Execute("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "'");
}
