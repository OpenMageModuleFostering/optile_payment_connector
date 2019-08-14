<?php
/**
 * This file is part of the Optile Payment Connector extension.
 *
 * Optile Payment Connector is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Optile Payment Connector is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Optile Payment Connector.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      i-Ways <dev@i-ways.hr>
 * @copyright   Copyright (c) 2013 Optile. (http://www.optile.de)
 * @license     http://www.gnu.org/licenses/gpl.txt
 */

/**
 * Optile notification handler model
 *
 */
class Optile_Payment_Model_Notification {
	
	/**
	 * Logging filename
	 */
	protected $_logFileName = 'optile.log';
	
	/**
	 * Log helper
	 */
	protected function log( $message, $level = null ) {
		if($this->isLogEnabled())
			Mage::log( $message, $level, $file = $this->_logFileName, true );
	}

	/**
	 * Returns whether logging is enabled
	 */
	protected function isLogEnabled() {
		return Mage::getStoreConfig(
			'payment/optile/log_enabled',
			Mage::app()->getStore()
		);		
	}	
	
	/**
	 * Magento request instance
	 * @var unknown
	 */
	protected $_request;
	
	/**
	 * Magento request params instance
	 */
	protected $_params;
	
	/**
	 * Returns optile notification IPs.
	 * Other IPs are blacklisted for notifications
	 * 
	 * Test server (sandbox.oscato.com) => 78.46.61.206
	 * Production server (oscato.com) => 213.155.71.162
	 * 
	 * @return string
	 */
	private function getAllowedIP() {
		$test_mode = Mage::getStoreConfig(
				'payment/optile/test',
				Mage::app()->getStore()
		);
		
		return $test_mode ? '78.46.61.206' : '213.155.71.162';
	}
	
	/**
	 * Process status notification
	 * 
	 * @param Magento Request $request
	 */
	public function processNotification($request) {
		$this->request = $request;
		
		$params = $this->_params = $request->getParams();
		 
		$this->log($params);
		 
        $allowed_ip = $this->getAllowedIP();
        $remote = array(
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '',
        );
        if ( !in_array($allowed_ip, $remote) ) {
            $this->log(__METHOD__ .' UNAUTHORIZED ACCESS FROM: '. $_SERVER['REMOTE_ADDR'], Zend_Log::ALERT);
            header($_SERVER['SERVER_PROTOCOL'] .' 403 Forbidden', true, 403);
            die('<h1>Access denied</h1>');
        }
        
		$quote = Mage::getModel('sales/quote')->load($params['transactionId']);
		if(!$quote->getId()) {
			$msg = 'Quote with ID '. $params['transactionId'] .' not found.';
			$this->log($msg, Zend_Log::ERR);
			header($_SERVER['SERVER_PROTOCOL'] .' 500 Internal Error', true, 500);
			die('ERROR:'. $msg);
		}
		
		if(!$quote->getReservedOrderId()) {
			$msg = 'Quote with ID '. $params['transactionId'] .' found, but orderID not set yet.';
			$this->log($msg, Zend_Log::ERR);
			header($_SERVER['SERVER_PROTOCOL'] .' 500 Internal Error', true, 500);
			die('ERROR:'. $msg);
		}
		 
		$orderIncrementId = $quote->getReservedOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		
		switch($params['statusCode']) {
			case 'charged':
				$this->handleCharged($order);
				break;
			case 'registered':
				// @TODO: handle 'registered'?
				die("registered?");
			case 'failed':
			case 'declined':
			case 'aborted':
			case 'canceled':
				$this->handleFailed($order);
				break;
			case 'expired':
				$this->handleExpired($order);
				break;
			case 'pending':
				$this->handlePending($order);
				break;
			default:
				$msg = "Not handling ".$params['statusCode'];
				Mage::log($msg, Zend_Log::NOTICE);
				die($msg);
		}
		 
	}
	
	/**
	 * Process optile status notification with statusCode 'charged'
	 * @param Mage_Sales_Model_Order $order
	 */
	private function handleCharged($order) {
		$payment = $order->getPayment();
		
		// Match amount.
		$orderSum = (float) $order->getGrandTotal();
		$paramSum = (float) $this->_params['amount'];
		if ($orderSum !== $paramSum) {
			$msg = ' Authorized amount ('. $paramSum .') does not match order amount ('. $orderSum .')';
			$this->log(__METHOD__ . $msg, Zend_Log::ERR);
		
			$payment->registerCaptureNotification($paramSum);
			$order->save();
		
			header($_SERVER['SERVER_PROTOCOL'] .' 500 Internal Error', true, 500);
			die('ERROR:'. $msg);
		}
		
		// Check current order status.
		if (!$order->isPaymentReview() || $order->getStatus() == Mage_Sales_Model_Order::STATUS_FRAUD) {
			$comment = 'Rejected payment notification because order status is: ';
			$comment .= $order->getStatusLabel();
			$order->addStatusHistoryComment($comment)->save();
		
			$state = $order->getState();
			$status = $order->getStatus();
			$msg = sprintf(' Current order state does not allow payments (%s / %s)', $state, $status);
			$this->log(__METHOD__ . $msg, Zend_Log::ERR);
		
			header($_SERVER['SERVER_PROTOCOL'] .' 500 Internal Error', true, 500);
			die('ERROR:'. $msg);
		}
		
		// Update payment.
		$this->log(__METHOD__ .' Updating payment...');
		
		$payment->registerCaptureNotification($paramSum);
		$order->save();
		
		$this->log(__METHOD__ .' DONE');
		
		die('OK');
	}
	
	/**
	 * Process optile status notification with statusCode 'failed', 'declined', 'aborted', 'canceled'
	 * @param Mage_Sales_Model_Order $order
	 */
	private function handleFailed($order) {
		$request = $this->_request;
		$params = $this->_params;
		
		// Only handle failures regarding payments.
		if ($params['entity'] != 'payment') {
			$msg = ' Not handling '. $params['entity'] .' status: "'. $params['statusCode'] .'"';
			$this->log(__METHOD__ . $msg, Zend_Log::NOTICE);
			die('NOTICE:'. $msg);
		}
				
		// Security overkill.
		if (!$order->canCancel()) {
			// If order is already canceled this is OK.
			if ($order->getStatus() == 'canceled') {
				$this->log(__METHOD__ .' Order already canceled.', Zend_Log::NOTICE);
				die('OK');
			}
			$msg = ' Order cannot be canceled! Current status: '. $order->getStatus();
			$this->log(__METHOD__ . $msg, Zend_Log::ERR);
			header($_SERVER['SERVER_PROTOCOL'] .' 500 Internal Error', true, 500);
			die('ERROR:'. $msg);
		}
		
		// Update order status.
		switch ($params['statusCode']) {
			case 'failed':
				$comment = 'Operation failed due to technical reasons';
				break;
			case 'declined':
				$comment = 'Operation declined by institution';
				break;
			case 'aborted':
				$comment = 'Operation aborted by customer';
				break;
			case 'canceled':
				$comment = 'Operation canceled';
				break;
		}
		
		if ($params['resultInfo']) {
			$comment .= ' ('. $params['resultInfo'] .')';
		}
		
		$this->log(__METHOD__ .' Updating order status...');
		
		$order
		->getPayment()
		->cancel();
		
		$order
		->registerCancellation($comment)
		->save();
		
		$this->log(__METHOD__ .' DONE');
		
		die('OK');
	}
	
	/**
	 * Process optile status notification with statusCode 'expired'
	 * @param Mage_Sales_Model_Order $order
	 */
	private function handleExpired($order) {
		$request = $this->_request;
		$params = $this->_params;
		
		// "expired" notifications should reference entity types "session" or "payment".
		if (!in_array($params['entity'], array('session', 'payment'))) {
			$msg = ' Not handling '. $params['entity'] .' status: "'. $params['statusCode'] .'"';
			$this->log(__METHOD__ . $msg, Zend_Log::NOTICE);
			die('NOTICE:'. $msg);
		}
				
		$orderId = (int)$order->getId();		
		// Security overkill.
		if (!$order->canCancel()) {
			$msg = ' Order cannot be canceled! Current status: '. $order->getStatus();
			$this->log(__METHOD__ . $msg, Zend_Log::ERR);
			header($_SERVER['SERVER_PROTOCOL'] .' 500 Internal Error', true, 500);
			die('ERROR:'. $msg);
		}
		
		$comment = 'Payment session timed out';
		
		if ($params['resultInfo']) {
			$comment .= ' ('. $params['resultInfo'] .')';
		}
		
		$this->log(__METHOD__ .' Updating order status...');
		
		$order
		->getPayment()
		->cancel();
		
		$order
		->registerCancellation($comment)
		->save();
		
		$this->log(__METHOD__ .' DONE');
		
		die('OK');
		
	}
	
	/**
	 * Process optile status notification with statusCode 'charged_back'
	 * @param Mage_Sales_Model_Order $order
	 */	
	private function handleChargedBack($order) {
		$request = $this->_request;
		
		// @TODO: how to handle?
	}
	
	/**
	 * Process optile status notification with statusCode 'pending' - pending payments (eg 3D Secure).
	 * 
	 * @param Mage_Sales_Model_Order $order
	 */	
	private function handlePending($order) {
		$request = $this->_request;
		$payment = $order->getPayment();
		$params = $this->_params;
		
		// Only handle pending payments (we're here to verify the amount).
		if ($params['entity'] != 'payment') {
			$msg = ' Not handling '. $params['entity'] .' status: "'. $params['statusCode'] .'"';
			$this->log(__METHOD__ . $msg, Zend_Log::NOTICE);
			die('NOTICE:'. $msg);
		}
		
		// Match amount if there is one in the message.
		$orderSum = (float) $order->getGrandTotal();
		$paramSum = (float) $params['amount'];
		if (strlen($params['amount']) && $orderSum !== $paramSum) {
			$msg = ' Authorized amount ('. $paramSum .') does not match order amount ('. $orderSum .')';
			$this->log(__METHOD__ . $msg, Zend_Log::ERR);
		
			$payment->registerCaptureNotification($paramSum);
			$order->save();
		
			header($_SERVER['SERVER_PROTOCOL'] .' 500 Internal Error', true, 500);
			die('ERROR:'. $msg);
		}
		
		$comment = 'Payment is pending (3D Secure authentication)';
		$order->addStatusHistoryComment($comment)->save();
		
		$this->log(__METHOD__ .' DONE');
		
		die('OK');
		
	}
}