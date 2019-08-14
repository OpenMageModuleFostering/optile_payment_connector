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
 * Exception
 *
 * Extending Exception to handle array type of message for development purposes.
 * Not used in live environment.
 *
 */
class Exception extends \Exception {
	public function __construct($fields, $code=null, $previous=null) {
		$message = '';

        if(is_array($fields)){
            foreach ($fields as $value) {
                $message .= $value."\n\r";
            }
        }else{
            $message = $fields;
        }

		parent::__construct($message, $code, $previous);
	}
}

class UrlException extends \Exception {

}
