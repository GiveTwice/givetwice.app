<?php

namespace App\Console\Commands;

use App\Enums\SupportedLocale;
use App\Helpers\OccasionHelper;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the sitemap.xml file';

    private array $staticPages = [
        'home' => '',
        'about' => '/about',
        'faq' => '/faq',
        'privacy' => '/privacy',
        'terms' => '/terms',
        'contact' => '/contact',
        'transparency' => '/transparency',
        'brand' => '/brand',
        'subprocessors' => '/subprocessors',
    ];

    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();
        $baseUrl = config('app.url');

        // Add static pages
        foreach ($this->staticPages as $name => $path) {
            foreach (SupportedLocale::cases() as $locale) {
                $url = Url::create("{$baseUrl}/{$locale->value}{$path}")
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority($name === 'home' ? 1.0 : 0.8);

                foreach (SupportedLocale::cases() as $altLocale) {
                    $url->addAlternate(
                        "{$baseUrl}/{$altLocale->value}{$path}",
                        $altLocale->hreflang()
                    );
                }

                $sitemap->add($url);
            }
        }

        // Add occasion pages (locale-aware)
        $this->addOccasionPages($sitemap, $baseUrl);

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at public/sitemap.xml');

        return Command::SUCCESS;
    }

    private function addOccasionPages(Sitemap $sitemap, string $baseUrl): void
    {
        foreach (OccasionHelper::all() as $occasionData) {
            $slug = $occasionData['slug'];
            $allowedLocales = $occasionData['locales'];

            // Determine which locales this occasion should appear in
            $localesForOccasion = $allowedLocales === null
                ? SupportedLocale::cases()
                : array_filter(
                    SupportedLocale::cases(),
                    fn ($locale) => in_array($locale->value, $allowedLocales)
                );

            foreach ($localesForOccasion as $locale) {
                $url = Url::create("{$baseUrl}/{$locale->value}/{$slug}")
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.7);

                // Add alternate links only for locales where this occasion is available
                foreach ($localesForOccasion as $altLocale) {
                    $url->addAlternate(
                        "{$baseUrl}/{$altLocale->value}/{$slug}",
                        $altLocale->hreflang()
                    );
                }

                $sitemap->add($url);
            }
        }
    }
}
