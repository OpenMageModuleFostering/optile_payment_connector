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

class Optile_Payment_Block_Adminhtml_Order_Creditmemo_Create_Items extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items
{

  /**
   * Prepare child blocks
   *
   * @return Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items
   */
  protected function _prepareLayout()
  {
      if($this->getOrder()->getPayment()->getMethodInstance()->getCode() !== 'optile') return parent::_prepareLayout();

      $layout = parent::_prepareLayout();

      $onclick = "submitAndReloadArea($('creditmemo_item_container'),'".$this->getUpdateUrl()."')";
      $this->setChild(
          'update_button',
          $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
              'label'     => Mage::helper('sales')->__('Update Qty\'s'),
              'class'     => 'update-button',
              'onclick'   => $onclick,
          ))
      );

      if ($this->getCreditmemo()->canRefund()) {
          if ($this->getCreditmemo()->getInvoice() && $this->getCreditmemo()->getInvoice()->getTransactionId()) {
            if(Mage::getStoreConfig('payment/optile/refund_enabled')) {
              $this->setChild(
                  'submit_button',
                  $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                      'label'     => Mage::helper('sales')->__('Refund Online via Optile'),
                      'class'     => 'save submit-button',
                      'onclick'   => 'disableElements(\'submit-button\');submitCreditMemo()',
                  ))
              );
            } else {
                $this->unsetChild('submit_button');
            }
          }
          $this->setChild(
              'submit_offline',
              $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                  'label'     => Mage::helper('sales')->__('Refund Offline'),
                  'class'     => 'save submit-button back',
                  'onclick'   => 'disableElements(\'submit-button\');submitCreditMemoOffline()',
              ))
          );

      }
      else {
          $this->setChild(
              'submit_button',
              $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                  'label'     => Mage::helper('sales')->__('Refund Offline'),
                  'class'     => 'save submit-button back',
                  'onclick'   => 'disableElements(\'submit-button\');submitCreditMemoOffline()',
              ))
          );
      }

      return $layout;
  }

}
