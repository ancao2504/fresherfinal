/*
*   CallCenterClient.js
*   Author: Hieu Nguyen
*   Date: 2018-10-05
*   Purpose: To handle events from Call Center
*/

var CallCenterClient = {
	socket: null,
	user: null,

	init: function () {
		// Init socket client only if user is logged in
		if (!this.isAtLoginPage()) {
			// Check if socket.io is ready
			if (typeof io !== 'undefined') {
				this.user = NotificationHelper.getUserInfo();

				// User with empty ext number is not valid
				if (this.user.ext_number == null || this.user.ext_number == '') {
					this.log('User has no extension number!');
					return false;
				}

				// Init socket client bridge server url is available
				if (_CALL_CENTER_BRIDGE_SERVER_URL != '') {
					this.initSocketClient(_CALL_CENTER_BRIDGE_SERVER_URL);
				}
			}

			// Init web phone when the web phone token is available
			if (_CALL_CENTER_WEB_PHONE_TOKEN && _CALL_CENTER_GATEWAY_NAME != 'Stringee') {
				this.initWebPhone(_CALL_CENTER_WEB_PHONE_TOKEN);
			}
		}
	},

	// Init the real-time client.
	initSocketClient: function (serverUrl) {
		var ssl = serverUrl.indexOf('wss') >= 0;
		
		this.socket = io.connect(serverUrl, { 
			path: '/socket.io',
			query: `domain=${_CALL_CENTER_BRIDGE_ACCESS_DOMAIN}&access_token=${_CALL_CENTER_BRIDGE_ACCESS_TOKEN}`,
			transports: ['websocket'],
			secure: ssl,
			reconnect: true,
			rejectUnauthorized: false
		});

		this.socket.on('connect', (msg) => {
			this.log('Socket is connected.');
		});

		this.socket.on('error', (msg) => {
			this.log('Socket is error!');
		});

		this.socket.on('reconnecting', (msg) => {
			this.log('Socket is reconnecting!');
		});

		// When a message is comming
		this.socket.on('message', (msg) => {
			this.log(msg);

			if (msg.command == 'MUTE_CALL') {
				this.webPhone.muteIncomingCall(msg.call_id);
				return;
			}

			if (msg.state == 'CALL_LOG_SAVED') {
				this.handleCallLogSavedEvent(msg);
				return;
			}

			if (msg.state == 'ADDITION_INFO') {
				this.handleAdditionInfoEvent(msg);
				return;
			}

			// Added by Vu Mai on 2022-10-19 to handle data changed event
			if (msg.state == 'DATA_CHANGED') {
				this.handleDataChangedEvent(msg);
				return;
			}
			// End Vu Mai

			// For call event handled by WebPhone
			var callId = msg.call_id;
			
			if (this.webPhone && callId.indexOf('_transferred') > 0) {
				callId = callId.replace('_transferred', '');                    // Remove suffix for transferred call
				this.webPhone.currentCallExtraData.is_transferred_call = true;  // Mark this call as transferred
			}

			// For Stringee WebPhone
			if (this.webPhone && _CALL_CENTER_GATEWAY_NAME == 'Stringee') {
				// Tell the call popup that it can handle the phone call using WebPhone
				if (callId == this.webPhone.currentCall.callId) {
					msg.handled_by_webphone = true;
				}

				// Mute the ring tone for all tabs in case user open mutiple tabs
				if (msg.state == 'REJECTED') {
					this.webPhone.muteIncomingCall(msg.call_id);
				}
			}

			// For vCS WebClient
			if (this.webPhone && _CALL_CENTER_GATEWAY_NAME == 'VCS') {
				// Tell the call popup that it can handle the phone call using WebPhone
				if (callId == this.webPhone.currentCall.call_id) {
					msg.handled_by_webphone = true;
				}
			}

			// Check if session is alive before passing new state to call popup
			vtUtils.checkSession();

			// Pass the new state to Call Popup
			CallPopup.newState(msg);
		});

		this.socket.on('disconnect', () => {
			this.log('Socket is disconnected.');
		});
	},

	initWebPhone(token) {
		var thisInstance = this;

		if (_CALL_CENTER_GATEWAY_NAME == 'Stringee') {
			thisInstance.webPhone = new StringeeWebPhone(token, _CALL_CENTER_DEFAULT_OUTBOUND_HOTLINE);
		}

		if (_CALL_CENTER_GATEWAY_NAME == 'VCS') {
			thisInstance.webPhone = new VCSWebClient();
		}
	},

	handleAdditionInfoEvent: function (data) {
		if (data.addition_info) {
			CallPopup.newState(data);
		}
	},

	// Added by Vu Mai on 2022-10-19 to call new state to popup when data changed
	handleDataChangedEvent(data) {
		if (data) {
			CallPopup.newState(data);
		}
	},

	handleCallLogSavedEvent: function (data) {
		// An outbound call log saved
		if (data.direction == 'Outbound') {
			// Refresh the planned calls widget as the corresponding call in this list may already held
			if ($('.planned-calls-widget')[0] != null) {
				$('.planned-calls-widget').closest('.dashboardWidget').find('[name="drefresh"]').trigger('click');
			}

			// Refresh the missed calls widget as the call may linked to one of the customers in this list
			if ($('.missed-calls-widget')[0] != null) {
				$('.missed-calls-widget').closest('.dashboardWidget').find('[name="drefresh"]').trigger('click');
			}   

			// Refresh the activities related list if user is forcus on the corresponding customer detailview
			if ((app.getModuleName() == 'Contacts' || app.getModuleName() == 'Leads') && app.getViewName() == 'Detail') {
				if (data.customer_id == app.getRecordId() && $('.tab-item.active').data('module') == 'Calendar') {
					$('.tab-item.active').trigger('click');
				}
			}
		}
	},

	triggerMuteCall: function (callId) {
		if (this.socket) {
			// Notify other tabs to mute call
			this.socket.emit('message', {
				call_id: callId,
				receiver_id: this.user.id,
				command: 'MUTE_CALL'
			});
		}
	},

	notifyCompletedCall: function (callId) {
		if (this.socket) {
			// Notify other tabs for the completed call
			this.socket.emit('message', {
				call_id: callId,
				receiver_id: this.user.id,
				state: 'COMPLETED'
			});
		}
	},

	isAtLoginPage() {
		return _META.module == 'Users' && _META.view == 'Login';
	},

	log: function (message, params) {
		var timestamp = new Date().toLocaleString();

		if (params) {
			console.log(timestamp, '[CallCenter] ', message, params);
		}
		else {
			console.log(timestamp, '[CallCenter] ', message);
		}
	}
};

class BaseWebPhone {

	constructor (token, hotlineNumber) {
		this.token = token;
		this.hotlineNumber = this._formatPhoneNumber(hotlineNumber);
		this.eventCallback = null;
		this.currentCall = null;
		this.currentCallExtraData = null;
	}

	onStateChange (callback) {
		this.eventCallback = callback;
	}

	_triggerEventCallback (eventName, param) {
		CallCenterClient.log('WebPhone::' + eventName, param);

		if (typeof this.eventCallback == 'function') {
			if (param) {
				this.eventCallback(eventName, param);
			} 
			else {
				this.eventCallback(eventName);
			}
		}
		else {
			CallCenterClient.log('WebPhone:: No event callback function privided!');
		}
	}

	_triggerCallPopup(newState) {
		CallCenterClient.log('WebPhone::callPopupState', newState);
		CallPopup.newState(newState);
	}

	_cleanupPhoneNumber(phoneNumber) {
		var specialChars = ['(', ')', '+', '-', ' '];

		specialChars.forEach(function (char) {
			phoneNumber = phoneNumber.replace(char, '');
		});

		phoneNumber = phoneNumber.trim();
		return phoneNumber;
	}

	_formatPhoneNumber (phoneNumber) {
		phoneNumber = this._cleanupPhoneNumber(phoneNumber);

		if (phoneNumber[0] === '0') {
			phoneNumber = '84' + phoneNumber.substr(1);
		}

		if (phoneNumber.substr(0, 2) != '84') {
			phoneNumber = '84' + phoneNumber.substr(1);
		}

		return phoneNumber;
	}

	_requestNewToken(callback) {
		var params = {
			module: 'PBXManager',
			action: 'CallPopupAjax',
			mode: 'getWebPhoneToken'
		};

		app.request.post({ data: params })
		.then(function (err, res) {
			app.helper.hideProgress();

			if (err || !res || !res.success) {
				CallCenterClient.log('BaseWebPhone::_requestNewToken Error', err);
				return;
			}

			if (typeof callback == 'function') {
				callback(res.token);
			}
		});
	}
}

// Stringee WebPhone
class StringeeWebPhone extends BaseWebPhone {

	constructor (token, hotlineNumber) {
		super(token, hotlineNumber);
		this._init();
		this._resetCurrentCall();
	}

	_init () {
		var thisInstance = this;

		// Init WebPhone
		var config = {
			showMode: 'none',
			arrowDisplay: 'none',
			fromNumbers: [{ alias: 'Hotline', number: this.hotlineNumber }],
			askCallTypeWhenMakeCall: false,
			appendToElement: null,
			makeAndReceiveCallInNewPopupWindow: false
		};

		StringeeSoftPhone.init(config);

		// Wait until the client ready to connect
		vtUtils.doWhen(
			function () {
				return StringeeSoftPhone._ready;
			}, 
			function () {
				CallCenterClient.log(thisInstance.constructor.name + '::connecting');
				StringeeSoftPhone.connect(thisInstance.token);

				// Use custom ringtone if user uploaded a new one
				if (_CALL_CENTER_WEB_PHONE_CUSTOM_RING_TONE_URL) {
					// StringeeSoftPhone.setRingTone(_CALL_CENTER_WEB_PHONE_CUSTOM_RING_TONE_URL);
					StringeeSoftPhone._iframe.contentWindow.stringeePhone.ringtonePlayer.src = _CALL_CENTER_WEB_PHONE_CUSTOM_RING_TONE_URL;
				}
			}
		);

		// Init event handlers
		StringeeSoftPhone.on('requestNewToken', function () {
			thisInstance._triggerEventCallback('REQUEST_NEW_TOKEN');

			// Request new token and then re-connect
			thisInstance._requestNewToken(function (token) {
				_CALL_CENTER_WEB_PHONE_TOKEN = token;
				thisInstance.token = token;
				StringeeSoftPhone.connect(token);
			});
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

		StringeeSoftPhone.on('signalingstate', function (data) {
			var state = data.reason.toUpperCase().replace(' ', '_');
			state = (state != 'ENDED') ? 'CALL_' + state : 'CALL_HANGUP';
			thisInstance._triggerEventCallback(state, data);

			// Pass the new state to Call Popup
			if (state == 'CALL_CALLING') {
				// Nothing to do now
			}

			if (state == 'CALL_RINGING') {
				app.helper.hideProgress();  // Hide process after the outbound call is made and the customer phone is ringing

				/*var msg = {
					call_id: thisInstance.currentCall.callId,
					state: 'RINGING',
					direction: thisInstance.currentCallExtraData.direction,
					customer_number: thisInstance.currentCallExtraData.customer_number,
					customer_id: thisInstance.currentCallExtraData.customer_id,
					call_log_id: thisInstance.currentCallExtraData.call_log_id
				}

				thisInstance._triggerCallPopup(msg);*/
			}

			/*if (state == 'CALL_ANSWERED') {
				var msg = {
					call_id: thisInstance.currentCall.callId, 
					state: 'ANSWERED'
				};

				thisInstance._triggerCallPopup(msg);
			}*/

			// Work arround here to force the call popup into hangup state when the inbound call is timed-out for each agent but no webhook hangup state returned yet
			if (state == 'CALL_ENDED' || state == 'CALL_HANGUP' || state == 'CALL_BUSY_HERE') {
				if (thisInstance.currentCallExtraData.direction == 'INBOUND') {
					var msg = {
						call_id: thisInstance._getCallIdForNewStateMsg(), 
						state: 'HANGUP'
					};

					thisInstance._triggerCallPopup(msg);
					thisInstance._resetCurrentCall();
				}
			}
		});

		StringeeSoftPhone.on('beforeMakeCall', function (call, callType) {
			thisInstance._triggerEventCallback('MAKE_CALL', [call, callType]);

			// Setup current call info
			thisInstance.currentCall = call;    // Call ID empty now but it will be will be available when the call is making succesfully
			
			// Call object is StringeeCall and immutable so we have to store extra data in another variable
			thisInstance.currentCallExtraData.direction = 'OUTBOUND';
			thisInstance.currentCallExtraData.customer_number = call.toNumber;
		});

		StringeeSoftPhone.on('makeOutgoingCallResult', function (res) {
			thisInstance._triggerEventCallback('MAKE_CALL_RESULT', res);

			// Make call error
			if (res.r !== 0) {
				app.helper.hideProgress();
				var message = app.vtranslate('PBXManager.JS_MAKE_CALL_ERROR_MSG');
				var errorMsg = JSON.parse(res.customDataFromYourServer);

				if (errorMsg.msg == 'AGENT_NOT_FOUND_OR_IN_ANOTHER_CALL') {
					message = app.vtranslate('PBXManager.JS_MAKE_CALL_IN_BREAK_TIME_ERROR_MSG');
				}

				app.helper.showErrorNotification({ message: message });
				return;
			}
		});

		StringeeSoftPhone.on('incomingCall', function (call) {
			thisInstance._triggerEventCallback('INCOMING_CALL', call);

			// Do nothing for call from classic click-to-call event
			if (call.fromAlias.indexOf('callout_') === 0) return;

			// Setup current call info
			thisInstance.currentCall = call;
			thisInstance.currentCallExtraData.direction = 'INBOUND';
			thisInstance.currentCallExtraData.customer_number = call.fromNumber;

			/*// Pass the new state to Call Popup
			var msg = {
				call_id: call.callId,
				state: 'RINGING',
				direction: 'INBOUND',
				customer_number: call.fromNumber
			}

			// The call is from free call button
			if (call.fromNumber.indexOf('btncall_') >= 0) {
				msg.customer_number = call.fromNumber.replace('btncall_', '');
				msg.from_free_call_btn = true;
			}

			thisInstance._triggerCallPopup(msg);*/
		});

		StringeeSoftPhone.on('setAutoPickCall', function (res) {});
		StringeeSoftPhone.on('displayModeChange', function (res) {});
	}

	_getCallIdForNewStateMsg() {
		if (this.currentCallExtraData.is_transferred_call) {
			return this.currentCall.callId + '_transferred';
		}

		return this.currentCall.callId;
	}

	_resetCurrentCall () {
		this.currentCall = {};
		this.currentCallExtraData = {
			direction: '',
			customer_number: '',
			call_log_id: '',
			is_transferred_call: false,
		};
	}

	isConnected () {
		var connected = StringeeSoftPhone.connected;
		CallCenterClient.log(this.constructor.name + '::isConnected', connected);
		return connected;
	}

	makeCall (hotlineNumber = '', customerNumber, customerId, callLogId) {
		if (!this.isConnected()) return;
		app.helper.showProgress();  // Show progress when making outbound calll
		var thisInstance = this;
		var outboundHotline = (hotlineNumber != '') ? hotlineNumber : this.hotlineNumber;
		CallCenterClient.log(this.constructor.name + '::makeCall', [outboundHotline, customerNumber, customerId, callLogId]);

		StringeeSoftPhone.makeCall(outboundHotline, this._formatPhoneNumber(customerNumber), function (res) {
			CallCenterClient.log(thisInstance.constructor.name + '::makeCall', res);

			// Set customer id for outbound call
			thisInstance.currentCallExtraData.customer_id = customerId;
			thisInstance.currentCallExtraData.call_log_id = callLogId;
		});
	}

	rejectCall () {
		if (!this.isConnected() || this.currentCallExtraData.direction == 'OUTBOUND') return;
		CallCenterClient.log(this.constructor.name + '::rejectCall');

		this.currentCall.reject(function (res) {
			console.log('reject res', res);
		});

		this.muteIncomingCall();
	}

	answerCall () {
		if (!this.isConnected()) return;
		CallCenterClient.log(this.constructor.name + '::answerCall');
		StringeeSoftPhone.answerCall();

		// Pass the new state to Call Popup
		/*var msg = {
			call_id: this._getCallIdForNewStateMsg(), 
			state: 'ANSWERED'
		};

		this._triggerCallPopup(msg);*/
	}
	
	hangupCall () {
		if (!StringeeSoftPhone.connected) return;
		CallCenterClient.log(this.constructor.name + '::hangupCall');
		StringeeSoftPhone.hangupCall();

		// Worarround for some incoming call not stop ringing after hangup
		if (this.currentCallExtraData.direction == 'INBOUND') {
			this.muteIncomingCall();
		}

		// Pass the new state to Call Popup (for case ringing then hangup)
		/*var msg = {
			call_id: this._getCallIdForNewStateMsg(), 
			state: 'HANGUP'
		};

		this._triggerCallPopup(msg);*/
		this._resetCurrentCall();
	}

	muteIncomingCall () {
		if (!this.currentCall.callId) return;
		StringeeSoftPhone._iframe.contentWindow.stringeePhone.stopRingtoneIncomingCall();
	}
}

// vCS WebClient (it will be a WebPhone when the WebRTC is integrated)
class VCSWebClient extends BaseWebPhone {

	constructor () {
		super('-', '-');    // Bypass the BaseWebPhone constructor
		this._init();
		this._resetCurrentCall();
	}

	_init () {
		let thisInstance = this;
		thisInstance.vcsClient = new pbx_3CX();
		thisInstance.vcsClient.Start();

		thisInstance.vcsClient.Event_Ringing_3CX = function (otherPartyNumber, currentExtension) {
			CallCenterClient.log('Inbound Call Ringing', [otherPartyNumber, currentExtension]);
			thisInstance._triggerEventCallback('INBOUND_RINGING', [otherPartyNumber, currentExtension]);
			thisInstance._forwardEventToWebhook(otherPartyNumber, currentExtension);
			thisInstance.currentCall.call_id = otherPartyNumber.CallID;
		}

		thisInstance.vcsClient.Event_Dialing_3CX = function (otherPartyNumber, currentExtension) {
			CallCenterClient.log('Outbound Call Dialing', [otherPartyNumber, currentExtension]);
			thisInstance._triggerEventCallback('OUTBOUND_DIALING', [otherPartyNumber, currentExtension]);
			thisInstance._forwardEventToWebhook(otherPartyNumber, currentExtension);
			thisInstance.currentCall.call_id = otherPartyNumber.CallID;
		}

		thisInstance.vcsClient.Event_Connected_3CX = function (otherPartyNumber, currentExtension) {
			CallCenterClient.log('Call Connected', [otherPartyNumber, currentExtension]);
			thisInstance._triggerEventCallback('CALL_CONNECTED', [otherPartyNumber, currentExtension]);
			thisInstance._forwardEventToWebhook(otherPartyNumber, currentExtension);
		}

		thisInstance.vcsClient.Event_Ended_3CX = function (otherPartyNumber, currentExtension){
			CallCenterClient.log('Call Ended', [otherPartyNumber, currentExtension]);
			thisInstance._triggerEventCallback('CALL_ENDED', [otherPartyNumber, currentExtension]);
			thisInstance._forwardEventToWebhook(otherPartyNumber, currentExtension);
			thisInstance._resetCurrentCall();
		}

		thisInstance.vcsClient.Event_ChangeProfile_3CX = function (profileId, currentExtension) {}
		thisInstance.vcsClient.Event_ChangeIsHold_3CX = function (isHold, currentExtension) {}
		thisInstance.vcsClient.Event_ChangeIsMute_3CX = function (isMuted, currentExtension) {}
	}

	_forwardEventToWebhook (otherPartyNumber, currentExtension) {
		$.ajax({
			url: 'webhook.php?name=VCSConnector',
			type: 'POST',
			data: {
				otherPartyNumber,
				currentExtension
			},
			success: function (res) {
				CallCenterClient.log('Forward Event Success');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				CallCenterClient.log('Forward Event Error', xhr.responseText);
			},
		});
	}

	_resetCurrentCall () {
		this.currentCall = {};
		this.currentCallExtraData = {
			direction: '',
			customer_number: '',
			call_log_id: '',
			is_transferred_call: false,
		};
	}

	makeCall (hotlineNumber = '', customerNumber, customerId, callLogId) {
		CallCenterClient.log(this.constructor.name + '::makeCall', [hotlineNumber, customerNumber, customerId, callLogId]);
		this.vcsClient.Send_MakeCall_3CX(customerNumber);
		this.currentCallExtraData.direction = 'OUTBOUND';
		this.currentCallExtraData.customer_id = customerId;
		this.currentCallExtraData.call_log_id = callLogId;
	}

	answerCall () {
		CallCenterClient.log(this.constructor.name + '::answerCall');
		this.vcsClient.Send_ActiveCall_3CX();
	}
	
	hangupCall () {
		CallCenterClient.log(this.constructor.name + '::hangupCall');
		this.vcsClient.Send_DropCall_3CX();	// Hangup connected call
		this._resetCurrentCall();
	}

	transferCall (targetExt) {
		CallCenterClient.log(this.constructor.name + '::transferCall', [targetExt]);
		this.vcsClient.Send_Transfer_3CX(targetExt);
	}
}

jQuery(function ($) {
	CallCenterClient.init();
});

// Stringee WebPhone need to be init outside jquery ready
if (!CallCenterClient.isAtLoginPage() && _CALL_CENTER_GATEWAY_NAME == 'Stringee' && _CALL_CENTER_WEB_PHONE_TOKEN) {
	CallCenterClient.initWebPhone(_CALL_CENTER_WEB_PHONE_TOKEN);
}