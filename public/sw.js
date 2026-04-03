const CACHE_VERSION = 'tofof-pwa-v5';
const STATIC_CACHE = `tofof-static-${CACHE_VERSION}`;
const CORE_ASSETS = ['./', './manifest.webmanifest', './applogo.jpg'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(STATIC_CACHE)
            .then((cache) => cache.addAll(CORE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key.startsWith('tofof-') && key !== STATIC_CACHE)
                        .map((key) => caches.delete(key))
                )
            )
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const req = event.request;
    const url = new URL(req.url);
    const sameOrigin = url.origin === self.location.origin;

    if (!sameOrigin) {
        return;
    }

    // Do not cache dynamic downloads/exports or manifest to avoid stale installability/download behavior.
    if (
        url.pathname.endsWith('/manifest.webmanifest') ||
        url.pathname.includes('/backups/download/') ||
        url.pathname.endsWith('/export')
    ) {
        event.respondWith(fetch(req));
        return;
    }

    // HTML navigation: network first, then cache/offline fallback.
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(STATIC_CACHE).then((cache) => cache.put(req, copy));
                    return res;
                })
                .catch(async () => {
                    const cached = await caches.match(req);
                    return cached || caches.match('/') || Response.error();
                })
        );
        return;
    }

    // Static files: cache first, then network, then cache fallback.
    event.respondWith(
        caches.match(req).then((cached) => {
            if (cached) return cached;

            return fetch(req)
                .then((res) => {
                    if (!res || res.status !== 200 || res.type !== 'basic') {
                        return res;
                    }
                    const copy = res.clone();
                    caches.open(STATIC_CACHE).then((cache) => cache.put(req, copy));
                    return res;
                })
                .catch(() => caches.match(req).then((r) => r || caches.match('/') || Response.error()));
        })
    );
});

self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data = {};
    try {
        data = event.data.json();
    } catch (_) {
        data = { title: 'Tofof', body: event.data.text() };
    }

    event.waitUntil(
        self.registration.showNotification(data.title || 'Tofof', {
            body: data.body || 'لديك إشعار جديد',
            icon: data.icon || '/applogo.jpg',
            badge: '/applogo.jpg',
            data: {
                url: data.url || '/'
            }
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = (event.notification.data && event.notification.data.url) || '/';

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
            return null;
        })
    );
});