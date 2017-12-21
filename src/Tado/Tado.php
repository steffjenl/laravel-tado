<?php

namespace Tado;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\GenericProvider;
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

        $provider = $this->getProvider();
        try {
            $accessToken = $provider->getAccessToken('password', [
                'username' => config('tado.username'),
                'password' => config('tado.password'),
                'scope' => 'home.user',
            ]);
            $this->setAccessToken($accessToken);
            return true;
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e){
            throw new TadoException($e->getMessage());
        }

        return false;
    }

    /**
     * setAccessToken
     *
     * @param \League\OAuth2\Client\Token\AccessToken $accessToken
     */
    private function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return GenericProvider
     */
    private function getProvider() {
        return new GenericProvider([
            'clientId'                => config('tado.clientId'),
            'clientSecret'            => config('tado.clientSecret'),
            'urlAuthorize'            => 'https://auth.tado.com/oauth/token',
            'urlAccessToken'          => 'https://auth.tado.com/oauth/token',
            'urlResourceOwnerDetails' => null,
        ]);
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
    private function client($methode, $endpoint, $data = '')
    {
        // when accessToken not exists we must login
        if (empty($this->accessToken))
        {
            $this->login();
        }

        try {
            // get oauth provider
            $provider = $this->getProvider();

            // set options for request
            $options['body'] = json_encode($data);
            $options['headers']['content-type'] = 'application/json';

            // prepare guzzle request with authorisation headers
            $request = $provider->getAuthenticatedRequest(
                $methode,
                $this->rootUrl . $endpoint,
                $this->accessToken,
                $options
            );

            // create Guzzle client
            $client = new Client();
            // send request to server
            $response = $client->send($request);

            return json_decode($response->getBody());

        } catch (GuzzleException $ex) {
            if (empty($ex->getResponse()->getBody()->getContents())) {
                throw new TadoException("Can't connect to my.tado.com servers");
            }
            throw new TadoException($ex->getMessage());
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $ex) {
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
