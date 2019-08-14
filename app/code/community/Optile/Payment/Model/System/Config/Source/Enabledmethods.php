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

class Optile_Payment_Model_System_Config_Source_Enabledmethods
{
    protected $_result;
    public function toOptionArray($isMultiSelect=false)
    {
        if (isset($this->_result)) {
            return $this->_result;
        }

        $this->_result = array();
        foreach (Mage::getStoreConfig('payment') as $methodCode => $methodData) {
            if (!isset($methodData['active']) || !$methodData['active']) {
                continue;
            }
            // Don't offer to disable the following payment methods:
            if (in_array($methodCode, array('free', 'cashondelivery', 'optile'))) {
                continue;
            }

            $this->_result[$methodCode] = array(
                'label' => isset($methodData['title']) ? $methodData['title'] : $methodCode,
                'value' => $methodCode,
            );
        }
        return $this->_result;
    }
}