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

class Optile_Payment_Block_Adminhtml_Widget_Grid_Column_Renderer_Url
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column - a URL, a bit like Action. This one allows external
     * locations though.
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $actionAttributes = new Varien_Object();
        $attr = (array)$this->getColumn()->getAttributes();

        $value = $this->_getValue($row);

        if ($this->getColumn()->hasUrlFormat()) {
            $attr['href'] = sprintf($this->getColumn()->getUrlFormat(), $value);
        }
        elseif ($this->getColumn()->hasUrlIndex()) {
            $attr['href'] = $row->getData($this->getColumn()->getUrlIndex());
        }

        if (!isset($attr['href']) || !$attr['href']) {
            return $value;
        }

        if ($this->getColumn()->getPopup()) {
            $attr['onclick'] =
                'popWin(this.href,\'_blank\',\'width=800,height=700,resizable=1,scrollbars=1\');return false;';
        }


        $actionAttributes->setData($attr);
        $html = '<a ' . $actionAttributes->serialize() . '>' . $value . '</a>';

        return $html;
    }

}