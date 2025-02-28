<?php
/*
 * This file is part of the Fireflies package.
 *
 * (c) Haruna Ahmadu <akhmadharuna@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Fireflies API Key
    |--------------------------------------------------------------------------
    |
    | This value is the API key for your Fireflies.ai account. This key
    | can be found in your Fireflies dashboard.
    |
    */
    'api_key' => env('FIREFLIES_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | The default language for transcriptions
    |
    */
    'default_language' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time to wait for API responses (in seconds)
    |
    */
    'timeout' => 30,
];