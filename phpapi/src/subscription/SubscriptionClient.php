<?php

namespace BankOfCyprus\Subscription;

use BankOfCyprus\BaseClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


class SubscriptionClient extends BaseClient
{
    const URL = 'https://sandbox-apis.bankofcyprus.com/df-boc-org-sb/sb/psd2/';

    private $subscriptionAccessToken;
    private $redirectUrl;

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
        $this->setRedirectUrl($config['redirect_url']);
    }

    public function createSubscription()
    {
        $subscription = null;

        $header = [
            'cache-control'       => 'no-cache',
            'journeyId'           => $this->getJourneyId(),
            'timeStamp'           => '1540493118',
            'originUserId'        => $this->getOriginUserId(),
            'tppid'               => $this->getTtpId(),
            'app_name'            => $this->getAppId(),
            'APIm-Debug-Trans-Id' => 'true',
            'Content-Type'        => 'application/json',
            'Authorization'       => 'Bearer ' . $this->getAccessToken()
        ];

        $body = [
            'accounts' =>
                [
                    'transactionHistory'     => true,
                    'balance'                => true,
                    'details'                => true,
                    'checkFundsAvailability' => true,
                ],
            'payments' =>
                [
                    'limit'    => 99999999,
                    'currency' => 'EUR',
                    'amount'   => 999999999,
                ],
        ];

        $query = [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];

        $request = new Request('POST', 'v1/subscriptions');

        try {
            $response = $this->client->send($request, [
                    'headers' => $header,
                    'json'    => $body,
                    'query'   => $query
                ]
            );

            $subscription = json_decode((string)$response->getBody())->subscriptionId;

        } catch ( \Exception $e ) {

            dump($e->getMessage());
        }

        return $subscription;
    }


    public function getSubscription( string $subscriptionId )
    {
        $subscription = null;

        $header = [
            'cache-control'       => 'no-cache',
            'journeyId'           => $this->getJourneyId(),
            'timeStamp'           => '1540493118',
            'originUserId'        => $this->getOriginUserId(),
            'tppid'               => $this->getTtpId(),
            'app_name'            => $this->getAppId(),
            'APIm-Debug-Trans-Id' => 'true',
            'Content-Type'        => 'application/json',
            'Authorization'       => 'Bearer ' . $this->getAccessToken()
        ];

        $query = [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];

        $request = new Request('GET', 'v1/subscriptions/' . $subscriptionId);

        try {
            $response = $this->client->send($request, [
                    'headers' => $header,
                    'query'   => $query
                ]
            );

            $subscription = json_decode((string)$response->getBody())[0];

        } catch ( \Exception $e ) {

            dump($e->getMessage());
        }

        return $subscription;
    }


    public function updateSubscription( $subscription )
    {
        $header = [
            'cache-control'       => 'no-cache',
            'journeyId'           => $this->getJourneyId(),
            'timeStamp'           => '1540493118',
            'originUserId'        => $this->getOriginUserId(),
            'tppid'               => $this->getTtpId(),
            'app_name'            => $this->getAppId(),
            'APIm-Debug-Trans-Id' => 'true',
            'Content-Type'        => 'application/json',
            'Authorization'       => 'Bearer ' . $this->subscriptionAccessToken
        ];


        $query = [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];

        $request = new Request('PATCH', 'v1/subscriptions/' . $subscription->subscriptionId);

        try {
            $response = $this->client->send($request, [
                    'headers' => $header,
                    'json'    => $subscription,
                    'query'   => $query
                ]
            );

            $subscription = json_decode((string)$response->getBody());

        } catch ( \Exception $e ) {

            dump($e->getMessage());
        }

        return $subscription;
    }


    public function redirectToOneBankLogin( $subscriptionId )
    {
        header('Location: ' . $this->getOneBankLoginUrl($subscriptionId));
        die();

    }


    /**
     * @return mixed
     */
    public function getSubscriptionAccessToken()
    {
        return $this->subscriptionAccessToken;
    }

    /**
     * @param mixed $subscriptionAccessToken
     */
    public function setSubscriptionAccessToken( $subscriptionAccessToken )
    {
        $this->subscriptionAccessToken = $subscriptionAccessToken;
    }


    public function getOneBankLoginUrl( $subscriptionId )
    {
        $url = self::URL . 'oauth2/authorize?response_type=code&redirect_uri=' .$this->redirectUrl
            . '&scope=UserOAuth2Security&client_id=' . $this->getClientId()
            . '&subscriptionid=' . $subscriptionId;

        return $url;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param mixed $redirectUrl
     */
    public function setRedirectUrl( $redirectUrl )
    {
        $this->redirectUrl = $redirectUrl;
    }


}