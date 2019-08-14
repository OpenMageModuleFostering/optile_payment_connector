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

class Optile_Payment_PaymentController extends Mage_Core_Controller_Front_Action
{
    public function cancelAction()
    {
        try {
            // TODO verify if this logic of order cancelation is deprecated
            // if there is an order - cancel it
            $orderId = $this->_getCheckoutSession()->getLastOrderId();
            $order = ($orderId) ? Mage::getModel('sales/order')->load($orderId) : false;

            Mage::helper('optile')->log("Customer landed on order cancellation page");
            Mage::helper('optile')->log("Order #".$order->getIncrementId()." ". ($order->canCancel() ? "can" : "cannot")." be cancelled.");
            if ($order && $order->getId() && $order->getQuoteId() == $this->_getCheckoutSession()->getQuoteId()) {
//                Mage::helper('optile')->log("Denying the payment...");
//                $order->getPayment()->deny();
//                Mage::helper('optile')->log("Payment denied, proceeding to cancel the order");
//                $order->cancel()->save(); // Payment deny() method already canceles the order.
                $this->_getCheckoutSession()
//                    ->unsLastQuoteId()
//                    ->unsLastSuccessQuoteId()
//                    ->unsLastOrderId()
//                    ->unsLastRealOrderId()
                    ->addSuccess($this->__('Checkout and order has been canceled.'))
                ;
//                if($order->isCanceled()){
//                    Mage::helper('optile')->log("Order #".$order->getIncrementId()." has been canceled.", Zend_log::INFO);
//                }else{
//                    Mage::helper('optile')->log("Order #".$order->getIncrementId()." has NOT been canceled.", Zend_log::INFO);
//                }
            } else {
                $this->_getCheckoutSession()->addSuccess($this->__('Checkout has been canceled.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            Mage::helper('optile')->log(__METHOD__.": ".$e->getMessage());
        } catch (Exception $e) {
            $this->_getCheckoutSession()->addError($this->__('Unable to cancel checkout.'));
            Mage::logException($e);
            Mage::helper('optile')->log(__METHOD__.": ".$e->getMessage());
        }

        $this->_redirect('checkout/cart');
    }

    public function ilogAction(){
        $params = Mage::app()->getRequest()->getPost();
        $message = isset($params['message']) ? $params['message'] : null;
        $data = isset($params['data']) ? $params['data'] : null;

        if($message == null){
            die(); // nothing to log, obscuring log API.
        }

        Mage::helper('optile')->log($message, Zend_Log::ALERT);
        Mage::helper('optile')->log($data,  Zend_log::ALERT);


        // Send out alert emails, if so configured in Admin
        Mage::helper('optile/notification')->processMessage($message, $data);

        die();
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }



}
