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

require_once implode(DS, array(Mage::getBaseDir('lib'), 'Optile', 'Request', 'RequestFactory.php'));
use \Optile\Request\RequestFactory;

/**
 * Optile payment method checkout model
 *
 * Handles all calls to the Optile SDK, like for new LIST request.
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

    public function __construct() {
        RequestFactory::setLogger(Mage::helper('optile'));
        RequestFactory::setCacher(Mage::helper('optile/cache'));
    }

    protected function getQuote() {
        if (!isset($this->_quote)) {
            $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
            $this->_quote
                ->collectTotals()
                ->save();
        }
        return $this->_quote;
    }

	/**
	 * Returns merchant_code setting
	 */
	protected function getMerchantCode() {
		return Mage::getStoreConfig('payment/optile/merchant_code');
	}

	/**
	 * Returns merchant_division setting
	 */
	protected function getMerchantDivision() {
		return Mage::getStoreConfig('payment/optile/merchant_division');
	}

	/**
	 * Returns merchant_token setting
	 */
	protected function getMerchantToken() {
		return Mage::getStoreConfig('payment/optile/merchant_token');
	}

	/**
	 * Returns preselection_deferral setting
	 */
	protected function getPreselectionDeferral() {
		return Mage::getStoreConfig('payment/optile/preselection_deferral');
	}

	/**
	 * Returns customer's billing address country
	 */
	protected function getCountry() {
        return $this->getQuote()->getBillingAddress()->getCountryId();
	}

    /**
     * Returns display name for current Magento store
     */
    protected function getStoreName() {
        return Mage::getStoreConfig('general/store_information/name');
    }

    /**
     * Returns API URL from config.
     */
    protected function getApiUrl() {
        return Mage::getStoreConfig('payment/optile/api_url');
    }

	/**
	 * Returns checkout cancel url
	 */
	protected function getCancelUrl() {
		return Mage::getUrl('optile/payment/cancel', array('_secure' => 1));
	}

	/**
	 * Returns notification url that handles status notification updates
	 * like successful charge
	 */
	protected function getNotificationUrl() {
		return Mage::getUrl('optile/notification/index', array('_secure' => 1));
	}

	/**
	 * Returns checkout success url
	 */
	protected function getReturnUrl() {
		return Mage::getUrl('checkout/onepage/success', array('_secure' => 1));
	}

	/**
	 * Refreshes an existing LIST request, or makes a new LIST request if no
     * existing LIST request is available.
     * @return \Optile\Response\Response Available payment networks
	 */
	public function requestAvailableNetworks($listRequestSelfLink = null) {

        if ($listRequestSelfLink) {
			Mage::helper('optile')->log("Repeating existing LIST request... ".$listRequestSelfLink);
            $request = RequestFactory::getReloadListRequest($listRequestSelfLink);
		} else {
			Mage::helper('optile')->log("Making new LIST request...");
            $request = RequestFactory::getListRequest($this->getApiUrl());
			$this->newListRequest($request);
		}

        // Authentication
        $request
            ->setMerchantCode($this->getMerchantCode())
            ->setMerchantToken($this->getMerchantToken());

        $response = $request->send();
        /* @var $response Response */
        if(!$response){
            Mage::helper("optile")->log("Error while executing LIST request:");
            Mage::helper("optile")->log("Info: ".$response);
            throw new Exception("Error while fetching response from Optile");
        }
        if($response->getInteraction()->getCode() != "PROCEED"){
            Mage::helper("optile")->log("Error while executing LIST request:");
            Mage::helper("optile")->log("Info: ".$response->getInfo());
            Mage::helper("optile")->log("Code: ".$response->getInteraction()->getCode());
            Mage::helper("optile")->log("Reason: ".$response->getInteraction()->getReason());
//            throw new Exception($response->getInfo());
        }

//        Mage::helper('optile')->log("LIST response:");
//        Mage::helper('optile')->log($result);
        return $response;
	}

	/**
     * Sets request data on new LIST request object.
     * @param \Optile\Request\ListRequest $request
     */
	protected function newListRequest(\Optile\Request\ListRequest $request) {
		$quote = $this->getQuote();
        $totals = $quote->getTotals();
		$billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();

        // Root request data
        $request
            ->setTransactionId(Mage::helper('optile')->formatQuoteId($quote->getId()))
            ->setCountry($this->getCountry())
        ;

        if($this->getMerchantDivision()){
            $request->setDivision($this->getMerchantDivision());
        }

        $reference = Mage::helper('checkout')->__('Quote #%s, %s order', Mage::helper('optile')->formatQuoteId($quote->getId()), $this->getStoreName());

        // Payment
		$request->addPayment()
            ->setAmount($quote->getGrandTotal())
            ->setCurrency($quote->getQuoteCurrencyCode())
            ->setReference($reference)
        ;

        // Products
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            /* @var $quoteItem Mage_Sales_Model_Quote_Item */
            $request->addProduct()
                ->setCode($quoteItem->getSku())
                ->setName($quoteItem->getName())
                ->setQuantity($quoteItem->getQty())
                ->setAmount($quoteItem->getRowTotalInclTax())
            ;
        }

        if ($shippingAddress->getShippingAmount()) {
            // Add shipping as an additional product, to complete the total.
            $request->addProduct()
                ->setCode($shippingAddress->getShippingMethod())
                ->setName(Mage::helper('optile')->__("Shipping costs").': '.$shippingAddress->getShippingDescription())
                ->setQuantity(1)
                ->setAmount($shippingAddress->getShippingAmount())
            ;
        }
        if (isset($totals['discount']) && $totals['discount']->getValue()) {
            $discount = $totals['discount']->getValue();

            // Add shipping as an additional product, to complete the total.
            $request->addProduct()
                ->setCode("discount")
                ->setName(Mage::helper('optile')->__("Discount"))
                ->setQuantity(1)
                ->setAmount($discount)
            ;
        }
        
        //T: This feature is not yet ready for production. TODO: validate 
        //   additional payment charge before charge request to Optile.
//        if($quote->getGrandTotal() > $quote->getSubtotal()){
//            
//            $payment_charge = $quote->getGrandTotal() - $quote->getSubtotal();
//            $request->addProduct()
//                ->setCode("payment_charge")
//                ->setName(Mage::helper('optile')->__("Payment charge"))
//                ->setQuantity(1)
//                ->setAmount($payment_charge)
//            ;
//        }
        
        // Customer

        $email =
            $billingAddress->getEmail() ?
            $billingAddress->getEmail() :
            $quote->getCustomerEmail();

        $customerId =
            $quote->getCustomerId() ?
            $quote->getCustomerId() :
            'Guest';

		$reqCustomer = $request->addCustomer();
		$reqCustomer
            ->setEmail($email)
            ->setNumber($customerId);

        if ($quote->hasCustomerDob()) {
            $dob = date('Y-m-d', strtotime($quote->getCustomerDob()));
            $reqCustomer->setBirthday($dob .'T00:00:00.000Z');
        }

        $reqCustomer->addName()
            ->setTitle($quote->getCustomerPrefix())
            ->setFirstName($quote->getCustomerFirstname())
            ->setMiddleName($quote->getCustomerMiddlename())
            ->setLastName($quote->getCustomerLastname())
        ;

        $reqCustomer->addAddress(\Optile\Request\CustomerAddress::TYPE_BILLING)
            ->setCity($billingAddress->getCity())
            ->setCountry($billingAddress->getCountry())
            ->setState($billingAddress->getRegionCode())
            ->setStreet($billingAddress->getStreetFull())
            ->setZip($billingAddress->getPostcode())
            ->addName()
                ->setTitle($billingAddress->getPrefix())
                ->setFirstName($billingAddress->getFirstname())
                ->setMiddleName($billingAddress->getMiddlename())
                ->setLastName($billingAddress->getLastname())
        ;

        $reqCustomer->addAddress(\Optile\Request\CustomerAddress::TYPE_SHIPPING)
            ->setCity($shippingAddress->getCity())
            ->setCountry($shippingAddress->getCountry())
            ->setState($shippingAddress->getRegionCode())
            ->setStreet($shippingAddress->getStreetFull())
            ->setZip($shippingAddress->getPostcode())
            ->addName()
                ->setTitle($shippingAddress->getPrefix())
                ->setFirstName($shippingAddress->getFirstname())
                ->setMiddleName($shippingAddress->getMiddlename())
                ->setLastName($shippingAddress->getLastname())
        ;

        $reqCustomer->addPhone(\Optile\Request\CustomerPhone::TYPE_OTHER)
            ->setUnstructuredNumber($billingAddress->getTelephone());

        // Callback
		$request->addCallback()
            ->setReturnUrl($this->getReturnUrl())
            ->setCancelUrl($this->getCancelUrl())
            ->setNotificationUrl($this->getNotificationUrl())
        ;

        // Deferred payments support
        $request->addPreselection()
                ->setDeferral($this->getPreselectionDeferral())
        ;

		Mage::helper('optile')->log("Grand total: ".$quote->getGrandTotal());
	}

}
