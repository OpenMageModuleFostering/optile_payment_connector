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

jQuery(function() {
	// overrides optilevalidation.js so it doesn't require 'submit' button instance
	ValidationController.prototype.Init = function() {
		var self = this;
		this.forms.forEach(function(item) {
			item.Build(self.widget);
		});
	};
	
	ValidationController.prototype.Validate = function(event) {
		var self = this;
		var network = this.widget.find("input[name=\"optile-selected-network\"]:checked").val();
		var form = this.FindFormByFormId(network);
		var data = form.ToJSON();
		var url = form.validationUrl;
		var ok = false;
	
		jQuery.post(url, data, function(resultData, resultStatus, xhr) {
	        ok = resultData.valid;
	        self.ValidationResult.call(self, resultData, response.status);
	        if(ok) { 
	            checkout.setLoadWaiting('payment');
	            var request = new Ajax.Request(
	                payment.saveUrl,
	                {
	                    method:'post',
	                    onComplete: payment.onComplete,
	                    onSuccess: payment.onSave,
	                    onFailure: checkout.ajaxFailure.bind(checkout),
	                    parameters: jQuery.param({payment:{method:'optile'}})
	                }
	            );	        		
	        };            
		}, "json");
		
		return ok;
	};
	
	       
	ValidationController.prototype.ValidationResult = function(resultData, resultStatus, xhr) {
		var network = this.widget.find("input[name=\"optile-selected-network\"]:checked").val();
		var form = this.FindFormByFormId(network);
		form.BindMessages(resultData.messages);
	};           
	       
	if(payment) {
	
		/**
		 * overrides opcheckout.js payment.save() so it doesn't call magento for
		 * validation, instead calls optile javascript api from ValidationController
		 */
		payment.save = payment.save.wrap(function(parentMethod) {
			if(payment.currentMethod == 'optile') {
		        if (checkout.loadWaiting!=false) return;
		        var validator = new Validation(this.form);
		        if (validator.validate()) {
		        	self.controller.Validate();
		        }
			} else {
				parentMethod();
			}
			
		});
		
	    payment.validate = payment.validate.wrap(function(parentMethod) {
	        if(payment.currentMethod == 'optile') {
				self.controller.Validate(null, parentMethod);
				return false;
	        } else {
	            return parentMethod();
	        }
	    });
	
	}
	
	if(Review) {
		 
		Review.prototype.nextStep = Review.prototype.nextStep.wrap(function(parentMethod, transport) {
			if(payment.currentMethod == 'optile') {
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
		Review.prototype.save = Review.prototype.save.wrap(function(parentMethod){
			if(payment.currentMethod == 'optile') {
		        if (checkout.loadWaiting!=false) return;
		        checkout.setLoadWaiting('review');
		        
		        var params;
	            var network = jQuery('#payment_form_optile').find("input[name=\"optile-selected-network\"]:checked").val();                
	            var data = {
	            };
	            
	            var block = network + '-network';
	            element = $(block);
	            if (element) {
	                element.select('input', 'select', 'textarea', 'button').each(function(field) {
	                	if(field.name == 'optile-selected-network') return;
	                	if(field.name == 'operation') return;
	                	if(field.name == 'optile-list-self') return;
	                	data[field.name] = field.value;
	                });
	            }
	            
	            params = jQuery.param(data);
		        	        
		        var operationUrl = jQuery('#' + network + '-operation').val();
		        var listRequestSelfLink = jQuery('#optile-list-self').val();
		        var self = this;
		        
		        jQuery.post(operationUrl, params, function(response) {
		        	var paymentResponse = new OptilePaymentResponseFactory().newOptilePaymentResponse(response);
		        	var handler;
		        	switch(paymentResponse.interaction.code) {
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
	        			console.log("ERROR! can't find interaction code handler");
	        			break;
		        	}
		        	if(handler) {
		        		handler.handle(self);
		        	}
		        	
		        });
		        
			} else {
				parentMethod();
			}
	    });
	}
	
	if(ShippingMethod) {
		/**
		 * Overrides shippingMethod.save() call for the case where we want to refresh
		 * payment method list without sending another LIST request to optile
		 * (required for TRY_OTHER_ACCOUNT, TRY_OTHER_NETWORK response codes).
		 * If there's existing list request, it will reuse it.
		 */
		ShippingMethod.prototype.save = ShippingMethod.prototype.save.wrap(function(parentMethod, listRequestSelfLink) {
			if(listRequestSelfLink) {
		        if (checkout.loadWaiting!=false) return;
		        if (this.validate()) {
		            checkout.setLoadWaiting('shipping-method');
		            var request = new Ajax.Request(
		                this.saveUrl,
		                {
		                    method:'post',
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
	
		    var request = new Ajax.Request(
		        context.saveUrl,
		        {
		            method:'post',
		            parameters: jQuery.param({payment:{method:'optile'}}),
		            onComplete: context.onComplete,
		            onSuccess: this.redirect.bindAsEventListener(this),
		            onFailure: checkout.ajaxFailure.bind(checkout)
		        }
		    );
			
		},
		
		redirect: function(transport) {
	        if (transport && transport.responseText) {
	            try{
	                response = eval('(' + transport.responseText + ')');
	            }
	            catch (e) {
	                response = {};
	            }
	            if (response.redirect) {
	                location.href = response.redirect;
	                return;
	            }
	            if (response.success) {
	                var redirect = this.paymentResponse.redirect;
	                switch(redirect.method) {
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
	            else{
	                var msg = response.error_messages;
	                if (typeof(msg)=='object') {
	                    msg = msg.join("\n");
	                }
	                if (msg) {
	                    alert(msg);
	                }
	            }
	
	            if (response.update_section) {
	                $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
	            }
	
	            if (response.goto_section) {
	                checkout.gotoSection(response.goto_section);
	            }
	        }		
			
		}
	});
	
	var OptileErrorHandler = {
			error: function(msg) {
				alert(msg);
			}
	}
	
	/**
	 * Handles RETRY response code
	 */
	var OptileInteractionCodeHandlerRETRY = Class.create(OptileInteractionCodeHandler, {
		handle: function(context) {
			
			OptileErrorHandler.error(Translator.translate("Error processing payment. Please retry. (Reason: " + 
							this.paymentResponse.interaction.reason + ")"));
			
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

			OptileErrorHandler.error(Translator.translate("Error processing payment. Please retry with different network. (Reason: " + 
					this.paymentResponse.interaction.reason + ")"));			
			
			// simulate shipping method save so it reloads payment section
			checkout.setLoadWaiting(false);		
			shippingMethod.save(this.listRequestSelfLink);
		}
	});
	
	/**
	 * Handles TYR_OTHER_NETWORK response code - same as TRY_OTHER_ACCOUNT
	 */
	var OptileInteractionCodeHandlerTRY_OTHER_NETWORK = Class.create(OptileInteractionCodeHandlerTRY_OTHER_ACCOUNT);
	
	var OptileInteractionCodeHandlerABORT = Class.create(OptileInteractionCodeHandler, {
		handle: function(context) {
	        var redirect = this.paymentResponse.redirect;
	        switch(redirect.method) {
	        case "GET":
	        	window.location.href = redirect.url;
	        	break;
	        case "POST":
	        	// hacky way to do a POST redirect in javascript from stackoverflow
	        	var form = jQuery('<form action="' + redirect.url + '" method="post" style="display:none;">' +
	//        	  '<input type="text" name="api_url" value="' + Return_URL + '" />' +
	        	  '</form>');
	        	jQuery('body').append(form);
	        	form.submit();
	        	break;
	        }
	
		}	
	});
	
	var OptilePaymentResponseFactory = Class.create();
	OptilePaymentResponseFactory.prototype = {
		initialize: function() { },
		newOptilePaymentResponse: function(responseData) {
			return new OptilePaymentResponse(responseData.links, responseData.info, responseData.interaction, responseData.redirect);
		}
	}
	
	var OptilePaymentResponse = Class.create();
	OptilePaymentResponse.prototype = {
		initialize: function(links, info, interaction, redirect) {
			this.links = links;
			this.info = info;
			this.interaction = interaction;
			this.redirect = redirect;
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
	    beforeInitFunc:$H({}),
	    afterInitFunc:$H({}),
	    beforeValidateFunc:$H({}),
	    afterValidateFunc:$H({}),
	    
	    initialize: function(form) {
	        this.form = form;
	    },
	
	    init : function () {
	        var elements = Form.getElements(this.form);
	        var method = null;
	        for (var i=0; i<elements.length; i++) {
	            if (elements[i].name=='optile-selected-network') {
	                if (elements[i].checked) {
	                    method = elements[i].value;
	                }
	            }
	        }
	        if (method) this.switchMethod(method);
	    },
	
	    switchMethod: function(method){
	        if (this.currentMethod && $(this.currentMethod + '-network')) {
	            this.changeVisible(this.currentMethod, true);
	            $(this.currentMethod + '-network').fire('optile-network:switched-off', {network_code : this.currentMethod});
	        }
	        if ($(method + '-network')){
	            this.changeVisible(method, false);
	            $(method + '-network').fire('optile-network:switched', {network_code : method});
	        } else {
	            //Event fix for payment methods without form like "Check / Money order"
	            document.body.fire('optile-network:switched', {network_code : method});
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


