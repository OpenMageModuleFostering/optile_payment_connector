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

class OptileConnection{
	public function Get($url,$parameters = array(), $merchantCode = null, $merchantToken = null){
		
		$parameters =  array_map(function($item){
			return urlencode($item);
		}, $parameters);
		
		$ch = curl_init();
		
		if ($parameters!=array()){
			$query = array();
			
			foreach($parameters as $key => $value){
				$query[]="$key=$value";
			}
			
			$query= implode('&', $query);
			
			$url.='?'.$query;
				
		}
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		if(isset($merchantCode, $merchantToken))
			curl_setopt($ch, CURLOPT_USERPWD, $merchantCode.'/'.$merchantToken);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		
		if (strlen($result)==0)
			return "";
		
		$parts = explode("\r\n", $result);
		$result = $parts[(count($parts)-1)];
		
		return $result;
	}
	
	public function GetResponse($url, $fields, $merchantCode = null, $merchantToken = null){
		$fieldsEncoded = array();
		
		foreach($fields as $key => $value){
			if (!is_array($value)){
				$fieldsEncoded[]=$key."=".urlencode($value);
				continue;
			}
			
			
			$this->encodeFieldsArray($key, $value, $fieldsEncoded);
		}
		
		$serializedFields = implode('&', $fieldsEncoded);
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $serializedFields);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE,0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-length:'.strlen($serializedFields),
			'Accept:application/vnd.optile.payment.simple-v1+json',
			'Content-type:application/x-www-form-urlencoded'
		));
		
		if(isset($merchantCode, $merchantToken))
			curl_setopt($ch, CURLOPT_USERPWD, $merchantCode.'/'.$merchantToken);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		
		if (strlen($result)==0)
			return "";
		
		$parts = explode("\r\n", $result);
		$result = $parts[(count($parts)-1)];
		
		return $result;
	}
	
	private function encodeFieldsArray($key, $value, &$fieldsEncoded){
		$i = 0;
		foreach ($value as $item){
	
			foreach($item as $fieldName => $fieldValue){
				$fieldsEncoded[]=$key."[$i].$fieldName=".urlencode($fieldValue);
			}
			$i++;
		}
	}
}