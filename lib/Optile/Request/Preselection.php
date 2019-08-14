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
 * Preselection
 *
 * Used for describing Preselection entity in the List request. Preselect
 * entity determines the preferred deferral mode for the upcoming transaction.
 * For possible deferral values, use constants. For more information,
 * refer to Optile documentation:
 * https://docs.optile.de/archive/PIN/Deferred%20Payments.html
 *
 * @method Product setDeferral(string $value)
 */
class Preselection extends Component {

    const TYPE_DEFERRED     = 'DEFERRED';
    const TYPE_NON_DEFERRED = 'NON_DEFERRED';
    const TYPE_ANY          = 'ANY';

	protected function validation() {
		$this->validateRequired('deferral', 'Deferral mode');

	}
}