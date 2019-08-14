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

class OptileResponseFactory {
	public function BuildOptileResponse($data){
		$structure = json_decode($data,true);
	
		$interaction = $this->buildInteraction($structure);
		$networks = $this->buildNetworks($structure);
		$redirect = $this->buildRedirect($structure);
		
		$links = array_key_exists('links', $structure) ? $structure['links'] : array();
		
		$response = new OptileResponse($structure['resultInfo'],
									   $interaction, 
									   $links,
									   $networks,
									   $redirect
										);
		
		return $response;
	}
	
	private function buildInteraction($structure){
		$interaction = new OptileInteraction($structure['interaction']['code'], 
											 $structure['interaction']['reason']);
		return $interaction;
	}
	
	private function buildNetworks($structure){
		if (!array_key_exists('networks', $structure))
			return array();
		
		if (!array_key_exists('applicable', $structure['networks']))
			return array();
		
		$applicable = $structure['networks']['applicable'];
		
		$networks = array();
		
		foreach($applicable as $network){
			
			
			$optileNetwork = new OptileNetwork();
			$optileNetwork->setCode($network['code']);
			$optileNetwork->setMethod($network['method']);
			$optileNetwork->setLabel($network['label']);
			$optileNetwork->setRegistration($network['registration']);
			$optileNetwork->setRecurrence($network['recurrence']);
		
			
			$optileLink = new OptileNetworkLink($network['links']['operation'], 
												$network['links']['form'], 
					                            $network['links']['logo'], 
					                            $network['links']['lang'], 
												$network['links']['localizedForm'],
												$network['links']['validation'],
												$network['links']['operation']);
			
			$optileNetwork->setLinks($optileLink);
			
			$networks[$optileNetwork->getMethod()][]= $optileNetwork;
			
		}
	
		return $networks;
	}
	
	private function buildRedirect($structure){
		if (!array_key_exists('redirect', $structure))
			return null;
		
		
		$parameters = array();
		
		if (array_key_exists('parameters',$structure['redirect']) && $structure['redirect']['parameters']!=array()){
			foreach ($structure['redirect']['parameters'] as $name => $value)
				$parameters[$name]=$value;
		}
		
		$redirect = new OptileRedirect($structure['redirect']['url'],
									   $structure['redirect']['method'],
					                   $parameters,
									  $structure['redirect']['suppressIFrame']
				);
		
		return $redirect;
	}
}