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

class Optile_Payment_Helper_Notification extends Mage_Core_Helper_Abstract {

    const XML_PATH_ALERT_EMAIL_TEMPLATE        = 'sales_email/transaction_failed_notification/template';
    const XML_PATH_ALERT_EMAIL_IDENTITY        = 'sales_email/transaction_failed_notification/identity';
    const XML_PATH_ALERT_EMAIL_TO         = 'sales_email/transaction_failed_notification/to';
    const XML_PATH_ALERT_EMAIL_ENABLED         = 'sales_email/transaction_failed_notification/enabled';

    public function processMessage($message, $data){

        if(isset($data['status']) && $data['status'] > 200){

            $this->sendTransactionFailedNotification($message, $data);

        }
    }


    // order_cancel_after
    private function sendTransactionFailedNotification($message, $data)
    {

        if (!Mage::getStoreConfigFlag(self::XML_PATH_ALERT_EMAIL_ENABLED)) {
            Mage::helper('optile')->log(__METHOD__.': Transaction failure Notification email is not enabled. Will not send.');
            return;
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        /* @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $emailInfo = Mage::getModel('core/email_info');
        /* @var $emailInfo Mage_Core_Model_Email_Info */

        $emailInfo->addTo(Mage::getStoreConfig(self::XML_PATH_ALERT_EMAIL_TO));
        $templateId = Mage::getStoreConfig(self::XML_PATH_ALERT_EMAIL_TEMPLATE);
        $mailer->addEmailInfo($emailInfo);


        Mage::helper('optile')->log(__METHOD__.': Sending Transaction failure Notification email');
        // Set all required params and send emails
        $mailer
            ->setSender(Mage::getStoreConfig(self::XML_PATH_ALERT_EMAIL_IDENTITY))
//            ->setStoreId($storeId)
            ->setTemplateId($templateId)
            ->setTemplateParams(array(
                'message'   => $message,
                'data'      => $this->array2ul($data),
            ))
            ->send()
        ;
        Mage::helper('optile')->log(__METHOD__.': Transaction failure Notification email sent');
    }



    function array2ul($array) {
        $out = "<ul>";
        foreach($array as $key => $elem){
            if(!is_array($elem)){
                $out .= "<li><span>" . $key . ": " . $elem . "</span></li>";
            } else {
                $out .= "<li><span>" . $key . "</span>" . $this->array2ul($elem) . "</li>";
            }
        }
        $out .= "</ul>";
        return $out;
    }



}
