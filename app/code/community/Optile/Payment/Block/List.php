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
 * Optile payment method block
 *
 */
class Optile_Payment_Block_List extends Mage_Payment_Block_Form {
	
	/**
	 * payment method code
	 */
	protected $_methodCode = 'optile';
	
	/**
	 * Logging filename
	 */
	protected $_logFileName = 'optile.log';
	
	/**
	 * Block constructor
	 */
	public function __construct() {
		$this->setTemplate ( 'optile/list.phtml' );
		parent::__construct ();
	}
	
	/**
	 * Returns whether logging is enabled
	 */
	private function isLogEnabled() {
		return Mage::getStoreConfig ( 'payment/optile/log_enabled', Mage::app ()->getStore () );
	}
	
	/**
	 * Logging helper
	 * @param unknown $what thing to log
	 * @param string $level log level
	 */
	public function log($what, $level = null) {
		if ($this->isLogEnabled ())
			Mage::log ( $what, $level, $file = $this->_logFileName, true );
	}
	
	/**
	 * Adds optilevalidation.js into <script> when block is shown
	 */
	protected function _prepareLayout() {
		if ($this->getLayout ()->getBlock ( 'head' )) {
// 			$this->getLayout ()->getBlock ( 'head' )->addJs ( 'optile/optilevalidation.js' );
// 			$this->getLayout ()->getBlock ( 'head' )->addJs ( 'optile/checkout.js' );
		}
		
		return parent::_prepareLayout ();
	}

	/**
	 * Returns available networks to render in html
	 * 
	 * ----
	 * If optile charge request responds with RETRY/TRY_OTHER_ACCOUNT/TRY_OTHER_NETWORK,
	 * existing list request will be refreshed through listRequestSelfLink sent
	 * by AJAX in checkout.js
	 */
	public function getListResponse() {
		$listRequestSelfLink = Mage::app ()->getRequest ()->getParam ( 'listRequestSelfLink', null );
		
		$quote = Mage::getSingleton ( 'checkout/type_onepage' )->getQuote ();
		$optile = Mage::getModel ( 'optile/checkout', array (
				'quote' => $quote,
				'listRequestSelfLink' => $listRequestSelfLink 
		) );
		$response = $optile->requestAvailableNetworks ();
		
		return $response;
	}
	
	/**
	 * Prepares optile payment network form, replaces ${formId} with our form id
	 * @param unknown $form
	 * @param unknown $form_id
	 * @return mixed
	 */
	public function renderFormHtml($form, $form_id) {
		return str_replace ( '${formId}', $form_id, $form );
	}

	/**
	 * Returns form id for network code
	 * 
	 * E.g. if networkcode is 'VISA', return 'optile-VISA'
	 * 
	 * @param unknown $networkCode
	 * @return string
	 */
	public function getFormId($networkCode) {
		return $this->getMethodCode () . '-' . $networkCode;
	}

}