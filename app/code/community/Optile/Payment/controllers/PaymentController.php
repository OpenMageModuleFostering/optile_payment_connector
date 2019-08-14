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

class Optile_Payment_PaymentController extends Mage_Core_Controller_Front_Action
{
  public function cancelAction()
  {
      try {
          // TODO verify if this logic of order cancelation is deprecated
          // if there is an order - cancel it
          $orderId = $this->_getCheckoutSession()->getLastOrderId();
          $order = ($orderId) ? Mage::getModel('sales/order')->load($orderId) : false;
          if ($order && $order->getId() && $order->getQuoteId() == $this->_getCheckoutSession()->getQuoteId()) {
              $order->cancel()->save();
              $this->_getCheckoutSession()
                  ->unsLastQuoteId()
                  ->unsLastSuccessQuoteId()
                  ->unsLastOrderId()
                  ->unsLastRealOrderId()
                  ->addSuccess($this->__('Checkout and order has been canceled.'))
              ;
          } else {
              $this->_getCheckoutSession()->addSuccess($this->__('Checkout has been canceled.'));
          }
      } catch (Mage_Core_Exception $e) {
          $this->_getCheckoutSession()->addError($e->getMessage());
      } catch (Exception $e) {
          $this->_getCheckoutSession()->addError($this->__('Unable to cancel checkout.'));
          Mage::logException($e);
      }

      $this->_redirect('checkout/cart');
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