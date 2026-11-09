<?php

use Illuminate\Support\Str;

return [

    /*
     * The framework default is a snake_case name. Changing it renames the
     * cookie and signs every user out, so keep the slug form.
     */
    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

];
