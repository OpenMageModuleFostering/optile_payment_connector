/**
 * Copyright optile GmbH 2013
 * Licensed under the Software License Agreement in effect between optile and
 * Licensee/user (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 * http://www.optile.de/software-license-agreement; in addition, a countersigned
 * copy has been provided to you for your records. Unless required by applicable
 * law or agreed to in writing or otherwise stipulated in the License, software
 * distributed under the License is distributed on an "as is" basis without
 * warranties or conditions of any kind, either express or implied.  See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * @author      i-Ways <dev@i-ways.hr>
 * @copyright   Copyright (c) 2013 optile GmbH. (http://www.optile.de)
 * @license     http://www.optile.de/software-license-agreement
 */
function OptilePaymentNetworkForm(formId, validationUrl, operationUrl, networkCode) {
	this.formId = formId;
	this.validationUrl = validationUrl;
	this.operationUrl = operationUrl;
	this.networkCode = networkCode;
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

OptilePaymentNetworkForm.prototype.Build = function(parentWidget) {
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

OptilePaymentNetworkForm.prototype.ToJSON = function() {
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

OptilePaymentNetworkForm.prototype.BindValues = function(data) {
	for(item in data) {
		if(this.hasOwnProperty(item) && this.hasOwnProperty(item + "Message")) {
			var itemWidget = this[item];

			if(data[item] !== null) {
				itemWidget.val(data[item]);
			}
		}
	}
};

OptilePaymentNetworkForm.prototype.BindMessages = function(messages) {
	for(item in messages) {
		if(this.hasOwnProperty(item) && this.hasOwnProperty(item + "Message")) {
			var messageWidget = this[item + "Message"];
			var itemWidget = this[item];

			// reset message
			messageWidget.html("");
			messageWidget.removeClass('optile-error-msg');
			messageWidget.removeClass('optile-info-msg');
			itemWidget.removeClass('input-text validation-passed');
			itemWidget.removeClass('input-text validation-failed');

			if(messages[item] !== null) {
				switch(messages[item].type) {
				case 'ERROR':
					messageWidget.addClass('optile-error-msg');
					if(!itemWidget.is('select')) {
						itemWidget.addClass('input-text');
					}
					itemWidget.addClass('validation-failed');
					break;
				case 'INFO':
					messageWidget.addClass('optile-info-msg');
					itemWidget.addClass('input-text validation-passed');
					break;
				}

				messageWidget.html(messages[item].message);
			}
		}
	}
};

// alias new OptilePaymentNetworkForm to old ValidationForm
function ValidationForm() {
	OptilePaymentNetworkForm.apply(this, arguments);
}
ValidationForm.prototype = new OptilePaymentNetworkForm();

function ValidationController(widget, forms, submit, selectedNetworkSelector) {
	this.widget = widget;
	this.forms = forms;
	this.submit = submit;
	this.selectedNetworkSelector = selectedNetworkSelector;
}

ValidationController.prototype.Init = function() {
	var self = this;
	this.forms.forEach(function(item) {
		item.Build(self.widget);
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
	var network = this.widget.find(this.selectedNetworkSelector).val();
	var form = this.FindFormByFormId(network);
	var data = form.ToJSON();
	var url = form.validationUrl;

	jQuery.post(url, data, function(resultData, resultStatus, xhr) {
		self.ValidationResult.call(self, resultData, resultStatus, xhr);
	}, "json");
};

ValidationController.prototype.ValidationResult = function(resultData, resultStatus, xhr) {
	var network = this.widget.find(this.selectedNetworkSelector).val();
	var form = this.FindFormByFormId(network);
	form.BindMessages(resultData.messages);
};
