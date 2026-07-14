<?php

namespace App\Support;

/**
 * Builds a clickable social profile URL from a full URL or a network handle/id.
 */
class SocialNetworkUrl
{
    public static function resolve(string $network, string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            return $value;
        }

        $template = (string) config("social-networks.{$network}.url", '');

        if ($template === '') {
            return $value;
        }

        return str_replace('{username}', ltrim($value, '@'), $template);
    }
}
