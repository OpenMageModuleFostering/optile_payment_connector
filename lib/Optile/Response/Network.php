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
 * Network
 *
 * Simple class that defines Network response from Optile
 */
class Network extends Entity{
	private $code;
	private $method;
	private $registration;
	private $recurrence;
	private $links;
	private $label;

	public function getCode(){
		return $this->code;
	}

	public function setCode($code){
		$this->code = $code;
	}

	public function getMethod(){
		return $this->method;
	}

	public function setMethod($method){
		$this->method = $method;
	}

	public function getRegistration(){
		return $this->registration;
	}

	public function setRegistration($registration){
		$this->registration = $registration;
	}

	public function getRecurrence(){
		return $this->recurrence;
	}

	public function setRecurrence($recurrence){
		$this->recurrence = $recurrence;
	}

	public function getLinks(){
		return $this->links;
	}

	public function setLinks($links){
		$this->links = $links;
	}

	public function getLabel(){
		return $this->label;
	}

	public function setLabel($label){
		$this->label = $label;
	}
}