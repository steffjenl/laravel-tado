<?php

namespace Tado;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Tado\Exception\TadoException;

class Tado
{
    private $rootUrl = 'https://my.tado.com/api';
    private $accessToken;
    private $refreshToken;
    private $expireToken;

    private function login()
    {
        if (empty(config('tado.clientId'))) {
            throw new TadoException("Tado Client-ID not found in config file");
        }

        if (empty(config('tado.clientSecret'))) {
            throw new TadoException("Tado Client-Secret not found in config file");
        }

        if (empty(config('tado.username'))) {
            throw new TadoException("Tado Username not found in config file");
        }

        if (empty(config('tado.password'))) {
            throw new TadoException("Tado Password not found in config file");
        }

        $client = new Client();

        try {
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

            $body = json_decode($result->getBody()->getContents());
            $this->setAccessToken($body->access_token);
            $this->setRefreshToken($body->refresh_token);
            return true;
        }
        catch (GuzzleException $ex) {
            if (empty($ex->getResponse()->getBody()->getContents()))
            {
                throw new TadoException("Can't connect to my.tado.com servers");
            }
            $body = json_decode($ex->getResponse()->getBody()->getContents());
            throw new TadoException($body->error_description);
        }

        return false;
    }

    private function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    private function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    private function client($methode, $endpoint, $data = [])
    {
        $client = new Client();

        try {
            $result = $client->request($methode, $this->rootUrl . $endpoint, [
                'form_params' => $data,
                'Authorization' => 'Bearer ' . $this->accessToken
            ]);

            $body = json_decode($result->getBody()->getContents());
            return $body;
        }
        catch (GuzzleException $ex) {
            if (empty($ex->getResponse()->getBody()->getContents()))
            {
                throw new TadoException("Can't connect to my.tado.com servers");
            }
            $body = json_decode($ex->getResponse()->getBody()->getContents());
            throw new TadoException($body->error_description);
        }
        return false;
    }

    public function getHomesZonesState($homeId,$zone)
    {
        if (empty($this->accessToken))
        {
            $this->login();
        }

        $data   = $this->client('get', '/v2/homes/150056/zones/1/state');
        return $data;

    }

}
