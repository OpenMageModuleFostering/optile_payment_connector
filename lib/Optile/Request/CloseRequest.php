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
 * CloseRequest
 *
 * Used for closing deferred payment transaction. Should be triggered when
 * Merchant wants to capture the reserved funds from Customer. It can be executed
 * without setting the payment and product parameters. In this case entire
 * order amount will be captured, however it is recommended to specify the
 * payment and product paramaters.
 *
 * @method ListRequest setChannel(string $value)
 * @method ListRequest setCountry(string $value)
 * @method ListRequest setTransactionId(string $value)
 */
class CloseRequest extends Request {

    public function __construct($apiUrl) {
        parent::__construct($apiUrl);

        $this->urlSuffix = '';

//        $this->setChannel(OptileChannel::WEB_ORDER);
    }

    protected function validation() {

    }

    /**
     * Send CLOSE request to Optile and parse response.
     * @return \Optile\Response\Response|null
     */
    public function send($method = self::METHOD_POST) {
        $result = parent::send($method);
        Logger::log(__METHOD__.': '.$result);
        if ($result) {
            $responseFactory = new \Optile\Response\ResponseFactory();
            $optileResponse = $responseFactory->BuildOptileResponse($result);

            return $optileResponse;
        }
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

}