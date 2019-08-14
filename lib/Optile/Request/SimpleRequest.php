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
/**
 * SimpleRequest
 *
 * Generic and most simple request class.
 * In practice used for internal and development purposes (simulating Optile
 * IPN, etc...)
 */
class SimpleRequest extends Request {

    /**
     * Do a simple GET request on a URL.
     * @return string Response body
     */
	public function send($method = self::METHOD_GET) {
        return parent::send($method);
    }

}
