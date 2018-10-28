<?php

/**
 * Bank of Cyprus Payment Gateway.
 *
 */
add_action('plugins_loaded', 'init_custom_gateway_class');

use BankOfCyprus\Payments\Transaction;


if ( session_id() == '' )
    session_start();


function init_custom_gateway_class()
{

    class WC_Gateway_Boc extends WC_Payment_Gateway
    {

        public $domain;

        /**
         * Constructor for the gateway.
         */
        public function __construct()
        {
            $this->domain = 'bocgateway';

            $this->id = 'bocgateway';
            $this->icon = apply_filters('woocommerce_custom_gateway_icon', 'http://hackathon.saturn-digital.com/wp-content/uploads/2018/10/bocapi.png');
            $this->has_fields = false;
            $this->method_title = __('Bank of Cyprus Gateway', $this->domain);
            $this->method_description = __('Allows payments with Bank of Cyprus gateway.', $this->domain);

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title = $this->get_option('title');
            $this->description = 'Pay with Bank of Cyprus wuth zero transaction fees.';
            $this->instructions = $this->get_option('instructions', $this->description);
            $this->order_status = $this->get_option('order_status', 'completed');

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        }

        /**
         * Initialise Gateway Settings Form Fields.
         */
        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled'      => array(
                    'title'   => __('Enable/Disable', $this->domain),
                    'type'    => 'checkbox',
                    'label'   => __('Enable Custom Payment', $this->domain),
                    'default' => 'yes'
                ),
                'title'        => array(
                    'title'       => __('Title', $this->domain),
                    'type'        => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', $this->domain),
                    'default'     => __('Bank of Cyprus', $this->domain),
                    'desc_tip'    => true,
                ),
                'order_status' => array(
                    'title'       => __('Order Status', $this->domain),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __('Choose whether status you wish after checkout.', $this->domain),
                    'default'     => 'wc-completed',
                    'desc_tip'    => true,
                    'options'     => wc_get_order_statuses()
                ),
                'description'  => array(
                    'title'       => __('Description', $this->domain),
                    'type'        => 'textarea',
                    'description' => __('Payment method description that the customer will see on your checkout.', $this->domain),
                    'default'     => __('Payment Information', $this->domain),
                    'desc_tip'    => true,
                ),
                'instructions' => array(
                    'title'       => __('Instructions', $this->domain),
                    'type'        => 'textarea',
                    'description' => __('Instructions that will be added to the thank you page and emails.', $this->domain),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'cliendid'     => array(
                    'title'       => __('Client ID', $this->domain),
                    'type'        => 'text',
                    'description' => __('This is your client ID', $this->domain),
                    'default'     => 'This is your client ID',
                    'desc_tip'    => true,
                ),
                'clientsecret' => array(
                    'title'       => __('Client Secret', $this->domain),
                    'type'        => 'text',
                    'description' => __('This is your client Secret', $this->domain),
                    'default'     => 'This is your client Secret',
                    'desc_tip'    => true,
                ),
                'redirecturl'  => array(
                    'title'       => __('Redirect Url', $this->domain),
                    'type'        => 'text',
                    'description' => __('This is the redirect url', $this->domain),
                    'default'     => 'This is the 1bank Redirect',
                    'desc_tip'    => true,
                ),
            );
        }

        /**
         * Payment form used for OTP
         */
        public function payment_fields()
        {


            if ( $description = $this->get_description() ) {
                echo wpautop(wptexturize($description));
            }


            if ( $_SESSION['order-state'] == 'otp' ) { ?>

                <div id="custom_input">
                    <p class="form-row form-row-wide">
                        <label for="otp" class=""><?php _e('One Time Password', $this->domain); ?>
                            <abbr class="required" title="required">*</abbr>
                        </label>
                        <input type="text" class="" name="otp" id="otp" placeholder="OTP code" value="">

                    <p style="font-size: 14px; margin-top: 5px;">
                        Please enter the OTP code included in the sms.
                    </p>
                    </p>
                </div>


                <?php
            }
        }


        /**
         * Process the payment and return the result.
         *
         * @param int $order_id
         * @return array
         */
        public function process_payment( $order_id )
        {
            global $bocApi;

            $order = wc_get_order($order_id);

            if ( $_SESSION['order-state'] == 'otp' ) {

                if ( $this->validate_otp() ) {
                    return [
                        'result' => 'error',
                    ];

                }

                $bocApi->paymentClient->setSubscriptionId($_SESSION['subscription']);
                $status = $bocApi->paymentClient->approvePayment($_SESSION['payment']->payment, $_POST['otp']);

                if ( isset($status->refNumber) ) {

                    $this->mark_order_completed($order);

                    $_SESSION['order-state'] = null;

                    return array(
                        'result'   => 'success',
                        'redirect' => $this->get_return_url($order)
                    );
                }
            }

            $subscription = $bocApi->subscriptionClient->createSubscription();

            $_SESSION['subscription'] = $subscription;
            $_SESSION['order-id'] = $order_id;

            // Return thankyou redirect
            return array(
                'result'   => 'success',
                'redirect' => $bocApi->subscriptionClient->getOneBankLoginUrl($subscription)
            );
        }


        /**
         * Validate One time password
         * Required and min len
         * @return array
         */
        private function validate_otp()
        {
            $otp = $_POST['otp'];

            $error = false;

            if ( empty($otp) ) {
                wc_add_notice('Please add OTP code.', 'error');
                $error = true;
            } else if ( strlen($otp) < 6 ) {
                wc_add_notice('OTP should be 6 characters.', 'error');
                $error = true;
            }

            return $error;
        }


        /**
         * Mark woocommerce order as completed
         * @param $order
         */
        private function mark_order_completed( $order )
        {
            // Set order status
            $order->update_status('completed');

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            WC()->cart->empty_cart();
        }


    }
}


add_action('parse_request', 'handle_one_bank_redirect');
function handle_one_bank_redirect()
{
    global $bocApi;

    $urlParts = parse_url($_SERVER["REQUEST_URI"]);

    if ( $urlParts['path'] != '/one_bank_redirect' )
        return;

    $order = wc_get_order($_SESSION['order-id']);

    if ( !$order ) {
        wp_redirect('/checkout');
        exit();
    }

    $code = $_GET['code'];

    // Get Subscription Access Token
    $subscriptionAccessToken = $bocApi->authorizationClient->getSubscriptionAccessToken($code);
    $bocApi->subscriptionClient->setSubscriptionAccessToken($subscriptionAccessToken);

    $subscriptionId = $_SESSION['subscription'];

    // Update user subscription
    $subscription = $bocApi->subscriptionClient->getSubscription($subscriptionId);
    $subscription = $bocApi->subscriptionClient->updateSubscription($subscription);

    $bocApi->paymentClient->setSubscriptionId($subscription->subscriptionId);


    // Create Transaction
    $transaction = new Transaction();
    $transaction->debtor->accountId = $subscription->selectedAccounts[0]->accountId;
    $transaction->creditor->accountId = CREDITOR;
    $transaction->transactionAmount->amount = $order->get_total();
    $transaction->paymentDetails = 'Test payment';

    // Create Sign request for payment
    $signRequest = $bocApi->paymentClient->createSignRequest($transaction);
    $payment = $bocApi->paymentClient->createPayment($signRequest);

    $_SESSION['order-state'] = 'otp';
    $_SESSION['payment'] = $payment;

    wp_redirect('/checkout#order_review');
    exit();
}


add_filter('woocommerce_payment_gateways', 'add_custom_gateway_class');
function add_custom_gateway_class( $methods )
{
    $methods[] = 'WC_Gateway_Boc';

    return $methods;
}



