<?php

namespace BankOfCyprus\Accounts;

use BankOfCyprus\BaseClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class AccountClient extends BaseClient
{
    const URL = 'https://sandbox-apis.bankofcyprus.com/df-boc-org-sb/sb/psd2/';

    private $subscriptionId;

    public function __construct( array $config )
    {
        parent::__construct();

        $this->client = new Client([
            'base_uri' => self::URL,
            'handler'  => $this->stack,
        ]);

        $this->setClientId($config['client_id']);
        $this->setClientSecret($config['client_secret']);
        $this->setAppId($config['app_id']);
        $this->setOriginUserId($config['origin_user_id']);
        $this->setJourneyId($config['journey_id']);
        $this->setTtpId($config['tpp_id']);
    }

    public function getAccounts()
    {
        $accounts = [];

        $header = [
            'cache-control'       => 'no-cache',
            'journeyId'           => $this->getJourneyId(),
            'timeStamp'           => '1540493118',
            'originUserId'        => $this->getOriginUserId(),
            'tppid'               => $this->getTtpId(),
            'app_name'            => $this->getAppId(),
            'APIm-Debug-Trans-Id' => 'true',
            'Content-Type'        => 'application/json',
            'Authorization'       => 'Bearer ' . $this->getAccessToken(),
            'subscriptionId'      => $this->subscriptionId
        ];

        $query = [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];

        $request = new Request('GET', 'v1/accounts');

        try {

            $response = $this->client->send($request, [
                'headers' => $header,
                'query'   => $query,
            ]);

            $accounts = json_decode((string)$response->getBody());

        } catch ( \Exception $e ) {

            dump($e->getMessage());
        }

        return $accounts;
    }


    public function getBalance($accountId)
    {
        $balance = null;

        $header = [
            'cache-control'       => 'no-cache',
            'journeyId'           => $this->getJourneyId(),
            'timeStamp'           => '1540493118',
            'originUserId'        => $this->getOriginUserId(),
            'tppid'               => $this->getTtpId(),
            'app_name'            => $this->getAppId(),
            'APIm-Debug-Trans-Id' => 'true',
            'Content-Type'        => 'application/json',
            'Authorization'       => 'Bearer ' . $this->getAccessToken(),
            'subscriptionId'      => $this->subscriptionId
        ];


        $query = [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];

        $request = new Request('GET', 'v1/accounts/'.$accountId.'/balance');

        try {

            $response = $this->client->send($request, [
                'headers' => $header,
                'query'   => $query,
            ]);

            $balance = json_decode((string)$response->getBody());

        } catch ( \Exception $e ) {

            dump($e->getMessage());
        }

        return $balance;
    }


    /**
     * @return mixed
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @param mixed $subscriptionId
     */
    public function setSubscriptionId( $subscriptionId )
    {
        $this->subscriptionId = $subscriptionId;
    }


}