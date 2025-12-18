<?php

use App\Enums\SupportedLocale;

beforeEach(function () {
    $this->bladeKeys = extractBladeTranslationKeys();
    $this->translations = loadAllTranslations();
});

it('has a translation file for each supported locale', function () {
    foreach (SupportedLocale::values() as $locale) {
        $path = lang_path("{$locale}.json");
        expect(file_exists($path))
            ->toBeTrue("Missing translation file: {$locale}.json");
    }
});

it('has all Blade template keys in the base English translation file', function () {
    $en = $this->translations['en'] ?? [];
    $missing = [];

    foreach ($this->bladeKeys as $key) {
        if (! isset($en[$key])) {
            $missing[] = $key;
        }
    }

    expect($missing)
        ->toBeEmpty('Missing keys in en.json: '.formatKeyList($missing, 10));
});

it('has all English keys translated in every locale', function () {
    $en = $this->translations['en'] ?? [];
    $enKeys = array_keys($en);

    foreach (SupportedLocale::values() as $locale) {
        if ($locale === 'en') {
            continue;
        }

        $localeTranslations = $this->translations[$locale] ?? [];
        $missing = [];

        foreach ($enKeys as $key) {
            if (! isset($localeTranslations[$key])) {
                $missing[] = $key;
            }
        }

        expect($missing)
            ->toBeEmpty('Missing '.count($missing)." keys in {$locale}.json: ".formatKeyList($missing));
    }
});

it('has no orphaned keys in translation files', function () {
    $en = $this->translations['en'] ?? [];
    $enKeys = array_keys($en);

    foreach (SupportedLocale::values() as $locale) {
        if ($locale === 'en') {
            continue;
        }

        $localeTranslations = $this->translations[$locale] ?? [];
        $extra = array_diff(array_keys($localeTranslations), $enKeys);

        expect($extra)
            ->toBeEmpty("Extra keys in {$locale}.json not in en.json: ".formatKeyList($extra));
    }
});

it('has same key count across all locales', function () {
    $counts = [];
    foreach (SupportedLocale::values() as $locale) {
        $counts[$locale] = count($this->translations[$locale] ?? []);
    }

    $uniqueCounts = array_unique($counts);

    expect(count($uniqueCounts))
        ->toBe(1, 'Key counts differ across locales: '.json_encode($counts));
});

it('does not have empty translation values', function () {
    foreach (SupportedLocale::values() as $locale) {
        $translations = $this->translations[$locale] ?? [];
        $empty = [];

        foreach ($translations as $key => $value) {
            if (trim($value) === '') {
                $empty[] = $key;
            }
        }

        expect($empty)
            ->toBeEmpty("Empty values in {$locale}.json: ".formatKeyList($empty));
    }
});

it('does not have placeholder translation values', function () {
    $placeholderPatterns = [
        '/^TODO/',
        '/^TRANSLATE/',
        '/^XXX/',
        '/^\[.*\]$/',
    ];

    foreach (SupportedLocale::values() as $locale) {
        $translations = $this->translations[$locale] ?? [];
        $placeholders = [];

        foreach ($translations as $key => $value) {
            foreach ($placeholderPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $placeholders[] = $key;
                    break;
                }
            }
        }

        expect($placeholders)
            ->toBeEmpty("Placeholder values in {$locale}.json: ".formatKeyList($placeholders));
    }
});

function formatKeyList(array $keys, int $limit = 5): string
{
    $formatted = implode(', ', array_map(fn ($k) => "\"{$k}\"", array_slice($keys, 0, $limit)));
    $remaining = count($keys) - $limit;

    if ($remaining > 0) {
        $formatted .= "... and {$remaining} more";
    }

    return $formatted;
}

function extractBladeTranslationKeys(): array
{
    $keys = [];
    $viewsPath = resource_path('views');

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsPath)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());

            preg_match_all("/__\('([^']+)'\)/", $content, $singleQuoteMatches);
            foreach ($singleQuoteMatches[1] as $key) {
                $keys[$key] = true;
            }

            preg_match_all('/__\("((?:[^"\\\\]|\\\\.)*)"\)/', $content, $doubleQuoteMatches);
            foreach ($doubleQuoteMatches[1] as $key) {
                $unescaped = stripslashes($key);
                $keys[$unescaped] = true;
            }
        }
    }

    return array_keys($keys);
}

function loadAllTranslations(): array
{
    $translations = [];

    foreach (SupportedLocale::values() as $locale) {
        $path = lang_path("{$locale}.json");
        if (file_exists($path)) {
            $translations[$locale] = json_decode(file_get_contents($path), true) ?? [];
        }
    }

    return $translations;
}
