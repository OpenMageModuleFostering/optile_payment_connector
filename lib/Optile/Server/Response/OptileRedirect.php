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

class OptileRedirectMethod {
	const GET = "GET";
	const POST = "POST";
}

class OptileRedirect extends OptileResponseEntity {
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