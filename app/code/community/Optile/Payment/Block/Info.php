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

class Optile_Payment_Block_Info extends Mage_Payment_Block_Info
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('optile/info.phtml');
    }

    /**
     * Get some specific information in format of array($label => $value)
     *
     * @return array
     */
    public function getSpecificInformation() {
        $info = (array)parent::getSpecificInformation();

        $order = $this->getInfo()->getOrder();

        if ($order !== null) {
            $quoteId = $order->getQuoteId();
            $store_id = $order->getStoreId();
        } else {
            $quoteId = $this->getInfo()->getQuote()->getId();
            $store_id = $this->getInfo()->getQuote()->getStoreId();
        }

        $optileQuote = Mage::getModel('optile/quote')->load($quoteId);
        $network = $optileQuote->getPaymentNetwork();
        $instructions = unserialize(Mage::getStoreConfig('payment/optile/instructions', $store_id));

        if (is_array($instructions)) {
            foreach ($instructions as $instruction) {
                if (strtolower($instruction['payment_method']) == strtolower($network)) {
                    $this->setInstructions($instruction['value']);
                    break;
                }
            }
        }

        $info['Payment Network'] = $network;
        $info['Optile Long Id'] = $optileQuote->getLongId();

        return $info;
    }
}