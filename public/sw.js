const CACHE_NAME = 'ge-hrms-v1';
const PRECACHE_URLS = [
    '/gestao-equipe/public/',
    '/gestao-equipe/public/manifest.json',
];

// Install — precache shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

// Activate — cleanup old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

// Fetch — network-first with cache fallback
self.addEventListener('fetch', (event) => {
    // Skip non-GET and Livewire/API requests
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/livewire/')) return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Cache successful GET responses
                if (response.status === 200) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});

// Push notification handler
self.addEventListener('push', (event) => {
    let data = { title: 'Gestão de Equipe', body: 'Nova notificação', icon: '/gestao-equipe/public/images/icon-192.png' };

    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch (e) {
            data.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: data.icon,
            badge: '/gestao-equipe/public/images/icon-192.png',
            vibrate: [200, 100, 200],
            data: data.url || '/',
        })
    );
});

// Notification click — navigate to app
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes('/gestao-equipe/') && 'focus' in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(event.notification.data || '/gestao-equipe/public/');
        })
    );
});
