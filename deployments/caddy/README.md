# FrankenPHP + Caddy Deployment

This directory contains the Caddy configuration for running GiveTwice with FrankenPHP.

## Overview

FrankenPHP is built on **Caddy 2** and provides:
- Automatic HTTPS via Let's Encrypt
- HTTP/2 and HTTP/3 (QUIC) support
- Worker mode (keeps Laravel in memory)
- ~10-15x faster than PHP-FPM

## Files

- `Caddyfile` - Production configuration for givetwice.app

## Quick Start

### Option 1: Via Laravel Octane (recommended)

```bash
php artisan octane:start \
    --server=frankenphp \
    --host=0.0.0.0 \
    --port=443 \
    --caddyfile=deployments/caddy/Caddyfile \
    --https
```

### Option 2: Via FrankenPHP directly

```bash
frankenphp run --config deployments/caddy/Caddyfile
```

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `FRANKENPHP_WORKERS` | `auto` | Number of worker processes (auto = 2x CPU cores) |

## Production Checklist

1. **DNS**: Point `givetwice.app` and `www.givetwice.app` to your server IP
2. **Firewall**: Open ports 80 (HTTP), 443 (HTTPS), and 443/udp (HTTP/3)
3. **Log directory**: Create `/var/log/caddy/` with proper permissions
4. **HSTS**: Uncomment the `Strict-Transport-Security` header after confirming HTTPS works

## Caddyfile Features

### Automatic HTTPS
Caddy automatically obtains and renews Let's Encrypt certificates. No configuration needed.

### www Redirect
All requests to `www.givetwice.app` are permanently redirected to `givetwice.app`.

### Static Asset Caching
- Build assets (`/build/*`): 1 year cache, immutable
- Images, fonts, media: 1 year cache, immutable
- Favicon, robots.txt: 1 day cache

### Compression
Responses are compressed using (in order of preference):
1. Zstandard (zstd) - fastest
2. Brotli (br) - best compression
3. Gzip - fallback

### Security Headers
- `X-Frame-Options: SAMEORIGIN` - prevents clickjacking
- `X-Content-Type-Options: nosniff` - prevents MIME sniffing
- `X-XSS-Protection: 1; mode=block` - XSS filter
- `Referrer-Policy: strict-origin-when-cross-origin`

## Graceful Restart

With the admin API enabled, restart workers without downtime:

```bash
curl -X POST http://localhost:2019/frankenphp/workers/restart
```

## Monitoring

View running workers:
```bash
curl http://localhost:2019/frankenphp/workers
```

## Troubleshooting

### Certificate issues
Use the staging CA for testing:
```
acme_ca https://acme-staging-v02.api.letsencrypt.org/directory
```

### Permission errors
Ensure Caddy can write to the log directory:
```bash
sudo mkdir -p /var/log/caddy
sudo chown caddy:caddy /var/log/caddy
```

### Worker memory issues
Set `MAX_REQUESTS` environment variable to restart workers periodically:
```bash
MAX_REQUESTS=500 php artisan octane:start ...
```
