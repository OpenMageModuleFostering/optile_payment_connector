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

class OptileChargeRequest extends OptileRequest {
	protected function buildPOSTRequest(){ 
		$result = array();
		
		$account = $this->getAccount();
		
		if (strlen("".$account->getBankCode())!=0)
			$result['account.bankCode'] = $account->getBankCode();
		
		if (strlen("".$account->getBankName())!=0)
			$result['account.bankName'] = $account->getBankName();
		
		if (strlen("".$account->getBic())!=0)
			$result['account.bic'] = $account->getBic();
		
		if (strlen("".$account->getBranch())!=0)
			$result['account.branch'] = $account->getBranch();
		
		if (strlen("".$account->getCity())!=0)
			$result['account.city'] = $account->getCity();
		
		if (strlen("".$account->getExpiryMonth())!=0)
			$result['account.expiryMonth'] = $account->getExpiryMonth();
		
		if (strlen("".$account->getExpiryYear())!=0)
			$result['account.expiryYear'] = $account->getExpiryYear();
		
		if (strlen("".$account->getHolderName())!=0)
			$result['account.holderName'] = $account->getHolderName();
		
		if (strlen("".$account->getLogin())!=0)
			$result['account.login'] = $account->getLogin();
		
		if (strlen("".$account->getNumber())!=0)
			$result['account.number'] = $account->getNumber();
		
		if (strlen("".$account->getOptIn())!=0)
			$result['account.optIn'] = $account->getOptIn();
		
		if (strlen("".$account->getPassword())!=0)
			$result['account.password'] = $account->getPassword();
		
		if (strlen("".$account->getVerificationCode())!=0)
			$result['account.verificationCode'] = $account->getVerificationCode();
		
		
		return $result;
		
		
	}
	
	public function Validate(){
		$result = array();
		
		$this->validateRequired($this->getAccount(), "Account", $result);
		
		return $result;
	}
}