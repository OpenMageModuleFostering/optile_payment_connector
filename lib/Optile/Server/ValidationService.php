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

class ValidationService {
	private static $allowed_fields = array(
			'holderName',
			'number',
			'expiryMonth',
			'expiryYear',
			'verificationCode',
			'bankCode',
			'bankName',
			'bic',
			'iban',
			'branch',
			'country',
			'city',
			'login',
			'password'
	);
	
	public static function GetResponse($url, $params = array()) {
		if (!$url)
			throw new OptileUrlException("Url must be set");
		if (strlen($url)==0)
			throw new OptileUrlException("Url must be set");
		
		$fields = array();
		foreach($params as $key => $value) {
			if(in_array($key, self::$allowed_fields)) {
				$fields[$key] = $value;
			}
		}
	
		$connection = new OptileConnection();
		$result = $connection->GetResponse($url, $fields);	
			
		return $result;
	}
}