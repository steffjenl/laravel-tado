<?php

namespace Tado;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Tado\Exception\TadoException;

/**
 * Class Tado
 *
 * @package   Tado
 * @author    Stephan Eizinga <stephan.eizinga@gmail.com>
 */
class Tado
{
    /**
     * @var string $rootUrl
     */
    private $rootUrl = 'https://my.tado.com/api';
    /**
     * @var  $accessToken
     */
    private $accessToken;
    /**
     * @var  $refreshToken
     */
    private $refreshToken;
    /**
     * @var  $expireToken
     */
    private $expireToken;

    /**
     * login
     *
     * @return bool
     * @throws TadoException
     */
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
            if (!empty($this->refreshToken)) {
                $result = $client->post('https://auth.tado.com/oauth/token', [
                    'form_params' => [
                        'client_id'     => config('tado.clientId'),
                        'client_secret' => config('tado.clientSecret'),
                        'grant_type'    => 'refresh_token',
                        'scope'         => 'home.user',
                        'refresh_token' => $this->refreshToken,
                    ],
                ]);

                $body = json_decode($result->getBody()->getContents());
                $this->setAccessToken($body->access_token);
                $this->setRefreshToken($body->refresh_token);
                $this->setExpireToken($body->expires_in);

                return true;
            }
            $result = $client->post('https://auth.tado.com/oauth/token', [
                'form_params' => [
                    'client_id'     => config('tado.clientId'),
                    'client_secret' => config('tado.clientSecret'),
                    'grant_type'    => 'password',
                    'scope'         => 'home.user',
                    'username'      => config('tado.username'),
                    'password'      => config('tado.password'),
                ],
            ]);

            $body = json_decode($result->getBody()->getContents());
            $this->setAccessToken($body->access_token);
            $this->setRefreshToken($body->refresh_token);
            $this->setExpireToken($body->expires_in);

            return true;
        } catch (GuzzleException $ex) {
            if (empty($ex->getResponse()->getBody()->getContents())) {
                throw new TadoException("Can't connect to auth.tado.com servers");
            }
            $body = json_decode($ex->getResponse()->getBody()->getContents());
            throw new TadoException($body->error_description);
        }

        return false;
    }

    /**
     * setAccessToken
     *
     * @param $accessToken
     */
    private function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * setRefreshToken
     *
     * @param $refreshToken
     */
    private function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * setExpireToken
     *
     * @param $expireToken
     */
    private function setExpireToken($expireToken)
    {
        $this->expireToken = (new Carbon())->addSeconds($expireToken);
    }

    /**
     * client
     *
     * @param       $methode
     * @param       $endpoint
     * @param array $data
     *
     * @return bool|mixed
     * @throws TadoException
     */
    private function client($methode, $endpoint, $data = [])
    {
        // when accessToken is empty or token is expired, please login again.
        if (empty($this->accessToken) || (new Carbon())->diffInSeconds($this->expireToken) < 1) {
            $this->login();
        }

        $client = new Client();

        try {
            $result = $client->request($methode, $this->rootUrl . $endpoint, [
                'form_params' => $data,
                'headers'     => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
            ]);

            $body = json_decode($result->getBody()->getContents());

            return $body;
        } catch (GuzzleException $ex) {
            if (empty($ex->getResponse()->getBody()->getContents())) {
                throw new TadoException("Can't connect to my.tado.com servers");
            }

            throw new TadoException($ex->getMessage());
        }

        return false;
    }

    /**
     * getHomesZonesState
     *
     * @param $homeId
     * @param $zone
     *
     * @return bool|mixed
     */
    public function getHomesZonesState($homeId, $zone)
    {
        $data = $this->client('get', '/v2/homes/' . $homeId . '/zones/' . $zone . '/state');

        return $data;
    }

}
