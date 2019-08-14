<?php

//error_reporting(E_ALL | E_STRICT);
//Mage::setIsDeveloperMode(true);
//ini_set('display_errors', 1);

/**
 * @author frans
 */
class Optile_Payment_Block_Adminhtml_System_Config_Frontend_Instructions_TextArea
extends Mage_Core_Block_Abstract
{
    protected function _toHtml() {
        $inputName = $this->getInputName();
        $columnName = $this->getColumnName();
        $column = $this->getColumn();

        return '<textarea name="'. $inputName .'"'.
            ($column['size'] ? 'size="'. $column['size'] .'"' : '') .' class="'.
            (isset($column['class']) ? $column['class'] : 'input-textarea') .'"'.
            (isset($column['style']) ? ' style="'. $column['style'] .'"' : '') .'>'.
            '#{'. $columnName .'}</textarea>';
    }
}
