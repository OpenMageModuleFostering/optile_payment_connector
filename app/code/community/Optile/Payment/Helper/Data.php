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

class Optile_Payment_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Logging filename
	 */
	protected $_logFileName = 'optile.log';

	/**
	 * Log helper
	 */
	public function log( $message, $level = Zend_log::DEBUG ) {

		if($this->isLogEnabled() && $level <= $this->getLogLevel()){
			Mage::log( $message, $level, $this->_logFileName, true );
        }
	}
    
    public function getQuotePrefix(){
        return Mage::getStoreConfig('payment/optile/quote_prefix');
    }
    
    public function formatQuoteId($quote_id){
        return $this->getQuotePrefix().$quote_id;
    }

	/**
	 * Returns whether logging is enabled
	 */
	protected function isLogEnabled() {
		return Mage::getStoreConfig('payment/optile/log_enabled');
	}
	protected function getLogLevel() {
		return Mage::getStoreConfig('payment/optile/log_level');
	}

	/**
	 * Returns payment info/instructions for payment method.
	 * @param string $network
	 * @param int $store_id
	 * @return string
	 */
	public function getPaymentInstructions($network, $store_id=null) {
		$address = sprintf('payment/optile/instructions');

		$instructions = Mage::getStoreConfig($address, $store_id);

		$instructions = unserialize($instructions);
		if (!is_array($instructions)) {
			return;
		}

		foreach ($instructions as $instruction) {
			if (strtolower($instruction['payment_method']) == strtolower($network)) {
				return $instruction['value'];
			}
		}
	}
}