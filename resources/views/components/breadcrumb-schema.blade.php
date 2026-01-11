@props(['items'])

@php
$baseUrl = config('app.url');
$locale = app()->getLocale();
$breadcrumbs = [];

// Always start with Home
$breadcrumbs[] = [
    '@type' => 'ListItem',
    'position' => 1,
    'name' => __('Home'),
    'item' => "{$baseUrl}/{$locale}",
];

// Add additional items
$position = 2;
foreach ($items as $item) {
    $breadcrumb = [
        '@type' => 'ListItem',
        'position' => $position,
        'name' => $item['name'],
    ];

    // Only add item URL if it's not the current page (last item)
    if (isset($item['url'])) {
        $breadcrumb['item'] = $item['url'];
    }

    $breadcrumbs[] = $breadcrumb;
    $position++;
}
@endphp

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": {!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
}
</script>
