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

require_once 'Interaction.php';
require_once 'Network.php';
require_once 'NetworkLink.php';
require_once 'Redirect.php';
require_once 'Response.php';

/**
 * ResponseFactory
 *
 * Factory class that is being used to instantiate response objects. For usage
 * example, please refer to ListRequest::send() method.
 */
class ResponseFactory {

    /**
     * Receives json encoded data and maps the response as object instances.
     *
     * @param string $data
     * @return Response
     */
	public function BuildOptileResponse($data) {
		$structure = json_decode($data,true);

		$interaction = $this->buildInteraction($structure);
		$networks = $this->buildNetworks($structure);
		$redirect = $this->buildRedirect($structure);

		$links = array_key_exists('links', $structure) ? $structure['links'] : array();

		$response = new Response($structure['resultInfo'],
									   $interaction,
									   $links,
									   $networks,
									   $redirect
										);

		return $response;
	}

    /**
     * Maps the Interaction response to Interaction object
     *
     * @param array $structure
     * @return Interaction
     */
	private function buildInteraction($structure) {
		$interaction = new Interaction($structure['interaction']['code'],
											 $structure['interaction']['reason']);
		return $interaction;
	}

    /**
     * Maps the Networks response to Newtork object
     *
     * @param array $structure
     * @return Network
     */
	private function buildNetworks($structure) {
        if(!is_array($structure)){
            return array();
        }
		if (!array_key_exists('networks', $structure))
			return array();

		if (!array_key_exists('applicable', $structure['networks']))
			return array();

		$applicable = $structure['networks']['applicable'];

		$networks = array();

		foreach($applicable as $network){


			$optileNetwork = new Network();            
			if(isset($network['code'])) {
                $optileNetwork->setCode($network['code']);
            }
			if(isset($network['method'])) {
                $optileNetwork->setMethod($network['method']);
            }
			if(isset($network['label'])) {
                $optileNetwork->setLabel($network['label']);
            }
			if(isset($network['registration'])) {
                $optileNetwork->setRegistration($network['registration']);
            }
            if(isset($network['recurrence'])) {
                $optileNetwork->setRecurrence($network['recurrence']);
            }

			$optileLink = new NetworkLink($network['links']['operation'],
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

    /**
     * Maps the Redirect response to Redirect object
     *
     * @param type $structure
     * @return Redirect
     */
	private function buildRedirect($structure) {
		if (!array_key_exists('redirect', $structure))
			return null;


		$parameters = array();

		if (array_key_exists('parameters',$structure['redirect']) && $structure['redirect']['parameters']!=array()){
			foreach ($structure['redirect']['parameters'] as $name => $value)
				$parameters[$name]=$value;
		}

		$redirect = new Redirect($structure['redirect']['url'],
									   $structure['redirect']['method'],
					                   $parameters,
									  $structure['redirect']['suppressIFrame']
				);

		return $redirect;
	}

}
