importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// We don't initialize here with hardcoded config because the V8 SDK allows
// the service worker to automatically pick up the config if we pass it 
// properly, or we can listen for a message to initialize it.
// However, the simplest way is to pass the config in the URL parameters when registering,
// or use the v9 modular SDK. Let's use the v8 compat script and wait for the config via postMessage.

let messaging = null;

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'FIREBASE_CONFIG') {
        if (!firebase.apps.length) {
            firebase.initializeApp(event.data.config);
            messaging = firebase.messaging();
            
            messaging.onBackgroundMessage((payload) => {
                console.log('[firebase-messaging-sw.js] Received background message ', payload);
                const notificationTitle = payload.notification.title;
                const notificationOptions = {
                    body: payload.notification.body,
                    icon: '/icon.png', // Assuming you have a default icon
                    data: payload.data
                };
                
                self.registration.showNotification(notificationTitle, notificationOptions);
            });
        }
    }
});
