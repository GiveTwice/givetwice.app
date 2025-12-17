# GiveTwice

A wishlist and gift list app where all affiliate revenue goes to charity. Create wishlists, share them with friends and family, and feel good knowing that every purchase supports a good cause.

## Philosophy

GiveTwice is built on a simple idea: **gifting that gives back**. When someone buys a gift from your wishlist through an affiliate link, the revenue doesn't go to us - it goes directly to charity. You get exactly what you want, your loved ones know they're making you happy, and together you're making a difference.

## Tech stack

- **Backend:** Laravel 12, PHP 8.4, MySQL
- **Frontend:** Blade templates, Tailwind CSS v4, Alpine.js
- **App server:** Laravel Octane + FrankenPHP (high-performance)
- **Queue:** Laravel Horizon + Redis
- **WebSockets:** Laravel Reverb (real-time updates)
- **Auth:** Laravel Fortify + Socialite (Google/Facebook OAuth)

## Architecture overview

### Multi-language routing

All user-facing routes are prefixed with `/{locale}` (en, nl, fr). The root `/` redirects to the user's detected browser locale. Auth POST routes (handled by Fortify) have no locale prefix.

### Data models

```
User
 ├── has many: Gifts (items they want)
 ├── has many: GiftLists (organized collections)
 └── has many: Claims (gifts they've claimed for others)

Gift
 ├── belongs to: User
 ├── belongs to many: GiftLists
 └── has many: Claims

GiftList
 ├── belongs to: User
 └── belongs to many: Gifts

Claim
 ├── belongs to: Gift
 └── belongs to: User (nullable, supports anonymous claims)
```

### Event-driven architecture

GiveTwice uses Laravel events to decouple core workflows:

| Event | Trigger | Listeners |
|-------|---------|-----------|
| `GiftCreated` | User adds a gift | Dispatches `FetchGiftDetailsAction` to queue |
| `GiftFetchCompleted` | Product details fetched | Broadcasts to user via WebSocket |
| `GiftClaimed` | Someone claims a gift | Broadcasts to list owner via WebSocket |

### Action pattern

Business logic lives in single-responsibility Action classes (`app/Actions/`):

- `FetchGiftDetailsAction` - Fetches product title, image, price from URL (queued)
- `ClaimGiftAction` - Registered user claims a gift
- `CreatePendingClaimAction` - Anonymous user starts claim (sends confirmation email)
- `ConfirmClaimAction` - Anonymous user confirms via email token

### Real-time updates

Laravel Reverb provides WebSocket connections. When a gift is fetched or claimed, the frontend updates instantly without page refresh. Private channels ensure users only see their own updates.

## Getting started

### Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+
- MySQL
- Redis

### Installation

```bash
# Clone the repository
git clone https://github.com/GiveTwice/givetwice.app.git
cd givetwice.app

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up database
# Update .env with your MySQL credentials (default: gifting_app, root, no password)
php artisan migrate --seed

# Build assets
npm run build
```

### Development

```bash
# Start all services (recommended)
composer dev

# Or start individually:
php artisan octane:start --watch  # App server with hot reload
php artisan horizon               # Queue workers
php artisan reverb:start          # WebSocket server
npm run dev                       # Vite dev server
```

### Test users

| Email | Password | Admin |
|-------|----------|-------|
| m@ttias.be | localdevelopment | Yes |
| john@doe.tld | localdevelopment | No |

## Contributing

### Code style

Run Laravel Pint after modifying PHP files:

```bash
./vendor/bin/pint path/to/file.php
```

### Testing

We use [Pest](https://pestphp.com/) for testing:

```bash
./vendor/bin/pest
```

Write tests in Pest's functional syntax:

```php
it('creates a gift for the authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('gifts.store', 'en'), [
            'url' => 'https://example.com/product',
        ]);

    expect($user->gifts)->toHaveCount(1);
});
```

### Translations

All user-facing text uses `__()` for translations. When adding new strings:

1. Add the English string to `lang/en.json`
2. Add translations to `lang/nl.json` and `lang/fr.json` etc

### Adding a new language

1. Add the locale to `app/Enums/SupportedLocale.php`
2. Run `php artisan lang:add <locale>`
3. Copy `lang/en.json` to `lang/<locale>.json` and translate

### Key directories

```
app/
├── Actions/        # Single-responsibility business logic
├── Enums/          # SupportedLocale, SupportedCurrency
├── Events/         # Domain events (GiftCreated, GiftClaimed)
├── Http/Controllers/
├── Models/
└── Policies/       # Authorization rules

resources/
├── css/app.css     # Tailwind config + custom component classes
├── views/
│   ├── components/ # Blade components
│   ├── layouts/    # App and guest layouts
│   └── ...
└── js/

lang/
├── en.json         # App translations
├── nl.json
└── fr.json
```

## What's next

Features on the roadmap:

- **Affiliate integration** - Connect with affiliate networks to track purchases and route revenue to charity (PRIORITY)
- **Friend system** - Connect with friends to see their wishlists and get notified about updates
- **Notification emails** - Email claimer with confirmation of claim, receive emails when friends update their wishlists

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
