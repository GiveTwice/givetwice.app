const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');

puppeteer.use(StealthPlugin());

let request;
try {
    request = JSON.parse(process.argv[2]);
} catch (e) {
    console.log(JSON.stringify({
        success: false,
        error: 'Invalid JSON input: ' + e.message,
    }));
    process.exit(1);
}

if (!request || !request.url) {
    console.log(JSON.stringify({
        success: false,
        error: 'Missing required parameter: url',
    }));
    process.exit(1);
}

function isBlockedStatusCode(statusCode) {
    return statusCode !== 200;
}

function parseProxyUrl(proxyUrl) {
    if (!proxyUrl) return null;

    const url = new URL(proxyUrl);
    const server = `${url.protocol}//${url.host}`;
    const auth = url.username ? { username: decodeURIComponent(url.username), password: decodeURIComponent(url.password || '') } : null;

    return { server, auth };
}

(async () => {
    let browser;
    try {
        const proxy = parseProxyUrl(request.proxy);

        const launchOptions = {
            headless: true,
            pipe: true,
            timeout: 30000,
            ignoreDefaultArgs: ['--enable-automation'],
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--disable-software-rasterizer',
                '--disable-blink-features=AutomationControlled',
                '--disable-features=IsolateOrigins,site-per-process',
                '--window-size=1920,1080',
            ],
        };

        if (proxy) {
            launchOptions.args.push(`--proxy-server=${proxy.server}`);
        }

        if (request.chromePath) {
            launchOptions.executablePath = request.chromePath;
        }

        browser = await puppeteer.launch(launchOptions);

        const page = await browser.newPage();

        if (proxy && proxy.auth) {
            await page.authenticate(proxy.auth);
        }

        await page.setViewport({ width: 1920, height: 1080 });

        if (request.userAgent) {
            await page.setUserAgent(request.userAgent);
        }

        if (request.headers && Object.keys(request.headers).length > 0) {
            await page.setExtraHTTPHeaders(request.headers);
        }

        // Block non-essential resources to prevent analytics/tracking from stalling page load
        await page.setRequestInterception(true);
        const blockedTypes = new Set(['image', 'stylesheet', 'font', 'media', 'other']);
        const blockedDomains = [
            'google-analytics', 'googletagmanager', 'doubleclick', 'facebook', 'twitter',
            'analytics', 'tracking', 'ads', 'pixel', 'segment', 'mixpanel', 'hotjar',
            'adobedtm', 'mparticle', 'braze', 'amplitude', 'heap', 'fullstory',
            'sentry', 'bugsnag', 'newrelic', 'datadog', 'dynatrace',
        ];
        page.on('request', (req) => {
            const url = req.url();
            const type = req.resourceType();
            if (blockedTypes.has(type) || blockedDomains.some(d => url.includes(d))) {
                req.abort();
            } else {
                req.continue();
            }
        });

        const response = await page.goto(request.url, {
            waitUntil: 'domcontentloaded',
            timeout: request.timeout || 30000,
        });

        // Give JS-heavy sites up to 3s to populate og:title, then proceed regardless
        await Promise.race([
            page.waitForFunction(
                () => document.querySelector('meta[property="og:title"]')?.content?.length > 0,
                { timeout: 3000 }
            ).catch(() => null),
            new Promise(r => setTimeout(r, 3000)),
        ]);

        const html = await page.content();
        const statusCode = response ? response.status() : 200;

        if (isBlockedStatusCode(statusCode)) {
            console.log(JSON.stringify({
                success: false,
                error: `Request failed (status: ${statusCode})`,
                statusCode: statusCode,
                html: html,
                finalUrl: response ? response.url() : request.url,
            }));
            process.exit(1);
        }

        console.log(JSON.stringify({
            success: true,
            html: html,
            statusCode: statusCode,
            finalUrl: response ? response.url() : request.url,
        }));
    } catch (error) {
        console.log(JSON.stringify({
            success: false,
            error: error.message,
        }));
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();
