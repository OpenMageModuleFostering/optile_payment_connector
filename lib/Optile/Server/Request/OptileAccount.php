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

class OptileAccount extends Entity {

	private $holderName;
	private $number;
	private $bankCode;
	private $bankName;
	private $city;
	private $bic;
	private $branch;
	private $expiryMonth;
	private $expiryYear;
	private $iban;
	private $login;
	private $optIn;
	private $password;
	private $verificationCode;
	
	public function getHolderName(){
		return $this->holderName;
	}
	
	public function setHolderName($holderName){
		$this->holderName = $holderName;
	}
	
	public function getNumber(){
		return $this->number;
	}
	
	public function setNumber($number){
		$this->number = $number;
	}
	
	public function getBankCode(){
		return $this->bankCode;
	}
	
	public function setBankCode($bankCode){
		$this->bankCode = $bankCode;
	}
	
	public function getBankName(){
		return $this->bankName;
	}
	
	public function setBankName($bankName){
		$this->bankName = $bankName;
	}
	
	public function getCity(){
		return $this->city;
	}
	
	public function setCity($city){
		$this->city = $city;
	}
	
	public function getBic(){
		return $this->bic;
	}
	
	public function setBic($bic){
		$this->bic = $bic;
	}
	
	public function getBranch(){
		return $this->branch;
	}
	
	public function setBranch($branch){
		$this->branch = $branch;
	}
	
	public function getExpiryMonth(){
		return $this->expiryMonth;
	}
	
	public function setExpiryMonth($expiryMonth){
		$this->expiryMonth = $expiryMonth;
	}
	
	public function getExpiryYear(){
		return $this->expiryYear;
	}
	
	public function setExpiryYear($expiryYear){
		$this->expiryYear = $expiryYear;
	}
	
	public function getIban(){
		return $this->iban;
	}
	
	public function setIban($iban){
		$this->iban = $iban;
	}
	
	public function getLogin(){
		return $this->login;
	}
	
	public function setLogin($login){
		$this->login = $login;
	}
	
	public function getOptIn(){
		return $this->optIn;
	}
	
	public function setOptIn($optIn){
		$this->optIn = $optIn;
	}
	
	public function getPassword(){
		return $this->password;
	}
	
	public function setPassword($password){
		$this->password = $password;
	}
	
	public function getVerificationCode(){
		return $this->verificationCode;
	}
	
	public function setVerificationCode($verificationCode){
		$this->verificationCode = $verificationCode;
	}
	
	public function Validate(){
		return array();
	}

	
}