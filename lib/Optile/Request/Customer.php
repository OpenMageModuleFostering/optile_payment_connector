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

require_once 'Component.php';

/**
 * Customer
 *
 * Used for define a customer when doing LIST request. Allows for adding
 * additional customer data as child entities.
 * See classes CustomerName, CustomerAddress, CustomerPhone below
 *
 * @method Customer setEmail(string $value)
 * @method Customer setNumber(string $value)
 * @method Customer setBirthday(string $value) Format: ISO 8601. Use date('c', $timestamp) in PHP.
 */
class Customer extends Component {

    protected function validation() {
        $this->validateRequired('number', 'Customer number');
        $this->validateRequired('email', 'Customer e-mail');
    }

    /**
     * @return CustomerName
     */
    public function addName() {
        $component = new CustomerName();
        $this->setData('name', $component);
        return $component;
    }

    /**
     * @param type $type
     * @return CustomerAddress
     */
    public function addAddress($type) {
        $component = new CustomerAddress();
        $this->addData('addresses', $component, $type);
        return $component;
    }

    /**
     * @param type $type
     * @return CustomerPhone
     */
    public function addPhone($type) {
        $component = new CustomerPhone();
        $this->addData('phones', $component, $type);
        return $component;
    }

}

/**
 * @method CustomerName setTitle(string $value)
 * @method CustomerName setFirstName(string $value)
 * @method CustomerName setMiddleName(string $value)
 * @method CustomerName setLastName(string $value)
 * @method CustomerName setMaidenName(string $value)
 */
class CustomerName extends Component {

}

/**
 * @method CustomerAddress setStreet(string $value)
 * @method CustomerAddress setHouseNumber(string $value)
 * @method CustomerAddress setZip(string $value)
 * @method CustomerAddress setCity(string $value)
 * @method CustomerAddress setState(string $value)
 * @method CustomerAddress setCountry(string $value)
 */
class CustomerAddress extends Component {

    const TYPE_BILLING = 'billing';
    const TYPE_SHIPPING = 'shipping';

    public function addName() {
        $component = new CustomerName();
        $this->setData('name', $component);
        return $component;
    }

}

/**
 * @method CustomerPhone setCountryCode(int $value)
 * @method CustomerPhone setAreaCode(int $value)
 * @method CustomerPhone setSubscriberNumber(int $value)
 * @method CustomerPhone setUnstructuredNumber(string $value)
 */
class CustomerPhone extends Component {

    const TYPE_WORK = 'work';
    const TYPE_COMPANY = 'company';
    const TYPE_HOME = 'home';
    const TYPE_OTHER = 'other';
    const TYPE_MOBILE = 'mobile';
    const TYPE_MOBILE_SECONDARY = 'mobileSecondary';
    const TYPE_FAX = 'fax';

}
