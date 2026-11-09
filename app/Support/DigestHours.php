<?php

namespace App\Support;

/**
 * Wishlist digests only go out between 09:00 and 20:00 Brussels time, so nobody
 * gets one in the middle of the night. A run outside that window waits for the next one.
 */
class DigestHours
{
    public static function isOpen(): bool
    {
        $hour = now('Europe/Brussels')->hour;

        return $hour >= 9 && $hour < 20;
    }
}
