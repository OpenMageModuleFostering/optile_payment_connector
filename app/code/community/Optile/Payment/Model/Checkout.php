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

include_once Mage::getBaseDir('lib').DS.'Optile'.DS.'Server'.DS.'optile.sdk.lib.inc.php';

/**
 * optile payment method checkout model
 * 
 * Requires quote instance and handles all calls to the optile sdk, like
 * for new LIST request.
 *
 */
class Optile_Payment_Model_Checkout {
	
	/**
	 * Logging filename
	 */
	protected $_logFileName = 'optile.log';	
	
	/**
	 * Quote instance
	 * @var Mage_Sales_Model_Quote
	 */
	protected $_quote = null;

	/**
	 * Config instance
	 * @var Mage_Paypal_Model_Config
	 */
	protected $_config = null;
	
	/**
	 * Stores existing list request instance (self link)
	 */
	protected $_listRequestSelfLink = null;
	
	/**
	 * Set quote and listRequestSelfLink instances
	 * @param array $params
	 */
	public function __construct($params = array())
	{
		if (isset($params['quote']) && $params['quote'] instanceof Mage_Sales_Model_Quote) {
			$this->_quote = $params['quote'];
		} else {
			throw new Exception('Quote instance is required.');
		}
		if (isset($params['listRequestSelfLink'])) {
			$this->_listRequestSelfLink = $params['listRequestSelfLink'];
		}
	}	
	
	/**
	 * Returns merchant_code setting
	 */
	private function getMerchantCode() {
		return Mage::getStoreConfig(
			'payment/optile/merchant_code',
			Mage::app()->getStore()
		);
	}
	
	/**
	 * Returns merchant_token setting
	 */
	private function getMerchantToken() {
		return Mage::getStoreConfig(
			'payment/optile/merchant_token',
			Mage::app()->getStore()
		);
	}
	
	/**
	 * Returns store current currency code
	 */
	private function getCurrencyCode() {
		return Mage::app()->getStore()->getCurrentCurrencyCode();
	}
	
	/**
	 * Returns store country
	 */
	private function getCountry() {
		return Mage::getStoreConfig('general/country/default');
	}
	
	/**
	 * Returns optile api base url based on test_mode setting
	 * 
	 * If the test mode is on, returns sandbox url,
	 * otherwise, live url
	 * 
	 * @return string
	 */
	private function getBaseUrl() {
		$test_mode = Mage::getStoreConfig(
			'payment/optile/test',
			Mage::app()->getStore()
		);
		
		$base_url = $test_mode ? "https://sandbox.oscato.com" : "https://oscato.com"; 
		return $base_url;
	}
	
	/**
	 * Returns whether logging is enabled
	 */
	private function isLogEnabled() {
		return Mage::getStoreConfig(
			'payment/optile/log_enabled',
			Mage::app()->getStore()
		);
	}
	
	/**
	 * Logging helper
	 * @param unknown $what array() or string to log
	 */
	public function log($what, $level = null) {
		if($this->isLogEnabled())
			Mage::log( $what, $level, $file = $this->_logFileName, true );
	}
	
	/**
	 * Returns checkout cancel url
	 */
	private function getCancelUrl() {
		return Mage::getUrl('optile/payment/cancel');
	}
	
	/**
	 * Returns notification url that handles status notification updates
	 * like successful charge
	 */
	private function getNotificationUrl() {
		return Mage::getUrl('optile/notification/index');
	}
	
	/**
	 * Returns checkout success url
	 */
	private function getReturnUrl() {
		return Mage::getUrl('checkout/onepage/success');
	}
	
	/**
	 * Returns available payment networks
	 * If there's no existing list request available, it will make new LIST
	 * request to optile, otherwise it will refresh existing list request
	 */
	public function requestAvailableNetworks() {
		if($this->_listRequestSelfLink) {
			$this->log("Repeating existing LIST request... ".$this->_listRequestSelfLink);
			$listRequest = new OptileReloadListRequest();
			$listRequest->setMerchantCode($this->getMerchantCode());
			$listRequest->setMerchantToken($this->getMerchantToken());
			
			$result = $listRequest->GetResponse($this->_listRequestSelfLink);
			
			return $result;
		} else {
			$this->log("Making new LIST request...");
			return $this->newListRequest();
		}
	}
	
	/**
	 * Makes new LIST request to optile and returns its response
	 */
	private function newListRequest() {
		$quote = $this->_quote;
		$quote->collectTotals()->save();
		
		$request = new OptileListRequest();
		$request->setMerchantCode($this->getMerchantCode());
		$request->setMerchantToken($this->getMerchantToken());
		$request->setCountry($this->getCountry());
		$request->setTransactionId($quote->getId());
		
		$billingAddress = $quote->getBillingAddress();
		
		$customer = new OptileCustomer();
		$customer->setEmail($billingAddress->getEmail());
		$customer->setNumber("None");
		
		$request->setCustomer($customer);
		
		$callBack = new OptileCallback();
		$callBack->setCancelUrl($this->getCancelUrl());
		$callBack->setNotificationUrl($this->getNotificationUrl());
		$callBack->setReturnUrl($this->getReturnUrl());
		
		$request->setCallback($callBack);
		
		$payment = new OptilePayment();
		$payment->setAmount($quote->getGrandTotal());
		$payment->setCurrency($this->getCurrencyCode());
		$payment->setReference($quote->getId());
		
		$request->setPayment($payment);
		
		$this->log("Grand total: ".$quote->getGrandTotal());
				
		$result = $request->GetResponse($this->getBaseUrl());
		
		return $result;
	}
}

