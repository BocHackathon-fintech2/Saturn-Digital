<?php

namespace BankOfCyprus\Authorization;

use BankOfCyprus\BaseClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

class AuthorizationClient extends BaseClient
{
    const URL = 'https://sandbox-apis.bankofcyprus.com/df-boc-org-sb/sb/psd2/';

    public function __construct( array $config )
    {
        parent::__construct();

        $this->client = new Client([
            'base_uri' => self::URL,
            'handler'  => $this->stack,
        ]);

        $this->setClientId($config['client_id']);
        $this->setClientSecret($config['client_secret']);
    }

    public function getAppAccessToken()
    {
        $token = null;

        $body = [
            'grant_type'    => 'client_credentials',
            'scope'         => 'TPPOAuth2Security',
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret()
        ];

        $request = new Request('POST', 'oauth2/token');

        try {

            $response = $this->client->send($request, ['form_params' => $body]);
            return json_decode((string)$response->getBody(), true)['access_token'];

        } catch ( ClientException $e ) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            dump(json_decode($responseBodyAsString));

        }

        return $token;
    }


    public function getSubscriptionAccessToken( $code )
    {
        $token = null;

        $body = [
            'grant_type'    => 'authorization_code',
            'scope'         => 'UserOAuth2Security',
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'code'          => $code
        ];

        $request = new Request('POST', 'oauth2/token');

        try {
            $response = $this->client->send($request, ['form_params' => $body]);

            return json_decode((string)$response->getBody(), true)['access_token'];

        } catch ( \Exception $e ) {
        }

        return $token;

    }

}