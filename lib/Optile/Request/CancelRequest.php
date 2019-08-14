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
 * CancelRequest
 *
 * Used for cancelling deferred payment transaction. Should be triggered when
 * a merchant or eCommerce system realizes that the payment will not be made.
 * This will result in release of customer's funds.
 *
 * @method ListRequest setChannel(string $value)
 * @method ListRequest setCountry(string $value)
 * @method ListRequest setTransactionId(string $value)
 */
class CancelRequest extends Request {

    public function __construct($apiUrl) {
        parent::__construct($apiUrl);

        $this->urlSuffix = '';

//        $this->setChannel(OptileChannel::WEB_ORDER);
    }

    protected function validation() {

    }

    /**
     * Send Cancel request to Optile and parse response.
     * @return \Optile\Response\Response|null
     */
    public function send($method = self::METHOD_DELETE) {
        $result = parent::send($method);

        if ($result) {
            $responseFactory = new \Optile\Response\ResponseFactory();
            $optileResponse = $responseFactory->BuildOptileResponse($result);

            return $optileResponse;
        }
    }
}