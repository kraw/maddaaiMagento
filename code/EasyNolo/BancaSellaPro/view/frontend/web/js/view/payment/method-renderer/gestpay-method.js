/**
 * Created by maintux on 22/01/17.
 */
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert',
        'Magento_Ui/js/modal/modal',
        window.checkoutConfig.payment.easynolo_bancasellapro.base_url + '/pagam/JavaScript/js_GestPay.js'
    ],
    function ($,
              Component,
              setPaymentMethodAction,
              creditCardValidators,
              additionalValidators,
              quote,
              customerData,
              url,
              fullScreenLoader,
              alert,
              modal) {
        'use strict';
        return Component.extend({

            redirectAfterPlaceOrder: false,
            gestpayIframeError: false,
            transKey: null,
            modal3d: null,

            defaults: {
                template: 'EasyNolo_BancaSellaPro/payment/gestpay-form',
                creditCardUserName: '',
                creditCardUserEmail: ''
            },

            initObservable: function () {
                this._super().observe(['creditCardUserName', 'creditCardUserEmail']);

                window.alternativePaymentMethodChange = function() {
                    var select = jQuery('#alternative-payments-select');
                    if (select && select.length) {
                        jQuery('#alternative-payments > div').hide();

                        if (select.val()) {
                            jQuery('#credit-card-payment-wrapper').hide();
                            jQuery('#alternative-payments > div#alternative-payment-'+select.val()).show();
                        } else {
                            jQuery('#credit-card-payment-wrapper').show();
                        }
                    }
                };

                this.GestPayExternalClass = GestPay;
                return this;
            },

            getData: function () {
                var parent = this._super();
                var additionalData = {};
                additionalData['cc_user_name'] = this.creditCardUserName();
                additionalData['cc_user_email'] = this.creditCardUserEmail();

                if ($('#alternative-payments-select:visible').length && $('#alternative-payments-select:visible').val()) {
                    var serial = $('#alternative-payments :input:visible').serializeArray();
                    additionalData['alternative_payment'] = JSON.stringify(serial);
                }

                return $.extend(true, parent, {
                    'additional_data': additionalData
                });
            },

            getCode: function () {
                return 'easynolo_bancasellapro';
            },

            isActive: function () {
                return $('#easynolo_bancasellapro-form').is(':visible');
            },

            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            handlePaymentError: function (Result) {
                this.gestpayIframeError = true;
                this.transKey = null;
                this.isPlaceOrderActionAllowed(true);
                fullScreenLoader.stopLoader();
                console.log(Result);
                console.log('Error during payment process.');
                alert({
                    title: "Error",
                    content: "Error during payment process. Please check the provided data and try again.",
                    autoOpen: true,
                    clickableOverlay: false,
                    focus: ""
                });
            },

            handlePaymentSuccess: function (Result) {
                this.gestpayIframeError = false;
                console.log('Payment successfully processed!');
                var params = [];
                params.push("a=" + window.checkoutConfig.payment.easynolo_bancasellapro.shop_login);
                params.push("b=" + Result.EncryptedString);
                window.location.replace(url.build(window.checkoutConfig.payment.easynolo_bancasellapro.success_redirect_url) + '?' + params.join('&'));
            },

            handle3dConfirmation: function (Result) {
                this.transKey = Result.TransKey;
                var params = [];
                params.push("a=" + window.checkoutConfig.payment.easynolo_bancasellapro.shop_login);
                params.push("b=" + Result.VBVRisp);
                params.push("c=" + encodeURI(url.build(window.checkoutConfig.payment.easynolo_bancasellapro['3d_auth_redirect_url']) + '?' + (new Date().getTime())));
                var finalUrl = window.checkoutConfig.payment.easynolo_bancasellapro['3d_auth_page_url'] + '?' + params.join('&');
                console.log(finalUrl);
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    clickableOverlay: false,
                    title: '3D Secure Authorization',
                    buttons: [],
                    opened: function ($Event) {
                        $('.modal-header button.action-close', $Event.srcElement).hide();
                    },
                    keyEventHandlers: {
                        escapeKey: function () {
                            return;
                        }
                    }
                };
                $('#3d-modal-iframe').attr('src', finalUrl);
                $('#3d-modal-iframe').on('load', function () {
                    fullScreenLoader.stopLoader();
                });
                this.modal3d = modal(options, $('#3d-modal'));
                $('#3d-modal').modal('openModal');
            },

            sendPaRes: function (paRes) {
                fullScreenLoader.startLoader();
                var self = this;
                GestPay.SendPayment({PARes: paRes, TransKey: this.transKey}, function (Result) {
                    if (parseInt(Result.ErrorCode, 10) == 0) {
                        self.handlePaymentSuccess(Result);
                    } else {
                        self.handlePaymentError(Result);
                    }
                });
            },

            _sendPaymentIframe: function () {
                var data = this.getData();
                var payload = {
                    CC: data.additional_data.cc_number,
                    EXPMM: '0'.substring(0, 2 - data.additional_data.cc_exp_month.length) + data.additional_data.cc_exp_month,
                    EXPYY: data.additional_data.cc_exp_year.slice(-2),
                    CVV2: data.additional_data.cc_cid,
                    Name: data.additional_data.cc_user_name,
                    Email: data.additional_data.cc_user_email
                };
                this._sendPayment(payload);
            },

            _sendPayment: function (payload) {
                var self = this;
                GestPay.SendPayment(payload, function (Result) {
                    if (parseInt(Result.ErrorCode, 10) == 0) {
                        self.handlePaymentSuccess(Result);
                    } else {
                        if (parseInt(Result.ErrorCode, 10) == 8006) {
                            self.handle3dConfirmation(Result);
                        } else {
                            self.handlePaymentError(Result);
                        }
                    }
                });
            },

            sendPayment: function () {
                var data = this.getData();
                var payload = {
                    CC: data.additional_data.cc_number,
                    EXPMM: '0'.substring(0, 2 - data.additional_data.cc_exp_month.length) + data.additional_data.cc_exp_month,
                    EXPYY: data.additional_data.cc_exp_year.slice(-2),
                    CVV2: data.additional_data.cc_cid,
                    Name: data.additional_data.cc_user_name,
                    Email: data.additional_data.cc_user_email
                };
                this._sendPayment(payload);
            },

            sendPaymentIframe: function () {
                var iframe = $('iframe#GestPay');
                if (iframe.length > 0) {
                    iframe.remove();
                }
                var self = this;
                $.getJSON(window.checkoutConfig.payment.easynolo_bancasellapro.get_encrypted_string_url, function (data) {
                    if (jQuery('#alternative-payments-select').length && jQuery('#alternative-payments-select').val()) {
                        window.location.replace(url.build(window.checkoutConfig.payment.easynolo_bancasellapro.redirect_url));
                    } else {
                        GestPay.CreatePaymentPage(
                            window.checkoutConfig.payment.easynolo_bancasellapro.shop_login,
                            data.EncString,
                            function (Result) {
                                if (parseInt(Result.ErrorCode, 10) == 10) {
                                    self._sendPaymentIframe();
                                } else {
                                    alert(Result);
                                }
                            }
                        );
                    }
                });
            },

            sendPaymentWithAlternative: function (tokenID) {
                window.location.replace(url.build(window.checkoutConfig.payment.easynolo_bancasellapro.pay_using_token_url) + '?token=' + tokenID);
            },


            sendPaymentWithToken: function (tokenID) {
                window.location.replace(url.build(window.checkoutConfig.payment.easynolo_bancasellapro.pay_using_token_url) + '?token=' + tokenID);
            },

            placeOrder: function (data, event) {
                if (!$('#easynolo_bancasellapro-form').is(':visible')) {
                    return this._super();
                }

                if (!window.checkoutConfig.payment.easynolo_bancasellapro.is_iframe_enabled)
                    this._super();
                else {
                    if (this.gestpayIframeError) {
                        // Order already placed, so we have just to resend payment through the iframe
                        if (event) event.preventDefault();
                        if (this.validate() && additionalValidators.validate()) {
                            fullScreenLoader.startLoader();
                            this._sendPaymentIframe();
                        }
                        return true;
                    } else {
                        this._super();
                    }
                    return false;
                }
            },

            afterPlaceOrder: function () {
                if (!$('#easynolo_bancasellapro-form').is(':visible')) {
                    return this._super();
                }

                if (!window.checkoutConfig.payment.easynolo_bancasellapro.is_iframe_enabled) {
                    window.location.replace(url.build(window.checkoutConfig.payment.easynolo_bancasellapro.redirect_url));
                } else {
                    if ($('#credit-cards-wrapper').is(':visible') && $('input[name="bancasella_iframe[token]"]:checked').val()) {
                        this.sendPaymentWithToken($('input[name="bancasella_iframe[token]"]:checked').val());
                    } else {
                        this.sendPaymentIframe();
                    }
                }
            }
        });
    }
);