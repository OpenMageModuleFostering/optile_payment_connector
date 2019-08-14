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
//// Disable console.log
//window['console']['log'] = function() {
//};

jQuery(function() {

  Checkout.prototype.setLoadWaiting = function(step, keepDisabled) {
    return false;
  }

  ValidationController.prototype.Validate = function(event) {
    var self = this;
    var network = this.widget.find("input[name=\"optile-selected-network\"]:checked").val();
    var form = this.FindFormByFormId(network);
    var data = form.ToJSON();
    var url = form.validationUrl;
    var ok = false;

    //T: Converted this to .ajax(), in order to utilize async:false option
    //   This was done in order to be able to return success true/false
    jQuery.ajax({
      type: 'POST',
      url: url,
      data: JSON.stringify(data),
      dataType: 'json',
      async: false,
      success: function(resultData, resultStatus, xhr) {
        ok = resultData.valid;
        console.log("resultData.valid: " + ok);
        if (typeof (response) == "undefined") {
          response = {};
        }
        self.ValidationResult.call(self, resultData, true);
        if (ok) {
	            checkout.setLoadWaiting('payment');
          var request = new Ajax.Request(
                  payment.saveUrl,
                  {
                    method: 'post',
	                    onComplete: payment.onComplete,
	                    onSuccess: payment.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: jQuery.param({
                      payment: {
                        method: 'optile',
                        network: form.networkCode
                      }
                    })
                  }
          );
        }
        ;
      }

    });


//		jQuery.post(url, data, function... , "json");
    return ok;
  };

  if (payment) {

    /**
     * overrides opcheckout.js payment.save() so it doesn't call magento for
     * validation, instead calls optile javascript api from ValidationController
     */
    payment.save = payment.save.wrap(function(parentMethod) {
      if (payment.currentMethod == 'optile') {
        if (checkout.loadWaiting != false)
          return;

        var validator = new Validation(this.form);
        if (validator.validate()) {
          var selectedNetworkWidget = self.controller.widget.find("input[name=\"optile-selected-network\"]:checked");
          if (selectedNetworkWidget.length === 0) {
            OptileErrorHandler.error(Translator.translate('Error, no payment network is selected'));
            return;
          }

          self.controller.Validate();
        }
      } else {
        parentMethod();
      }

    });

    /**
     * this method is overriden from the default so that optile payment method
     * section doesn't get disabled/hidden when we select other Magento payment methods
     */
    payment.switchMethod = payment.switchMethod.wrap(function(parentMethod, method) {
      console.log("Firing payment.switchMethod. Selected method: " + method);

      if(typeof(self.controller) == "undefined") {
        return;
      }
      if (this.currentMethod && $('payment_form_' + this.currentMethod)) {
        if (this.currentMethod === 'optile' && method !== 'optile') {
          // unselects and hides the inner optile payment method if we're switching
          // to other Magento payment method
          var selectedOptileNetworkWidget = self.controller.widget.find("input[name=\"optile-selected-network\"]:checked");
          if (selectedOptileNetworkWidget) {
            selectedOptileNetworkWidget.prop('checked', false);
            window.optileForm.changeVisible(window.optileForm.currentMethod, true);
          }
        } else {
          this.changeVisible(this.currentMethod, true);
          $('payment_form_' + this.currentMethod).fire('payment-method:switched-off', {method_code: this.currentMethod});
        }
      }
      if ($('payment_form_' + method)) {
        this.changeVisible(method, false);
        $('payment_form_' + method).fire('payment-method:switched', {method_code: method});
      } else {

        //Event fix for payment methods without form like "Check / Money order"
        document.body.fire('payment-method:switched', {method_code: method});
      }
      if (method) {
        this.lastUsedMethod = method;
      }
      this.currentMethod = method;
    });

      console.log("payment.validate wrap");
    payment.validate = payment.validate.wrap(function(parentMethod) {
        console.log("payment.validate");
      if (payment.currentMethod == 'optile') {
        var isValid = self.controller.Validate(null, parentMethod);
          console.log("isValid ", isValid);
//				return false;
      } else {
        return parentMethod();
      }
    });

  }

  if (Review) {

    Review.prototype.nextStep = Review.prototype.nextStep.wrap(function(parentMethod, transport) {
      console.log("Review.prototype.nextStep");
      if (payment.currentMethod == 'optile') {
        console.log("onSave");
      } else {
        parentMethod(transport);
      }
    });

    /**
     * Overrides Review.save() for optile. All <input> tags for payment
     * methods in one-page checkout are set in payment[] array. optile renders
     * its html forms differently so we have to make sure proper data is
     * collected before we call charge request
     *
     */
    Review.prototype.save = Review.prototype.save.wrap(function(parentMethod) {
      if (payment.currentMethod == 'optile') {
        if (checkout.loadWaiting != false)
          return;
        checkout.setLoadWaiting('review');

        // Handle terms and conditions
        agreements = true;
        jQuery(".checkout-agreements .checkbox").each(function(index){
            if(!jQuery(this).is(":checked")){
                agreements = false;
            };
        });
        console.log("checkout agreements:");
        console.log(agreements);
        if(agreements == false){
            if(typeof(terms_and_conditions_error_text) === 'undefined'){
                terms_and_conditions_error_text = "Please agree to all the terms and conditions before placing the order."
            }
            alert(terms_and_conditions_error_text);
            review.resetLoadWaiting();
            return;
        }

        var params;
        var network = jQuery('#payment_form_optile').find("input[name=\"optile-selected-network\"]:checked").val();
        var data = {
        };

        var block = network + '-network';
        element = $(block);
        if (element) {
          element.select('input', 'select', 'textarea', 'button').each(function(field) {
            if (field.name == 'optile-selected-network')
              return;
            if (field.name == 'operation')
              return;
            if (field.name == 'optile-list-self')
              return;
            data[field.name] = field.value;
          });
        }

        params = jQuery.param(data);

        var operationUrl = jQuery('#' + network + '-operation').val();
        var listRequestSelfLink = jQuery('#optile-list-self').val();
        var self = this;

        // F: converted to .ajax() in order to utilize async:false
        // so we can handle possible failure of charge request.
        jQuery.ajax({
          type: 'POST',
          url: operationUrl,
          data: JSON.stringify(data),
          dataType: 'json',
          async: false,
          success: function(resultData, resultStatus, xhr) {
            var paymentResponse = new OptilePaymentResponseFactory().newOptilePaymentResponse(resultData);
            var handler;
            switch (paymentResponse.interaction.code) {
              case OptileInteractionCode.PROCEED:
                handler = new OptileInteractionCodeHandlerPROCEED(paymentResponse, listRequestSelfLink);
                break;
              case OptileInteractionCode.RETRY:
                handler = new OptileInteractionCodeHandlerRETRY(paymentResponse, listRequestSelfLink);
                break;
              case OptileInteractionCode.TRY_OTHER_ACCOUNT:
                handler = new OptileInteractionCodeHandlerTRY_OTHER_ACCOUNT(paymentResponse, listRequestSelfLink);
                break;
              case OptileInteractionCode.TRY_OTHER_NETWORK:
                handler = new OptileInteractionCodeHandlerTRY_OTHER_NETWORK(paymentResponse, listRequestSelfLink);
                break;
              case OptileInteractionCode.ABORT:
                handler = new OptileInteractionCodeHandlerABORT(paymentResponse, listRequestSelfLink);
                break;
              default:
                console.log("ERROR! Can't find interaction code handler");
                break;
            }
            if (handler) {
              handler.handle(self);
            }

            if (paymentResponse.interaction.code != OptileInteractionCode.PROCEED) {
              self.optile_error = 1;
            }
          }
        });

      } else {
        console.log("review.prototype.save: doing parent method");
        parentMethod();
      }
    });
  }

  if (ShippingMethod) {
    /**
     * Overrides shippingMethod.save() call for the case where we want to refresh
     * payment method list without sending another LIST request to optile
     * (required for TRY_OTHER_ACCOUNT, TRY_OTHER_NETWORK response codes).
     * If there's existing list request, it will reuse it.
     */
    ShippingMethod.prototype.save = ShippingMethod.prototype.save.wrap(function(parentMethod, listRequestSelfLink) {
      if (listRequestSelfLink) {
        if (checkout.loadWaiting != false)
          return;
        if (this.validate()) {
          checkout.setLoadWaiting('shipping-method');
          var request = new Ajax.Request(
                  this.saveUrl,
                  {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form) + '&listRequestSelfLink=' + encodeURIComponent(listRequestSelfLink)
                  }
          );
        }
      } else {
        parentMethod();
      }
    });
  }

  var OptileInteractionCodeHandler = Class.create();
  OptileInteractionCodeHandler.prototype = {
    initialize: function(paymentResponse, listRequestSelfLink) {
      this.paymentResponse = paymentResponse;
      this.listRequestSelfLink = listRequestSelfLink;
    }
  }

  /**
   * Handles PROCEED optile payment response
   */
  var OptileInteractionCodeHandlerPROCEED = Class.create(OptileInteractionCodeHandler, {
    handle: function(context) {
      console.log("PROCEED handler: context:");
      console.log(context);
      console.log("PROCEED handler: doing AJAX to:" + context.saveUrl);
      var network = jQuery('#payment_form_optile').find("input[name=\"optile-selected-network\"]:checked").val();
      var form = self.controller.FindFormByFormId(network);
      var params = "";

      if (review.agreementsForm) {
          console.log("Agreements form detected, serializing it.")
          params += '&'+Form.serialize(review.agreementsForm);
      }

      params += '&'+jQuery.param({
                      payment: {
                          method: 'optile',
                          network: form.networkCode
                      }});


      var request = new Ajax.Request(
              context.saveUrl,
              {
                method: 'post',
                parameters: params,
                onComplete: context.onComplete,
                onSuccess: this.redirect.bindAsEventListener(this),
                onFailure: checkout.ajaxFailure.bind(checkout)
              }
      );

    },
    redirect: function(transport) {
      console.log("PROCEED handler: redirect");
      if (transport && transport.responseText) {
        console.log("transport:");
        console.log(transport);
        try {
          response = eval('(' + transport.responseText + ')');
        }
        catch (e) {
          response = {};
        }
        console.log("response");
        console.log(response);
        if (response.redirect) {
          location.href = response.redirect;
          return;
        }
        if (response.success) {
          var redirect = this.paymentResponse.redirect;
          console.log("PROCEED handler: redirect to: " + redirect.url);
          switch (redirect.method) {
            case "GET":
              window.location.href = redirect.url;
              break;
            case "POST":
              // hacky way to do a POST redirect in javascript from stackoverflow
              var form = jQuery('<form action="' + redirect.url + '" method="post" style="display:none;">' +
                      //                	  '<input type="text" name="api_url" value="' + Return_URL + '" />' +
                      '</form>');
              jQuery('body').append(form);
              form.submit();
              break;
          }
        }
        else {
          var msg = response.error_messages;
          if (typeof (msg) == 'object') {
            msg = msg.join("\n");
          }
          if (msg) {
            alert('Error: ' + msg);
          }
        }

        if (response.update_section) {
          $('checkout-' + response.update_section.name + '-load').update(response.update_section.html);
        }

        if (response.goto_section) {
          checkout.gotoSection(response.goto_section);
        }
      }

    }
  });

  var OptileErrorHandler = {
    error: function(msg) {
      if (msg) {
        alert('Error: ' + msg);
      }
    }
  }

  /**
   * Handles RETRY response code
   */
  var OptileInteractionCodeHandlerRETRY = Class.create(OptileInteractionCodeHandler, {
    handle: function(context) {

      OptileErrorHandler.error(this.paymentResponse.resultInfo);

      // save data before reloading payment section
      saveOptileData();

      // simulate shipping method save so it reloads payment section
      checkout.setLoadWaiting(false);
      shippingMethod.save(this.listRequestSelfLink);
    }
  });

  /**
   * Handles TRY_OTHER_ACCOUNT response code
   */
  var OptileInteractionCodeHandlerTRY_OTHER_ACCOUNT = Class.create(OptileInteractionCodeHandler, {
    handle: function(context) {

      OptileErrorHandler.error(this.paymentResponse.resultInfo);

      // save data before reloading payment section
      saveOptileData();

      // simulate shipping method save so it reloads payment section
      checkout.setLoadWaiting(false);
      shippingMethod.save(this.listRequestSelfLink);
    }
  });

  /**
   * Handles TYR_OTHER_NETWORK response code - same as TRY_OTHER_ACCOUNT
   */
  var OptileInteractionCodeHandlerTRY_OTHER_NETWORK = Class.create(OptileInteractionCodeHandler, {
    handle: function(context) {

      OptileErrorHandler.error(this.paymentResponse.resultInfo);

      // simulate shipping method save so it reloads payment section
      checkout.setLoadWaiting(false);
      shippingMethod.save(this.listRequestSelfLink);
    }
  });

  var OptileInteractionCodeHandlerABORT = Class.create(OptileInteractionCodeHandler, {
    handle: function(context) {
      checkout.setLoadWaiting(false);

      OptileErrorHandler.error(this.paymentResponse.resultInfo);
      window.location.href = window.cartPageUrl;
//			shippingMethod.save();
    }
  });

  var OptilePaymentResponseFactory = Class.create();
  OptilePaymentResponseFactory.prototype = {
    initialize: function() {
    },
    newOptilePaymentResponse: function(responseData) {
      return new OptilePaymentResponse(responseData.links, responseData.info, responseData.interaction, responseData.redirect, responseData.resultInfo);
    }
  }

  var OptilePaymentResponse = Class.create();
  OptilePaymentResponse.prototype = {
    initialize: function(links, info, interaction, redirect, resultInfo) {
      this.links = links;
      this.info = info;
      this.interaction = interaction;
      this.redirect = redirect;
      this.resultInfo = resultInfo;
    }
  }

  var OptileInteractionCode = {
    PROCEED: 'PROCEED',
    RETRY: 'RETRY',
    TRY_OTHER_ACCOUNT: 'TRY_OTHER_ACCOUNT',
    TRY_OTHER_NETWORK: 'TRY_OTHER_NETWORK',
    ABORT: 'ABORT'
  };

  window.OptileForm = Class.create();
  OptileForm.prototype = {
    beforeInitFunc: $H({}),
    afterInitFunc: $H({}),
    beforeValidateFunc: $H({}),
    afterValidateFunc: $H({}),
    initialize: function(form) {
      this.form = form;
    },
    init: function() {
      var elements = Form.getElements(this.form);
      var method = null;
      for (var i = 0; i < elements.length; i++) {
        if (elements[i].name == 'optile-selected-network') {
          if (elements[i].checked) {
            method = elements[i].value;
          }
        }
      }
      if (method)
        this.switchMethod(method);
    },
    // we hide the global 'Optile' payment method label so we're going
    // to select it by javascript when we select any Optile payment form
    switchMethod: function(method) {
      jQuery('#p_method_optile').prop('checked', true);
      payment.switchMethod('optile');

      if (this.currentMethod && $(this.currentMethod + '-network')) {
        this.changeVisible(this.currentMethod, true);
        $(this.currentMethod + '-network').fire('optile-network:switched-off', {network_code: this.currentMethod});
      }
      if ($(method + '-network')) {
        this.changeVisible(method, false);
        $(method + '-network').fire('optile-network:switched', {network_code: method});
      } else {
        //Event fix for payment methods without form like "Check / Money order"
        document.body.fire('optile-network:switched', {network_code: method});
      }
      if (method) {
        this.lastUsedMethod = method;
      }
      this.currentMethod = method;
    },
    changeVisible: function(method, mode) {
      var block = method + '-network';
      [block + '_before', block, block + '_after'].each(function(el) {
        element = $(el);
        if (element) {
          element.style.display = (mode) ? 'none' : '';
          element.select('input', 'select', 'textarea', 'button').each(function(field) {
            field.disabled = mode;
          });
        }
      });
    },
  }
});


function saveOptileData() {

  if (typeof (self.controller) == "undefined") {
    return;
  }
  console.log("Saving Optile data");
  var network = jQuery('#payment_form_optile').find("input[name=\"optile-selected-network\"]:checked").val();
  var form = self.controller.FindFormByFormId(network);
  if (form) {
    var data = form.ToJSON();
    if (data.hasOwnProperty('verificationCode')) {
      delete data['verificationCode'];
    }

    window.savedOptileNetworkCode = network;
    window.savedOptileData = data;
  }

}
