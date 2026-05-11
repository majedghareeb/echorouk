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
    let data = {};

    // Try to parse payload — if event.data is null (decryption failed or empty push),
    // show a generic fallback notification instead of silently doing nothing.
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            // data was not JSON (binary or plain text) — treat as title
            const text = event.data.text ? event.data.text() : '';
            data = { title: text || 'إشعار جديد', body: '', url: '/' };
        }
    }

    const title   = data.title  || 'الشروق';
    const options = {
        body:    data.body   || 'اضغط لمشاهدة آخر الأخبار',
        icon:    data.icon   || '/wp-content/themes/echoroukonline/assets/icons/notification-01-stroke-rounded.svg',
        badge:   data.badge  || '',
        data:    { url: data.url || '/' },
        dir:     'rtl',
        lang:    'ar',
        requireInteraction: false,
        vibrate: [200, 100, 200],
        tag:     'echorouk-push',
        renotify: true,
    };

    if (data.image) options.image = data.image;

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
