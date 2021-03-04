/**
* Loads checkout form for Ebizcharge.
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Ui/js/model/messageList',
        'mage/translate'
    ],
    function (Component, setPaymentInformationAction, validator, additionalValidators, quote, $, messageList, $t) {
        'use strict';
        var wpConfig = window.checkoutConfig.payment.ebizcharge;
        return Component.extend({
            defaults: {
                template: 'Ebizcharge_Ebizcharge/payment/ebizcharge',
                useSavedCard: true,
                addNewCard: false,
                saveCard: false,
                updateSavedCard: false,
                paymentToken: false,
                ebzc_cust_id: '',
                ebzc_option: '',
                SecondarySort: '',
                selectedCardToken: '',
                requestCC: false,
                has_token: false,
                storeInVault: false,
                useVault: false,
                additional: '',
                storedCards: []
            },
            initVars: function() {
                this.isPaymentProcessing = null;
                this.quoteBaseGrandTotals = quote.totals().base_grand_total;
            },
            
            initObservable: function (e) {
                //debugger;
                //self = this;
				self.name = this;
                this._super().observe([
                    'paymentToken',
                    'selectedCardToken',
                    'cardOwner',
                    'storeInVault',
                    'storedCards',
                    'useSavedCard',
                    'addNewCard',
                    'updateSavedCard',
                    'has_token',
                    'getEbzcCustId'
                ]);
                    
                if (!this.storedCards.length || this.has_token == false) {
                    this.useSavedCard(false);
                }
                
                if (this.storedCards.length && this.has_token) {
                    this.paymentToken(this.storedCards[0].MethodID);
                }
                
                this.initMage();
                this.updateEbizSavedOption();
                return this;
            },
            
            initMage: function(element) {
                // console.log(wpConfig);
                this.has_token = wpConfig.hasToken || false;
                this.ebzc_cust_id = wpConfig.getEbzcCustId || '';
                this.storedCards = wpConfig.storedCards || [];
                this.requestCC = wpConfig.requestCardCode || 0;
                this.saveCard = wpConfig.saveCard || 0;
                this.useVault = wpConfig.useVault || false;
                this.addNewCard(false);
                this.useSavedCard(true);
                this.updateSavedCard(false);
                this.selectedCardToken(false);
            },

            getCode: function() {
                return 'ebizcharge_ebizcharge';
            },

            isActive: function() {
                return true;
            },
            
            getUseVault: function() {
                return this.useVault;
            },

			deletePaymentMethod: function() {
                //console.log(this.getData());				
                var cid = this.getEbzcCustId();
                var mid = this.selectedCardToken();
                var deleteURL = wpConfig.getDeleteURL;
                if (mid && deleteURL) {
					$.post(deleteURL,
						{cid: cid,mid: mid},
                        function(data, textStatus, jqXHR)
                        {
                             //console.log("Success");
                        }).fail(function(jqXHR, textStatus, errorThrown) 
                        {
                             //console.log("Failed"+cid+mid); 
                        });
                    location.reload();
                } else {
                     //console.log("Missing info"); 
                }             
            },

            getRequestCardCode: function() {
                if (this.requestCC == 1) {
                    return true;
                }

                return false;
            },

            getSaveCard: function() {
                if (this.saveCard == 1) {
                    return false;
                }

                return true;
            },
			
			// Get all saved cards (List or null)
			getAllSavedCards: function() 
			{
				this.storedCards = wpConfig.storedCards;
				if (this.storedCards == null) {
                    return false;
                } else {
                	return true;
				}	
				
            },
			// Count for all saved cards
			getCountCards: function() 
			{
				this.storedCards = wpConfig.storedCards;
				//return this.storedCards.length;
				if (!this.storedCards.length || this.storedCards.length == '' || this.storedCards.length == null) {
                    return false;
                } else {
                	return true;
				}	
				
            },
            
            /**
             * @returns {*}
             */
            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'cc_owner': $('#' + this.getCode() + '_cc_owner').val(),
                        'ebzc_avs_street': $('#' + this.getCode() + '_avs_street').val(),
                        'ebzc_avs_zip': $('#' + this.getCode() + '_avs_zip').val(),
                        'ebzc_option': this.ebzc_option,
                        'ebzc_method_id': this.selectedCardToken(),
                        'ebzc_cust_id': this.getEbzcCustId(),
                        'ebzc_save_payment': this.storeInVault(),
                        'paymentToken': this.paymentToken()
                    }
                };
            },
            
            /**
             * Prepare and process payment information
             */
            preparePayment: function () {
                
                
                if (!this.getUseVault()) {
                    this.updateEbizNewOption();
                }

                // this.messageContainer.clear();
                // this.messageContainer.addErrorMessage({
                //     message: "Please select saved payment method."});

                if (this.validate()) {

                    var self = this;
                        
                    this.messageContainer.clear();
                    self.placeOrder();

                    // console.log("");
                }
            },
            
            getStoredCards: function() {
                return this.storedCards;
            },
            
            updateEbizSavedOption: function() {
                this.ebzc_option = 'saved'; 
                this.addNewCard(false);
                this.updateSavedCard(false);
                this.useSavedCard(true);
                this.paymentToken(true);
            },
            
            updateEbizNewOption: function() {
                this.ebzc_option = 'new';
                this.useSavedCard(false);
                this.updateSavedCard(false);
                this.addNewCard(true);
                this.paymentToken(false);
            },

            updateEbizUpdateOption: function() {
                this.ebzc_option = 'update';
                this.useSavedCard(false);
                this.addNewCard(false);
                this.updateSavedCard(true);
                this.paymentToken(true);
            },
            
            getEbzcCustId: function() {
                return this.ebzc_cust_id;
            },
            
            validate: function() {
                //return true;
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            }

        });
    }
);
