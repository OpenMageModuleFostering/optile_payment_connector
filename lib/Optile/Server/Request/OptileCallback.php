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

class OptileCallback extends Entity {
	private $returnUrl = "";
	private $cancelUrl = "";
	private $notificationUrl = "";
	
	public function getReturnUrl(){
		return $this->returnUrl;
	}
	
	public function setReturnUrl($returnUrl){
		$this->returnUrl = $returnUrl;
	}
	
	public function getCancelUrl(){
		return $this->cancelUrl;
	}
	
	public function setCancelUrl($cancelUrl){
		$this->cancelUrl = $cancelUrl;
	}
	
	public function getNotificationUrl(){
		return $this->notificationUrl;
	}
	
	public function setNotificationUrl($notificationUrl){
		$this->notificationUrl = $notificationUrl;
	}
	
	public function Validate(){
		$result = array();
		$this->validateRequired($this->getReturnUrl(), "ReturnURL", $result);
		$this->validateRequired($this->getCancelUrl(), "CancelURL", $result);
		$this->validateRequired($this->getNotificationUrl(), "NotificationURL", $result);
		return $result;
	}
};