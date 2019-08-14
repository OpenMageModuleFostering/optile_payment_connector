<?php
class Optile_Payment_Model_System_Config_Source_Loglevel extends Mage_Eav_Model_Entity_Attribute_Source_Abstract{

    public function getAllOptions()
    {
        if (is_null($this->_options)) {

            $methods = array(
                Zend_log::EMERG => array(
                        'label' => Zend_log::EMERG.' - Emergency',
                        'value' => Zend_log::EMERG
                    ),
                Zend_log::ALERT => array(
                        'label' => Zend_log::ALERT.' - Alert',
                        'value' => Zend_log::ALERT
                    ),
                Zend_log::CRIT => array(
                        'label' => Zend_log::CRIT.' - Critical',
                        'value' => Zend_log::CRIT
                    ),
                Zend_log::ERR => array(
                        'label' => Zend_log::ERR.' - Error',
                        'value' => Zend_log::ERR
                    ),
                Zend_log::WARN => array(
                        'label' => Zend_log::WARN.' - Warning',
                        'value' => Zend_log::WARN
                    ),
                Zend_log::NOTICE => array(
                        'label' => Zend_log::NOTICE.' - Notice',
                        'value' => Zend_log::NOTICE
                    ),
                Zend_log::INFO => array(
                        'label' => Zend_log::INFO.' - Info',
                        'value' => Zend_log::INFO
                    ),
                Zend_log::DEBUG => array(
                        'label' => Zend_log::DEBUG.' - Debug',
                        'value' => Zend_log::DEBUG
                    ),
            );

            $this->_options = $methods;
        }
        return $this->_options;
    }

    public function toOptionArray(){
        return $this->getAllOptions();
    }
}