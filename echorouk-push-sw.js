/**
 * Echorouk Push — Service Worker
 * Handles push events and notification clicks.
 * This file is copied to the web root on plugin activation.
 */

'use strict';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data = {};
    try {
        data = event.data.json();
    } catch (e) {
        data = { title: event.data.text(), body: '', url: '/' };
    }

    const title   = data.title  || 'إشعار جديد';
    const options = {
        body:    data.body   || '',
        icon:    data.icon   || '/wp-content/uploads/push-icon.png',
        badge:   data.badge  || '',
        image:   data.image  || undefined,
        data:    { url: data.url || '/' },
        dir:     'rtl',
        lang:    'ar',
        requireInteraction: false,
        vibrate: [200, 100, 200],
        tag:     'echorouk-push',
        renotify: true,
    };

    // Remove undefined keys (some browsers are strict)
    Object.keys(options).forEach(k => options[k] === undefined && delete options[k]);

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Focus existing tab if already open
            for (const client of windowClients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            // Otherwise open a new tab
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
