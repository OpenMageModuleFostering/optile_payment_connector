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

namespace Optile\Response;

require_once 'Entity.php';
/**
 * InteractionCode
 *
 * Wrapper for managing interaction codes internally.
 */
class InteractionCode {
	const PROCEED = "PROCEED";
	const ABORT   = "ABORT";
	const TRY_OTHER_NETWORK = "TRY_OTHER_NETWORK";
	const TRY_OTHER_ACCOUNT = "TRY_OTHER_ACCOUNT";
	const RETRY             = "RETRY";
}
/**
 * InteractionReason
 *
 * Wrapper for managing interaction reason codes internally.
 */
class InteractionReason{
	const OK                    = "OK";
	const PENDING               = "PENDING";
	const FRAUD                 = "FRAUD";
	const INVALID_ACCOUNT       = "INVALID_ACCOUNT";
	const SYSTEM_FAILURE        = "SYSTEM_FAILURE";
	const BLOCKED               = "BLOCKED";
	const NETWORK_FAILURE       = "NETWORK_FAILURE";
	const ADDITIONAL_NETWORKS   = "ADDITIONAL_NETWORKS";
	const BLACKLISTED           = "BLACKLISTED";
	const EXPIRED               = "EXPIRED";
	const TRUSTED               = "TRUSTED";
	const STRONG_AUTHENTICATION = "STRONG_AUTHENTICATION";
	const DECLINED              = "DECLINED";
	const EXCEEDS_LIMIT         = "EXCEEDS_LIMIT";
	const UNKNOWN               = "UNKNOWN";
	const TEMPORARY_FAILURE     = "TEMPORARY_FAILURE";
}

/**
 * Interaction
 *
 * Simple class that defines Interaction response from Optile
 */
class Interaction extends Entity{
	private $code;
	private $reason;

	public function getCode(){
		return $this->code;
	}

	public function setCode($code){
		$this->code = $code;
	}

	public function getReason(){
		return $this->reason;
	}

	public function setReason($reason){
		$this->reason = $reason;
	}

	public function __construct($code, $reason){
		$this->code = $code;
		$this->reason = $reason;
	}
}