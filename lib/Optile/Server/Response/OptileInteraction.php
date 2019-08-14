<?php
/**
 * This file is part of the Optile Payment Connector extension.
 *
 * Optile Payment Connector is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Optile Payment Connector is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Optile Payment Connector.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      i-Ways <dev@i-ways.hr>
 * @copyright   Copyright (c) 2013 Optile. (http://www.optile.de)
 * @license     http://www.gnu.org/licenses/gpl.txt
 */

class OptileInteractionCode {
	const PROCEED = "PROCEED";
	const ABORT   = "ABORT";
	const TRY_OTHER_NETWORK = "TRY_OTHER_NETWORK";
	const TRY_OTHER_ACCOUNT = "TRY_OTHER_ACCOUNT";
	const RETRY             = "RETRY";
}

class OptileInteractionReason{
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

class OptileInteraction extends OptileResponseEntity{
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