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

abstract class Validator{
	
	/**
	 * Validates the current object
	 * @returns array : key - invalid field name,
	 * 					value - invalid field name message
	 */
	abstract public function Validate();
	
	/**
	 * Checks if a field is required and generates an error message
	 * @param unknown $value
	 * @param unknown $name
	 * @param array $result array
	 */
	protected function validateRequired($value, $name, &$result){
		$className = get_class($this);
		
		if ($value)
			return;
		
		$result[$name]= "$name is Required";
	}
}