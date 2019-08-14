<?php
/**
 * Copyright optile GmbH 2013
 * Licensed under the Software License Agreement in effect between optile and
 * Licensee/user (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 * http://www.optile.de/software-license-agreement; in addition, a countersigned
 * copy has been provided to you for your records. Unless required by applicable
 * law or agreed to in writing or otherwise stipulated in the License, software
 * distributed under the License is distributed on an "as is” basis without
 * warranties or conditions of any kind, either express or implied.  See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * @author      i-Ways <dev@i-ways.hr>
 * @copyright   Copyright (c) 2013 optile GmbH. (http://www.optile.de)
 * @license     http://www.optile.de/software-license-agreement
 */

/**
 * Optile payment method model
 *
 */
require_once implode(DS, array(Mage::getBaseDir('lib'), 'Optile', 'Request', 'RequestFactory.php'));
use \Optile\Request\RequestFactory;

class Optile_Payment_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract {

    /**
    * Payment method identifier
    */
    protected $_code = 'optile';


    protected $_isGateway                   = false; // true
    protected $_canOrder                    = false;
    protected $_canAuthorize                = true; // true
    protected $_canCapture                  = true; // true
    protected $_canCapturePartial           = true; // false
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = false;
    protected $_canUseInternal              = true; // true
    protected $_canUseCheckout              = true; // true
    protected $_canUseForMultishipping      = true; // true
    protected $_isInitializeNeeded          = false;
    protected $_canFetchTransactionInfo     = false;
    protected $_canReviewPayment            = false; //false
    protected $_canCreateBillingAgreement   = false;
    protected $_canManageRecurringProfiles  = true;
    /**
     * TODO: whether a captured transaction may be voided by this gateway
     * This may happen when amount is captured, but not settled
     * @var bool
     */
    protected $_canCancelInvoice        = false;

//  protected $_isGateway = true;
//  protected $_canAuthorize = true;
//  protected $_canCapture = true;
//  protected $_canCapturePartial = false;
//  protected $_canRefund = false;
//  protected $_canUseInternal = true;
//  protected $_canUseCheckout = true;
//  protected $_canUseForMultishipping = true;



    /**
    * Payment method block rendered in checkout
    */
    protected $_formBlockType = 'optile/list';

    /**
    * Info block rendered in sales order view
    */
    protected $_infoBlockType = 'optile/info';

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

    //T: Email checking will now be done before LIST request.
    return true;


    $email = $quote->getBillingAddress()->getEmail();

    if (strlen($email) == 0) {
        $email = $quote->getCustomerEmail();
    }

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

        Mage::helper('optile')->log('Executing Authorize method');
        /* @var $payment Mage_Sales_Model_Order_Payment */
        $payment->getOrder()->setCanSendNewEmailFlag(false);
        $payment->setIsTransactionPending(true);
    }

    public function capture(Varien_Object $payment, $amount) {
        parent::capture($payment, $amount);
        /* @var $payment Mage_Sales_Model_Order_Payment */
        Mage::helper('optile')->log('Executing Capture method');

        return $this;

    }

    public function refund(Varien_Object $payment, $amount) {
        try {
            /* @var $payment Mage_Sales_Model_Order_Payment */
            $longId = $payment->getRefundTransactionId();
            $url = rtrim(Mage::getStoreConfig('payment/optile/api_url'), '/') . "/api/charges/{$longId}/payout";

            RequestFactory::setLogger(Mage::helper('optile'));
            $request = RequestFactory::getPayoutRequest($url);

            $reference = Mage::helper('checkout')->__('Quote #%s, Order from %s, ', $payment->getOrder()->getQuoteId(), $this->getStoreName());

            // Payment
            $request->addPayment()
                ->setAmount($amount)
                ->setCurrency($payment->getOrder()->getOrderCurrencyCode())
                ->setReference($reference);

            $request
                ->setMerchantCode($this->getMerchantCode())
                ->setMerchantToken($this->getMerchantToken());

            // execute Close request
            $response = $request->send();
            /* @var $response Response */

            // Check response for Close confirmation
            Mage::helper('optile')->log($response);

            if ($response->getInteraction()->getCode() != \Optile\Response\InteractionCode::PROCEED) {
                $msg = "Received interaction code: " . $response->getInteraction()->getCode() . ', ' . $response->getInfo() . '. Unable to proceed with refund.';
                Mage::helper('optile')->log($msg, Zend_Log::ERR);
                $order = $payment->getOrder();
                /* @var $order Mage_Sales_Model_Order */
                $order->addStatusHistoryComment($msg)->save();
                Mage::getSingleton('core/session')->addError($msg);

                throw new Exception($msg);
            }

            return $this;
        } catch(Exception $e){
            Mage::helper('optile')->log($e->getMessage(), Zend_log::ERR);
            Mage::helper('optile')->log($e->getTraceAsString());
            throw $e;
        }
    }

    public function processInvoice($invoice, $payment)
    {
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        /* @var $payment Mage_Sales_Model_Order_Payment */
        try{
            $optileQuote = Mage::getModel('optile/quote')
                ->load($payment->getOrder()->getQuoteId());
            /* @var $optileQuote Optile_Payment_Model_Quote */

            // Making sure that the order is in deferred mode
            if($optileQuote->getDeferredMode() != Optile_Payment_Model_Quote::DEFERRED){
                // TODO: test this with non_deferred payments. We should just exit gracefully.
                throw new Exception("Order is not in deferred mode, cannot continue with Capture.");
            }

            $longId = $optileQuote->getLongId();
            $url = rtrim(Mage::getStoreConfig('payment/optile/api_url'), '/')."/api/charges/{$longId}/closing";

            RequestFactory::setLogger(Mage::helper('optile')); // TODO: refactor the logger setter?
            $request = RequestFactory::getCloseRequest($url);

            $reference = Mage::helper('checkout')->__('Quote #%s, Order from %s, ', $payment->getOrder()->getQuoteId(), $this->getStoreName());

            // Payment
            $request->addPayment()
                ->setAmount($invoice->getGrandTotal())
                ->setCurrency($invoice->getOrderCurrencyCode())
                ->setReference($reference)
            ;

            $request
                ->setMerchantCode($this->getMerchantCode())
                ->setMerchantToken($this->getMerchantToken());

            // Optional Request data:

            // Invoice items
            foreach($invoice->getAllItems() as $item){

                if($item->getRowTotal()){ // Workaround for mysterious Invoice item...

                    if($item->getQty() > 1){
                        $name = (int)$item->getQty()."x ".$item->getName();
                    }else{
                        $name = $item->getName();
                    }

                    $request->addProduct()
                        ->setCode($item->getSku())
                        ->setName($name)
                        ->setAmount($item->getRowTotalInclTax())
                    ;
                }
            }

            // Shipping
            if($invoice->getShippingInclTax() > 0){
                $request->addProduct()
                    ->setCode('shipping')
                    ->setName(Mage::helper('optile')->__("Shipping costs").': '.$invoice->getShippingAddress()->getShippingDescription())
                    ->setAmount($invoice->getShippingInclTax()) // TODO: with or without tax?
                ;
            }

            // Discount
            if ($invoice->getDiscountAmount()) {

                $discount_amount = $invoice->getDiscountAmount();
                // Magento 1.7 fix
                if($discount_amount > 0){
                    Mage::helper('optile')->log("Detected positive discount amount: ". $discount_amount);
                    $discount_amount = $discount_amount * (-1);
                    Mage::helper('optile')->log("Discount amount set to: ". $discount_amount);
                }

                // Add shipping as an additional product, to complete the total.
                $request->addProduct()
                    ->setCode("discount")
                    ->setName(Mage::helper('optile')->__("Discount"))
                    ->setQuantity(1)
                    ->setAmount($discount_amount)
                ;
            }

            // End of optional Request data.

            // execute Close request
            $response = $request->send();
            /* @var $response Response */

            // Check response for Close confirmation
            Mage::helper('optile')->log($response);

            if($response->getInteraction()->getCode() != \Optile\Response\InteractionCode::PROCEED){
                $msg = "Received interaction code: ".$response->getInteraction()->getCode().', '.$response->getInfo().'. Unable to proceed with creation of invoice.';
                Mage::helper('optile')->log($msg, Zend_Log::ERR);
                $order = $payment->getOrder();
                /* @var $order Mage_Sales_Model_Order */
                $order->addStatusHistoryComment($msg)->save();
                Mage::getSingleton('core/session')->addError($msg);

                throw new Exception($msg);
            }

            Mage::helper('optile')->log(__METHOD__ .' Sending invoice email.');
            $invoice->sendEmail(true);

            $links = $response->getLinks();
            $selfLink = $links['self'];
            $closingId = ltrim($selfLink, rtrim(Mage::getStoreConfig('payment/optile/api_url'), '/').'/api/charges/');
            $invoice->setTransactionId($closingId);
            $invoice->save();
            return $this;

        }catch(Exception $e){
            Mage::helper('optile')->log($e->getMessage(), Zend_log::ERR);
            Mage::helper('optile')->log($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Method for cancelling deferred payment.
     * @param Varien_Object $payment
     * @return Optile_Payment_Model_PaymentMethod
     */
    public function cancelDeferred(Varien_Object $payment)
    {
        /* @var $payment Mage_Sales_Model_Order_Payment */
        Mage::helper('optile')->log('Handling cancellation of deferred payment');

        // Get the order info, find out if the order is made in deferred mode.
        $optileQuote = Mage::getModel('optile/quote')
            ->load($payment->getOrder()->getQuoteId());
        /* @var $optileQuote Optile_Payment_Model_Quote */

        // Making sure that the order is in deferred mode
        if($optileQuote->getDeferredMode() != Optile_Payment_Model_Quote::DEFERRED){
            Mage::helper('optile')->log('Quote not in deferred mode, not sending cancel request to Optile.', Zend_log::INFO);
            return $this;
        }

        // Will send a cancel request only if cancelling entire order. TODO: how to handle partial cancellations?
        // Answered by Sebastian: Whenever there is something to cancel, it is ok to execute.
//        if($payment->getAmountPaid() > 0){
//            Mage::helper('optile')->log('Quote already paid, not sending cancel request to Optile.', Zend_log::INFO);
//            return $this;
//        }

        // Create Cancel request and send to Optile
        $longId = $optileQuote->getLongId();
        $url = rtrim(Mage::getStoreConfig('payment/optile/api_url'), '/')."/api/charges/{$longId}";

        RequestFactory::setLogger(Mage::helper('optile')); // TODO: refactor the logger setter?
        $request = RequestFactory::getCancelRequest($url)
            ->setMerchantCode($this->getMerchantCode())
            ->setMerchantToken($this->getMerchantToken());

        $response = $request->send();
        /* @var $response Response */

        // Check response for Close confirmation
        Mage::helper('optile')->log($response);

        if($response->getInteraction()->getCode() != \Optile\Response\InteractionCode::PROCEED){
            $msg = "Received interaction code: ".$response->getInteraction()->getCode().', '.$response->getInfo().'. Unable to proceed with order cancellation.';
            $order = $payment->getOrder();
            /* @var $order Mage_Sales_Model_Order */
            $order->addStatusHistoryComment($msg)->save();
            Mage::getSingleton('core/session')->addError($msg);
            Mage::helper('optile')->log($msg, Zend_Log::ERR);
            throw new Exception($msg);

        }

        return $this;
    }

    public function getOrderPlaceRedirectUrl() {
//        Mage::helper('optile')->log('Getting redirect URL');
//        try{
//            throw new Exception();
//        }catch(Exception $e){
//            Mage::helper('optile')->log($e->getTraceAsString());
//        }
//        return "http://www.google.com";
    }

    /**
     * Attempt to accept a payment that us under review
     *
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
//        parent::acceptPayment($payment);
        //Note: $_canReviewPayment is set to false, but we are overriding
        //      the parent method that checks for this property.
        //      This is because we do not want to give a Merchant possibility
        //      to manually accept or deny payment in Magento admin - order page.
        return true; // Will make the payment model approve the payment.
    }

    public function denyPayment(Mage_Payment_Model_Info $payment) {
        //parent::denyPayment($payment);

        //Note: $_canReviewPayment is set to false, but we are overriding
        //      the parent method that checks for this property.
        //      This is because we do not want to give a Merchant possibility
        //      to manually accept or deny payment in Magento admin - order page.

        return true; // Will make the payment model deny the payment.
    }

  // /**
  //  * Refund support for order "Credit Memo" view
  //  **/
  // public function processBeforeRefund($invoice, $payment)
  // {
  //     // $payment->setRefundTransactionId($invoice->getTransactionId());
  //     return $this;
  // }

	/**
	 * Returns merchant_code setting
	 */
	protected function getMerchantCode() {
		return Mage::getStoreConfig('payment/optile/merchant_code');
	}

	/**
	 * Returns merchant_token setting
	 */
	protected function getMerchantToken() {
		return Mage::getStoreConfig('payment/optile/merchant_token');
	}

    /**
     * Returns display name for current Magento store
     */
    protected function getStoreName() {
        return Mage::getStoreConfig('general/store_information/name');
    }
}
