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
 * Product
 *
 * Used to describe the Product entity in the List request. Implements a workaround
 * to avoid Amount rounding issue. This workaround is in place for PayPal.
 * For more details, refer to validation() method.
 *
 * @method Product setCode(string $value)
 * @method Product setName(string $value)
 * @method Product setQuantity(int $value)
 * @method Product setCurrency(string $value)
 * @method Product setAmount(float $value)
 */
class Product extends Component {

	protected function validation() {
		$this->validateRequired('code', 'Product code');
		$this->validateRequired('name', 'Product name');

        // Tackle PayPal issue with rounded amounts per product.
        $qty = $this->getQuantity();
        $amount = $this->getAmount();

        if ($qty > 1) {
            $single = (float) number_format($amount / $qty, 2, '.', '');
            $total = $single * $qty;
            if ($total != $amount) {
                // Rounding issue, rowtotal isn't evenly divisible by qty.
                // Change all product qty to 1 and add qty to descriptions.
                $this->setQuantity(1);
                $this->setName($qty .'x '. $this->getName());
            }
        }

	}
}