<?php

namespace BankOfCyprus;

use BankOfCyprus\Accounts\AccountClient;
use BankOfCyprus\Authorization\AuthorizationClient;
use BankOfCyprus\Payments\PaymentClient;
use BankOfCyprus\Subscription\SubscriptionClient;

class BocClient
{
    public $authorizationClient;
    public $paymentClient;
    public $subscriptionClient;
    public $accountClient;

    /**
     * BocClient constructor.
     * @param $authorizationClient
     * @param $paymentClient
     * @param $subscriptionClient
     * @param $accountClient
     */
    public function __construct( array $config )
    {
        $this->authorizationClient = new AuthorizationClient($config);
        $this->subscriptionClient = new SubscriptionClient($config);
        $this->accountClient = new AccountClient($config);
        $this->paymentClient = new PaymentClient($config);
    }

}