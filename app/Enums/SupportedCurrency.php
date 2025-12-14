<?php

namespace App\Enums;

/**
 * When adding a new currency:
 * 1. Add a new case with the ISO 4217 currency code
 * 2. Add the symbol in symbol()
 * 3. Add the label in label()
 */
enum SupportedCurrency: string
{
    case EUR = 'EUR';
    case USD = 'USD';

    public function symbol(): string
    {
        return match ($this) {
            self::EUR => '€',
            self::USD => '$',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::EUR => 'Euro',
            self::USD => 'US Dollar',
        };
    }

    public function displayOption(): string
    {
        return $this->symbol().' '.$this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function default(): self
    {
        return self::EUR;
    }
}
