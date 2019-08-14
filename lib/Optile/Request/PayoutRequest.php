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
 * PayoutRequest
 *
 * Used to execute the refund transaction on Optile.
 *
 * For reference, use Optile's documentation:
 * https://docs.optile.de/archive/PIN/6.%20Refunds.html#6.Refunds-CHARGERequest
 **/
class PayoutRequest extends Request {

	protected function validation() {
        $this->validateRequired('payment', 'Payment data');
	}

    public function send($method = self::METHOD_POST) {
        $result = parent::send($method);

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

}
