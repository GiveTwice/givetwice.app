const CACHE_VERSION = 'v4';
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
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
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
        /\.(css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|webp|ico)$/.test(path);
}

function isGiftImage(url) {
    return url.pathname.startsWith('/storage/') &&
        /\.(png|jpg|jpeg|gif|webp)$/i.test(url.pathname);
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
                }).catch(() => caches.match(event.request));
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
        // Don't cache authenticated routes to prevent serving stale user data
        const pathWithoutLocale = url.pathname.replace(/^\/[a-z]{2}\//, '/');
        const authPaths = ['/dashboard', '/settings', '/list/', '/gifts/', '/lists/'];
        const isAuthRoute = authPaths.some((p) => pathWithoutLocale.startsWith(p));

        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    if (response.ok && !isAuthRoute) {
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
                        cached || caches.match('/offline').then((r) => r || new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } }))
                    )
                )
        );
        return;
    }
});
