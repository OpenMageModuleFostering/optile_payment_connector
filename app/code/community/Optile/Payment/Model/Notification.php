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
 * Optile notification handler model
 *
 */
class Optile_Payment_Model_Notification extends Mage_Core_Model_Abstract {

    const STATUS_UNEXPECTED = 'unexpected_notification';

    private $reason_codes = array(
        'charged' => array(
            'debited'           => 'Charge completed',
            'debited_partial'   => 'Debited partially', //2016-03-10 NEW
            'payment_received'  => 'Charge completed',
        ),
        'paid_out' => array(
            'refund_credited'   => 'Payout completed',
            'credited'          => 'Payout completed',
        ),
        'preauthorized' => array(
            // Deferred payment notification codes
            'preauthorized'     => 'Preauthorization OK',
            'debited_partial'   => 'A successfull Deferred Charge has been partially closed',
        ),

        'pending' => array(
            'debit_requested'           => 'Operation pending. Please wait for further notification',
            'refund_requested'          => 'Operation pending',
            'payment_demand_requested'  => 'Operation pending',
            'payment_demanded'          => 'Operation pending',
            'credit_requested'          => 'Operation pending',
            'retry_scheduled'           => 'Payment retry scheduled', //2016-03-10 NEW
            // Deferred payment notification codes
            'preauthorization_requested' => 'Operation pending',
            'preorder_issued'           => 'Operation pending',
            'preorder_requested'        => 'Operation pending',
            'cancelation_requested'     => 'Operation pending',
        ),
        'failed' => array(
            'debit_failed'              => 'Operation failed due to the technical reasons',
            'refund_failed'             => 'Operation failed due to the technical reasons',
            'payment_demand_failed'     => 'Operation failed due to the technical reasons',
            'credit_failed'             => 'Operation failed due to the technical reasons',
            // Deferred payment notification codes
            'preauthorization_failed'   => 'Preauthorization failed due to the technical reasons',
            'preorder_failed'           => 'Preorder failed due to the technical reasons',
        ),
        'declined' => array(
            'debit_declined'            => 'Operation declined by institution for business reasons',
            'refund_declined'           => 'Operation declined by institution for business reasons',
            'payment_demand_declined'   => 'Operation declined by institution for business reasons',
            'credit_declined'           => 'Operation declined by institution for business reasons',
            // Deferred payment notification codes
            'preauthorization_declined' => 'Operation declined by institution for business reasons',
            'preorder_declined'         => 'Operation declined by institution for business reasons',
        ),
        'aborted' => array(
            'debit_aborted'             => 'Operation aborted by customer',
            'payment_demand_aborted'    => 'Operation aborted by customer',
            // Deferred payment notification codes
            'preauthorization_aborted'  => 'Operation aborted by customer',
        ),
        'expired' => array(
            // Deferred payment notification codes
            'preauthorization_expired'  => 'Preauthorization reference has expired',
            'request_expired'           => 'Request operation has expired',
        ),
        'depleated' => array(
            // Deferred payment notification codes
            'preauthorization_depleted' => 'Authorized amount has been debited',
        ),
        'charged_back' => array(
            'charged_back'              => 'Amount charged back form merchant account due to customer complaint or dispute',
        ),
        'information_requested' => array(
            'information_requested'     => 'Charge disputed by customer',
        ),
        'dispute_closed' => array(
            'dispute_closed'            => 'Dispute closed',
            'chargeback_canceled'       => 'Chargeback canceled',
        ),
        'canceled' => array(
            'debit_canceled'            => 'Operation canceled by merchant',
            'refund_canceled '          => 'Operation canceled by merchant',
            'payment_demand_canceled '  => 'Operation canceled by merchant',
            'receipt_canceled '         => 'Operation canceled by merchant',
            'credit_canceled '          => 'Operation canceled by merchant',
            // Deferred payment notification codes
            'preorder_canceled'         => 'Preorder canceled',
            'preauthorization_canceled' => 'Preauthorization canceled',
        ),
    );

    protected function _construct() {
        $this->_init('optile/notification', 'id');
    }

    protected function isInvoiceNotificationEnabled(){
        return Mage::getStoreConfig('payment/optile/invoice_notification');
    }

	/**
	 * Magento request params instance
	 */
	protected $_params;

	/**
	 * Returns optile notification IPs (configured in admin).
	 * Other IPs are blacklisted for notifications
	 *
	 * Test server (sandbox.oscato.com) => 144.76.239.125
	 * Production server (oscato.com) => 213.155.71.162
	 *
	 * @return string
	 */
	private function getAllowedIP() {
		return Mage::getStoreConfig('payment/optile/remote_ip');
	}

	/**
	 * Process status notification
	 *
	 * @param Magento Request $request
	 */
	public function processNotification($request) {

        Mage::register('optile_ipn', true);
		$params = $request->getParams();
        if(isset($params['transactionId'])){
            //Stripping the quote prefix
            $params['transactionId'] = str_replace(Mage::helper('optile')->getQuotePrefix(), "", $params['transactionId']);
        }
        $this->_params = $params;

        $this->setDate(date('Y-m-d H:i:s'));
        $this->setReceivedData($params);
        $this->save();

        Mage::helper('optile')->log(__METHOD__ .' INCOMING IPN from: '.$_SERVER['REMOTE_ADDR'], Zend_Log::INFO);
		Mage::helper('optile')->log($params, Zend_Log::INFO);

        // Authenticity check by verifying IP address.
        $allowed_ip = $this->getAllowedIP();
        $proxy_ip = Mage::getStoreConfig('payment/optile/proxy_ip');

        if (!Mage::getIsDeveloperMode() && $proxy_ip && ($_SERVER['REMOTE_ADDR'] != $proxy_ip || !isset($_SERVER['HTTP_X_FORWARDED_FOR']))) {
            // Configured proxy / load balancer IP address does not match.
            Mage::helper('optile')->log(__METHOD__ .' Bad configuration value for proxy / load balancer', Zend_Log::ERR);
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                Mage::helper('optile')->log(__METHOD__ .' Suggested configuration: '. $_SERVER['REMOTE_ADDR'], Zend_Log::ERR);
            } else {
                Mage::helper('optile')->log(__METHOD__ .' No proxy detected!', Zend_Log::ERR);
            }
            $this->exitWithMsg("Access denied", 403);
        }
        if (!Mage::getIsDeveloperMode() && $allowed_ip != ($proxy_ip ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'])) {
            Mage::helper('optile')->log(__METHOD__ .' UNAUTHORIZED ACCESS FROM: '. $_SERVER['REMOTE_ADDR'], Zend_Log::ALERT);
            $this->exitWithMsg("Access denied", 403);
        }

		// Only handle failures regarding payments.
		if ($params['entity'] != 'payment') {
			$msg = ' Not handling '. $params['entity'] .' status: "'. $params['statusCode'] .'"';
			Mage::helper('optile')->log(__METHOD__ . $msg, Zend_Log::NOTICE);
            $this->exitWithMsg('NOTICE:'. $msg);
		}

        $i = 0;
        $quote_has_reserved_id = false;
        // Try a few times, Magento is slow.
        while($i < 3 &&  !$quote_has_reserved_id){
            $quote = Mage::getModel('sales/quote')->load($params['transactionId']);
            if(!$quote->getId()) {
                // MVR_NAT_SC2 - If order/quote ID is not found, accept the notification and do nothing.
                $msg = 'Quote with ID '. $params['transactionId'] .' not found.';
                Mage::helper('optile')->log($msg, Zend_Log::NOTICE);
                $this->exitWithMsg($msg);
            }
            $quote_has_reserved_id = $quote->getReservedOrderId();
            if($quote_has_reserved_id){
                Mage::helper('optile')->log('Reserved orderID found. Not Sleeping anymore.');
                break; // no need to sleep
            }
            Mage::helper('optile')->log('Reserved orderID not set yet. Sleeping: '.($i+1).' time(s).');
            sleep(2);
            $i++;
        }

        if(!$quote->getReservedOrderId()) {
            //2016-03-10: T: What if order will never exist, because payment failed?
            //Check if the statusCode is failed/aborted/canceled/expired first. If so, it is ok to return status 200
            if(in_array($params['statusCode'], ['declined', 'aborted', 'expired', 'failed'])){
                $this->exitWithMsg("Transaction #".Mage::helper('optile')->getQuotePrefix().$params['transactionId']." has failed. Returning status 200");
            }

            $msg = 'Quote with ID '. $params['transactionId'] .' found, but orderID not set yet.';
            Mage::helper('optile')->log($msg, Zend_Log::NOTICE);
            $this->exitWithMsg($msg, 500);
        }

		$orderIncrementId = $quote->getReservedOrderId();
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

		switch($params['statusCode']) {
			case 'charged':
				$this->handleCharged($order);
				break;
            case 'charged_back':
                $this->handleChargedBack($order);
                break;
            case 'information_requested':
                $this->handleInfoRequested($order);
                break;
            case 'dispute_closed':
                $this->handleDisputeClosed($order);
                break;
//			case 'registered':
//				// @TODO
			case 'failed':
			case 'declined':
			case 'aborted':
			case 'canceled':
			case 'expired':
				$this->handleFailed($order);
				break;
			case 'pending':
				$this->handlePending($order);
				break;
			case 'preauthorized':
				$this->handlePreauthorized($order);
				break;
			default:
				$msg = "Not handling ".$params['statusCode'];
                Mage::helper('optile')->log($msg, Zend_Log::NOTICE);
                $this->exitWithMsg($msg);
		}

	}

    private function handlePreauthorized($order){

        $payment = $order->getPayment();
        /* @var $payment Mage_Sales_Model_Order_Payment */

		$params = $this->_params;
        $reason_code = isset($this->_params['reasonCode']) ? $this->_params['reasonCode'] : "";
        $interaction_code = isset($this->_params['interactionCode']) ? $this->_params['interactionCode'] : "";

        Mage::helper('optile')->log(__METHOD__ .' Registering preauthorized notification...');
        Mage::helper('optile')->log(__METHOD__.': Loading OptileQuote #'.$order->getQuoteId(), Zend_log::INFO);
        // Update our own table with some useful data.
        $optileQuote = Mage::getModel('optile/quote')
            ->load($order->getQuoteId());

        // 'debited_partial' IPN come after a partial invoice.
        if($reason_code == 'debited_partial') {
            $this->exitWithMsg("Received 'debited_partial' IPN, accepting.");
        }

        Mage::helper('optile')->log(__METHOD__ .' Order is in payment review: '. ($order->isPaymentReview() ? 'YES' : 'NO'), Zend_Log::INFO);
        if ($order->isPaymentReview()) {
            if ($optileQuote->hasLongId()) {
                $comment = 'Payment has been preauthorized, status will be updated later';
            } else {
                $comment = 'Payment has been preauthorized. Updating longId to '. $this->_params['longId'].'. In order to preform payment Capture, please create an Invoice now.';
            }

            $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";
            Mage::helper('optile')->log(__METHOD__.': '.$comment, Zend_log::INFO);
            $order->addStatusHistoryComment($comment); //->save();
        } else {
            // MVR_NAT_SC2 5.3 Unexpected notification, accept and change status.
            $msg = 'Order is no longer pending!';
            $comment = 'Received notification "'. $params['statusCode'] .'" but the order is already paid or canceled.';
            $comment .= ' Details: '. $params['resultInfo'];

            $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";
            Mage::helper('optile')->log(__METHOD__.': '.$comment, Zend_log::INFO);
            $this->exitUnexpectedNotification($order, $msg, $comment);
        }

        $optileQuote
            ->setLongId($this->_params['longId'])
            ->setPaymentNetwork($this->_params['network'])
            ->setMonitorLink($this->generateMonitorLink($order))
            ->setDeferredMode(Optile_Payment_Model_Quote::DEFERRED)
            ->save();
        ;

        Mage::helper('optile')->log(__METHOD__.'Accepting payment', Zend_Log::INFO);
        $payment->accept();
        $order->save();
//        $order->getPayment()->accept();
        try {
            $order->sendNewOrderEmail();
        } catch (Exception $e) {
            Mage::helper('optile')->log($e->getMessage(), Zend_log::WARN);
            Mage::logException($e);
        }
        $order->save();

		Mage::helper('optile')->log(__METHOD__ .' DONE');
        $this->exitWithMsg('OK');
    }

    /**
     * Internal helper method for generating a monitor link.
     * @param Mage_Sales_Model_Order $order
     */
    private function generateMonitorLink($order){
        // Base URL for viewing transactions in Optile Monitor.
        $viewUrl = rtrim(Mage::getStoreConfig('payment/optile/api_url', $order->getStore()), '/');
        $viewUrl .= '/monitor/transactions/%s';
        return sprintf($viewUrl, $this->_params['longId']);
    }

	/**
	 * Process optile status notification with statusCode 'charged'
	 * @param Mage_Sales_Model_Order $order
	 */
	private function handleCharged($order) {

        $reason_code = isset($this->_params['reasonCode']) ? $this->_params['reasonCode'] : "";
        $interaction_code = isset($this->_params['interactionCode']) ? $this->_params['interactionCode'] : "";
		$payment = $order->getPayment();    /* @var $payment Mage_Sales_Model_Order_Payment */

        $optileQuote = Mage::getModel('optile/quote')
            ->load($order->getQuoteId());   /* @var $optileQuote Optile_Payment_Model_Quote */

        // If order is in deferred mode, just accept the notification.
        if($optileQuote->getDeferredMode() == Optile_Payment_Model_Quote::DEFERRED){

            // TODO: Should we check everything again, just in case?
            $this->exitWithMsg("Deferred CHARGE accepted.");
        }

        // 'debited_partial' IPN comes unexpectedly after a partial refund. Refund is actually handled in PaymentMethod.php.
        if($reason_code == 'debited_partial') {
            $this->exitWithMsg("Received 'debited_partial' IPN, accepting.");
        }

		// Check current order status.
		if (!$order->isPaymentReview() || $order->getStatus() == Mage_Sales_Model_Order::STATUS_FRAUD) {
            // Prepare response to Optile.
			$state = $order->getState();
			$status = $order->getStatus();
			$msg = sprintf('Current order state does not allow payments (%s / %s)', $state, $status);

            // Prepare order status history comment.
			$comment = 'Rejected payment notification because order status is: ';
			$comment .= $order->getStatusLabel();
            $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";

            // Send messages and end execution.
            $this->exitUnexpectedNotification($order, $msg, $comment);
		}

		// Match amount. TODO: refactor this!
		$orderSum = (float) $order->getGrandTotal();
		$paramSum = (float) $this->_params['amount'];
		if ($orderSum !== $paramSum) {
			$msg = 'Authorized amount ('. $paramSum .') does not match order amount ('. $orderSum .')';

			$payment->registerCaptureNotification($paramSum);
			$order->save();

            Mage::helper('optile')->log($msg, Zend_Log::ERR);
            $this->exitWithMsg($msg);
		}

		// Update payment.
		Mage::helper('optile')->log(__METHOD__ .' Updating payment...');


		$payment->registerCaptureNotification($paramSum);
        $invoice = $payment->getCreatedInvoice();
        /* @var $invoice Mage_Sales_Model_Order_Invoice */

        if ($invoice) {
            try{
                $invoice->setTransactionId($this->_params['longId']);
                $invoice->save(); // Saving invoice to get the Increment ID
                $message = Mage::helper('optile')->__('Notified customer about invoice #%s.', $invoice->getIncrementId());
                $message .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";

                Mage::helper('optile')->log(__METHOD__ .' Sending invoice email.');
                $invoice->sendEmail(true);
                $order->addStatusHistoryComment($message)
                    ->setIsCustomerNotified(true);

            } catch (Exception $e){
                Mage::helper('optile')->log(__METHOD__ .' Sending invoice email failed:', Zend_Log::ERR);
                Mage::helper('optile')->log($e->getMessage(), Zend_Log::ERR);
            }
        }

        $order->save(); // Fix for Magento 1.8.1 because $order->sendNewOrderEmail now reloads the order
        try {
            $order->sendNewOrderEmail();
        } catch (Exception $e) {
            Mage::helper('optile')->log($e->getMessage(), Zend_log::ERR);
            Mage::logException($e);
        }
        $order->save();

		Mage::helper('optile')->log(__METHOD__ .' Updating Optile quote data..');

        $optileQuote
            ->setLongId($this->_params['longId'])
            ->setPaymentNetwork($this->_params['network'])
            ->setMonitorLink($this->generateMonitorLink($order))
            ->save();
        ;

		Mage::helper('optile')->log(__METHOD__ .' DONE');
        $this->exitWithMsg("OK");
	}

	/**
	 * Process optile status notification with statusCode 'failed', 'declined', 'aborted', 'canceled', 'expired'
	 * @param Mage_Sales_Model_Order $order
	 */
	private function handleFailed($order) {
		$params = $this->_params;
        $reason_code = isset($this->_params['reasonCode']) ? $this->_params['reasonCode'] : "";
        $interaction_code = isset($this->_params['interactionCode']) ? $this->_params['interactionCode'] : "";

        Mage::helper('optile')->log(__METHOD__ .' Handling payment failure...', Zend_Log::INFO);
        Mage::helper('optile')->log(__METHOD__ .' Loading Quote...');
        // Match Long Id on the quote.
        $optileQuote = Mage::getModel('optile/quote')
            ->load($order->getQuoteId());

        if (!$optileQuote->hasLongId()) {
            // LongId not set on quote yet. It's not possible to determine the
            // relevance of this IPN at this moment. Try again later.
            $msg = 'Long ID could not be verified yet; try again later.';
			$this->exitWithMsg($msg, 500);
        }

        if ($optileQuote->getLongId() != $this->_params['longId']) {
            // LongId is of a different LIST request. In this context that
            // should be rather strange.
            $msg = "Could not match LongId: {$this->_params['longId']} to Magento quote: {$optileQuote->getTransactionId()}. Ignoring this notification.";
			Mage::helper('optile')->log(__METHOD__ . $msg, Zend_Log::WARN);
            $this->exitWithMsg('OK');
        }

        Mage::helper('optile')->log('Current order status: '.$order->getStatus(), Zend_Log::INFO);

        // If order is already canceled this is OK.
        if ($order->isCanceled()) {
            Mage::helper('optile')->log(__METHOD__ .' Order already canceled.', Zend_Log::INFO);
            $this->exitWithMsg('OK');
        }

        if($order->isPaymentReview()){
            Mage::helper('optile')->log(__METHOD__ .' Registering payment review denial...', Zend_Log::INFO);
            $order->getPayment()->deny();
        }

		// Security overkill.
		if ($optileQuote->getDeferredMode() == Optile_Payment_Model_Quote::NON_DEFERRED
                && !$order->isCanceled()
                && !$order->canCancel()) {

            // MVR_NAT_SC2 5.3 Unexpected notification, accept and change status.
			$msg = 'Order cannot be canceled! Current status: '. $order->getStatus();
            $comment = 'Received notification "'. $params['statusCode'] .'" but order cannot be canceled!';
            $comment .= ' Details: '. $params['resultInfo'];
            $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";

            Mage::helper('optile')->log($msg, Zend_log::ERR);
            $this->exitUnexpectedNotification($order, $msg, $comment);
		}

		// Update order status.
        if(isset($this->reason_codes[$params['statusCode']]) && isset($this->reason_codes[$params['statusCode']][$params['reasonCode']])){
            $comment = $this->reason_codes[$params['statusCode']][$params['reasonCode']];
        }else{
            $comment = 'Received IPN notification: '.$params['statusCode'].' - '.$params['reasonCode'];
            Mage::helper('optile')->log('Unknown IPN received: '.$comment, Zend_log::WARN);
        }

//		if (isset($params['resultInfo']) && strlen($params['resultInfo']) > 0 ) {
//			$comment .= ' ('. $params['resultInfo'] .')';
//		}
        $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";

		Mage::helper('optile')->log(__METHOD__ .' Cancelling order and adding comment: '.$comment, Zend_Log::INFO);

		$order
            ->cancel()
            ->addStatusToHistory(FALSE, $comment, (int)$order->getEmailSent())
            ->setOptileStatusCode($this->_params['statusCode'])
            ->save()
        ;

        Mage::helper('optile')->log(__METHOD__ .' Dispatching optile_notification_failed event.', Zend_Log::INFO);
        Mage::dispatchEvent('optile_notification_failed', array('order' => $order));


		Mage::helper('optile')->log(__METHOD__ .' DONE');
        $this->exitWithMsg('OK');
	}

	/**
	 * Process optile status notification with statusCode 'charged_back'
	 * @param Mage_Sales_Model_Order $order
	 */
	private function handleChargedBack($order) {
        // Create creditmemo and set order status to Closed.
		$payment = $order->getPayment();
		$params = $this->_params;
        $reason_code = isset($this->_params['reasonCode']) ? $this->_params['reasonCode'] : "";
        $interaction_code = isset($this->_params['interactionCode']) ? $this->_params['interactionCode'] : "";

        Mage::helper('optile')->log(__METHOD__ .' Registering incoming chargeback notification...', Zend_Log::INFO);

        // This notification is "unexpected" if order not invoiced.
        $comment = 'Registered notification about refunded amount of '. $params['amount'];
        $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";

        if ($order->canCreditmemo()) {
            $order->addStatusHistoryComment($comment);
        } else {
            $order->addStatusToHistory(self::STATUS_UNEXPECTED, $comment);
        }
        $order->save();

        // On top of this in-context notification, notify the customer more intrusively.
        $inbox = Mage::getModel('adminnotification/inbox');
        /* @var $inbox Mage_AdminNotification_Model_Inbox */
        $inbox->add(
            Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR,
            'A Chargeback notification has been received from Optile. Please go to Order '. $order->getIncrementId() .' and review it.',
            'To review the order that has been charged back, go to Sales -> Orders, find the order with ID '. $order->getIncrementId() .' and open it.'
        );

        Mage::helper('optile')->log(__METHOD__ .' DONE');
        $this->exitWithMsg('OK');

        // The following causes serious problems if multiple partial charge backs
        // are received, so we're just setting status history comment for now.

        try {
            $payment->registerRefundNotification(abs($params['amount']));
            $order->save();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $this->exitWithMsg($msg);
        }

        Mage::helper('optile')->log(__METHOD__ .' DONE');
        $this->exitWithMsg('OK');
	}

	/**
	 * Process optile status notification with statusCode 'pending'
     *  - pending payments (eg 3D Secure, Paypal).
	 *
	 * @param Mage_Sales_Model_Order $order
	 */
	private function handlePending($order) {
		$payment = $order->getPayment();
		$params = $this->_params;
        $reason_code = isset($this->_params['reasonCode']) ? $this->_params['reasonCode'] : "";
        $interaction_code = isset($this->_params['interactionCode']) ? $this->_params['interactionCode'] : "";


        Mage::helper('optile')->log(__METHOD__ .' Registering pending notification...', Zend_Log::INFO);
        Mage::helper('optile')->log(__METHOD__.': Loading OptileQuote #'.$order->getQuoteId(), Zend_Log::INFO);
        // Update our own table with some useful data.
        $optileQuote = Mage::getModel('optile/quote')
            ->load($order->getQuoteId());

        if ($order->isPaymentReview()) {
            if ($optileQuote->hasLongId()) {
                $comment = 'Payment is pending, status will be updated later';
            } else {
                $comment = 'Payment is pending. Updating longId to '. $this->_params['longId'];
            }
            $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";
            Mage::helper('optile')->log(__METHOD__.': '.$comment, Zend_log::INFO);
            $order->addStatusHistoryComment($comment)->save();
        } else {
            // MVR_NAT_SC2 5.3 Unexpected notification, accept and change status.
            $msg = 'Order is no longer pending!';
            $comment = 'Received notification "'. $params['statusCode'] .'" but the order is already paid or canceled.';
            $comment .= ' Details: '. $params['resultInfo'];
            $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";

            Mage::helper('optile')->log(__METHOD__.': '.$comment, Zend_log::NOTICE);
//            $this->exitUnexpectedNotification($order, $msg, $comment);
        }

        $optileQuote
            ->setLongId($this->_params['longId'])
            ->setPaymentNetwork($this->_params['network'])
            ->setMonitorLink($this->generateMonitorLink($order))
            ->save();
        ;

		Mage::helper('optile')->log(__METHOD__ .' DONE');
        $this->exitWithMsg('OK');
	}

	/**
	 * Process optile status notification with statusCode 'information_requested'
     *  - customers opening disputes through their payment network.
	 *
	 * @param Mage_Sales_Model_Order $order
	 */
    private function handleInfoRequested($order) {
        $reason_code = isset($this->_params['reasonCode']) ? $this->_params['reasonCode'] : "";
        $interaction_code = isset($this->_params['interactionCode']) ? $this->_params['interactionCode'] : "";
        $comment = 'Charge disputed by customer; provide more information.';
        $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";

        if ($order->canCreditmemo()) {
            $order->addStatusHistoryComment($comment);
        } else {
            $order->addStatusToHistory(self::STATUS_UNEXPECTED, $comment);
        }
        $order->save();

        // On top of this in-context notification, notify the customer more intrusively.
        $inbox = Mage::getModel('adminnotification/inbox');
        /* @var $inbox Mage_AdminNotification_Model_Inbox */
        $inbox->add(
            Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR,
            'A dispute notification has been received from Optile. Please go to Order '. $order->getIncrementId() .' and review it.',
            'Information has been requested about a charge. To review the order in question, go to Sales -> Orders, find the order with ID '. $order->getIncrementId() .' and open it.'
        );

        $this->exitWithMsg('OK');
    }

	/**
	 * Process optile status notification with statusCode 'dispute_closed'
     *  - payment network / provider closing disputes.
	 *
	 * @param Mage_Sales_Model_Order $order
	 */
    private function handleDisputeClosed($order) {
        $params = $this->_params;
        $reason_code = isset($this->_params['reasonCode']) ? $this->_params['reasonCode'] : "";
        $interaction_code = isset($this->_params['interactionCode']) ? $this->_params['interactionCode'] : "";
        $comment = 'Dispute closed.';

        if ($params['reasonCode'] == 'chargeback_canceled') {
            $comment .= ' Chargeback canceled.';
        }

        $comment .= " (optile reason code: {$reason_code}, interaction code: {$interaction_code})";
        $order->addStatusHistoryComment($comment)->save();

        // On top of this in-context notification, notify the customer more intrusively.
        $inbox = Mage::getModel('adminnotification/inbox');
        /* @var $inbox Mage_AdminNotification_Model_Inbox */
        $inbox->add(
            Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE,
            'The charge dispute on Order '. $order->getIncrementId() .' has been closed.',
            'No further action is required.'
        );

        $this->exitWithMsg('OK');
    }

    /**
     * Handles unexpected notification by adding a critical message to the
     * notification inbox, adding a comment to the order history, logging a
     * message to the log file, and giving the log message as a response to
     * Optile. Other than that this will accept the message normally.
     * Calling this method ends code execution.
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $responseMsg
     * @param string $adminMsg
     */
    private function exitUnexpectedNotification($order, $responseMsg, $adminMsg) {

        // Change status to "Unexpected Optile notification" and add message.
        $order->addStatusToHistory(self::STATUS_UNEXPECTED, $adminMsg)->save();

        // Additionally raise a system-wide alert.
        // Encourage merchant to report bugs.
        $email = 'support@optile.zendesk.com';
        $subject = urlencode('Optile Magento extension: unexpected notification');
        $body = urlencode('Dear supportdesk,

The following unexpected notification was encountered in my Magento store:

statusCode: '. $this->_params['statusCode']. '
interactionCode: '. $this->_params['interactionCode'].'
longId: '. $this->_params['longId']. '

Order state and status: '. $order->getState() .' / '. $order->getStatus() .'
My merchant code is: '. Mage::getStoreConfig('payment/optile/merchant_code') .'
');
        $adminMsg .= "<br/>Please report this to Optile: <a href='mailto:$email?subject=$subject&body=$body'>$email</a>";

        $inbox = Mage::getModel('adminnotification/inbox');
        /* @var $inbox Mage_AdminNotification_Model_Inbox */
        $inbox->add(
            Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL,
            'Optile Payment Extension: unexpected notification received for order # '. $order->getRealOrderId(),
            $adminMsg
        );

        // Respond with an error and exit.
        $this->exitWithMsg($responseMsg);
    }

    /**
     * Send response to Optile and exit. Also logs response in the log file.
     *
     * @param string $responseMsg Message to be logged and sent to Optile
     * @param int $responseCode HTTP response code
     */
    private function exitWithMsg($responseMsg, $responseCode=200) {
        // Log our response to Optile in the log file.
        Mage::helper('optile')->log(__CLASS__.": ".$responseCode.", Message: ".$responseMsg, Zend_Log::INFO);
        $this->setStatus($responseCode);
        $this->save(); // Saving the request data before exiting.

        throw new Exception($responseMsg, $responseCode);
    }

    /**
     * Sets the data to serialized field. Also fills in searchable data to
     * their respectable fields: longId, network, currency, amount
     * @param array $params
     * @return Optile_Payment_Model_Notification
     */
    public function setReceivedData($params){

        if(isset($params['longId']))    { $this->setData('long_id',  $params['longId']); }
//        if(isset($params['network']))   { $this->setData('network',  $params['network']); }
//        if(isset($params['currency']))  { $this->setData('currency', $params['currency']); }
//        if(isset($params['amount']))    { $this->setData('amount',   $params['amount']); }

        // T: Saving only interesting dataset
        if(isset($params['returnCode']))        { $this->setData('return_code',         $params['returnCode']); }
        if(isset($params['interactionCode']))   { $this->setData('interaction_code',    $params['interactionCode']); }
        if(isset($params['reasonCode']))        { $this->setData('reason_code',         $params['reasonCode']); }
        if(isset($params['resultInfo']))        { $this->setData('result_info',         $params['resultInfo']); }

        if(isset($params['transactionId'])){ $this->setData('transaction_id',   $params['transactionId']); }

        return $this->setData('received_data', json_encode($params));
    }

    public function getReceivedData(){
        return json_decode($this->getData('received_data'));
    }

}
