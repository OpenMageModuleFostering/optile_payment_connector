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

class OptileChannel {
	const WEB_ORDER              = "WEB_ORDER";
	const MOBILE_ORDER           = "MOBILE_ORDER";
	const EMAIL_ORDER            = "EMAIL_ORDER";
	const CALLCENTER_ORDER       = "CALLCENTER_ORDER";
	const MAIL_ORDER             = "MAIL_ORDER";
	const TERMINAL_ORDER         = "TERMINAL_ORDER";
	const CUSTOMER_SUPPORT       = "CUSTOMER_SUPPORT";
	const CUSTOMER_SELF_SERVICE  = "CUSTOMER_SELF_SERVICE";
	const RECURRING              = "RECURRING";
	const FULFILLMENT            = "FULFILLMENT";
	const DUNNING                = "DUNNING";
	const IMPORT                 = "IMPORT";
}

abstract class OptileRequest extends Entity{
	private $merchantCode = "";
	private $merchantToken = "";
	private $transactionId = "";
	private $country       = "";
	private $channel       = OptileChannel::WEB_ORDER;
	private $callback     = null;
	private $customer     = null;
	private $payment      = null;
	private $products     = array();
	private $account      = null;
	
	protected $urlSuffix = "";
	
	public function getAccount() {
		return $this->account;
	}
	
	public function setAccount($account){
		$this->account = $account;
	}
	
	
	public function getMerchantCode(){
		return $this->merchantCode;
	}

	public function setMerchantCode($merchantCode){
		$this->merchantCode = $merchantCode;
	}

	public function getMerchantToken(){
		return $this->merchantToken;
	}

	public function setMerchantToken($merchantToken){
		$this->merchantToken = $merchantToken;
	}

	public function getTransactionId(){
		return $this->transactionId;
	}

	public function setTransactionId($transactionId){
		$this->transactionId = $transactionId;
	}

	public function getCountry(){
		return $this->country;
	}

	public function setCountry($country){
		$this->country = $country;
	}

	public function getChannel(){
		return $this->channel;
	}

	public function setChannel($channel){
		$this->channel = $channel;
	}

	public function getCallback(){
		return $this->callback;
	}

	public function setCallback($callback){
		$this->callback = $callback;
	}

	public function getCustomer(){
		return $this->customer;
	}

	public function setCustomer($customer){
		$this->customer = $customer;
	}

	public function getPayment(){
		return $this->payment;
	}

	public function setPayment($payment){
		$this->payment = $payment;
	}

	public function getProducts(){
		return $this->products;
	}

	public function setProducts($products){
		$this->products = $products;
	}
	
	public function addProduct(OptileProduct $product){
		$this->products[] = $product;
	}
	
	
	public function GetResponse($url) {
		if (!$url)
			throw new OptileUrlException("Url must be set");
		if (strlen($url)==0)
			throw new OptileUrlException("Url must be set");
		
		$url = rtrim($url,"/");
		$url.=$this->urlSuffix;
		
		
		$validationResult = $this->Validate();
		
		if ($validationResult != array()){
			throw new RequestException($validationResult);
		}
		
		$fields = $this->buildPOSTRequest();
		
		$connection = new OptileConnection();
		$result = $connection->GetResponse($url, $fields,
				$this->getMerchantCode(),
				$this->getMerchantToken());
	
		
		if (strlen("".$result)==0)
			return null;
		
		$responseFactory = new OptileResponseFactory();
		$response = $responseFactory->BuildOptileResponse($result);
		
		return $response;
	}
	
	protected abstract function buildPOSTRequest();
	
}