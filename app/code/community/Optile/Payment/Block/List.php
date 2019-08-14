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
 * Optile payment method block
 *
 */
class Optile_Payment_Block_List extends Mage_Payment_Block_Form {

	/**
	 * payment method code
	 */
	protected $_methodCode = 'optile';
    protected $_listResponse = null;

	/**
	 * Block constructor
	 */
	public function __construct() {
		$this->setTemplate ( 'optile/list.phtml' );
		parent::__construct ();
	}
    
    public function _toHtml() {
        Mage::helper('optile')->log("Doing _toHTML method");
        // check if there is sufficient data to execute List request

        try{
            $response = $this->getListResponse();

            if($response->getInteraction()->getCode() != "PROCEED"){
                $this->setTemplate ( 'optile/list_error.phtml' );
            }
        }catch (Exception $e){
            Mage::helper("optile")->log("Exception during LIST:");
            Mage::helper("optile")->log($e->getMessage());
            $this->setTemplate ( 'optile/list_error.phtml' );
        }
        return parent::_toHtml();
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

    public function getListResponse(){
        if($this->_listResponse === null){
            $this->_listResponse = $this->_getListResponse();
        }

        return $this->_listResponse;
    }

	/**
	 * Returns available networks to render in html
	 *
	 * ----
	 * If optile charge request responds with RETRY/TRY_OTHER_ACCOUNT/TRY_OTHER_NETWORK,
	 * existing list request will be refreshed through listRequestSelfLink sent
	 * by AJAX in checkout.js
	 */
	public function _getListResponse() {
        
		$listRequestSelfLink = Mage::app()->getRequest()->getParam('listRequestSelfLink');

		$optile = Mage::getModel('optile/checkout');
		$response = $optile->requestAvailableNetworks($listRequestSelfLink);

		return $response;
	}

	/**
	 * Prepares optile payment network form, replaces ${formId} with our form id
	 * @param unknown $form
	 * @param unknown $form_id
	 * @return mixed
	 */
	public function renderFormHtml($form, $form_id) {

		return str_replace (
                array(
                    'function ${formId}_WhatIsPayPal()', //T: temporary fix to avoid AJAX issues with Prototype
                    '${formId}',
                    ),
                array(
                    '${formId}_WhatIsPayPal = function()', //T: temporary fix to avoid AJAX issues with Prototype
                    $form_id,
                    ),
                $form );
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
		return $this->getMethodCode () . '_' . $networkCode;
	}

}