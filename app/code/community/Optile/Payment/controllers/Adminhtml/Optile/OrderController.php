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

class Optile_Payment_Adminhtml_Optile_OrderController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/optile_order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('optile Orders'), $this->__('optile Orders'));
        return $this;
    }

    /**
     * pass on params to IPN testing script to simulate IPN action
     */
    public function simulateAction(){

        $transaction_id = $this->getRequest()->getParam("transaction_id");
        $status_code = $this->getRequest()->getParam("status_code");
        $reason_code = $this->getRequest()->getParam("reason_code");
        $optile_quote = Mage::getModel('optile/quote')->load($transaction_id);
        $long_id = $optile_quote->getLongId();


        RequestFactory::setLogger(Mage::helper('optile'));
        $request = RequestFactory::getSimpleRequest(Mage::getUrl('optile/notification'));
        /* @var $request SimpleRequest */

        $request->setinteractionCode('IPN_TEST');
        $request->setreturnCode('IPN_TEST');
        $request->settransactionId($transaction_id);
        $request->setinteractionReason('EXPIRED');
        $request->setentity('payment');
        $request->setlongId($long_id);
        $request->setreferenceId($long_id);
        $request->setreasonCode($reason_code);
        if($this->getRequest()->has('network')){
            $request->setnetwork($this->getRequest()->getParam('network'));
        }
        $request->setresultInfo('IPN test script');
        $request->setstatusCode($status_code);
        $request->settimestamp(date("Y-m-d H:i:s"));
        $request->setnotificationId('IPNTest123');
        $request->setshortId('IPNTest123');
        $request->setresultCode('IPNTest123');

        $response = $request->send();

        $session = Mage::getSingleton('core/session');

        $session->addNotice("IPN responded: ".$response);

        $this->_redirect('*/*/');
    }

    /**
     * Orders grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('optile Orders'));

        $this->_initAction();
        $this->_addContent(
            $this->getLayout()->createBlock('optile/adminhtml_order')
        );
        $this->renderLayout();
    }

    /**
     * Order grid
     */
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('optile/adminhtml_order_grid')->toHtml()
        );
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $now = date('Ymd_Hi');
        $fileName   = "optile_orders_${now}.csv";
        $grid       = $this->getLayout()->createBlock('optile/adminhtml_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $now = date('Ymd_Hi');
        $fileName   = "optile_orders_${now}.xml";
        $grid       = $this->getLayout()->createBlock('optile/adminhtml_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
