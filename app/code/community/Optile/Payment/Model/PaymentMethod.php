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
 * Optile payment method model
 *
 */
class Optile_Payment_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract {

  /**
   * Payment method identifier
   */
  protected $_code = 'optile';
  
  /**
   * Payment method block rendered in checkout
   */
  protected $_formBlockType = 'optile/list';

  /**
   * Is the payment method gateway?
   * @var unknown
   */
  protected $_isGateway = true;

  /**
   * Can the payment method authorize?
   * @var unknown
   */
  protected $_canAuthorize = true;

  /**
   * Can the payment method capture?
   * @var unknown
   */
  protected $_canCapture = true;

  /**
   * Can the payment method capture partial amount?
   * @var unknown
   */
  protected $_canCapturePartial = false;

  /**
   * Can the payment method refund?
   * @var unknown
   */
  protected $_canRefund = false;

  /**
   * Can the method be used in backend?
   */
  protected $_canUseInternal = true;

  /**
   * Show this method on the checkout page
   */
  protected $_canUseCheckout = true;

  /**
   * Available for multi-shipping checkouts?
   */
  protected $_canUseForMultishipping = true;
  
  /**
   * Checks whether method can be used.
   * Disables the payment method if the email is not available (required 
   * in optile sdk)
   * 
   * @param Mage_Sales_Model_Quote $quote
   * @return boolean
   */
  public function isAvailable($quote = null) {
  	$available = parent::isAvailable($quote);
  	if(!$available) return false;
  	
  	$email = $quote->getBillingAddress()->getEmail();
  	if(strlen($email) == 0) return false;
  	
	return true;
  }
  
  /**
   * Called after 'Place Order' action in checkout, sets order status to
   * 'Payment Processing'
   * 
   * @param Varien_Object $payment
   * @param unknown $amount
   */
  public function authorize(Varien_Object $payment, $amount) {
  	$payment->setIsTransactionPending(true);
  }
  
}