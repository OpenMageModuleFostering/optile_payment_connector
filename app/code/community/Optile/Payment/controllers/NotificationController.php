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
 * Processes Optile payment status notification
 * Each time the status of a transaction changes, a back-channel notification is sent to the <notificationUrl> with additional parameters.
 *
 * Note: notifications are sent in a clear way without any additional security.
 * HTTPS protocol should always be used in a production environment.
 * To allow notifications from Optile open payments servers to reach a merchant
 * system that is protected by firewall, the firewall has to be configured to
 * accept requests from Optile servers IP addresses.
 * In this case the following IPs should be used:
 * Test server (sandbox.oscato.com) => 144.76.239.125
 * Production server (oscato.com) => 213.155.71.162
 */
class Optile_Payment_NotificationController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $request = $this->getRequest();

        try{
            Mage::getModel('optile/notification')->processNotification($request);
        }
        catch(Exception $e){
            Mage::helper('optile')->log(__METHOD__.' - Exception caught: '.$e->getMessage(), Zend_Log::DEBUG);
            Mage::helper('optile')->log(__METHOD__.' - Trace: '.$e->getTraceAsString(), Zend_Log::DEBUG);

            $headerTexts = array(
                500 => $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Error', // Response code 500; effectively this makes Optile resend the message later.
                403 => $_SERVER['SERVER_PROTOCOL'] .' 403 Forbidden'
            );
            $responseMsg = $e->getMessage();
            $responseCode = $e->getCode();

            // Notify Optile of the error.
            if ($responseCode != 200) {

                if(!isset($headerTexts[$responseCode])){
                    $headerTexts[$responseCode] = $_SERVER['SERVER_PROTOCOL'] .' '.$responseCode. ' Error';
                }

                header($headerTexts[$responseCode], true, $responseCode);
                $responseMsg = 'ERROR: '.$responseMsg;
            }
            die($responseMsg);
        }

    }
}
