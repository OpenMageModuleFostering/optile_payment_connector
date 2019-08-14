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
 * Payment
 *
 * Used for describing Payment entity in the List request.
 * See ListRequest class for more info.
 *
 * @method Payment setAmount(float $value)
 * @method Payment setCurrency(string $value)
 * @method Payment setReference(string $value)
 */
class Payment extends Component {

	protected function validation() {
		$this->validateRequired('amount', 'Payment amount');
		$this->validateRequired('currency', 'Payment currency');
		$this->validateRequired('reference', 'Payment reference');
	}

}
