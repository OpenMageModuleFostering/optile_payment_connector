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

/**
 * ChargeRequest
 *
 * Used to execute the payment transaction on Optile.
 *
 * NOTE: This SDK currently supports only NATIVE_WITHOUT_PCI implementation, so
 * charge request is not being handled through the merchant's system.
 * For reference, use Optile's documentation:
 * https://docs.optile.de/archive/PIN/CHARGE%20Request.html
 **/
class ChargeRequest extends Request {

	protected function validation() {
        $this->validateRequired('account', 'Account data');
	}

    public function send($method = self::METHOD_POST) {
        return parent::send($method);
    }

    /**
     * @return OptileAccount
     */
    public function addAccount() {
        $component = RequestFactory::getComponent('account');
        $this->setData('account', $component);
        return $component;
    }

}
