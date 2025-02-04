/*
	SocialChatClient.js
	Author: Hieu Nguyen
	Date: 2021-01-06
	Purpose: to register real-time client for social chat
*/

var SocialChatClient = {
	socket: null,
	user: null,

	init: function () {
		// Check if socket.io is ready
		if (typeof io !== 'undefined') {
			this.user = NotificationHelper.getUserInfo();

			// Init socket client bridge server url is available
			if (_CHATBOX_BRIDGE_SERVER_URL != '') {
				this.initSocketClient(_CHATBOX_BRIDGE_SERVER_URL);
			}
		}
	},

	// Init the real-time client
	initSocketClient: function (serverUrl) {
		var ssl = serverUrl.indexOf('wss') >= 0;

		this.socket = io.connect(serverUrl, { 
			path: '/socket.io',
			query: `domain=${_CHATBOX_BRIDGE_ACCESS_DOMAIN}&access_token=${_CHATBOX_BRIDGE_ACCESS_TOKEN}`,
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

			// Check if session is alive before passing new event to chatbox popup
			vtUtils.checkSession();

			// Pass the event to chatbox popup
			SocialChatboxPopup.newEvent(msg);
		});

		this.socket.on('disconnect', () => {
			this.log('Socket is disconnected.');
		});
	},

	notifyUserTyping: function(channel, socialPageId, customerSocialId) {
		if (!this.socket) return;

		var msg = {
			receiver_id: 'all',
			state: 'USER_TYPING',
			channel: channel,
			social_page_id: socialPageId,
			customer_social_id: customerSocialId,
			user_id: this.user.id,
			user_info: this.user,
		};

		this.socket.emit('message', msg);
	},

	log: function (message, params) {
		var timestamp = new Date().toLocaleString();

		if (params) {
			console.log(timestamp, '[SocialChat] ', message, params);
		}
		else {
			console.log(timestamp, '[SocialChat] ', message);
		}
	}
};

jQuery(function ($) {
	SocialChatClient.init();
});