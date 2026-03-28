<?php

namespace App\Helpers;

class ExchangeHelper
{
    private static ?array $cachedAll = null;

    /**
     * Map from locale to exchange type slug (for CTA links).
     */
    private static array $exchangeTypeMap = [
        'en' => 'secret-santa',
        'nl' => 'lootjes-trekken',
        'fr' => 'tirage-au-sort',
    ];

    /**
     * All Secret Santa / gift exchange SEO landing pages.
     *
     * Each entry is keyed by an internal identifier. The slug is the URL path
     * segment (locale-specific — not a translated version of the same slug).
     *
     * @return array<string, array{slug: string, locale: string, page_title: string}>
     */
    public static function all(): array
    {
        return self::$cachedAll ??= [
            'secret-santa-en' => [
                'slug' => 'secret-santa-gift-exchange',
                'locale' => 'en',
                'page_title' => 'Secret Santa Gift Exchange',
            ],
            'lootjes-trekken-nl' => [
                'slug' => 'lootjes-trekken-online',
                'locale' => 'nl',
                'page_title' => 'Lootjes Trekken Online',
            ],
            'tirage-au-sort-fr' => [
                'slug' => 'tirage-au-sort-noel',
                'locale' => 'fr',
                'page_title' => 'Tirage au Sort Noël',
            ],
        ];
    }

    /**
     * Get a single exchange SEO page entry by key.
     *
     * @return array{slug: string, locale: string, page_title: string}|null
     */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /**
     * Get full page content for a given exchange SEO key.
     * Returns null if the key doesn't exist or the locale doesn't match.
     *
     * @return array<string, mixed>|null
     */
    public static function getPageContent(string $key, ?string $locale = null): ?array
    {
        $base = self::get($key);

        if (! $base) {
            return null;
        }

        // Each page is strictly single-locale — 404 if wrong locale is requested
        $locale ??= app()->getLocale();
        if ($base['locale'] !== $locale) {
            return null;
        }

        $content = self::allPageContent()[$key] ?? null;

        if (! $content) {
            return null;
        }

        return array_merge($base, $content);
    }

    /**
     * Get the exchange type slug for a given locale (used for CTA links).
     */
    public static function exchangeTypeForLocale(string $locale): string
    {
        return self::$exchangeTypeMap[$locale] ?? 'secret-santa';
    }

    /**
     * Get the SEO landing page entry for a given locale.
     *
     * @return array{slug: string, locale: string, page_title: string}|null
     */
    public static function getForLocale(string $locale): ?array
    {
        foreach (self::all() as $key => $entry) {
            if ($entry['locale'] === $locale) {
                return array_merge(['key' => $key], $entry);
            }
        }

        return null;
    }

    /**
     * All page-level content for Secret Santa / exchange SEO pages.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function allPageContent(): array
    {
        return [

            // ─── English: Secret Santa ───────────────────────────────────────
            'secret-santa-en' => [
                'hero' => [
                    'h1_title' => 'Secret Santa',
                    'h1_subtitle' => 'gift exchange',
                    'description' => "Draw names, set a budget, buy one person a gift they'll love. We handle the shuffle, the secrecy, and the charity donation.",
                    'bullets' => [
                        'Names drawn privately — no one knows who got whom',
                        'Everyone gets a link to their person\'s wishlist',
                        'Every purchase donates to charity at no extra cost',
                    ],
                    'cta_text' => 'Start my Secret Santa group',
                    'cta_emoji' => '&#127877;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🎧', 'name' => 'Wireless Headphones', 'price' => 79,  'gradient' => 'from-blue-100 to-blue-200'],
                    ['emoji' => '🧣', 'name' => 'Cosy Scarf',          'price' => 35,  'gradient' => 'from-red-100 to-red-200'],
                    ['emoji' => '🍫', 'name' => 'Chocolate Gift Box',  'price' => 28,  'gradient' => 'from-amber-100 to-amber-200'],
                ],
                'why' => [
                    'title' => 'Why use GiveTwice for Secret Santa?',
                    'subtitle' => 'Because coordinating a gift exchange over WhatsApp is how friendships end.',
                    'benefits' => [
                        [
                            'emoji' => '&#127381;',
                            'bg' => 'coral',
                            'title' => 'Fair, random draw',
                            'description' => 'We handle the shuffle so nobody cheats, nobody gets themselves, and nobody ends up with their ex. Add exclusions if needed.',
                        ],
                        [
                            'emoji' => '&#128141;',
                            'bg' => 'sunny',
                            'title' => 'Wishlist-powered gifting',
                            'description' => 'Everyone can share their wishlist. Your person gets a link to it automatically. No more buying things that go straight to a donation pile.',
                        ],
                        [
                            'emoji' => '&#10084;&#65039;',
                            'bg' => 'teal',
                            'title' => 'Giving that keeps giving',
                            'description' => "When gifts are bought from wishlists on GiveTwice, we donate our commission to charity. Your group celebration helps people you'll never meet.",
                        ],
                    ],
                ],
                'givetwice' => [
                    'title' => 'Your Secret Santa, someone else\'s Christmas too',
                    'description' => "When participants buy gifts from each other's wishlists on GiveTwice, the stores pay us a commission. We donate 100% of that to charity. Your group exchange creates a ripple of good — no extra cost, no extra steps.",
                    'link_text' => 'Learn how the charity model works',
                ],
                'tips_title' => 'Tips for a smooth Secret Santa',
                'tips' => [
                    [
                        'title' => 'Set the budget first',
                        'description' => 'Agree on a number before anyone draws names. Nothing more awkward than a €200 gift meeting a €20 one.',
                    ],
                    [
                        'title' => 'Add exclusions',
                        'description' => "Couples who live together shouldn't buy for each other. Frenemies probably shouldn't either. Use exclusions.",
                    ],
                    [
                        'title' => 'Nudge for wishlists',
                        'description' => 'After the draw, remind everyone to add a few things to their wishlist. Your buyer will thank you.',
                    ],
                    [
                        'title' => 'Set a gift deadline',
                        'description' => "Add an event date when you create the group. It shows on everyone's reveal page and keeps stragglers honest.",
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'How does Secret Santa work on GiveTwice?',
                        'answer' => 'You add participants (name + email), set an optional budget and event date, then click Draw. GiveTwice shuffles the names fairly, making sure nobody draws themselves. Each participant gets an email with a private link revealing who they\'re buying for — and a link to that person\'s wishlist if they have one.',
                    ],
                    [
                        'question' => 'Can I set rules so certain people don\'t draw each other?',
                        'answer' => 'Yes. Before running the draw you can add exclusion pairs — for example, partners who already exchange gifts separately, or colleagues who don\'t know each other well. The draw algorithm will avoid those pairings.',
                    ],
                    [
                        'question' => 'Does everyone need a GiveTwice account?',
                        'answer' => 'No. Participants receive a private reveal link by email — no account required to see who they\'re buying for. Creating a wishlist to share with their Secret Santa is optional, but free and takes about a minute.',
                    ],
                    [
                        'question' => 'How does the charity donation work?',
                        'answer' => "When a participant buys a gift from a GiveTwice wishlist, the store pays us a small affiliate commission (the same as if you'd gone through any price-comparison site). We donate 100% of those commissions to charity. You pay the same price you'd pay anywhere — we just make sure the extra goes somewhere good.",
                    ],
                ],
                'final_cta' => [
                    'title' => 'Ready to start your Secret Santa?',
                    'subtitle' => 'Takes two minutes. No spreadsheets. No spoilers.',
                    'button_text' => 'Create my Secret Santa group',
                ],
            ],

            // ─── Dutch: Lootjes trekken ──────────────────────────────────────
            'lootjes-trekken-nl' => [
                'hero' => [
                    'h1_title' => 'Lootjes trekken',
                    'h1_subtitle' => 'online geregeld',
                    'description' => 'Trekjes doen zonder gedoe. Voeg iedereen toe, stel een budget in en wij zorgen voor de loting — eerlijk, willekeurig, geheim.',
                    'bullets' => [
                        'Niemand weet wie jou getrokken heeft',
                        'Iedereen krijgt een link naar de verlanglijst van zijn persoon',
                        'Elke aankoop doneert aan een goed doel, gratis',
                    ],
                    'cta_text' => 'Lootjes trekken starten',
                    'cta_emoji' => '&#127381;',
                ],
                'hero_gifts' => [
                    ['emoji' => '📚', 'name' => 'Boeken',          'price' => 25, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '🧣', 'name' => 'Warme sjaal',     'price' => 35, 'gradient' => 'from-red-100 to-red-200'],
                    ['emoji' => '🎮', 'name' => 'Gezelschapsspel', 'price' => 40, 'gradient' => 'from-blue-100 to-blue-200'],
                ],
                'why' => [
                    'title' => 'Waarom lootjes trekken via GiveTwice?',
                    'subtitle' => 'Omdat een WhatsAppgroep met twintig mensen geen goede manier is om geheimen te bewaren.',
                    'benefits' => [
                        [
                            'emoji' => '&#127381;',
                            'bg' => 'coral',
                            'title' => 'Eerlijke, willekeurige loting',
                            'description' => 'Wij schudden de namen door elkaar, zodat niemand zichzelf trekt en niemand vals speelt. Uitzonderingen instellen kan ook — handig voor koppels.',
                        ],
                        [
                            'emoji' => '&#128141;',
                            'bg' => 'sunny',
                            'title' => 'Verlanglijstjes ingebouwd',
                            'description' => 'Deelnemers kunnen een verlanglijstje aanmaken. Je trekje krijgt automatisch een link. Nooit meer een cadeautje kopen dat direct naar de kringloopwinkel gaat.',
                        ],
                        [
                            'emoji' => '&#10084;&#65039;',
                            'bg' => 'teal',
                            'title' => 'Geven dat verder reikt',
                            'description' => 'Als cadeautjes via GiveTwice-verlanglijstjes gekocht worden, doneren wij onze commissie aan een goed doel. Jouw lootjestrekkronde helpt mensen die je nooit zult ontmoeten.',
                        ],
                    ],
                ],
                'givetwice' => [
                    'title' => 'Jouw lootje, iemand anders\' kerst',
                    'description' => 'Wanneer deelnemers cadeautjes kopen via GiveTwice-verlanglijstjes, betaalt de winkel ons een kleine commissie. Wij doneren 100% daarvan aan een goed doel. Jij betaalt niks extra — het geld dat anders bij de winkel bleef, gaat nu naar mensen die het nodig hebben.',
                    'link_text' => 'Zo werkt het goede-doelmodel',
                ],
                'tips_title' => 'Tips voor een vlotte loting',
                'tips' => [
                    [
                        'title' => 'Spreek het budget af',
                        'description' => 'Stel een bedrag in vóór de loting. Een verschil van €150 tussen twee cadeautjes maakt het ongemakkelijk.',
                    ],
                    [
                        'title' => 'Gebruik uitzonderingen',
                        'description' => 'Koppels die al voor elkaar kopen, hoeven elkaar niet te trekken. Stel dat in als uitzondering.',
                    ],
                    [
                        'title' => 'Herinner iedereen aan een verlanglijstje',
                        'description' => 'Na de loting: vraag deelnemers om een paar wensen toe te voegen. Je trekje zal je dankbaar zijn.',
                    ],
                    [
                        'title' => 'Zet een deadline in',
                        'description' => "Voeg een evenementdatum toe. Die staat op alle onthullingspagina's en houdt uitstellers op de juiste weg.",
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Hoe werkt lootjes trekken op GiveTwice?',
                        'answer' => "Je voegt deelnemers toe (naam + e-mail), stelt optioneel een budget en datum in, en klikt op 'Loting uitvoeren'. GiveTwice schudt de namen eerlijk door elkaar — niemand trekt zichzelf. Elke deelnemer krijgt een e-mail met een privélink die onthult wie hij of zij een cadeau mag geven, plus een link naar de verlanglijst van die persoon.",
                    ],
                    [
                        'question' => 'Kan ik instellen dat bepaalde mensen elkaar niet trekken?',
                        'answer' => "Ja. Vóór de loting kun je uitsluitingsparen toevoegen — bijvoorbeeld koppels die al cadeautjes uitwisselen, of collega's die elkaar niet zo goed kennen. Het lotingsalgoritme houdt daar rekening mee.",
                    ],
                    [
                        'question' => 'Hebben alle deelnemers een account nodig?',
                        'answer' => 'Nee. Deelnemers ontvangen een privélink via e-mail — geen account nodig om te zien wie ze getrokken hebben. Een verlanglijstje aanmaken om te delen is optioneel, gratis en duurt minder dan een minuut.',
                    ],
                    [
                        'question' => 'Hoe werkt de donatie aan het goede doel?',
                        'answer' => 'Wanneer een deelnemer een cadeau koopt via een GiveTwice-verlanglijst, betaalt de winkel ons een kleine affiliatecommissie — hetzelfde als bij elke prijsvergelijkingssite. Wij doneren 100% van die commissies aan een goed doel. Jij betaalt de normale prijs; het verschil gaat naar mensen die het nodig hebben.',
                    ],
                ],
                'final_cta' => [
                    'title' => 'Klaar om lootjes te trekken?',
                    'subtitle' => 'In twee minuten geregeld. Geen Excel, geen spoilers.',
                    'button_text' => 'Loting aanmaken',
                ],
            ],

            // ─── French: Tirage au sort ──────────────────────────────────────
            'tirage-au-sort-fr' => [
                'hero' => [
                    'h1_title' => 'Tirage au sort',
                    'h1_subtitle' => 'père noël secret en ligne',
                    'description' => 'Organisez votre tirage au sort de Noël sans prise de tête. Ajoutez les participants, fixez un budget et laissez-nous gérer le tirage — équitable, aléatoire, secret.',
                    'bullets' => [
                        'Personne ne sait qui vous a tiré',
                        'Chacun reçoit un lien vers la liste de cadeaux de son filleul',
                        'Chaque achat fait un don à une association, sans frais supplémentaires',
                    ],
                    'cta_text' => 'Lancer mon tirage au sort',
                    'cta_emoji' => '&#127381;',
                ],
                'hero_gifts' => [
                    ['emoji' => '🍫', 'name' => 'Boîte de chocolats', 'price' => 28, 'gradient' => 'from-amber-100 to-amber-200'],
                    ['emoji' => '📚', 'name' => 'Romans',             'price' => 25, 'gradient' => 'from-blue-100 to-blue-200'],
                    ['emoji' => '🧴', 'name' => 'Coffret beauté',     'price' => 45, 'gradient' => 'from-pink-100 to-pink-200'],
                ],
                'why' => [
                    'title' => 'Pourquoi utiliser GiveTwice pour votre tirage ?',
                    'subtitle' => "Parce qu'organiser un père Noël secret par SMS, c'est la recette pour les spoilers.",
                    'benefits' => [
                        [
                            'emoji' => '&#127381;',
                            'bg' => 'coral',
                            'title' => 'Tirage équitable et aléatoire',
                            'description' => 'Nous mélangeons les noms pour que personne ne se tire soi-même et que personne ne triche. Des exclusions sont possibles pour les couples ou collègues.',
                        ],
                        [
                            'emoji' => '&#128141;',
                            'bg' => 'sunny',
                            'title' => 'Listes de cadeaux intégrées',
                            'description' => 'Les participants peuvent créer leur liste de souhaits. Votre filleul reçoit automatiquement un lien. Fini les cadeaux inutiles qui finissent dans un tiroir.',
                        ],
                        [
                            'emoji' => '&#10084;&#65039;',
                            'bg' => 'teal',
                            'title' => 'Un cadeau qui va plus loin',
                            'description' => 'Quand les cadeaux sont achetés via les listes GiveTwice, nous donnons notre commission à une association. Votre échange de Noël aide des personnes que vous ne croiserez jamais.',
                        ],
                    ],
                ],
                'givetwice' => [
                    'title' => 'Votre cadeau, le Noël de quelqu\'un d\'autre aussi',
                    'description' => "Quand les participants achètent des cadeaux via des listes GiveTwice, le magasin nous verse une petite commission d'affiliation. Nous en donnons 100 % à une association caritative. Vous payez le même prix qu'ailleurs — l'argent qui resterait normalement en poche du magasin va à ceux qui en ont besoin.",
                    'link_text' => 'Comment fonctionne notre modèle caritatif',
                ],
                'tips_title' => 'Conseils pour un tirage réussi',
                'tips' => [
                    [
                        'title' => 'Fixez le budget d\'abord',
                        'description' => 'Mettez-vous d\'accord sur un montant avant le tirage. Un écart de 150 € entre deux cadeaux rend la soirée gênante.',
                    ],
                    [
                        'title' => 'Utilisez les exclusions',
                        'description' => "Les couples qui échangent déjà des cadeaux n'ont pas besoin de se tirer. Configurez ça comme exclusion.",
                    ],
                    [
                        'title' => 'Encouragez les listes de souhaits',
                        'description' => "Après le tirage, rappelez à chacun d'ajouter quelques idées à sa liste. Votre filleul vous remerciera.",
                    ],
                    [
                        'title' => 'Ajoutez une date limite',
                        'description' => "Indiquez une date d'événement à la création. Elle apparaît sur toutes les pages de révélation et motive les retardataires.",
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Comment fonctionne le tirage au sort sur GiveTwice ?',
                        'answer' => "Vous ajoutez les participants (nom + e-mail), définissez optionnellement un budget et une date, puis cliquez sur « Effectuer le tirage ». GiveTwice mélange les noms équitablement — personne ne se tire soi-même. Chaque participant reçoit un e-mail avec un lien privé révélant son filleul, ainsi qu'un lien vers sa liste de souhaits s'il en a une.",
                    ],
                    [
                        'question' => 'Puis-je empêcher certaines personnes de se tirer mutuellement ?',
                        'answer' => "Oui. Avant le tirage, vous pouvez ajouter des paires d'exclusion — par exemple des couples qui échangent déjà des cadeaux, ou des collègues qui ne se connaissent pas bien. L'algorithme en tiendra compte.",
                    ],
                    [
                        'question' => 'Les participants ont-ils besoin d\'un compte ?',
                        'answer' => "Non. Les participants reçoivent un lien privé par e-mail — pas besoin de compte pour voir leur filleul. Créer une liste de souhaits à partager est optionnel, gratuit et prend moins d'une minute.",
                    ],
                    [
                        'question' => 'Comment fonctionne le don à l\'association ?',
                        'answer' => "Quand un participant achète un cadeau via une liste GiveTwice, le magasin nous verse une petite commission d'affiliation — comme pour n'importe quel comparateur de prix. Nous donnons 100 % de ces commissions à des associations. Vous payez le prix normal ; la différence va à des personnes qui en ont besoin.",
                    ],
                ],
                'final_cta' => [
                    'title' => 'Prêt à lancer votre tirage au sort ?',
                    'subtitle' => 'Deux minutes suffisent. Pas de tableur. Pas de spoilers.',
                    'button_text' => 'Créer mon groupe père Noël secret',
                ],
            ],

        ];
    }
}
