/*
    PushServiceWorker.js
    Author: Hieu Nguyen
    Date: 2019-03-21
    Purpose: handle push notification in the background at client side
*/

// Give the service worker access to Firebase Messaging.
importScripts('https://www.gstatic.com/firebasejs/4.9.1/firebase-app.js')
importScripts('https://www.gstatic.com/firebasejs/4.9.1/firebase-messaging.js')

// Initialize the Firebase app in the service worker by passing in the messagingSenderId.
var config = {
	messagingSenderId: '195744590510'
};

firebase.initializeApp(config);

// Retrieve an instance of Firebase Data Messaging so that it can handle background messages.
const messaging = firebase.messaging();

// Handle notification received event
messaging.setBackgroundMessageHandler(function(payload) {
	console.log('Notification', payload);
	
	const notificationTitle = 'Data Message Title';
	const notificationOptions = {
		body: 'Data Message body',
		icon: 'alarm.png'
	};

	return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification clicked event
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    // TODO: open crm in browser
});