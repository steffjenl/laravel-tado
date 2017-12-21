<?php

return [
    /*
     * my.tado.com Username
     *
     */
    'username' => env('TADO_USERNAME'),

    /*
     * my.tado.com Password
     *
     */
    'password' => env('TADO_PASSWORD'),

    /*
     * Tado OAUth Client ID
     *
     */
    'clientId' => env('TADO_CLIENTID', 'public-api-preview'),

    /*
     * Tado OAUth Client Secret
     *
     */
    'clientSecret' => env('TADO_CLIENTSECRET', '4HJGRffVR8xb3XdEUQpjgZ1VplJi6Xgw'),
];
