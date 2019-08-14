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

class OptileNetworkLink extends  OptileResponseEntity{
	private $operation;
	private $formLink;
	private $logoLink;
	private $langLink;
	private $operationLink;
	private $validationLink;
	private $localizedFormLink;
	private $formHtml;
	private $langProperties;
	private $localizedFormHtml;
	
	public function getValidationLink(){
		return $this->validationLink;
	}
	
	public function setValidationLink($validationLink){
		$this->validationLink = $validationLink;
	}
	
	public function getOperation(){
		return $this->operation;
	}
	
	public function setOperation($operation){
		$this->operation = $operation;
	}
	
	public function getFormLink(){
		return $this->formLink;
	}
	
	public function setFormLink($formLink){
		$this->formLink = $formLink;
	}
	
	public function getLogoLink(){
		return $this->logoLink;
	}
	
	public function setLogoLink($logoLink){
		$this->logoLink = $logoLink;
	}
	
	public function getLangLink(){
		return $this->langLink;
	}
	
	public function setLangLink($langLink){
		$this->langLink = $langLink;
	}
	
	public function getLocalizedFormLink(){
		return $this->localizedFormLink;
	}
	
	public function setLocalizedFormLink($localizedFormLink){
		$this->localizedFormLink = $localizedFormLink;
	}
	
	public function getFormHtml(){
		return $this->formHtml;
	}
	
	public function setFormHtml($formHtml){
		$this->formHtml = $formHtml;
	}
	
	public function getLangProperties(){
		return $this->langProperties;
	}
	
	public function setLangProperties($langProperties){
		$this->langProperties = $langProperties;
	}
	
	public function getLocalizedFormHtml(){
		return $this->localizedFormHtml;
	}
	
	public function setLocalizedFormHtml($localizedFormHtml){
		$this->localizedFormHtml = $localizedFormHtml;
	}
	
	public function __construct($operation, 
							    $formLink,
							    $logoLink, 
			                    $langLink, 
			                    $localizedFormLink,
								$validationLink,
								$operationLink){
		$this->operation = $operation;
		$this->formLink =  $formLink;
		$this->logoLink = $logoLink;
		$this->langLink = $langLink;
		$this->localizedFormLink = $localizedFormLink;
		$this->validationLink = $validationLink;
		$this->operationLink = $operationLink;
		
		$this->formHtml = $this->getContent($this->formLink);
		$this->langProperties = $this->getContent($this->langLink);
		$this->localizedFormHtml = $this->getContent($this->localizedFormLink);
	}
	
	private function getContent($url){
		$request = new OptileConnection();
		$response = $request->Get($url);
		return $response;
	}
}