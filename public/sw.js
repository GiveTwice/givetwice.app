const CACHE_VERSION = 'v3';
const STATIC_CACHE = `givetwice-static-${CACHE_VERSION}`;
const PAGE_CACHE = `givetwice-pages-${CACHE_VERSION}`;
const IMAGE_CACHE = `givetwice-images-${CACHE_VERSION}`;

const MAX_PAGES = 50;
const MAX_IMAGES = 100;

const PRECACHE_URLS = [
    '/offline',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) =>
            PRECACHE_URLS.length > 0 ? cache.addAll(PRECACHE_URLS) : Promise.resolve()
        )
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key.startsWith('givetwice-') &&
                        key !== STATIC_CACHE &&
                        key !== PAGE_CACHE &&
                        key !== IMAGE_CACHE)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

async function trimCache(cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    if (keys.length > maxItems) {
        for (let i = 0; i < keys.length - maxItems; i++) {
            await cache.delete(keys[i]);
        }
    }
}

function isStaticAsset(url) {
    const path = url.pathname;
    if (path.startsWith('/storage/')) return false;
    return path.startsWith('/build/assets/') ||
        path.match(/\.(css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|webp|ico)$/) !== null;
}

function isGiftImage(url) {
    return url.pathname.startsWith('/storage/') &&
        url.pathname.match(/\.(png|jpg|jpeg|gif|webp)$/i) !== null;
}

function isNavigationRequest(request) {
    return request.mode === 'navigate' ||
        (request.method === 'GET' && request.headers.get('accept')?.includes('text/html'));
}

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (event.request.method !== 'GET') return;
    if (url.origin !== self.location.origin) return;
    if (url.pathname.startsWith('/horizon') || url.pathname.startsWith('/admin')) return;

    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) return cached;
                return fetch(event.request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(STATIC_CACHE).then((cache) => cache.put(event.request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }

    if (isGiftImage(url)) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(IMAGE_CACHE).then((cache) => {
                            cache.put(event.request, clone);
                            trimCache(IMAGE_CACHE, MAX_IMAGES);
                        });
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(event.request)
                )
        );
        return;
    }

    if (isNavigationRequest(event.request)) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(PAGE_CACHE).then((cache) => {
                            cache.put(event.request, clone);
                            trimCache(PAGE_CACHE, MAX_PAGES);
                        });
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(event.request).then((cached) =>
                        cached || caches.match('/offline')
                    )
                )
        );
        return;
    }
});

self.addEventListener('push', (_event) => {
    // Stub for future push notification support
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    // Stub for future notification click handling
});
