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

class Optile_Payment_Model_Order_Payment extends Mage_Sales_Model_Order_Payment
{
    // public function refund($creditmemo)
    // {
    //     $gateway = $this->getMethodInstance();
    //     if($gateway->getCode() !== 'optile') return parent::refund($creditmemo);
    //
    //     $baseAmountToRefund = $this->_formatAmount($creditmemo->getBaseGrandTotal());
    //     $order = $this->getOrder();
    //
    //     $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);
    //
    //     // call refund from gateway if required
    //     $isOnline = false;
    //     $invoice = null;
    //     if ($gateway->canRefund() && $creditmemo->getDoTransaction()) {
    //         $this->setCreditmemo($creditmemo);
    //         // $invoice = $creditmemo->getInvoice();
    //         // if ($invoice) {
    //             $isOnline = true;
    //             // $captureTxn = $this->_lookupTransaction($invoice->getTransactionId());
    //             // if ($captureTxn) {
    //                 // $this->setParentTransactionId($captureTxn->getTxnId());
    //             // }
    //             // $this->setShouldCloseParentTransaction(true); // TODO: implement multiple refunds per capture
    //             try {
    //                 $gateway->setStore($this->getOrder()->getStoreId())
    //                     ->processBeforeRefund($invoice, $this)
    //                     ->refund($this, $baseAmountToRefund)
    //                     ->processCreditmemo($creditmemo, $this)
    //                 ;
    //             } catch (Mage_Core_Exception $e) {
    //                 if (!$captureTxn) {
    //                     $e->setMessage(' ' . Mage::helper('sales')->__('If the invoice was created offline, try creating an offline creditmemo.'), true);
    //                 }
    //                 throw $e;
    //             }
    //         // }
    //     }
    //
    //     // update self totals from creditmemo
    //     $this->_updateTotals(array(
    //         'amount_refunded' => $creditmemo->getGrandTotal(),
    //         'base_amount_refunded' => $baseAmountToRefund,
    //         'base_amount_refunded_online' => $isOnline ? $baseAmountToRefund : null,
    //         'shipping_refunded' => $creditmemo->getShippingAmount(),
    //         'base_shipping_refunded' => $creditmemo->getBaseShippingAmount(),
    //     ));
    //
    //     // update transactions and order state
    //     $transaction = $this->_addTransaction(
    //         Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND,
    //         $creditmemo,
    //         $isOnline
    //     );
    //     if ($invoice) {
    //         $message = Mage::helper('sales')->__('Refunded amount of %s online.', $this->_formatPrice($baseAmountToRefund));
    //     } else {
    //         $message = $this->hasMessage() ? $this->getMessage()
    //             : Mage::helper('sales')->__('Refunded amount of %s offline.', $this->_formatPrice($baseAmountToRefund));
    //     }
    //     $message = $message = $this->_prependMessage($message);
    //     $message = $this->_appendTransactionToMessage($transaction, $message);
    //     $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);
    //
    //     Mage::dispatchEvent('sales_order_payment_refund', array('payment' => $this, 'creditmemo' => $creditmemo));
    //     return $this;
    // }
}
