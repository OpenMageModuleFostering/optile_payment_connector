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

class OptileListRequest extends OptileRequest{
	
    public function __construct(){
    	$this->urlSuffix = '/api/lists';
    }
	
	public function Validate(){
		$result = array();
	
		$this->validateRequired($this->getTransactionId(), "TransactionId", $result);
		$this->validateRequired($this->getChannel(), "Channel", $result);
		$this->validateRequired($this->getCountry(), "Country", $result);
		$this->validateRequired($this->getCallback(), "Callback", $result);
		$this->validateRequired($this->getCustomer(), "Customer", $result);
	
		if ($result!=array())
			return $result;
		 
		$callBack = $this->getCallback();
		$customer = $this->getCustomer();
		$payment = $this->getPayment();
		$products  = $this->getProducts();
		 
		$items = array($callBack,$customer,$payment);
		 
		foreach($items as $item){
			if ($item == null)
				continue;
			 
			$tempResult = $item->Validate();
			$result = array_merge($result,$tempResult);
			 
		}
		 
		foreach($products as $item){
			if ($item == null)
				continue;
			 
			$tempResult = $item->Validate();
			$result = array_merge($result,$tempResult);
		}
	
		return $result;
	}
	
	protected function buildPOSTRequest(){
		$result = array();
		$result['transactionId']=$this->getTransactionId();
		$result['country']=$this->getCountry();
		$result['channel']=$this->getChannel();
		
		$callback = $this->getCallback();
		
		if ($callback!=null){
			
			$result['callback.returnUrl']=$callback->getReturnUrl();
			$result['callback.cancelUrl']=$callback->getCancelUrl();
			$result['callback.notificationUrl'] = $callback->getNotificationUrl();
		}
		
		$customer = $this->getCustomer();
		
		if ($customer != null){
			$result['customer.number'] = $customer->getNumber();
			$result['customer.email'] = $customer->getEmail();
		}
		
		$payment = $this->getPayment();
		
		if ($payment != null){
			$result['payment.reference'] = $payment->getReference();
			$result['payment.amount']    = $payment->getAmount();
			$result['payment.currency']  = $payment->getCurrency();
		}
		
		$products = $this->getProducts();
		
		if ($products != array()){
			$this->buildProductsPOSTRequest($products, $result);
		}
		
		
		return $result;
		
	}
	
	private function buildProductsPOSTRequest($products, &$result){
		foreach($products as $product){
			if ($product == null)
				continue;
		
			$result['products'][]= array(
				'code' => $product->getCode(),
				'name' => $product->getName(),
				'amount' => $product->getAmount(),
				'currency'=> $product->getCurrency()
			);
		}
		
		
		
	}
}