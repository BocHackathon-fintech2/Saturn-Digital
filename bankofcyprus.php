<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              saturn-digital.com
 * @since             1.0.0
 * @package           Bocapi
 *
 * @wordpress-plugin
 * Plugin Name:       BocApi
 * Plugin URI:        saturn
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Saturn Digital
 * Author URI:        saturn-digital.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bocapi
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( !defined('WPINC') ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PLUGIN_NAME_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bocapi-activator.php
 */


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'phpapi/vendor/autoload.php';
require plugin_dir_path(__FILE__) . 'bankofcyprus-payments.php';


const CLIENT_ID = '8322f28b-1858-4b5a-ad8e-450503df94c5';
const CLIENT_SECRET = 'cK1uD1kQ4lA3vF0yF2eS4nM7qN3eW5xH3iA8eQ2mP5fQ7uU8nP';
const APP_ID = 'Eshop Woocommerce';
const ORIGIN_USER_ID = 'qwerty';
const JOURNEY_ID = 'zxcv';
const TPP_ID = 'singpaymentdata';
const REDIRECT_URL = 'http://localhost:8000/one_bank_redirect';
const CREDITOR = '351092345672';
const OTP = '123456';


// Global bocApi object
$bocApi = new \BankOfCyprus\BocClient([
    'client_id'      => CLIENT_ID,
    'client_secret'  => CLIENT_SECRET,
    'app_id'         => APP_ID,
    'origin_user_id' => ORIGIN_USER_ID,
    'journey_id'     => JOURNEY_ID,
    'tpp_id'         => TPP_ID,
    'redirect_url'   => REDIRECT_URL,
]);


// Set oauth token to client apis
$token = $bocApi->authorizationClient->getAppAccessToken();
$bocApi->subscriptionClient->setAccessToken($token);
$bocApi->paymentClient->setAccessToken($token);





