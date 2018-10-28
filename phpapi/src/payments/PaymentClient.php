<?php

namespace BankOfCyprus\Payments;

use BankOfCyprus\BaseClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


class PaymentClient extends BaseClient
{
    const SING_URL = 'https://sandbox-apis.bankofcyprus.com/df-boc-org-sb/sb/jwssignverifyapi/';
    const URL = 'https://sandbox-apis.bankofcyprus.com/df-boc-org-sb/sb/psd2/';

    private $subscriptionId;

    public function __construct(array $config)
    {
        parent::__construct();

        $this->client = new Client([
            'handler' => $this->stack,
        ]);

        $this->setClientId($config['client_id']);
        $this->setClientSecret($config['client_secret']);
        $this->setAppId($config['app_id']);
        $this->setOriginUserId($config['origin_user_id']);
        $this->setJourneyId($config['journey_id']);
        $this->setTtpId($config['tpp_id']);

    }


    public function createSignRequest( $transaction )
    {
        $signRequest = null;

        $header = [
            'cache-control' => 'no-cache',
            'tppId'         => $this->getTtpId(),
            'Content-Type'  => 'application/json'
        ];

        $request = new Request('POST', self::SING_URL . 'sign');

        try {
            $response = $this->client->send($request, [
                    'headers' => $header,
                    'json'    => $transaction
                ]
            );

            $signRequest = json_decode((string)$response->getBody());

        } catch ( \Exception $e ) {
            dump($e->getMessage());
        }

        return $signRequest;

    }

    public function createPayment( $signRequest )
    {
        $payment = null;

        $header = [
            'cache-control'  => 'no-cache',
            'lang'           => 'en',
            'correlationId'  => 'xyz',
            'timeStamp'      => '1540569083',
            'journeyId'      => $this->getJourneyId(),
            'tppId'          => $this->getTtpId(),
            'originUserId'   => $this->getOriginUserId(),
            'subscriptionId' => $this->subscriptionId,
            'Authorization'  => 'Bearer ' . $this->getAccessToken(),
            'Content-Type'   => 'application/json'
        ];


        $query = [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];

        $request = new Request('POST', self::URL . 'v1/payments');

        try {
            $response = $this->client->send($request, [
                    'headers' => $header,
                    'json'    => $signRequest,
                    'query'   => $query
                ]
            );

            $payment = json_decode((string)$response->getBody());

        } catch ( \Exception $e ) {
            dump($e->getMessage());
        }

        return $payment;
    }

    public function approvePayment( $payment, $otp )
    {
        $status = null;

        $header = [
            'cache-control'  => 'no-cache',
            'timeStamp'      => '1540572811',
            'journeyId'      => $this->getJourneyId(),
            'tppId'          => $this->getTtpId(),
            'originUserId'   => $this->getOriginUserId(),
            'subscriptionId' => $this->subscriptionId,
            'Authorization'  => 'Bearer ' . $this->getAccessToken(),
            'Content-Type'   => 'application/json'
        ];


        $body = [
            'transactionTime' => $payment->transactionTime,
            'authCode'        => $otp,
        ];

        $query = [
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];

        $request = new Request('POST', self::URL . 'v1/payments/' . $payment->paymentId . '/authorize');

        try {
            $response = $this->client->send($request, [
                    'headers' => $header,
                    'json'    => $body,
                    'query'   => $query
                ]
            );

            $status = json_decode((string)$response->getBody());

        } catch ( \Exception $e ) {

            dump($e->getMessage());
        }

        return $status;
    }

    public function cancelPayment()
    {

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
