<?php

use App\Helpers\ExchangeHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

describe('Exchange SEO landing pages', function () {

    describe('English Secret Santa page', function () {
        it('returns 200 at the English slug', function () {
            $this->get('/en/secret-santa-gift-exchange')
                ->assertOk()
                ->assertViewIs('pages.exchange-landing');
        });

        it('passes the correct key to the view', function () {
            $this->get('/en/secret-santa-gift-exchange')
                ->assertViewHas('key', 'secret-santa-en');
        });

        it('contains English copy', function () {
            $this->get('/en/secret-santa-gift-exchange')
                ->assertSeeText('Secret Santa')
                ->assertSeeText('gift exchange');
        });

        it('returns 404 for wrong locale', function () {
            // English slug requested under NL locale
            $this->get('/nl/secret-santa-gift-exchange')
                ->assertNotFound();
        });
    });

    describe('Dutch lootjes trekken page', function () {
        it('returns 200 at the Dutch slug', function () {
            $this->get('/nl/lootjes-trekken-online')
                ->assertOk()
                ->assertViewIs('pages.exchange-landing');
        });

        it('passes the correct key to the view', function () {
            $this->get('/nl/lootjes-trekken-online')
                ->assertViewHas('key', 'lootjes-trekken-nl');
        });

        it('contains Dutch copy', function () {
            $this->get('/nl/lootjes-trekken-online')
                ->assertSeeText('Lootjes trekken');
        });

        it('returns 404 for wrong locale', function () {
            // Dutch slug requested under EN locale
            $this->get('/en/lootjes-trekken-online')
                ->assertNotFound();
        });
    });

    describe('French tirage au sort page', function () {
        it('returns 200 at the French slug', function () {
            $this->get('/fr/tirage-au-sort-noel')
                ->assertOk()
                ->assertViewIs('pages.exchange-landing');
        });

        it('passes the correct key to the view', function () {
            $this->get('/fr/tirage-au-sort-noel')
                ->assertViewHas('key', 'tirage-au-sort-fr');
        });

        it('contains French copy', function () {
            $this->get('/fr/tirage-au-sort-noel')
                ->assertSeeText('Tirage au sort');
        });

        it('returns 404 for wrong locale', function () {
            // French slug requested under EN locale
            $this->get('/en/tirage-au-sort-noel')
                ->assertNotFound();
        });
    });

    describe('ExchangeHelper', function () {
        it('returns all three exchange landing pages', function () {
            $all = ExchangeHelper::all();
            expect($all)->toHaveCount(3);
            expect($all)->toHaveKeys(['secret-santa-en', 'lootjes-trekken-nl', 'tirage-au-sort-fr']);
        });

        it('returns page content for valid key and matching locale', function () {
            $content = ExchangeHelper::getPageContent('secret-santa-en', 'en');
            expect($content)->not->toBeNull();
            expect($content['page_title'])->toBe('Secret Santa Gift Exchange');
            expect($content)->toHaveKeys(['hero', 'why', 'tips', 'faqs', 'final_cta']);
        });

        it('returns null for valid key but wrong locale', function () {
            $content = ExchangeHelper::getPageContent('secret-santa-en', 'nl');
            expect($content)->toBeNull();
        });

        it('returns null for unknown key', function () {
            $content = ExchangeHelper::getPageContent('does-not-exist', 'en');
            expect($content)->toBeNull();
        });

        it('each page has required keys', function () {
            $required = ['hero', 'hero_gifts', 'why', 'givetwice', 'tips', 'tips_title', 'faqs', 'final_cta'];

            foreach (ExchangeHelper::all() as $key => $exchange) {
                $content = ExchangeHelper::getPageContent($key, $exchange['locale']);
                expect($content)->not->toBeNull();
                expect(array_keys($content))->toContain(...$required);
            }
        });
    });

    describe('Sitemap', function () {
        it('includes all three exchange landing pages', function () {
            config(['app.url' => 'https://givetwice.app']);

            Artisan::call('sitemap:generate');

            $sitemap = File::get(public_path('sitemap.xml'));

            expect($sitemap)->toContain('/en/secret-santa-gift-exchange');
            expect($sitemap)->toContain('/nl/lootjes-trekken-online');
            expect($sitemap)->toContain('/fr/tirage-au-sort-noel');
        });

        it('does not include exchange landing slugs under wrong locales in sitemap', function () {
            config(['app.url' => 'https://givetwice.app']);

            Artisan::call('sitemap:generate');

            $sitemap = File::get(public_path('sitemap.xml'));

            // Dutch slug must not appear under /en/ or /fr/
            expect($sitemap)->not->toContain('/en/lootjes-trekken-online');
            expect($sitemap)->not->toContain('/fr/lootjes-trekken-online');

            // English slug must not appear under /nl/ or /fr/
            expect($sitemap)->not->toContain('/nl/secret-santa-gift-exchange');
            expect($sitemap)->not->toContain('/fr/secret-santa-gift-exchange');
        });
    });

});
