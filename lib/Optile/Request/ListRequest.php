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

namespace Optile\Request;

require_once __DIR__.'/../Response/ResponseFactory.php';

/**
 * ListRequest
 *
 * Used for getting the payment network list on checkout.
 * For list of required fields see validation() method.
 *
 *
 * @method ListRequest setChannel(string $value)
 * @method ListRequest setCountry(string $value)
 * @method ListRequest setTransactionId(string $value)
 */
class ListRequest extends Request {

    public function __construct($apiUrl) {
        parent::__construct($apiUrl);

        $this->urlSuffix = '/api/lists';

        $this->setChannel(OptileChannel::WEB_ORDER);
    }

    protected function validation() {
        $this->validateRequired('transactionId', 'Transaction ID');
        $this->validateRequired('channel', 'Channel');
        $this->validateRequired('country', 'Country');
        $this->validateRequired('callback', 'Callback data');
        $this->validateRequired('customer', 'Customer data');
    }

    /**
     * Send LIST request to Optile and parse response.
     * @return \Optile\Response\Response|null
     */
    public function send($method = self::METHOD_POST) {
        $result = parent::send($method);

        if ($result) {
            $responseFactory = new \Optile\Response\ResponseFactory();
            $optileResponse = $responseFactory->BuildOptileResponse($result);

            return $optileResponse;
        }else{
            return $result;
        }
    }

    /**
     * @return Callback
     */
    public function addCallback() {
        $component = RequestFactory::getComponent('callback');
        $this->setData('callback', $component);
        return $component;
    }

    /**
     * @return Customer
     */
    public function addCustomer() {
        $component = RequestFactory::getComponent('customer');
        $this->setData('customer', $component);
        return $component;
    }

    /**
     * @return Payment
     */
    public function addPayment() {
        $component = RequestFactory::getComponent('payment');
        $this->setData('payment', $component);
        return $component;
    }

    /**
     * @return Product
     */
    public function addProduct() {
        $component = RequestFactory::getComponent('product');
        $this->addData('products', $component);
        return $component;
    }

    /**
     * @return ClientInfo
     */
    public function addClientInfo() {
        $component = RequestFactory::getComponent('clientInfo');
        $this->setData('clientInfo', $component);
        return $component;
    }

    public function addPreselection(){
        $component = RequestFactory::getComponent('preselection');
        $this->setData('preselection', $component);
        return $component;
    }

}

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
