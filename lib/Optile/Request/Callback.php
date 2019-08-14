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
 * Callback
 *
 * Defines data structure for callback request object.
 * See class Request (and/or all of it's subclass) for implementation specifics.
 *
 * @method Callback setReturnUrl(string $value)
 * @method Callback setCancelUrl(string $value)
 * @method Callback setNotificationUrl(string $value)
 */
class Callback extends Component {

	protected function validation() {
		$this->validateRequired('returnUrl', "Return URL");
		$this->validateRequired('cancelUrl', "Cancel URL");
		$this->validateRequired('notificationUrl', "Notification URL");
	}

}
