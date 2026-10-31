<?php

namespace App\Support;

class EmailSuggestion
{
    public static function for(string $email): ?string
    {
        $parts = explode('@', strtolower(trim($email)));
        if (count($parts) !== 2) {
            return null;
        }
        [$name, $domain] = $parts;
        $typos = ['gmial.com' => 'gmail.com', 'gmal.com' => 'gmail.com', 'hotmial.com' => 'hotmail.com', 'outlok.com' => 'outlook.com', 'telent.be' => 'telenet.be', 'telenet.com' => 'telenet.be', 'telenet.b' => 'telenet.be', 'skyent.be' => 'skynet.be', 'skynt.be' => 'skynet.be', 'proxims.be' => 'proximus.be', 'proximux.be' => 'proximus.be'];

        return isset($typos[$domain]) ? $name.'@'.$typos[$domain] : null;
    }
}
