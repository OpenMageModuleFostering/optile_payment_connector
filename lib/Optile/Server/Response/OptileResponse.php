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

class OptileResponse extends OptileResponseEntity{

	
	private $info;
	private $interaction;
	private $links;
	private $networks;
	private $redirect;
	
	public function __construct($info, 
					            $interaction, 
								$links = array(), 
								$networks = array(),
								$redirect=null){
		$this->setInfo($info);
		$this->setInteraction($interaction);
		$this->setLinks($links);
		$this->setNetworks($networks);
		$this->setRedirect($redirect);
	}
	
	public function getInfo(){
		return $this->info;
	}
	
	public function setInfo($info){
		$this->info = $info;
	}
	
	public function getInteraction(){
		return $this->interaction;
	}
	
	public function setInteraction($interaction){
		$this->interaction = $interaction;
	}
	
	public function getLinks(){
		return $this->links;
	}
	
	public function setLinks($links){
		$this->links = $links;
	}
	
	public function getNetworks(){
		return $this->networks;
	}
	
	public function setNetworks($networks){
		$this->networks = $networks;
	}
	
	public function getRedirect(){
		return $this->redirect;
	}
	
	public function setRedirect($redirect){
		$this->redirect = $redirect;
	}
}