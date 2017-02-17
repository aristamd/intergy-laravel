<?php

/*
 * This file is part of OAuth 2.0 Laravel.
 *
 * (c) Luca Degasperi <packages@lucadegasperi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Output Token Type
    |--------------------------------------------------------------------------
    |
    | This will tell the authorization server the output format for the access
    | token and the resource server how to parse the access token used.
    |
    | Default value is League\OAuth2\Server\TokenType\Bearer
    |
    */

    'INTERGY_URL' => 'https://api-test.greenwayhealth.com/Integration/RESTv1.0/IntergyAPIService',

    /*
    |--------------------------------------------------------------------------
    | State Parameter
    |--------------------------------------------------------------------------
    |
    | Whether or not the state parameter is required in the query string.
    |
    */

    'INTERGY_TOKEN' => 'puw7wx2s4bgkubjftf87dspy',

    /*
    |--------------------------------------------------------------------------
    | Scope Parameter
    |--------------------------------------------------------------------------
    |
    | Whether or not the scope parameter is required in the query string.
    |
    */

    'INTERGY_USERNAME' => 'API_AristaMD',

    /*
    |--------------------------------------------------------------------------
    | Scope Delimiter
    |--------------------------------------------------------------------------
    |
    | Which character to use to split the scope parameter in the query string.
    |
    */

    'INTERGY_PASSWORD' => 'password',

    /*
    |--------------------------------------------------------------------------
    | Default Scope
    |--------------------------------------------------------------------------
    |
    | The default scope to use if not present in the query string.
    |
    */

    'INTERGY_APPLICATION_NAME' => "Intergy's Favorite 3rd Party Application",

    /*
    |--------------------------------------------------------------------------
    | Access Token TTL
    |--------------------------------------------------------------------------
    |
    | For how long the issued access token is valid (in seconds) this can be
    | also set on a per grant-type basis.
    |
    */

    'INTERGY_LICENSE_ID' => 'DevIntAPI',

    /*
    |--------------------------------------------------------------------------
    | Limit clients to specific grants
    |--------------------------------------------------------------------------
    |
    | Whether or not to limit clients to specific grant types. This is useful
    | to allow only trusted clients to access your API differently.
    |
    */

    'INTERGY_USER_LOGON' => 'API_AristaMD'

];
