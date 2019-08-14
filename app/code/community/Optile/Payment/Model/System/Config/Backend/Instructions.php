<?php

//error_reporting(E_ALL | E_STRICT);
//Mage::setIsDeveloperMode(true);
//ini_set('display_errors', 1);

/**
 * @author frans
 */
class Optile_Payment_Model_System_Config_Backend_Instructions
extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    protected $_eventPrefix = 'optile_config_backend_instructions';
}
