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
 * Response
 *
 * Defines standard Optile response structure.
 */
class Response extends Entity{

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