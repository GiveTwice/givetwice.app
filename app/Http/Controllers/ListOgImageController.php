<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class ListOgImageController extends Controller
{
    // Image dimensions (standard OG)
    private const WIDTH = 1200;

    private const HEIGHT = 630;

    // GiveTwice palette (RGB)
    private const CORAL = [240, 112, 96];

    private const TEAL = [45, 159, 147];

    private const SUNNY = [245, 214, 128];

    private const CREAM_BG = [254, 253, 251];

    private const GRAY_900 = [17, 24, 39];

    private const GRAY_500 = [107, 114, 128];

    private const GRAY_300 = [209, 213, 219];

    private const FONT_BOLD = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

    private const FONT_REGULAR = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';

    public function __invoke(string $locale, string $list): Response
    {
        /** @var GiftList $list */
        $list = GiftList::with('creator:id,name')->findOrFail((int) $list);

        $cacheKey = "og-image:{$list->id}:{$list->updated_at->timestamp}";
        $cachePath = storage_path("app/og-cache/{$cacheKey}.png");

        // Serve from cache if available
        if (file_exists($cachePath)) {
            return response(file_get_contents($cachePath), 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=86400');
        }

        $png = $this->generate($list);

        // Persist to filesystem cache
        @mkdir(dirname($cachePath), 0755, true);
        file_put_contents($cachePath, $png);

        return response($png, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    private function generate(GiftList $list): string
    {
        $giftCount = $list->gifts()->count();
        /** @var \App\Models\User $creator */
        $creator = $list->creator;
        $ownerName = $creator->name;
        $listName = $list->name;

        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Colours
        $bg = imagecolorallocate($img, ...self::CREAM_BG);
        $coral = imagecolorallocate($img, ...self::CORAL);
        $teal = imagecolorallocate($img, ...self::TEAL);
        $sunny = imagecolorallocate($img, ...self::SUNNY);
        $gray900 = imagecolorallocate($img, ...self::GRAY_900);
        $gray500 = imagecolorallocate($img, ...self::GRAY_500);
        $gray300 = imagecolorallocate($img, ...self::GRAY_300);
        $white = imagecolorallocate($img, 255, 255, 255);

        // Soft background
        imagefill($img, 0, 0, $bg);

        // ── Decorative blobs (low-opacity circles) ─────────────────────────
        $this->drawSoftCircle($img, 1050, -80, 480, self::SUNNY, 25);
        $this->drawSoftCircle($img, -60, 500, 380, self::TEAL, 18);
        $this->drawSoftCircle($img, 900, 520, 180, self::CORAL, 15);

        // ── Left content panel ──────────────────────────────────────────────
        $padX = 90;
        $padY = 80;

        // GiveTwice wordmark
        $this->drawWordmark($img, $padX, $padY, $coral, $gray900);

        // Owner name headline: "Sarah's wishlist"
        $possessive = $this->possessive($ownerName);
        $headline = $possessive.' wishlist';
        $headlineY = $padY + 100;
        $this->drawWrappedText($img, $headline, self::FONT_BOLD, 58, $gray900, $padX, $headlineY, 640);

        // List name (if it's not just the default "My … wishlist" pattern)
        $listNameY = $headlineY + 145;
        if ($this->shouldShowListName($listName, $ownerName)) {
            $this->drawWrappedText($img, $listName, self::FONT_REGULAR, 28, $gray500, $padX, $listNameY, 620);
            $listNameY += 50;
        }

        // Gift count pill
        $pillY = $listNameY + 30;
        $giftLabel = $giftCount === 1 ? '1 gift' : "{$giftCount} gifts";
        $this->drawPill($img, $padX, $pillY, $giftLabel, $teal, $white, self::FONT_BOLD, 26);

        // Charity tagline
        $taglineY = $pillY + 90;
        $this->drawWrappedText($img, 'Every gift also gives to charity ♥', self::FONT_REGULAR, 22, $gray500, $padX, $taglineY, 600);

        // Domain watermark
        $this->drawText($img, 'givetwice.app', self::FONT_REGULAR, 20, $gray300, $padX, self::HEIGHT - 44);

        // ── Right visual card ───────────────────────────────────────────────
        $this->drawCard($img, 830, 110, $white, $coral, $teal, $sunny, $gray900, $gray500, $gray300);

        // Flush to string
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    // ── Drawing helpers ─────────────────────────────────────────────────────

    /** Draw the GiveTwice wordmark at (x, y) */
    private function drawWordmark(\GdImage $img, int $x, int $y, int $coral, int $dark): void
    {
        // Heart ❤ symbol as a filled circle proxy — use a red oval
        $hx = $x;
        $hy = $y + 2;
        imagefilledellipse($img, $hx + 14, $hy + 14, 28, 28, $coral);

        $textX = $x + 42;
        imagettftext($img, 28, 0, $textX, $y + 28, $dark, self::FONT_BOLD, 'Give');
        $giveWidth = (int) imagettfbbox(28, 0, self::FONT_BOLD, 'Give')[4];
        imagettftext($img, 28, 0, $textX + $giveWidth, $y + 28, $coral, self::FONT_BOLD, 'Twice');
    }

    /** Draw text at a fixed position */
    private function drawText(\GdImage $img, string $text, string $font, int $size, int $color, int $x, int $y): void
    {
        imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
    }

    /** Draw text wrapping at maxWidth, returns final Y */
    private function drawWrappedText(\GdImage $img, string $text, string $font, int $size, int $color, int $x, int $y, int $maxWidth): int
    {
        $words = explode(' ', $text);
        $line = '';
        $lineHeight = (int) ($size * 1.4);
        $curY = $y + $size;

        foreach ($words as $word) {
            $test = $line === '' ? $word : $line.' '.$word;
            $bbox = imagettfbbox($size, 0, $font, $test);
            $testWidth = abs($bbox[4] - $bbox[0]);

            if ($testWidth > $maxWidth && $line !== '') {
                imagettftext($img, $size, 0, $x, $curY, $color, $font, $line);
                $curY += $lineHeight;
                $line = $word;
            } else {
                $line = $test;
            }
        }

        if ($line !== '') {
            imagettftext($img, $size, 0, $x, $curY, $color, $font, $line);
            $curY += $lineHeight;
        }

        return $curY;
    }

    /** Draw a rounded pill/badge */
    private function drawPill(\GdImage $img, int $x, int $y, string $label, int $bg, int $textColor, string $font, int $fontSize): void
    {
        $bbox = imagettfbbox($fontSize, 0, $font, $label);
        $textW = abs($bbox[4] - $bbox[0]);
        $padH = 18;
        $padV = 12;
        $w = $textW + $padH * 2;
        $h = $fontSize + $padV * 2;
        $r = (int) ($h / 2);

        $this->drawRoundedRect($img, $x, $y, $x + $w, $y + $h, $r, $bg);
        imagettftext($img, $fontSize, 0, $x + $padH, $y + $padV + $fontSize - 2, $textColor, $font, $label);
    }

    /** Fill a rounded rectangle */
    private function drawRoundedRect(\GdImage $img, int $x1, int $y1, int $x2, int $y2, int $r, int $color): void
    {
        imagefilledrectangle($img, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $r, $x2, $y2 - $r, $color);
        imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
    }

    /** Draw a soft decorative circle (low opacity approximation via layered fills) */
    private function drawSoftCircle(\GdImage $img, int $cx, int $cy, int $r, array $rgb, int $alpha): void
    {
        $color = imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], $alpha);
        imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $color);
    }

    /** Draw the decorative wishlist card on the right side */
    private function drawCard(
        \GdImage $img,
        int $x, int $y,
        int $white, int $coral, int $teal, int $sunny,
        int $gray900, int $gray500, int $gray300
    ): void {
        $w = 290;
        $h = 400;

        // Card shadow (simple darker offset rect)
        $shadow = imagecolorallocatealpha($img, 0, 0, 0, 100);
        $this->drawRoundedRect($img, $x + 6, $y + 6, $x + $w + 6, $y + $h + 6, 20, $shadow);

        // Card background
        $this->drawRoundedRect($img, $x, $y, $x + $w, $y + $h, 20, $white);

        // Card border (light gray)
        // (skipped for simplicity — rounded rect over white is sufficient)

        // Card header
        $headerBg = imagecolorallocate($img, 254, 242, 240); // coral-50
        $this->drawRoundedRect($img, $x, $y, $x + $w, $y + 70, 20, $headerBg);
        imagefilledrectangle($img, $x, $y + 50, $x + $w, $y + 70, $headerBg); // flatten bottom corners

        imagettftext($img, 16, 0, $x + 20, $y + 30, $coral, self::FONT_BOLD, 'My Wishlist');
        imagettftext($img, 12, 0, $x + 20, $y + 52, $gray500, self::FONT_REGULAR, '3 gift ideas');

        // Gift items
        $items = [
            ['🎧', 'Headphones', '€ 79', $teal, 'Available'],
            ['📚', 'Book Set', '€ 32', $sunny, 'Claimed'],
            ['🧣', 'Cozy Scarf', '€ 45', $teal, 'Available'],
        ];

        $itemColors = [
            imagecolorallocate($img, 219, 234, 254), // blue-100
            imagecolorallocate($img, 253, 230, 138), // amber-100
            imagecolorallocate($img, 167, 243, 208), // emerald-100
        ];

        $iy = $y + 88;
        foreach ($items as $i => [$emoji, $name, $price, $badgeBg, $badgeLabel]) {
            // Emoji box
            $this->drawRoundedRect($img, $x + 16, $iy, $x + 60, $iy + 44, 10, $itemColors[$i]);
            imagettftext($img, 20, 0, $x + 22, $iy + 30, $gray900, self::FONT_REGULAR, $emoji);

            // Name + price
            imagettftext($img, 13, 0, $x + 70, $iy + 16, $gray900, self::FONT_BOLD, $name);
            imagettftext($img, 12, 0, $x + 70, $iy + 34, $coral, self::FONT_BOLD, $price);

            // Badge
            $this->drawPill($img, $x + 175, $iy + 10, $badgeLabel, $badgeBg, $gray900, self::FONT_REGULAR, 10);

            // Divider
            if ($i < 2) {
                $divColor = imagecolorallocate($img, 243, 240, 234); // cream-200
                imageline($img, $x + 16, $iy + 52, $x + $w - 16, $iy + 52, $divColor);
            }

            $iy += 60;
        }
    }

    /** Build a possessive form (Sarah → Sarah's, James → James') */
    private function possessive(string $name): string
    {
        return str_ends_with($name, 's') ? $name."'" : $name."'s";
    }

    /** Decide whether the list name is distinct enough to show */
    private function shouldShowListName(string $listName, string $ownerName): bool
    {
        $normalized = strtolower($listName);
        $owner = strtolower($ownerName);

        // Skip generic patterns: "My birthday wishlist", "Sarah's wishlist", etc.
        if (str_contains($normalized, 'wishlist') && str_contains($normalized, 'my')) {
            return false;
        }
        if (str_contains($normalized, $owner)) {
            return false;
        }

        return true;
    }
}
