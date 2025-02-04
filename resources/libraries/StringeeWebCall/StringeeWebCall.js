/*
    StringeeWebCall - V1
    Author: Hieu Nguyen
    Date: 2020-04-16
    Purpose: provide util function for button free call at public website
    Dependencies: jquery.min.js, socket.io.js, StringeeSDK.js, StringeeSoftPhone-lastest.js
*/

window.StringeeWebCall = class {

    constructor (phoneNumber, hotlineNumber, customerInfo = {}) {
        this.phoneNumber = this._formatPhoneNumber(phoneNumber);
        this.hotlineNumber = this._formatPhoneNumber(hotlineNumber);
        this.customerInfo = customerInfo;
        this._init();
    }

    _init () {
        var thisInstance = this;

        var config = {
            showMode: 'none',
            arrowDisplay: 'none',
            fromNumbers: [{ alias: 'Hotline', number: this.hotlineNumber }],
            askCallTypeWhenMakeCall: false,
            appendToElement: null,
            makeAndReceiveCallInNewPopupWindow: false
        };

        StringeeSoftPhone.init(config);

        this._getToken(function (token) {
            StringeeSoftPhone.connect(token);
        });

        StringeeSoftPhone.on('requestNewToken', function () {
            thisInstance._triggerEventCallback('REQUEST_NEW_TOKEN');
        });

        StringeeSoftPhone.on('authen', function (res) {
            if (res.message == 'SUCCESS') {
                thisInstance._triggerEventCallback('AUTH_SUCCESS', res);
            }
            else {
                thisInstance._triggerEventCallback('AUTH_ERROR', res);
            }
        });

        StringeeSoftPhone.on('disconnect', function () {
            thisInstance._triggerEventCallback('DISCONNECTED');
        });

        StringeeSoftPhone.on('signalingstate', function (state) {
            thisInstance._triggerEventCallback(state.reason.toUpperCase(), state);
        });
    }

    isConnected () {
        return StringeeSoftPhone.connected;
    }

    _formatPhoneNumber (phoneNumber) {
        if (phoneNumber[0] === '0') {
            phoneNumber = '84' + phoneNumber.substr(1);
        }

        return phoneNumber;
    }

    _getToken (callback) {
        var requestUrl = 'https://v1.stringee.com/dev_urls/access_token/access-token-for-btncall.php?phone_number=' + this.phoneNumber;

        jQuery.ajax({
            url: requestUrl,
            method: 'POST',
            dataType: 'JSON',
            success: function (data) {
                if (data.message == 'Success') {
                    callback(data.access_token);
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    makeCall () {
        if (!this.isConnected()) return;
        console.log('StringeeWebCall::makeCall', this.phoneNumber, this.customerInfo);

        StringeeSoftPhone.makeCall(this.phoneNumber, this.hotlineNumber, function (res) {
            console.log('StringeeSoftPhone::makeCall', res);
        });
    }

    triggerInCallKeyPress (key) {
        console.log('StringeeWebCall::triggerInCallKeyPress', key);
        StringeeSoftPhone._iframe.contentWindow.stringeePhone.keypadKeyDtmfPress(key);
    }
    
    hangupCall () {
        if (!StringeeSoftPhone.connected) return;
        console.log('StringeeWebCall::hangupCall');
        StringeeSoftPhone.hangupCall();
    }

    onStateChange (callback) {
        this.eventCallback = callback;
    }

    _triggerEventCallback (eventName, param) {
        console.log('StringeeWebCall::' + eventName, param);

        if (typeof this.eventCallback == 'function') {
            if (param) {
                this.eventCallback(eventName, param);
            } 
            else {
                this.eventCallback(eventName);
            }
        }
        else {
            console.log('StringeeWebCall:: No event callback function privided!');
        }
    }
};