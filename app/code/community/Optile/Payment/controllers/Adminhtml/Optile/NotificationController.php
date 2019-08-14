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

class Optile_Payment_Adminhtml_Optile_NotificationController extends Mage_Adminhtml_Controller_Action
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
            ->_addBreadcrumb($this->__('optile IPNs'), $this->__('optile IPNs'));
        return $this;
    }

    /**
     * Orders grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('optile IPNs'));

        $this->_initAction();
        $this->_addContent(
            $this->getLayout()->createBlock('optile/adminhtml_notification')
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
            $this->getLayout()->createBlock('optile/adminhtml_notification_grid')->toHtml()
        );
    }
    
    public function reprocessAction(){

        $request = $this->getRequest();
        
        $ipn_id = $request->getParam("id");
        $ipn = Mage::getModel('optile/notification')->load($ipn_id);

        
        foreach($ipn->getReceivedData() as $k=>$v){
            $request->setParam($k, $v);
        }
        

        $params = $request->getParams();
        
        $quote = Mage::getModel('sales/quote')->getCollection()
                ->addFieldToFilter("entity_id", $params['transactionId'])
                ->getFirstItem();

        $session = Mage::getSingleton('core/session');
        
        $appEmulation = Mage::getSingleton('core/app_emulation');
        //Start environment emulation of the specified store
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($quote->getStoreId());

        //to skip IP checks
        Mage::setIsDeveloperMode(true);
        try{
            Mage::helper('optile')->log(__METHOD__.' - REPROCESS - Reprocessing IPN #'.$ipn_id);
            Mage::getModel('optile/notification')->processNotification($request);
        }
        catch(Exception $e){
            Mage::helper('optile')->log(__METHOD__.' - REPROCESS - Exception caught: '.$e->getMessage(), Zend_Log::ERR);
            Mage::helper('optile')->log(__METHOD__.' - REPROCESS - Trace: '.$e->getTraceAsString(), Zend_Log::ERR);

            $headerTexts = array(
                500 => $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Error', // Response code 500; effectively this makes Optile resend the message later.
                403 => $_SERVER['SERVER_PROTOCOL'] .' 403 Forbidden'
            );
            $responseMsg = $e->getMessage();
            $responseCode = $e->getCode();

            // Notify Optile of the error.
            if ($responseCode != 200) {
                
                $responseMsg = 'ERROR: '.$responseMsg;
                $session->addError("REPROCESS: ".$responseMsg);
            }
            $session->addNotice("REPROCESS: ".$responseMsg);
        }
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        $this->_redirect("/*/index");
    }

    /**
     * Order grid
     */
    public function viewAction()
    {
        $this->loadLayout();

        $block = $this->getLayout()
                ->createBlock('optile/adminhtml_notification_view')
                ->setTemplate('optile/notification_view.phtml')
                ->setNotificationId($this->getRequest()->getParam('notification_id'))
        ;

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $now = date('Ymd_Hi');
        $fileName   = "optile_ipns_${now}.csv";
        $grid       = $this->getLayout()->createBlock('optile/adminhtml_notification_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $now = date('Ymd_Hi');
        $fileName   = "optile_orders_${now}.xml";
        $grid       = $this->getLayout()->createBlock('optile/adminhtml_notification_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
