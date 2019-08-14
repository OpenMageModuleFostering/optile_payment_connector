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

class Optile_Payment_Model_Observer
{
    const XML_PATH_CANCEL_EMAIL_TEMPLATE        = 'sales_email/order_cancel/template';
    const XML_PATH_CANCEL_EMAIL_GUEST_TEMPLATE  = 'sales_email/order_cancel/guest_template';
    const XML_PATH_CANCEL_EMAIL_IDENTITY        = 'sales_email/order_cancel/identity';
    const XML_PATH_CANCEL_EMAIL_COPY_TO         = 'sales_email/order_cancel/copy_to';
    const XML_PATH_CANCEL_EMAIL_COPY_METHOD     = 'sales_email/order_cancel/copy_method';
    const XML_PATH_CANCEL_EMAIL_ENABLED         = 'sales_email/order_cancel/enabled';

    public function disableOtherPaymentMethods($observer)
    {
        if ($observer->getSection() != 'payment') {
            return;
        }

        if ($observer->getStore()) {
            $scope   = 'stores';
            $scopeId = (int)Mage::getConfig()->getNode('stores/'. $observer->getStore() .'/system/store/id');
        } elseif ($observer->getWebsite()) {
            $scope   = 'websites';
            $scopeId = (int)Mage::getConfig()->getNode('websites/'. $observer->getWebsite() .'/system/website/id');
        } else {
            $scope   = 'default';
            $scopeId = 0;
        }

        // Get previously instantiated config data.
        $configData = Mage::getSingleton('adminhtml/config_data');

        $disable = (bool)$configData->getData('groups/optile/fields/disable_methods/value');
        $methods = (array)$configData->getData('groups/optile/fields/methods_to_disable/value');

        // Disable selected payment methods, if that is what the admin requested.
        if ($disable) {
            foreach ($methods as $methodCode) {
                Mage::getConfig()->saveConfig('payment/'.$methodCode.'/active', 0, $scope, $scopeId);
            }

            // Mark the notification (install script) as read.
            $inbox = Mage::getModel('adminnotification/inbox')
                ->load('Optile Payment Extension: Please disable other payment methods', 'title')
            ;
            if ($inbox->getId() && !$inbox->getIsRead()) {
                $inbox
                    ->setIsRead(1)
                    ->save()
                ;
            }
        }

        // Delete these config values, they're not real configuration.
        Mage::getConfig()
            ->deleteConfig('payment/optile/active_methods_found', $scope, $scopeId)
            ->deleteConfig('payment/optile/disable_methods', $scope, $scopeId)
            ->deleteConfig('payment/optile/methods_to_disable', $scope, $scopeId)
            ->reinit()
        ;
    }

    // order_cancel_after
    public function sendOrderCancelEmail($observer)
    {
        Mage::helper('optile')->log(__METHOD__.': Event triggered.');
        $storeId = Mage::app()->getStore()->getId();
        $order = $observer->getOrder();
        /* @var $order Mage_Sales_Model_Order */

        if (!Mage::getStoreConfigFlag(self::XML_PATH_CANCEL_EMAIL_ENABLED, $storeId)) {
            Mage::helper('optile')->log(__METHOD__.': Cancel email is not enabled. Will not send.');
            return;
        }

        // Send these cancel emails only for Optile orders.
        if ($order->getPayment()->getMethod() != 'optile') {
            Mage::helper('optile')->log(__METHOD__.': Payment method is not Optile. Will not send.');
            return;
        }

        // Do not send emails for the following status codes...
        $removed_statuses = array('debit_failed', 'debit_declined', 'debit_aborted');
        Mage::helper('optile')->log(__METHOD__.': '.$order->getOptileStatusCode());
        if(in_array($order->getOptileStatusCode(), $removed_statuses)){
            return;
        }

        // Get the destination email addresses to send copies to
        $copyToData = (string)Mage::getStoreConfig(self::XML_PATH_CANCEL_EMAIL_COPY_TO, $storeId);
        $copyTo = $copyToData ? explode(',', $copyToData) : array();
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_CANCEL_EMAIL_COPY_METHOD, $storeId);

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_CANCEL_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_CANCEL_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        /* @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $emailInfo = Mage::getModel('core/email_info');
        /* @var $emailInfo Mage_Core_Model_Email_Info */

        Mage::helper('optile')->log(__METHOD__.': mail recipient: '.$order->getCustomerEmail());
        $emailInfo->addTo($order->getCustomerEmail(), $customerName);
        $mailer->addEmailInfo($emailInfo);

        if ($copyTo) {
            if ($copyMethod == 'bcc') {
                // Add bcc to customer email
                $emailInfo = Mage::getModel('core/email_info');

                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
                $mailer->addEmailInfo($emailInfo);
            }

            if ($copyMethod == 'copy') {
                // Email copies are sent as separated emails if their copy method is 'copy'
                foreach ($copyTo as $email) {
                    $emailInfo = Mage::getModel('core/email_info');
                    $emailInfo->addTo($email);
                    $mailer->addEmailInfo($emailInfo);
                }
            }
        }

        Mage::helper('optile')->log(__METHOD__.': Sending Cancellation email');
        // Set all required params and send emails
        $mailer
            ->setSender(Mage::getStoreConfig(self::XML_PATH_CANCEL_EMAIL_IDENTITY, $storeId))
            ->setStoreId($storeId)
            ->setTemplateId($templateId)
            ->setTemplateParams(array(
                'order'        => $order,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml,
            ))
            ->send()
        ;
        Mage::helper('optile')->log(__METHOD__.': Cancellation email sent');

        $order->setEmailSent(true);
    }

    public function updateOptileQuote($observer)
    {
        $payment = $observer->getPayment();
        /* @var $payment Mage_Payment_Model_Info */

        if ($payment->getMethod() != 'optile' || !$payment->hasNetwork()) {
            return;
        }

        $quoteId = $payment->getQuoteId();
        $optileQuote = Mage::getModel('optile/quote')->load($quoteId);

        if (!$optileQuote->getId()) {
            $optileQuote->setTransactionId($quoteId);
        }

        $optileQuote
            ->setPaymentNetwork($payment->getNetwork())
            ->save()
        ;
    }

    // sales_order_payment_cancel
    public function cancelDeferredPayment($observer){

        $payment = $observer->getPayment();
        /* @var $payment Mage_Sales_Model_Order_Payment */

        // Fix for error when cancelling orders that weren't made using Optile
        if($payment->getMethod() !== 'optile') return;

        // Fix for Magento 1.7.x - Not cancelling orders that are in payment review.
        if($payment->getOrder()->isPaymentReview()){
            Mage::helper('optile')->log(__METHOD__.": Order is in payment review, throwing an exception");
            throw new Exception("Order cannot be cancelled when in payment review.");
        }

        // Check if this is being triggered by Admin or IPN. Do not execute if triggered by IPN.
        if(Mage::registry('optile_ipn') == true){
            Mage::helper('optile')->log("Not triggering method instance::cancelDeferred");
            return $this;
        }
        $payment->getMethodInstance()->cancelDeferred($payment);
    }

}
