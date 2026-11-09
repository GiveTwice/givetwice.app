{{--
    Renders the Open Graph image for every page that has no image of its own.

    The package takes the screenshot from the first URL that showed a template.
    For this template that could be a page behind a login or a one-time link,
    and the screenshot browser cannot open those. So the image is registered
    against the home page of the same locale first, which every visitor can open.
--}}
@php
    $ogImage = app(\Spatie\OgImage\OgImage::class);
    $ogImageHtml = view('public.default-og-image')->render();
    $ogImage->storeInCache($ogImage->hash($ogImageHtml), route('home', ['locale' => app()->getLocale()]));
@endphp
{{ $ogImage->html($ogImageHtml) }}
