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
                    'description' => 'Draw names, set a budget, buy one person a gift they will love. We handle the shuffle and the secrecy.',
                    'bullets' => [
                        'Names drawn privately — no one knows who got whom',
                        'Everyone gets a link to their person\'s wishlist',
                        'Free. No ads.',
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
                            'title' => 'Our charity plan',
                            'description' => 'If you later buy through our shop links, all our commission will go to charity. We are setting that up now.',
                        ],
                    ],
                ],
                'givetwice' => [
                    'title' => 'Our charity plan',
                    'description' => 'If you later buy through our shop links, all our commission will go to charity. We are setting that up now.',
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
                        'answer' => 'If you later buy through our shop links, all our commission will go to charity. We are setting that up now.',
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
                        'Gratis. Geen reclame.',
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
                            'title' => 'Ons plan voor het goede doel',
                            'description' => 'Koop je later via onze winkellinks, dan gaat onze commissie naar een goed doel. Dat zetten we nu op.',
                        ],
                    ],
                ],
                'givetwice' => [
                    'title' => 'Ons plan voor het goede doel',
                    'description' => 'Koop je later via onze winkellinks, dan gaat onze commissie naar een goed doel. Dat zetten we nu op.',
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
                        'answer' => 'Koop je later via onze winkellinks, dan gaat onze commissie naar een goed doel. Dat zetten we nu op.',
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
                        'Gratuit. Sans pub.',
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
                            'title' => 'Notre projet solidaire',
                            'description' => 'Si tu achètes plus tard via nos liens marchands, toute notre commission ira à une association. On met ça en place.',
                        ],
                    ],
                ],
                'givetwice' => [
                    'title' => 'Notre projet solidaire',
                    'description' => 'Si tu achètes plus tard via nos liens marchands, toute notre commission ira à une association. On met ça en place.',
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
                        'answer' => 'Si tu achètes plus tard via nos liens marchands, toute notre commission ira à une association. On met ça en place.',
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
