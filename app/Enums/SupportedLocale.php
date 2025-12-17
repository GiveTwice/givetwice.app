<?php

namespace App\Enums;

/**
 * When adding a new locale:
 * 1. Add a new case with the ISO 639-1 language code
 * 2. Add the display label in label()
 * 3. Add the flag emoji in flag()
 * 4. Add the default currency mapping in defaultCurrency()
 * 5. Create the translation file in lang/{code}.json
 */
enum SupportedLocale: string
{
    case English = 'en';
    case Dutch = 'nl';
    case French = 'fr';

    public function label(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Dutch => 'Nederlands',
            self::French => 'Français',
        };
    }

    public function flag(): string
    {
        return match ($this) {
            self::English => '🇬🇧',
            self::Dutch => '🇳🇱',
            self::French => '🇫🇷',
        };
    }

    public function defaultCurrency(): SupportedCurrency
    {
        return match ($this) {
            self::English => SupportedCurrency::USD,
            default => SupportedCurrency::EUR,
        };
    }

    // Separate method to allow regional variants (e.g., en-GB, nl-BE) in the future
    public function hreflang(): string
    {
        return $this->value;
    }

    // Open Graph locale format (e.g., en_US, nl_NL, fr_FR)
    public function ogLocale(): string
    {
        return match ($this) {
            self::English => 'en_US',
            self::Dutch => 'nl_NL',
            self::French => 'fr_FR',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function routePattern(): string
    {
        return implode('|', self::values());
    }

    public static function default(): self
    {
        return self::English;
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::values(), true);
    }
}
