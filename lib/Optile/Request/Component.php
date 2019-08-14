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

namespace Optile\Request;

require_once 'RequestFactory.php';

/**
 * abstract class Component:
 *
 * Contains validation methods and magic getter/setter method
 *
 **/
abstract class Component {

    private $_data = array();

    /**
     * Validation error messages: array(fieldName => errorMessage)
     * @var array
     */
    protected $validationResult;

    /**
     * Validates the current object; generates error messages if appropriate.
     */
    protected function validation() {
    }

    private function validate() {
        $this->validationResult = array();

        $this->validation();

        if (!empty($this->validationResult)) {
            $e = RequestFactory::getException('request', $this->validationResult);
            throw $e;
        }
    }

    /**
     * Checks if a field is required and generates an error message.
     * @param string $field
     * @param string $title
     */
    protected function validateRequired($field, $title) {
        $value = $this->getData($field);
        if (empty($value)) {
            $this->validationResult[$title] = "$title is required";
        }
    }

    /**
     * Get request data from this object recursively.
     * RequestExceptions from invoked validate() passes through.
     * @return array
     */
    public function getValidatedData() {
        $this->validate();

        $data = array();

        foreach ($this->_data as $field => $component) {
            if ($component instanceof Component) {
                $data[$field] = $component->getValidatedData();
            }
            elseif (is_array($component)) {
                // @TODO: Some code duplication here, suggests further abstraction
                foreach ($component as $key => $value) {
                    if ($value instanceof Component) {
                        $data[$field][$key] = $value->getValidatedData();
                    } else {
                        $data[$field][$key] = $value;
                    }
                }
            }
            else {
                $data[$field] = $component;
            }
        }

        return $data;
    }

    public function __call($method, $args) {
        switch (substr($method, 0, 3)) {
            case 'set':
                $key = lcfirst(substr($method, 3));
                $value = (string) $args[0];
                $result = $this->setData($key, $value);
                return $result;
            case 'get':
                $key = lcfirst(substr($method, 3));
                $result = $this->getData($key);
                return $result;
        }
        throw new Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
    }

    /**
     * Set simple request data; only string values allowed from public interface.
     * @param string $key
     * @param mixed $value
     * @return Component
     */
    protected function setData($key, $value) {
        $this->_data[$key] = $value;
        return $this;
    }

    /**
     * Get single request value.
     * @param string $key
     * @return string|null
     */
    protected function getData($key) {
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
    }

    /**
     * Add a value to an array. Creates the array if this is the first value.
     * @param string $key
     * @param mixed $value
     * @param string $value
     * @return Component
     */
    protected function addData($key, $value, $index=null) {
        if (!isset($this->_data[$key])) {
            $this->_data[$key] = array();
        }

        if (strlen($index)) {
            $this->_data[$key][$index] = $value;
        } else {
            $this->_data[$key][] = $value;
        }

        return $this;
    }

}
