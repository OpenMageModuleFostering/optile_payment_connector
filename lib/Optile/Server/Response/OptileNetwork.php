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

class OptileNetwork extends  OptileResponseEntity{
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