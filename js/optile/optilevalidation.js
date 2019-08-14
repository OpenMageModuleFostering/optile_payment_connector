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

function ValidationForm(formId, validationUrl) {
	this.formId = formId;
	this.validationUrl = validationUrl;
	this.widget = null;
	this.holderName = null;
	this.holderNameMessage = null;
	this.number = null;
	this.numberMessage = null;
	this.expiryMonth = null;
	this.expiryMonthMessage = null;
	this.expiryYear = null;
	this.expiryYearMessage = null;
	this.verificationCode = null;
	this.verificationCodeMessage = null;
	this.bankCode = null;
	this.bankCodeMessage = null;
	this.bankName = null;
	this.bankNameMessage = null;
	this.bic = null;
	this.bicMessage = null;
	this.iban = null;
	this.ibanMessage = null;
	this.branch = null;
	this.branchMessage = null;
	this.country = null;
	this.countryMessage = null;
	this.city = null;
	this.cityMessage = null;
	this.login = null;
	this.loginMessage = null;
	this.password = null;
	this.passwordMessage = null;
}

ValidationForm.prototype.Build = function(parentWidget) {
	this.holderName = parentWidget.find("#" + this.formId + "-holderName").first();
	this.holderNameMessage = parentWidget.find("#" + this.formId + "-holderName-message").first();
	this.number = parentWidget.find("#" + this.formId + "-number").first();
	this.numberMessage = parentWidget.find("#" + this.formId + "-number-message").first();
	this.expiryMonth = parentWidget.find("#" + this.formId + "-expiryMonth").first();
	this.expiryMonthMessage = parentWidget.find("#" + this.formId + "-expiryMonth-message").first();
	this.expiryYear = parentWidget.find("#" + this.formId + "-expiryYear").first();
	this.expiryYearMessage = parentWidget.find("#" + this.formId + "-expiryYear-message").first();
	this.verificationCode = parentWidget.find("#" + this.formId + "-verificationCode").first();
	this.verificationCodeMessage = parentWidget.find("#" + this.formId + "-verificationCode-message").first();
	this.bankCode = parentWidget.find("#" + this.formId + "-bankCode").first();
	this.bankCodeMessage = parentWidget.find("#" + this.formId + "-bankCode-message").first();
	this.bankName = parentWidget.find("#" + this.formId + "-bankName").first();
	this.bankNameMessage = parentWidget.find("#" + this.formId + "-bankName-message").first();
	this.bic = parentWidget.find("#" + this.formId + "-bic").first();
	this.bicMessage = parentWidget.find("#" + this.formId + "-bic-message").first();
	this.iban = parentWidget.find("#" + this.formId + "-iban").first();
	this.ibanMessage = parentWidget.find("#" + this.formId + "-iban-message").first();
	this.branch = parentWidget.find("#" + this.formId + "-branch").first();
	this.branchMessage = parentWidget.find("#" + this.formId + "-branch-message").first();
	this.country = parentWidget.find("#" + this.formId + "-country").first();
	this.countryMessage = parentWidget.find("#" + this.formId + "-country-message").first();
	this.city = parentWidget.find("#" + this.formId + "-city").first();
	this.cityMessage = parentWidget.find("#" + this.formId + "-city-message").first();
	this.login = parentWidget.find("#" + this.formId + "-login").first();
	this.loginMessage = parentWidget.find("#" + this.formId + "-login-message").first();
	this.password = parentWidget.find("#" + this.formId + "-password").first();
	this.passwordMessage = parentWidget.find("#" + this.formId + "-password-message").first();
};

ValidationForm.prototype.ToJSON = function() {
	var data = {};
	if(this.holderName.length > 0) data.holderName = this.holderName.val();
	if(this.number.length > 0) data.number = this.number.val();
	if(this.expiryMonth.length > 0) data.expiryMonth = this.expiryMonth.val();
	if(this.expiryYear.length > 0) data.expiryYear = this.expiryYear.val();
	if(this.verificationCode.length > 0) data.verificationCode = this.verificationCode.val();
	if(this.bankCode.length > 0) data.bankCode = this.bankCode.val();
	if(this.bankName.length > 0) data.bankName = this.bankName.val();
	if(this.bic.length > 0) data.bic = this.bic.val();
	if(this.iban.length > 0) data.iban = this.iban.val();
	if(this.branch.length > 0) data.branch = this.branch.val();
	if(this.country.length > 0) data.country = this.country.val();
	if(this.city.length > 0) data.city = this.city.val();
	if(this.login.length > 0) data.login = this.login.val();
	if(this.password.length > 0) data.password = this.password.val();	

	return data;
};

ValidationForm.prototype.BindMessages = function(messages) {
	for(item in messages) {
		if(messages[item] === null) continue;
				
		if(this.hasOwnProperty(item) && this.hasOwnProperty(item + "Message")) {
			var widget = this[item + "Message"];
			
			widget.removeClass('optile-error-msg');
			
			switch(messages[item].type) {
			case 'ERROR':
				widget.addClass('optile-error-msg');
				break;
			}
			
			widget.html(messages[item].message);
		}
	}
};

function ValidationController(widget, forms, submit) {
	this.widget = widget;
	this.forms = forms;
	this.submit = submit;
}

ValidationController.prototype.Init = function() {
	var self = this;
	this.forms.forEach(function(item) {
		item.Build(self.widget);
	});
	this.submit.click(function(event) {
		event.preventDefault();
		self.Validate(event);
	});
};

ValidationController.prototype.FindFormByFormId = function(formId) {
	var result = null;
	for(var i = 0; i < this.forms.length; ++i) {
		if(this.forms[i].formId == formId) {
			result = this.forms[i];
		}
	}
	
	return result;
};

ValidationController.prototype.Validate = function(event) {
	var self = this;
	var network = this.widget.find("input[name=\"optile-selected-network\"]:checked").val();
	var form = this.FindFormByFormId(network);
	var data = form.ToJSON();
	var url = form.validationUrl;
	
	jQuery.post(url, data, function(resultData, resultStatus, xhr) {
		self.ValidationResult.call(self, resultData, resultStatus, xhr);
	}, "json");
};

ValidationController.prototype.ValidationResult = function(resultData, resultStatus, xhr) {
	var network = this.widget.find("input[name=\"optile-selected-network\"]:checked").val();
	var form = this.FindFormByFormId(network);
	form.BindMessages(resultData.messages);
	
	if(resultData.valid === true) {
		this.submit.off("click");
		this.submit.click();
	}
};