<?php

describe('Brand page', function () {
    it('loads the brand page for each locale', function (string $locale) {
        $response = $this->get("/{$locale}/brand");

        $response->assertSuccessful();
        $response->assertSee('GiveTwice');
    })->with(['en', 'nl', 'fr']);

    it('contains logo download links', function () {
        $response = $this->get('/en/brand');

        $response->assertSuccessful();
        $response->assertSee('logo-icon.svg');
        $response->assertSee('logo-full.svg');
        $response->assertSee('logo-icon.png');
        $response->assertSee('logo-text.png');
    });

    it('contains brand color hex values', function () {
        $response = $this->get('/en/brand');

        $response->assertSuccessful();
        $response->assertSee('#D03739');
        $response->assertSee('#0FB89A');
        $response->assertSee('#EBAB00');
        $response->assertSee('#FEEFDC');
    });

    it('contains social media snippets', function () {
        $response = $this->get('/en/brand');

        $response->assertSuccessful();
        $response->assertSee('Twitter / X');
        $response->assertSee('LinkedIn');
    });
});
