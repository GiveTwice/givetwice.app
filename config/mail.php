<?php

return [

    'default' => env('MAIL_MAILER', 'postmark'),

    /*
     * The sender name stays "GiveTwice" whatever MAIL_FROM_NAME says,
     * so every mail carries the brand name.
     */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => 'GiveTwice',
    ],

];
