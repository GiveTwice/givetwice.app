<?php

namespace App\Console\Commands;

use App\Enums\SupportedLocale;
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
    ];

    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();
        $baseUrl = config('app.url');

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

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at public/sitemap.xml');

        return Command::SUCCESS;
    }
}
