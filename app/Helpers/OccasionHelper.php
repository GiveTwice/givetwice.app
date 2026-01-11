<?php

namespace App\Helpers;

class OccasionHelper
{
    private static ?array $cachedAll = null;

    /**
     * All available occasions with their metadata.
     *
     * @return array<string, array{slug: string, emoji: string, locales: array<string>|null, category: string, page_title: string, list_name: string}>
     */
    public static function all(): array
    {
        return self::$cachedAll ??= [
            // Holidays
            'christmas' => [
                'slug' => 'christmas-wishlist',
                'emoji' => '🎄',
                'locales' => null,
                'category' => 'holidays',
                'page_title' => 'Christmas Wishlist',
                'list_name' => 'My Christmas wishlist',
            ],
            'sinterklaas' => [
                'slug' => 'sinterklaas-wishlist',
                'emoji' => '🎅',
                'locales' => ['nl', 'fr'],
                'category' => 'holidays',
                'page_title' => 'Sinterklaas Wishlist',
                'list_name' => 'My Sinterklaas wishlist',
            ],
            'valentines-day' => [
                'slug' => 'valentines-day-wishlist',
                'emoji' => '💝',
                'locales' => null,
                'category' => 'holidays',
                'page_title' => "Valentine's Day Wishlist",
                'list_name' => 'My Valentine wishlist',
            ],
            'easter' => [
                'slug' => 'easter-wishlist',
                'emoji' => '🐣',
                'locales' => null,
                'category' => 'holidays',
                'page_title' => 'Easter Wishlist',
                'list_name' => 'My Easter wishlist',
            ],
            'thanksgiving' => [
                'slug' => 'thanksgiving-wishlist',
                'emoji' => '🦃',
                'locales' => ['en'],
                'category' => 'holidays',
                'page_title' => 'Thanksgiving Wishlist',
                'list_name' => 'My Thanksgiving wishlist',
            ],
            'mothers-day' => [
                'slug' => 'mothers-day-wishlist',
                'emoji' => '💐',
                'locales' => null,
                'category' => 'holidays',
                'page_title' => "Mother's Day Wishlist",
                'list_name' => "Mother's Day wishlist",
            ],
            'fathers-day' => [
                'slug' => 'fathers-day-wishlist',
                'emoji' => '👔',
                'locales' => null,
                'category' => 'holidays',
                'page_title' => "Father's Day Wishlist",
                'list_name' => "Father's Day wishlist",
            ],
            'hanukkah' => [
                'slug' => 'hanukkah-wishlist',
                'emoji' => '🕎',
                'locales' => null,
                'category' => 'holidays',
                'page_title' => 'Hanukkah Wishlist',
                'list_name' => 'My Hanukkah wishlist',
            ],
            'eid' => [
                'slug' => 'eid-wishlist',
                'emoji' => '🌙',
                'locales' => null,
                'category' => 'holidays',
                'page_title' => 'Eid Wishlist',
                'list_name' => 'My Eid wishlist',
            ],
            'diwali' => [
                'slug' => 'diwali-wishlist',
                'emoji' => '🪔',
                'locales' => null,
                'category' => 'holidays',
                'page_title' => 'Diwali Wishlist',
                'list_name' => 'My Diwali wishlist',
            ],
            'lunar-new-year' => [
                'slug' => 'lunar-new-year-wishlist',
                'emoji' => '🧧',
                'locales' => null,
                'category' => 'holidays',
                'page_title' => 'Lunar New Year Wishlist',
                'list_name' => 'My Lunar New Year wishlist',
            ],

            // Life events
            'birthday' => [
                'slug' => 'birthday-wishlist',
                'emoji' => '🎂',
                'locales' => null,
                'category' => 'life-events',
                'page_title' => 'Birthday Wishlist',
                'list_name' => 'My birthday wishlist',
            ],
            'wedding' => [
                'slug' => 'wedding-wishlist',
                'emoji' => '💒',
                'locales' => null,
                'category' => 'life-events',
                'page_title' => 'Wedding Wishlist',
                'list_name' => 'Our wedding wishlist',
            ],
            'baby-shower' => [
                'slug' => 'baby-shower-wishlist',
                'emoji' => '👶',
                'locales' => null,
                'category' => 'life-events',
                'page_title' => 'Baby Shower Wishlist',
                'list_name' => 'Baby shower wishlist',
            ],
            'communion' => [
                'slug' => 'communion-wishlist',
                'emoji' => '✝️',
                'locales' => null,
                'category' => 'life-events',
                'page_title' => 'Communion Wishlist',
                'list_name' => 'My communion wishlist',
            ],
            'graduation' => [
                'slug' => 'graduation-wishlist',
                'emoji' => '🎓',
                'locales' => null,
                'category' => 'life-events',
                'page_title' => 'Graduation Wishlist',
                'list_name' => 'My graduation wishlist',
            ],
            'housewarming' => [
                'slug' => 'housewarming-wishlist',
                'emoji' => '🏠',
                'locales' => null,
                'category' => 'life-events',
                'page_title' => 'Housewarming Wishlist',
                'list_name' => 'Housewarming wishlist',
            ],
            'anniversary' => [
                'slug' => 'anniversary-wishlist',
                'emoji' => '💍',
                'locales' => null,
                'category' => 'life-events',
                'page_title' => 'Anniversary Wishlist',
                'list_name' => 'Our anniversary wishlist',
            ],
        ];
    }

    /**
     * Check if an occasion should be shown for a given locale.
     */
    public static function shouldShow(string $occasion, ?string $locale = null): bool
    {
        $locale ??= app()->getLocale();
        $data = self::all()[$occasion] ?? null;

        return $data && ($data['locales'] === null || in_array($locale, $data['locales']));
    }

    /**
     * Get occasions by category for a given locale.
     *
     * @return array<string, array{slug: string, emoji: string, locales: array<string>|null, category: string, page_title: string, list_name: string}>
     */
    public static function getByCategory(string $locale, string $category): array
    {
        return array_filter(
            self::all(),
            fn ($occasion, $key) => $occasion['category'] === $category && self::shouldShow($key, $locale),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Get the occasion data by key.
     *
     * @return array{slug: string, emoji: string, locales: array<string>|null, category: string, page_title: string, list_name: string}|null
     */
    public static function get(string $occasion): ?array
    {
        return self::all()[$occasion] ?? null;
    }

    /**
     * Get all page content for rendering an occasion page.
     * Returns null if occasion doesn't exist, has no page content, or isn't available for the given locale.
     *
     * @return array{slug: string, emoji: string, locales: array<string>|null, category: string, page_title: string, list_name: string, hero: array, hero_gifts: array, givetwice: array, similar: array, final_cta: array, why?: array, about?: array, tips?: array, tips_title?: string}|null
     */
    public static function getPageContent(string $occasion, ?string $locale = null): ?array
    {
        if (! self::shouldShow($occasion, $locale)) {
            return null;
        }

        $content = self::allPageContent()[$occasion] ?? null;

        if (! $content) {
            return null;
        }

        $baseData = self::get($occasion);

        return array_merge($baseData, $content);
    }

    /**
     * All page-specific content for occasion marketing pages.
     *
     * @return array<string, array>
     */
    private static function allPageContent(): array
    {
        return [
            'birthday' => [
                'hero' => [
                    'h1_subtitle' => 'birthday wishlist',
                    'description' => "Stop pretending you don't know what you want. Make a list, share it, and actually get gifts you'll use.",
                    'bullets' => [
                        'No more duplicate gifts',
                        'Your friends know exactly what to buy',
                        'Every purchase helps charity too',
                    ],
                    'cta_text' => 'Start my birthday wishlist',
                    'cta_emoji' => '&#127873;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🎧', 'name' => 'Wireless Headphones', 'price' => 79, 'gradient' => 'from-blue-100 to-blue-200'],
                    ['emoji' => '👟', 'name' => 'Running Shoes', 'price' => 120, 'gradient' => 'from-amber-100 to-orange-200'],
                    ['emoji' => '📚', 'name' => 'Book Collection', 'price' => 45, 'gradient' => 'from-emerald-100 to-teal-200'],
                ],
                'why' => [
                    'title' => 'Why a birthday wishlist?',
                    'subtitle' => "Because \"I don't need anything\" is a lie we all tell.",
                    'benefits' => [
                        ['emoji' => '&#127873;', 'bg' => 'coral', 'title' => 'Get what you actually want', 'description' => "No more politely smiling at gifts you'll never use. Your friends get guidance, you get stuff you love."],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'No duplicate disasters', 'description' => "When someone claims a gift, others can see it's taken. Three people won't show up with the same book anymore."],
                        ['emoji' => '&#128522;', 'bg' => 'teal', 'title' => 'Take the guesswork out', 'description' => 'Your friends actually want to get you something good. A wishlist makes their job easier, not lazier.'],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-coral-50 to-sunny-50',
                    'border' => 'border-coral-100',
                    'title' => 'Your birthday, their kindness, twice the impact',
                    'description' => 'When friends buy gifts from your list, the stores pay us a commission. We donate 100% of that to charity. You get your gift, charity gets a donation, nobody pays extra.',
                    'link_text' => 'Learn how it works',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your birthday wishlist',
                'tips' => [
                    ['title' => 'Mix price ranges', 'description' => 'Add some small treats and some bigger wishes. Different budgets, different friends.'],
                    ['title' => 'Share early', 'description' => 'Give people time to shop around. Last-minute lists mean last-minute panic buying.'],
                    ['title' => 'Be specific', 'description' => "Don't just say \"headphones\". Link to the exact ones you want. Colors, sizes, all of it."],
                    ['title' => 'Add more than you expect', 'description' => 'Things get claimed fast. Keep your list stocked so latecomers have options too.'],
                ],
                'similar' => ['christmas', 'wedding', 'graduation', 'anniversary'],
                'final_cta' => [
                    'title' => 'Ready to make your birthday wishlist?',
                    'subtitle' => 'It takes about a minute. No credit card, no catch.',
                    'button_text' => 'Create my birthday wishlist',
                ],
            ],

            'christmas' => [
                'hero' => [
                    'h1_subtitle' => 'Christmas wishlist',
                    'description' => 'Make gift-giving easy for everyone. Share your list, avoid the duplicate sweater situation, and let the holiday stress melt away.',
                    'bullets' => [
                        'No more guessing what to buy',
                        'Avoid the dreaded gift duplicates',
                        'Holiday shopping that helps charity',
                    ],
                    'cta_text' => 'Start my Christmas wishlist',
                    'cta_emoji' => '&#127876;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🧣', 'name' => 'Cozy Sweater', 'price' => 65, 'gradient' => 'from-red-100 to-red-200'],
                    ['emoji' => '🎮', 'name' => 'Video Game', 'price' => 59, 'gradient' => 'from-emerald-100 to-emerald-200'],
                    ['emoji' => '📖', 'name' => 'Book Set', 'price' => 42, 'gradient' => 'from-amber-100 to-orange-200'],
                ],
                'why' => [
                    'title' => 'Why a Christmas wishlist?',
                    'subtitle' => 'Because the holidays are stressful enough without gift-giving chaos.',
                    'benefits' => [
                        ['emoji' => '&#127877;', 'bg' => 'coral', 'title' => 'End the guessing game', 'description' => "Your family won't have to wonder what you want. Your list tells them exactly what would make you happy."],
                        ['emoji' => '&#127881;', 'bg' => 'sunny', 'title' => 'Coordinate without spoiling', 'description' => "Family members can claim items secretly. No awkward \"who's getting what\" conversations needed."],
                        ['emoji' => '&#128166;', 'bg' => 'teal', 'title' => 'Less holiday stress', 'description' => 'Share your list early and let everyone shop at their own pace. No more December 23rd panic.'],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-teal-50 to-emerald-50',
                    'border' => 'border-teal-100',
                    'title' => 'Holiday giving that keeps on giving',
                    'description' => 'The holiday season is about generosity. When your family buys from your wishlist, we donate our affiliate commission to charity. Your gifts create two moments of joy - one for you, one for someone in need.',
                    'link_text' => 'See how it works',
                    'link_color' => 'teal',
                ],
                'tips_title' => 'Tips for your Christmas wishlist',
                'tips' => [
                    ['title' => 'Start early', 'description' => 'Share your list by early November. Black Friday deals wait for no one.'],
                    ['title' => 'Include stocking stuffers', 'description' => 'Small items under $20 are perfect for extended family or coworkers.'],
                    ['title' => 'Link specific products', 'description' => "Don't just say \"a nice candle\". Link to the exact one. Size, scent, everything."],
                    ['title' => 'Share the family list', 'description' => 'Get your whole family on GiveTwice. One link per person, everyone coordinates easily.'],
                    ['title' => 'Keep it updated', 'description' => 'Bought something for yourself? Remove it. Found something new? Add it. Keep your list fresh.'],
                    ['title' => 'Mix wants and needs', 'description' => "That fancy coffee maker you've been eyeing? Put it next to the cozy socks. Balance is good."],
                ],
                'similar' => ['birthday', 'hanukkah', 'valentines-day', 'easter'],
                'final_cta' => [
                    'title' => 'Ready to make your Christmas wishlist?',
                    'subtitle' => 'Takes about a minute. Your family will thank you.',
                    'button_text' => 'Create my Christmas wishlist',
                ],
            ],

            'wedding' => [
                'hero' => [
                    'h1_subtitle' => 'wedding wishlist',
                    'description' => 'A modern registry that works anywhere. No store restrictions, no awkward returns. Just the things you actually want for your new life together.',
                    'bullets' => [
                        'Add items from any store',
                        "Guests can see what's been claimed",
                        'Your celebration helps charity',
                    ],
                    'cta_text' => 'Start our wedding wishlist',
                    'cta_emoji' => '&#128141;',
                ],
                'hero_gifts' => [
                    ['emoji' => '☕', 'name' => 'Espresso Machine', 'price' => 299, 'gradient' => 'from-rose-100 to-rose-200'],
                    ['emoji' => '🍳', 'name' => 'Dutch Oven', 'price' => 180, 'gradient' => 'from-amber-100 to-orange-200'],
                    ['emoji' => '🛋️', 'name' => 'Throw Blanket', 'price' => 85, 'gradient' => 'from-blue-100 to-blue-200'],
                ],
                'why' => [
                    'title' => 'Why a wedding wishlist?',
                    'subtitle' => 'Traditional registries are limiting. You deserve better.',
                    'benefits' => [
                        ['emoji' => '&#127758;', 'bg' => 'coral', 'title' => 'Shop anywhere', 'description' => 'Not tied to one store. Add that vintage lamp from Etsy, the mixer from Amazon, the sheets from that local boutique.'],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'No duplicates', 'description' => "Guests secretly claim items when they buy them. No more coordinating who's getting what."],
                        ['emoji' => '&#128172;', 'bg' => 'teal', 'title' => 'Easy to share', 'description' => 'One link. Put it on your wedding website, text it to family, or include it in your invitations.'],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-coral-50 to-rose-50',
                    'border' => 'border-coral-100',
                    'title' => 'Start your marriage with a gift to others',
                    'description' => 'Your wedding is about celebrating love. When guests buy from your list, we donate our commission to charity. Your special day creates ripples of kindness beyond your celebration.',
                    'link_text' => 'How our charity model works',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your wedding wishlist',
                'tips' => [
                    ['title' => 'Start with the essentials', 'description' => "Kitchen basics, quality linens, everyday items you'll actually use. Build from there."],
                    ['title' => 'Range the prices', 'description' => 'Mix $30 items with $300 items. Different guests have different budgets.'],
                    ['title' => 'Think about your space', 'description' => 'Moving into a studio? Skip the stand mixer. Your list should fit your actual life.'],
                    ['title' => 'Add experiences too', 'description' => 'Date night gift cards, cooking class vouchers, honeymoon contributions. Not everything has to be physical.'],
                    ['title' => 'Update as you go', 'description' => 'Engagement can be long. Your tastes might change. Keep the list fresh.'],
                    ['title' => 'Share early', 'description' => 'Let guests know where to find your list. Wedding website, save-the-dates, word of mouth.'],
                ],
                'similar' => ['housewarming', 'anniversary', 'baby-shower', 'birthday'],
                'final_cta' => [
                    'title' => 'Ready to create your wedding wishlist?',
                    'subtitle' => 'Free, simple, and your guests will thank you for making their job easier.',
                    'button_text' => 'Create our wedding wishlist',
                ],
            ],

            'valentines-day' => [
                'hero' => [
                    'h1_subtitle' => "Valentine's wishlist",
                    'description' => "Skip the guessing game. Share what you'd actually love and make February 14th about genuine connection, not retail anxiety.",
                    'bullets' => [
                        'No more gift guessing stress',
                        'Add items from any store',
                        'Love that gives twice',
                    ],
                    'cta_text' => "Start my Valentine's wishlist",
                    'cta_emoji' => '&#128157;',
                ],
                'hero_gifts' => [
                    ['emoji' => '💐', 'name' => 'Flower Subscription', 'price' => 45, 'gradient' => 'from-rose-100 to-rose-200'],
                    ['emoji' => '🍫', 'name' => 'Chocolate Box', 'price' => 35, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '📚', 'name' => 'Romance Novel', 'price' => 18, 'gradient' => 'from-pink-100 to-pink-200'],
                ],
                'why' => [
                    'title' => "Why a Valentine's wishlist?",
                    'subtitle' => "Because mind reading isn't real, even in relationships.",
                    'benefits' => [
                        ['emoji' => '&#128149;', 'bg' => 'coral', 'title' => 'Take the pressure off', 'description' => 'Stop stressing about finding the "perfect" surprise. Knowing what they want is romantic too.'],
                        ['emoji' => '&#127873;', 'bg' => 'sunny', 'title' => 'Get meaningful gifts', 'description' => "No more generic chocolates or sad gas station roses. Share ideas for things you'll actually treasure."],
                        ['emoji' => '&#128522;', 'bg' => 'teal', 'title' => 'Less retail stress', 'description' => "Your partner won't wander the mall in desperation. They'll know exactly where to go."],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-rose-50 to-pink-50',
                    'border' => 'border-rose-100',
                    'title' => 'Love that spreads further',
                    'description' => "Valentine's Day is about showing love. When your partner buys from your list, we donate our commission to charity. Your celebration becomes a gift to others too.",
                    'link_text' => 'Learn how we give',
                    'link_color' => 'coral',
                ],
                'tips_title' => "Tips for your Valentine's wishlist",
                'tips' => [
                    ['title' => 'Mix romance with practicality', 'description' => "That massage gun you've been wanting? Just as valid as jewelry."],
                    ['title' => 'Include experiences', 'description' => 'Concert tickets, spa vouchers, cooking class for two. Memories matter.'],
                    ['title' => 'Share early', 'description' => "Don't make your partner scramble on February 13th. Give them time."],
                    ['title' => 'Keep it personal', 'description' => 'Add things that show your personality. Inside jokes welcome.'],
                ],
                'similar' => ['anniversary', 'birthday', 'wedding'],
                'final_cta' => [
                    'title' => "Ready to create your Valentine's wishlist?",
                    'subtitle' => 'Help your partner help you.',
                    'button_text' => "Create my Valentine's wishlist",
                ],
            ],

            'easter' => [
                'hero' => [
                    'h1_subtitle' => 'Easter wishlist',
                    'description' => 'Beyond chocolate bunnies. Share ideas for meaningful spring gifts the whole family can coordinate on.',
                    'bullets' => [
                        'More than just candy',
                        'Help family coordinate',
                        'Spring gifts that give twice',
                    ],
                    'cta_text' => 'Start my Easter wishlist',
                    'cta_emoji' => '&#128035;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🌷', 'name' => 'Garden Set', 'price' => 45, 'gradient' => 'from-pink-100 to-pink-200'],
                    ['emoji' => '📚', 'name' => 'Spring Reading', 'price' => 28, 'gradient' => 'from-yellow-100 to-yellow-200'],
                    ['emoji' => '🎨', 'name' => 'Art Supplies', 'price' => 35, 'gradient' => 'from-green-100 to-green-200'],
                ],
                'why' => [
                    'title' => 'Why an Easter wishlist?',
                    'subtitle' => 'Make spring gifting thoughtful, not stressful.',
                    'benefits' => [
                        ['emoji' => '&#127799;', 'bg' => 'coral', 'title' => 'Beyond the basket', 'description' => "Easter doesn't have to mean only candy. Add books, games, outdoor gear, or anything that brings spring joy."],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'Family coordination', 'description' => 'Grandparents, aunts, uncles all want to give something. A shared list prevents duplicate bunnies.'],
                        ['emoji' => '&#127793;', 'bg' => 'teal', 'title' => 'Celebrate renewal', 'description' => 'Spring is about new beginnings. Start a new tradition of thoughtful, coordinated giving.'],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-yellow-50 to-green-50',
                    'border' => 'border-yellow-100',
                    'title' => 'Spring generosity blooms',
                    'description' => 'Easter celebrates renewal and hope. When family buys from your list, we donate our commission to charity. Your spring celebration plants seeds of kindness.',
                    'link_text' => 'See how giving works',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your Easter wishlist',
                'tips' => [
                    ['title' => 'Think beyond candy', 'description' => 'Books, games, outdoor toys, art supplies. Easter baskets can hold more than chocolate.'],
                    ['title' => 'Add spring activities', 'description' => 'Garden kits, sports equipment, picnic supplies. Embrace the season.'],
                    ['title' => 'Include all ages', 'description' => "Adults deserve Easter gifts too. Don't limit your thinking."],
                    ['title' => 'Share with family', 'description' => 'Send the link to relatives who always ask what to get.'],
                ],
                'similar' => ['christmas', 'birthday', 'mothers-day', 'fathers-day'],
                'final_cta' => [
                    'title' => 'Ready to create your Easter wishlist?',
                    'subtitle' => 'Make spring gifting meaningful.',
                    'button_text' => 'Create my Easter wishlist',
                ],
            ],

            'mothers-day' => [
                'hero' => [
                    'h1_subtitle' => "Mother's Day wishlist",
                    'description' => "Help your family get it right this year. Share what you'd actually love instead of hinting and hoping.",
                    'bullets' => [
                        'Skip the guessing game',
                        'Get gifts you really want',
                        'Make giving easy for them',
                    ],
                    'cta_text' => "Create my Mother's Day wishlist",
                    'cta_emoji' => '&#128144;',
                ],
                'hero_gifts' => [
                    ['emoji' => '💐', 'name' => 'Flower Delivery', 'price' => 55, 'gradient' => 'from-pink-100 to-pink-200'],
                    ['emoji' => '📚', 'name' => 'Book Club Pick', 'price' => 28, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '🧖', 'name' => 'Spa Gift Set', 'price' => 75, 'gradient' => 'from-teal-100 to-teal-200'],
                ],
                'why' => [
                    'title' => "Why a Mother's Day wishlist?",
                    'subtitle' => 'Because you deserve more than breakfast in bed.',
                    'benefits' => [
                        ['emoji' => '&#127873;', 'bg' => 'coral', 'title' => 'Finally get what you want', 'description' => "No more generic gift sets gathering dust. Share specific things you'll actually use and love."],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'Help the family coordinate', 'description' => "Kids, partner, relatives - everyone can see what's been claimed. No more duplicate bath bombs."],
                        ['emoji' => '&#128150;', 'bg' => 'teal', 'title' => "It's okay to ask", 'description' => "Sharing what you want isn't demanding. It's helping your family show their love in ways you'll appreciate."],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-pink-50 to-coral-50',
                    'border' => 'border-pink-100',
                    'title' => "A mother's love, extended",
                    'description' => "Moms spend their lives giving. This Mother's Day, when your family buys from your list, we donate our commission to charity. Your celebration creates ripples of caring.",
                    'link_text' => 'How we give back',
                    'link_color' => 'coral',
                ],
                'tips_title' => "Tips for your Mother's Day wishlist",
                'tips' => [
                    ['title' => 'Include self-care', 'description' => "Spa products, quiet-time treats, things just for you. You've earned it."],
                    ['title' => 'Add experiences', 'description' => 'Brunch gift cards, massage vouchers, concert tickets. Time matters too.'],
                    ['title' => 'Mix price points', 'description' => 'Small treats to big wishes. Different family members, different budgets.'],
                    ['title' => 'Share it directly', 'description' => "Send the link to your partner and kids. Don't make them guess."],
                ],
                'similar' => ['birthday', 'fathers-day', 'anniversary', 'valentines-day'],
                'final_cta' => [
                    'title' => "Ready to create your Mother's Day wishlist?",
                    'subtitle' => 'Help your family celebrate you properly.',
                    'button_text' => "Create my Mother's Day wishlist",
                ],
            ],

            'fathers-day' => [
                'hero' => [
                    'h1_subtitle' => "Father's Day wishlist",
                    'description' => 'Save your family from another novelty tie. Share what you actually want and make their shopping easy.',
                    'bullets' => [
                        'No more "World\'s Best Dad" mugs',
                        'Get gifts you\'ll actually use',
                        'Help them help you',
                    ],
                    'cta_text' => "Create my Father's Day wishlist",
                    'cta_emoji' => '&#128084;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🔧', 'name' => 'Tool Set', 'price' => 89, 'gradient' => 'from-slate-100 to-slate-200'],
                    ['emoji' => '🎧', 'name' => 'Wireless Earbuds', 'price' => 79, 'gradient' => 'from-blue-100 to-blue-200'],
                    ['emoji' => '📚', 'name' => 'Biography Book', 'price' => 28, 'gradient' => 'from-amber-100 to-amber-200'],
                ],
                'why' => [
                    'title' => "Why a Father's Day wishlist?",
                    'subtitle' => 'Because "I don\'t need anything" helps no one.',
                    'benefits' => [
                        ['emoji' => '&#127873;', 'bg' => 'coral', 'title' => 'Get stuff you want', 'description' => "Instead of things you'll hide in a drawer, get tools, gear, or experiences you'll actually enjoy."],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'Make it easy', 'description' => 'Your family wants to celebrate you. A list gives them direction without the guesswork.'],
                        ['emoji' => '&#128170;', 'bg' => 'teal', 'title' => 'Skip the stress', 'description' => 'No one wandering the mall wondering what to buy. One link, clear ideas, done.'],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-slate-50 to-blue-50',
                    'border' => 'border-slate-200',
                    'title' => "Dad's gift, everyone's benefit",
                    'description' => "When your family buys from your list, we donate our commission to charity. Your Father's Day celebration becomes a gift to others too. That's the kind of efficiency dads appreciate.",
                    'link_text' => 'See how it works',
                    'link_color' => 'coral',
                ],
                'tips_title' => "Tips for your Father's Day wishlist",
                'tips' => [
                    ['title' => 'Think hobbies', 'description' => 'Golfing gear, grilling tools, fishing equipment. What do you actually do for fun?'],
                    ['title' => 'Include experiences', 'description' => 'Sports tickets, concert passes, a nice dinner out. Memories count.'],
                    ['title' => 'Add practical items', 'description' => "That drill bit set you've been needing? Put it on there."],
                    ['title' => 'Share without shame', 'description' => "Send the link to your partner and kids. Helping them isn't greedy."],
                ],
                'similar' => ['birthday', 'mothers-day', 'anniversary', 'christmas'],
                'final_cta' => [
                    'title' => "Ready to create your Father's Day wishlist?",
                    'subtitle' => 'Help your family get it right this year.',
                    'button_text' => "Create my Father's Day wishlist",
                ],
            ],

            'thanksgiving' => [
                'hero' => [
                    'h1_subtitle' => 'Thanksgiving wishlist',
                    'description' => 'Thanksgiving gatherings often include gift exchanges. Share ideas so family can give thoughtfully.',
                    'bullets' => [
                        'Organize family gift exchanges',
                        'Coordinate with extended family',
                        'Thankful giving that helps others',
                    ],
                    'cta_text' => 'Start my Thanksgiving wishlist',
                    'cta_emoji' => '&#129411;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🍳', 'name' => 'Kitchen Gadget', 'price' => 45, 'gradient' => 'from-orange-100 to-orange-200'],
                    ['emoji' => '📚', 'name' => 'Cookbook', 'price' => 32, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '🕯️', 'name' => 'Candle Set', 'price' => 28, 'gradient' => 'from-yellow-100 to-yellow-200'],
                ],
                'why' => [
                    'title' => 'Why a Thanksgiving wishlist?',
                    'subtitle' => 'Many families exchange gifts during Thanksgiving gatherings.',
                    'benefits' => [
                        ['emoji' => '&#127869;', 'bg' => 'coral', 'title' => 'Organize exchanges', 'description' => 'Family reunions often include gift swaps. A wishlist keeps everyone coordinated.'],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'Extended family coordination', 'description' => "When the whole clan gathers, gifts happen. Make sure no one's guessing."],
                        ['emoji' => '&#128588;', 'bg' => 'teal', 'title' => 'Express gratitude', 'description' => 'Thanksgiving is about appreciation. Thoughtful gifts show you care about what others actually want.'],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-orange-50 to-amber-50',
                    'border' => 'border-orange-100',
                    'title' => 'Thankfulness that reaches further',
                    'description' => 'Thanksgiving reminds us to be grateful and generous. When family buys from your list, we donate our commission to charity. Your gathering spreads gratitude beyond your table.',
                    'link_text' => 'Learn about our giving',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your Thanksgiving wishlist',
                'tips' => [
                    ['title' => 'Think seasonal', 'description' => 'Cozy blankets, warm accessories, fall-themed items fit the mood.'],
                    ['title' => 'Include kitchen items', 'description' => 'Thanksgiving is about food. Tools for cooking and hosting are perfect.'],
                    ['title' => 'Add experiences', 'description' => 'Gift cards to restaurants, movie tickets for the long weekend.'],
                    ['title' => 'Share with the host', 'description' => 'Hosting gifts are always appreciated. Help guests know what to bring.'],
                ],
                'similar' => ['christmas', 'birthday', 'housewarming'],
                'final_cta' => [
                    'title' => 'Ready to create your Thanksgiving wishlist?',
                    'subtitle' => 'Make family gift exchanges thoughtful.',
                    'button_text' => 'Create my Thanksgiving wishlist',
                ],
            ],

            'sinterklaas' => [
                'hero' => [
                    'h1_subtitle' => 'Sinterklaas wishlist',
                    'description' => 'Make it easy for family and friends. Share your wishes and avoid duplicate gifts this Sinterklaas.',
                    'bullets' => [
                        'No more duplicate gifts',
                        'Family can easily choose',
                        'Shoe gifts that give twice',
                    ],
                    'cta_text' => 'Start my Sinterklaas wishlist',
                    'cta_emoji' => '&#127877;',
                ],
                'hero_gifts' => [
                    ['emoji' => '📚', 'name' => 'Books', 'price' => 25, 'gradient' => 'from-red-100 to-red-200'],
                    ['emoji' => '🎮', 'name' => 'Board Games', 'price' => 35, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '🧣', 'name' => 'Warm Scarf', 'price' => 45, 'gradient' => 'from-orange-100 to-orange-200'],
                ],
                'why' => [
                    'title' => 'Why a Sinterklaas wishlist?',
                    'subtitle' => 'Because it makes pakjesavond more fun for everyone.',
                    'benefits' => [
                        ['emoji' => '&#127873;', 'bg' => 'coral', 'title' => 'No more stress', 'description' => "Family doesn't have to guess anymore. They know exactly what makes you happy."],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'Smart coordination', 'description' => "Everyone can see what's been claimed. No more three identical books."],
                        ['emoji' => '&#127876;', 'bg' => 'teal', 'title' => 'More celebration fun', 'description' => 'Less hassle with gifts means more time for pepernoten and surprises.'],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-red-50 to-orange-50',
                    'border' => 'border-red-100',
                    'title' => 'Sinterklaas with a good cause',
                    'description' => 'Sinterklaas is all about giving. When family buys something from your list, we donate our commission to charity. Your pakjesavond brings joy to others too.',
                    'link_text' => 'Learn how it works',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your Sinterklaas wishlist',
                'tips' => [
                    ['title' => 'Mix big and small wishes', 'description' => 'From shoe gifts to bigger wishes. Something for every budget.'],
                    ['title' => 'Share on time', 'description' => 'Give people time to shop. Not everything can be last-minute.'],
                    ['title' => 'Be specific', 'description' => 'Link to exactly the product you want. Size, color, everything.'],
                    ['title' => 'Think about surprises', 'description' => 'Add things that could fit in a creative surprise package.'],
                ],
                'similar' => ['christmas', 'birthday', 'easter'],
                'final_cta' => [
                    'title' => 'Ready to create your Sinterklaas wishlist?',
                    'subtitle' => 'Make pakjesavond easy for everyone.',
                    'button_text' => 'Create my Sinterklaas wishlist',
                ],
            ],

            'baby-shower' => [
                'hero' => [
                    'h1_subtitle' => 'baby shower wishlist',
                    'description' => 'Help friends and family prepare for your little one. Share exactly what you need and avoid the sixth baby blanket.',
                    'bullets' => [
                        'Get exactly what baby needs',
                        'Avoid duplicate gifts',
                        'Every gift helps charity too',
                    ],
                    'cta_text' => 'Start our baby shower wishlist',
                    'cta_emoji' => '&#128118;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🍼', 'name' => 'Bottle Set', 'price' => 35, 'gradient' => 'from-pink-100 to-pink-200'],
                    ['emoji' => '👶', 'name' => 'Baby Monitor', 'price' => 120, 'gradient' => 'from-blue-100 to-blue-200'],
                    ['emoji' => '🧸', 'name' => 'Soft Toys', 'price' => 25, 'gradient' => 'from-yellow-100 to-yellow-200'],
                ],
                'why' => [
                    'title' => 'Why a baby shower wishlist?',
                    'subtitle' => "Because babies need specific things, and guessing doesn't help.",
                    'benefits' => [
                        ['emoji' => '&#128118;', 'bg' => 'coral', 'title' => 'Get what baby needs', 'description' => 'Diapers in the right size, the specific bottle brand that works, that exact stroller. No more returns.'],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'Coordinate gifts', 'description' => "Friends can see what's been claimed. No more three baby bathtubs at the shower."],
                        ['emoji' => '&#128150;', 'bg' => 'teal', 'title' => 'Focus on celebrating', 'description' => 'Less stress about gifts means more energy for the fun parts of preparing for baby.'],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-pink-50 to-blue-50',
                    'border' => 'border-pink-100',
                    'title' => 'Welcoming baby, helping others',
                    'description' => 'A new life is a time of hope and generosity. When loved ones buy from your list, we donate our commission to charity. Your growing family helps others too.',
                    'link_text' => 'Learn how we give',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your baby shower wishlist',
                'tips' => [
                    ['title' => 'Include essentials', 'description' => 'Diapers, wipes, basics. Not glamorous, but genuinely helpful.'],
                    ['title' => 'Add different sizes', 'description' => 'Babies grow fast. Include items for months 0-3, 3-6, and beyond.'],
                    ['title' => 'Mix price points', 'description' => 'Some big-ticket items, lots of smaller ones. Options for every budget.'],
                    ['title' => 'Be specific', 'description' => "Don't just say \"bottles.\" Link to the exact brand, size, everything."],
                ],
                'similar' => ['wedding', 'birthday', 'housewarming'],
                'final_cta' => [
                    'title' => 'Ready to create your baby shower wishlist?',
                    'subtitle' => 'Help loved ones prepare for your little one.',
                    'button_text' => 'Create our baby shower wishlist',
                ],
            ],

            'graduation' => [
                'hero' => [
                    'h1_subtitle' => 'graduation wishlist',
                    'description' => "You worked hard for this. Help family and friends celebrate your achievement with gifts you'll actually use in your next chapter.",
                    'bullets' => [
                        'Gifts for your next chapter',
                        'Help guests choose wisely',
                        'Celebration that gives twice',
                    ],
                    'cta_text' => 'Start my graduation wishlist',
                    'cta_emoji' => '&#127891;',
                ],
                'hero_gifts' => [
                    ['emoji' => '💻', 'name' => 'Laptop Stand', 'price' => 45, 'gradient' => 'from-blue-100 to-blue-200'],
                    ['emoji' => '👔', 'name' => 'Professional Outfit', 'price' => 150, 'gradient' => 'from-slate-100 to-slate-200'],
                    ['emoji' => '📚', 'name' => 'Career Books', 'price' => 35, 'gradient' => 'from-amber-100 to-amber-200'],
                ],
                'why' => [
                    'title' => 'Why a graduation wishlist?',
                    'subtitle' => "Because you're starting a new chapter and could use the right gear.",
                    'benefits' => [
                        ['emoji' => '&#127891;', 'bg' => 'coral', 'title' => 'Prepare for what\'s next', 'description' => "Starting a job? Moving out? Add what you'll actually need for this next phase of life."],
                        ['emoji' => '&#128176;', 'bg' => 'sunny', 'title' => 'Cash is fine too', 'description' => 'Many lists include cash funds. Student loans, apartment deposits, travel - it all counts.'],
                        ['emoji' => '&#127881;', 'bg' => 'teal', 'title' => 'Celebrate properly', 'description' => "You've earned this. Make it easy for people who want to honor your achievement."],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-blue-50 to-slate-50',
                    'border' => 'border-blue-100',
                    'title' => 'Your achievement, extended impact',
                    'description' => 'Graduation marks the start of giving back. When guests buy from your list, we donate our commission to charity. Your milestone celebration helps others reach theirs.',
                    'link_text' => 'How our model works',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your graduation wishlist',
                'tips' => [
                    ['title' => 'Think practical', 'description' => 'Professional clothes, apartment essentials, career tools. Real life is coming.'],
                    ['title' => 'Include experiences', 'description' => 'A celebratory trip, nice dinner, something memorable before the grind starts.'],
                    ['title' => 'Add learning', 'description' => 'Books, courses, subscriptions that help you grow in your field.'],
                    ['title' => 'Share widely', 'description' => 'Relatives at the party will ask what you want. Have an answer ready.'],
                ],
                'similar' => ['birthday', 'housewarming', 'wedding'],
                'final_cta' => [
                    'title' => 'Ready to create your graduation wishlist?',
                    'subtitle' => 'Help guests celebrate your achievement the right way.',
                    'button_text' => 'Create my graduation wishlist',
                ],
            ],

            'housewarming' => [
                'hero' => [
                    'h1_subtitle' => 'housewarming wishlist',
                    'description' => "Moving into a new place? Help friends and family help you make it home with things you'll actually use.",
                    'bullets' => [
                        'Furnish your new space',
                        'Avoid duplicate gifts',
                        'New home, good karma',
                    ],
                    'cta_text' => 'Start my housewarming wishlist',
                    'cta_emoji' => '&#127968;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🪴', 'name' => 'Indoor Plant', 'price' => 35, 'gradient' => 'from-green-100 to-green-200'],
                    ['emoji' => '🍳', 'name' => 'Cookware Set', 'price' => 120, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '🛋️', 'name' => 'Throw Pillows', 'price' => 45, 'gradient' => 'from-blue-100 to-blue-200'],
                ],
                'why' => [
                    'title' => 'Why a housewarming wishlist?',
                    'subtitle' => 'Because empty shelves need filling and friends want to help.',
                    'benefits' => [
                        ['emoji' => '&#127968;', 'bg' => 'coral', 'title' => 'Get what you need', 'description' => "A new place needs a lot. Kitchen basics, bathroom essentials, things you didn't know you needed."],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'Coordinate visitors', 'description' => 'People coming to see your new place want to bring something. A list ensures useful gifts.'],
                        ['emoji' => '&#127793;', 'bg' => 'teal', 'title' => 'Build your home', 'description' => "From plants to pots, create the space you've been imagining with help from people who care."],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-green-50 to-amber-50',
                    'border' => 'border-green-100',
                    'title' => 'New home, new good',
                    'description' => 'A new home is a fresh start. When friends buy from your list, we donate our commission to charity. Your new chapter opens doors for others too.',
                    'link_text' => 'See how giving works',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your housewarming wishlist',
                'tips' => [
                    ['title' => 'Start with basics', 'description' => 'Kitchen essentials, cleaning supplies, bathroom necessities. Boring but vital.'],
                    ['title' => 'Add personality', 'description' => 'Art, plants, decorative items. The things that make a house feel yours.'],
                    ['title' => 'Think long-term', 'description' => 'Quality items you\'ll use for years. This is investment territory.'],
                    ['title' => 'Include tools', 'description' => "You'll be hanging things, fixing things, building things. Be ready."],
                ],
                'similar' => ['wedding', 'graduation', 'baby-shower'],
                'final_cta' => [
                    'title' => 'Ready to create your housewarming wishlist?',
                    'subtitle' => 'Help friends help you make your house a home.',
                    'button_text' => 'Create my housewarming wishlist',
                ],
            ],

            'communion' => [
                'hero' => [
                    'h1_subtitle' => 'communion wishlist',
                    'description' => 'Help family and friends choose meaningful gifts for this important milestone in faith.',
                    'bullets' => [
                        'Meaningful milestone gifts',
                        'Help guests choose wisely',
                        'Celebration that gives twice',
                    ],
                    'cta_text' => 'Start my communion wishlist',
                    'cta_emoji' => '&#10013;',
                ],
                'hero_gifts' => [
                    ['emoji' => '📚', 'name' => 'Book Collection', 'price' => 35, 'gradient' => 'from-blue-100 to-blue-200'],
                    ['emoji' => '✝️', 'name' => 'Cross Necklace', 'price' => 45, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '🎁', 'name' => 'Special Gift', 'price' => 50, 'gradient' => 'from-purple-100 to-purple-200'],
                ],
                'why' => [
                    'title' => 'Why a communion wishlist?',
                    'subtitle' => 'Help guests honor this milestone meaningfully.',
                    'benefits' => [
                        ['emoji' => '&#127873;', 'bg' => 'coral', 'title' => 'Meaningful gifts', 'description' => 'Guide family toward gifts that honor this special day and will be treasured.'],
                        ['emoji' => '&#128101;', 'bg' => 'sunny', 'title' => 'Coordinate family', 'description' => 'Grandparents, godparents, aunts and uncles all want to give. Help them coordinate.'],
                        ['emoji' => '&#128150;', 'bg' => 'teal', 'title' => 'Focus on celebration', 'description' => 'Less gift stress means more focus on the spiritual significance of the day.'],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-blue-50 to-purple-50',
                    'border' => 'border-blue-100',
                    'title' => 'Faith in action',
                    'description' => 'Communion celebrates faith and community. When guests buy from your list, we donate our commission to charity. Your celebration extends the spirit of giving to those in need.',
                    'link_text' => 'Learn about our giving',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your communion wishlist',
                'tips' => [
                    ['title' => 'Mix meaningful and practical', 'description' => 'Religious items alongside things they\'ll use everyday.'],
                    ['title' => 'Include keepsakes', 'description' => "Things they'll treasure as reminders of this special day."],
                    ['title' => 'Add experiences', 'description' => 'A special outing, nice dinner, or memorable activity.'],
                    ['title' => 'Range the prices', 'description' => 'Options for close family and casual guests alike.'],
                ],
                'similar' => ['birthday', 'graduation', 'christmas'],
                'final_cta' => [
                    'title' => 'Ready to create your communion wishlist?',
                    'subtitle' => 'Help guests honor this milestone.',
                    'button_text' => 'Create my communion wishlist',
                ],
            ],

            'anniversary' => [
                'hero' => [
                    'h1_subtitle' => 'anniversary wishlist',
                    'description' => 'Celebrating another year together? Help each other (and guests at milestone parties) choose the perfect gifts.',
                    'bullets' => [
                        'Gifts you both want',
                        'No more guessing',
                        'Love that gives twice',
                    ],
                    'cta_text' => 'Start our anniversary wishlist',
                    'cta_emoji' => '&#128141;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🍷', 'name' => 'Wine Experience', 'price' => 85, 'gradient' => 'from-rose-100 to-rose-200'],
                    ['emoji' => '✈️', 'name' => 'Travel Fund', 'price' => 200, 'gradient' => 'from-blue-100 to-blue-200'],
                    ['emoji' => '📸', 'name' => 'Photo Album', 'price' => 45, 'gradient' => 'from-amber-100 to-amber-200'],
                ],
                'why' => [
                    'title' => 'Why an anniversary wishlist?',
                    'subtitle' => "Because after all this time, you shouldn't still be guessing.",
                    'benefits' => [
                        ['emoji' => '&#128149;', 'bg' => 'coral', 'title' => 'Know what they want', 'description' => "After years together, surprises are nice. But so is getting something you'll actually love."],
                        ['emoji' => '&#127881;', 'bg' => 'sunny', 'title' => 'Milestone parties', 'description' => 'Big anniversary? Guests will want to bring gifts. A shared list keeps everyone coordinated.'],
                        ['emoji' => '&#128150;', 'bg' => 'teal', 'title' => 'Experiences count', 'description' => "Trips, dinners, classes together. The best anniversary gifts often aren't physical."],
                    ],
                ],
                'givetwice' => [
                    'gradient' => 'from-rose-50 to-coral-50',
                    'border' => 'border-rose-100',
                    'title' => 'Years of love, extended',
                    'description' => 'Anniversaries celebrate lasting love. When gifts come from your list, we donate our commission to charity. Your years together become a gift to others too.',
                    'link_text' => 'How giving works',
                    'link_color' => 'coral',
                ],
                'tips_title' => 'Tips for your anniversary wishlist',
                'tips' => [
                    ['title' => 'Think experiences', 'description' => 'A trip, a fancy dinner, a cooking class together. Shared memories last.'],
                    ['title' => 'Include upgrades', 'description' => 'Better versions of things you use together. Kitchen, bedroom, living room.'],
                    ['title' => 'Add a trip fund', 'description' => 'Guests at big anniversaries often prefer contributing to something meaningful.'],
                    ['title' => 'Be romantic', 'description' => 'This is the one time sentimental gifts are definitely appropriate.'],
                ],
                'similar' => ['wedding', 'valentines-day', 'birthday'],
                'final_cta' => [
                    'title' => 'Ready to create your anniversary wishlist?',
                    'subtitle' => 'Celebrate your years together with gifts you both want.',
                    'button_text' => 'Create our anniversary wishlist',
                ],
            ],

            // Minimal template pages (religious occasions)
            'hanukkah' => [
                'hero' => [
                    'h1_subtitle' => 'Hanukkah wishlist',
                    'description' => 'Eight nights of thoughtful gifting. Share ideas with family so each evening brings something meaningful.',
                    'bullets' => [
                        'Plan eight nights of gifts',
                        'Help family choose meaningfully',
                        'Festival gifts that give twice',
                    ],
                    'cta_text' => 'Start my Hanukkah wishlist',
                    'cta_emoji' => '&#128334;',
                ],
                'hero_gifts' => [
                    ['emoji' => '📚', 'name' => 'Book Collection', 'price' => 45, 'gradient' => 'from-blue-100 to-blue-200'],
                    ['emoji' => '🎮', 'name' => 'Board Game', 'price' => 35, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '🎧', 'name' => 'Headphones', 'price' => 85, 'gradient' => 'from-slate-100 to-slate-200'],
                ],
                'about' => [
                    'title' => 'Thoughtful gifting for the Festival of Lights',
                    'text' => 'Hanukkah celebrates miracles and perseverance. A wishlist helps family and friends give thoughtfully, ensuring each of the eight nights brings joy. Share what matters to you.',
                ],
                'givetwice' => [
                    'gradient' => 'from-blue-50 to-amber-50',
                    'border' => 'border-blue-100',
                    'title' => 'Gifts that spread light',
                    'description' => "The Festival of Lights reminds us to bring light to darkness. When family buys from your list, we donate our commission to charity. Your celebration helps illuminate others' lives too.",
                    'link_text' => 'Learn about our giving model',
                    'link_color' => 'coral',
                ],
                'similar' => ['christmas', 'birthday', 'diwali'],
                'final_cta' => [
                    'title' => 'Ready to create your Hanukkah wishlist?',
                    'subtitle' => 'Help family make each of the eight nights meaningful.',
                    'button_text' => 'Start my Hanukkah wishlist',
                ],
            ],

            'eid' => [
                'hero' => [
                    'h1_subtitle' => 'Eid wishlist',
                    'description' => 'Celebrate Eid with thoughtful gifts from family and friends. Share your wishlist to make the celebration meaningful.',
                    'bullets' => [
                        'Meaningful gifts for Eid',
                        'Help family give thoughtfully',
                        'Celebration that helps charity',
                    ],
                    'cta_text' => 'Start my Eid wishlist',
                    'cta_emoji' => '&#127769;',
                ],
                'hero_gifts' => [
                    ['emoji' => '👔', 'name' => 'New Outfit', 'price' => 75, 'gradient' => 'from-green-100 to-green-200'],
                    ['emoji' => '📚', 'name' => 'Book Collection', 'price' => 45, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '🎁', 'name' => 'Gift Set', 'price' => 55, 'gradient' => 'from-purple-100 to-purple-200'],
                ],
                'about' => [
                    'title' => 'Thoughtful gifting for Eid',
                    'text' => 'Eid is a time of gratitude, family, and celebration. A wishlist helps loved ones give meaningful gifts that honor this special occasion. Share what would bring you joy.',
                ],
                'givetwice' => [
                    'gradient' => 'from-green-50 to-emerald-50',
                    'border' => 'border-green-100',
                    'title' => 'Generosity multiplied',
                    'description' => 'Eid celebrates gratitude and generosity. When family buys from your list, we donate our commission to charity. Your celebration extends kindness to those in need.',
                    'link_text' => 'Learn about our giving model',
                    'link_color' => 'coral',
                ],
                'similar' => ['birthday', 'diwali', 'lunar-new-year'],
                'final_cta' => [
                    'title' => 'Ready to create your Eid wishlist?',
                    'subtitle' => 'Help family celebrate Eid with meaningful gifts.',
                    'button_text' => 'Start my Eid wishlist',
                ],
            ],

            'diwali' => [
                'hero' => [
                    'h1_subtitle' => 'Diwali wishlist',
                    'description' => 'Celebrate the Festival of Lights with meaningful gifts. Share your wishlist so family can give thoughtfully.',
                    'bullets' => [
                        'Celebrate with meaningful gifts',
                        'Help family choose wisely',
                        'Festival gifts that give twice',
                    ],
                    'cta_text' => 'Start my Diwali wishlist',
                    'cta_emoji' => '&#129684;',
                ],
                'hero_gifts' => [
                    ['emoji' => '👗', 'name' => 'New Clothes', 'price' => 85, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '🎁', 'name' => 'Gift Hamper', 'price' => 65, 'gradient' => 'from-orange-100 to-orange-200'],
                    ['emoji' => '📚', 'name' => 'Book Set', 'price' => 45, 'gradient' => 'from-purple-100 to-purple-200'],
                ],
                'about' => [
                    'title' => 'Thoughtful gifting for Diwali',
                    'text' => 'Diwali celebrates the victory of light over darkness, knowledge over ignorance. A wishlist helps family give meaningful gifts that honor this auspicious time. Share what would brighten your celebration.',
                ],
                'givetwice' => [
                    'gradient' => 'from-amber-50 to-orange-50',
                    'border' => 'border-amber-100',
                    'title' => 'Light that spreads further',
                    'description' => 'Diwali reminds us that one light can illuminate many. When family buys from your list, we donate our commission to charity. Your celebration spreads light to others.',
                    'link_text' => 'Learn about our giving model',
                    'link_color' => 'coral',
                ],
                'similar' => ['birthday', 'hanukkah', 'lunar-new-year'],
                'final_cta' => [
                    'title' => 'Ready to create your Diwali wishlist?',
                    'subtitle' => 'Help family celebrate with meaningful gifts.',
                    'button_text' => 'Start my Diwali wishlist',
                ],
            ],

            'lunar-new-year' => [
                'hero' => [
                    'h1_subtitle' => 'Lunar New Year wishlist',
                    'description' => 'Welcome the new year with thoughtful gifts. Share your wishlist with family for a prosperous and joyful celebration.',
                    'bullets' => [
                        'Start the year with meaningful gifts',
                        'Help family give thoughtfully',
                        'New year gifts that give twice',
                    ],
                    'cta_text' => 'Start my Lunar New Year wishlist',
                    'cta_emoji' => '&#129511;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🧧', 'name' => 'Red Envelope Set', 'price' => 25, 'gradient' => 'from-red-100 to-red-200'],
                    ['emoji' => '🎁', 'name' => 'Gift Basket', 'price' => 65, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '📚', 'name' => 'Book Collection', 'price' => 45, 'gradient' => 'from-orange-100 to-orange-200'],
                ],
                'about' => [
                    'title' => 'Thoughtful gifting for Lunar New Year',
                    'text' => 'Lunar New Year celebrates new beginnings and family togetherness. A wishlist helps loved ones give meaningful gifts that honor this special time. Share what would bring you joy in the year ahead.',
                ],
                'givetwice' => [
                    'gradient' => 'from-red-50 to-amber-50',
                    'border' => 'border-red-100',
                    'title' => 'Prosperity shared',
                    'description' => 'Lunar New Year wishes prosperity for all. When family buys from your list, we donate our commission to charity. Your celebration shares good fortune with others.',
                    'link_text' => 'Learn about our giving model',
                    'link_color' => 'coral',
                ],
                'similar' => ['birthday', 'diwali', 'eid'],
                'final_cta' => [
                    'title' => 'Ready to create your Lunar New Year wishlist?',
                    'subtitle' => 'Help family celebrate the new year with meaningful gifts.',
                    'button_text' => 'Start my Lunar New Year wishlist',
                ],
            ],
        ];
    }
}
