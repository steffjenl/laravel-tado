<?php

namespace Tado;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class Tado
{
    private $clientId;
    private $clientSecret;
    private $userName;
    private $passWord;
    private $accessToken;

    private function login()
    {
        if (empty(config('tado.clientId'))) {
            throw new \Exception("Tado Client-ID not found in config file");
        }

        if (empty(config('tado.clientSecret'))) {
            throw new \Exception("Tado Client-Secret not found in config file");
        }

        if (empty(config('tado.username'))) {
            throw new \Exception("Tado Username not found in config file");
        }

        if (empty(config('tado.password'))) {
            throw new \Exception("Tado Password not found in config file");
        }

        $client = new Client();
        $result = $client->post('https://auth.tado.com/oauth/token', [
            'form_params' => [
                'client_id'     => config('tado.clientId'),
                'client_secret' => config('tado.clientSecret'),
                'grant_type'    => 'password',
                'scope'         => 'home.user',
                'username'      => config('tado.username'),
                'password'      => config('tado.password'),
            ]
        ]);

        return $result->getBody()->getContents();
    }

    private function client($methode, $endpoint, $data)
    {
        //
    }

    public function getTemprature()
    {

        return $this->login();

    }

}
