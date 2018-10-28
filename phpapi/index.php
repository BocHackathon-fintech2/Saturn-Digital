<?php

/**
 * Logins
 * ﻿[ 25 October 2018 10:34 PM ] Thomas Stavrou: 999999
 * [ 25 October 2018 10:34 PM ] Thomas Stavrou: 112233
 */
use BankOfCyprus\BocClient;
use BankOfCyprus\Payments\Transaction;

require_once 'vendor/autoload.php';

const CLIENT_ID = '8322f28b-1858-4b5a-ad8e-450503df94c5';
const CLIENT_SECRET = 'cK1uD1kQ4lA3vF0yF2eS4nM7qN3eW5xH3iA8eQ2mP5fQ7uU8nP';
const APP_ID = 'Eshop Woocommerce';
const ORIGIN_USER_ID = 'qwerty';
const JOURNEY_ID = 'zxcv';
const TPP_ID = 'singpaymentdata';
const REDIRECT_URL = 'http://localhost';
const CREDITOR = '351092345672';
const OTP = '123456';


$bocApi = new BocClient([
    'client_id'      => CLIENT_ID,
    'client_secret'  => CLIENT_SECRET,
    'app_id'         => APP_ID,
    'origin_user_id' => ORIGIN_USER_ID,
    'journey_id'     => JOURNEY_ID,
    'tpp_id'         => TPP_ID,
    'redirect_url'   => REDIRECT_URL,
]);


// Check if redirect and has code query string

if ( isset($_GET['code']) ) {

    $code = $_GET['code'];

    // get subscription access token
    $subscriptionAccessToken = $bocApi->authorizationClient->getSubscriptionAccessToken($code);

    $bocApi->subscriptionClient->setSubscriptionAccessToken($subscriptionAccessToken);

    $subscriptionId = file_get_contents('subscription.txt');

    $token = $bocApi->authorizationClient->getAppAccessToken();
    $bocApi->subscriptionClient->setAccessToken($token);

    $subscription = $bocApi->subscriptionClient->getSubscription($subscriptionId);

    $subscription = $bocApi->subscriptionClient->updateSubscription($subscription);

    $bocApi->paymentClient->setSubscriptionId($subscription->subscriptionId);


    $transaction = new Transaction();
    $transaction->debtor->accountId = $subscription->selectedAccounts[0]->accountId;;
    $transaction->creditor->accountId = CREDITOR;
    $transaction->transactionAmount->amount = 2.55;
    $transaction->paymentDetails = 'Test payment';

    $signRequest = $bocApi->paymentClient->createSignRequest($transaction);
    $bocApi->paymentClient->setAccessToken($token);

    $payment = $bocApi->paymentClient->createPayment($signRequest);

    $status = $bocApi->paymentClient->approvePayment($payment->payment,OTP);

    dd($status);
}


$token = $bocApi->authorizationClient->getAppAccessToken();
$bocApi->subscriptionClient->setAccessToken($token);

$subscription = $bocApi->subscriptionClient->createSubscription();

file_put_contents("subscription.txt", $subscription);
$bocApi->subscriptionClient->redirectToOneBankLogin($subscription);


?>

