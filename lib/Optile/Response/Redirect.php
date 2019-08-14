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
 * RedirectMethod
 *
 * Simple wrapper class for handling RedirectMethod internally
 */
class RedirectMethod {
	const GET = "GET";
	const POST = "POST";
}

/**
 * Redirect
 *
 * Simple class that defines Redirect response from Optile
 */
class Redirect extends Entity {
	private $url;
	private $method;
	private $parameters;
	private $suppressIFrame;

	public function getUrl(){
		return $this->url;
	}

	public function setUrl($url){
		$this->url = $url;
	}

	public function getMethod(){
		return $this->method;
	}

	public function setMethod($method){
		$this->method = $method;
	}

	public function getParameters(){
		return $this->parameters;
	}

	public function setParameters($parameters){
		$this->parameters = $parameters;
	}

	public function getSuppressIFrame(){
		return $this->suppressIFrame;
	}

	public function setSuppressIFrame($suppressIFrame){
		$this->suppressIFrame = $suppressIFrame;
	}

	public function __construct($url, $method, $parameters = array(),$suppressIFrame = false){
		$this->url = $url;
		$this->method = $method;
		$this->parameters = $parameters;
		$this->suppressIFrame = $suppressIFrame;
	}

	public function Redirect(){

		$url = $this->url;
		$parameters = $this->parameters;

		$parameters =  array_map(function($item){
			return urlencode($item);
		}, $parameters);

		if ($parameters!=array()){
			$query = array();

			foreach($parameters as $key => $value){
				$query="$key=$value";
			}

			$query= implode('&', $query);

			$url.='?'.$query;

		}

		header('Location:'.$url);
		exit;
	}
}