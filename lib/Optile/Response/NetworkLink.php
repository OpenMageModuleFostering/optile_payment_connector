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
require_once __DIR__.'/../Request/RequestFactory.php';
/**
 * NetworkLink
 *
 * Simple class that defines NetworkLink response from Optile
 */
class NetworkLink extends Entity{
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
		$request = \Optile\Request\RequestFactory::getSimpleRequest($url);
        $request->setUseCache(true);
		$response = $request->send();
		return $response;
	}
}