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
        if (empty(config('tado.clientid', $this->clientId))) {
            throw new \Exception("Tado Client-ID not fount in config file");
        }

        if (empty(config('tado.clientsecret', $this->clientSecret))) {
            throw new \Exception("Tado Client-Secret not fount in config file");
        }

        if (empty(config('tado.username', $this->userName))) {
            throw new \Exception("Tado Username not fount in config file");
        }

        if (empty(config('tado.password', $this->passWord))) {
            throw new \Exception("Tado Password not fount in config file");
        }

        $client = new Client();
        $result = $client->post('https://auth.tado.com/oauth/token', [
            'form_params' => [
                'client_id'     => config('tado.clientid'),
                'client_secret' => config('tado.clientsecret'),
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
