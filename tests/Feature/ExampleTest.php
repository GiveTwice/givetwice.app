<?php

describe('Application routing', function () {
    it('redirects root to locale', function () {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/en');
    });

    it('returns successful response for locale home', function () {
        $response = $this->get('/en');

        $response->assertStatus(200);
    });
});
