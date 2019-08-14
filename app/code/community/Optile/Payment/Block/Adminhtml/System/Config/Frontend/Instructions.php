<?php

//error_reporting(E_ALL | E_STRICT);
//Mage::setIsDeveloperMode(true);
//ini_set('display_errors', 1);

/**
 * @author frans
 */
class Optile_Payment_Block_Adminhtml_System_Config_Frontend_Instructions
extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_textareaRenderer;

    protected function _prepareToRender() {
        $this->addColumn('payment_method', array(
            'label' => Mage::helper('adminhtml')->__('Payment Method'),
            'style' => 'width:120px',
        ));
        $this->addColumn('value', array(
            'label' => Mage::helper('adminhtml')->__('Instructions'),
            'renderer' => $this->_getTextareaRenderer(),
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add instructions');
    }

    protected function _getTextareaRenderer() {
        if ($this->_textareaRenderer === null) {
            $this->_textareaRenderer = $this->getLayout()->createBlock(
                'optile/adminhtml_system_config_frontend_instructions_textarea'
            );
//            $this->_textareaRenderer->setClass('input-textarea');
//            $this->_textareaRenderer->setExtraParams('style="width:120px"');
        }

        return $this->_textareaRenderer;
    }
}
