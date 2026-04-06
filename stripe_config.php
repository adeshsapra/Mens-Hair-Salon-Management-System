<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/payment_integration_helpers.php';

$stripeConfig = [
    'active_mode' => 'sandbox',
    'is_enabled' => false,
    'is_connected' => false,
    'publishable_key' => '',
    'secret_key' => '',
];

if (isset($con) && $con instanceof mysqli) {
    $stripeConfig = paymentIntegrationGetStripeConfig($con);
}

if (!defined('STRIPE_ACTIVE_MODE')) {
    define('STRIPE_ACTIVE_MODE', $stripeConfig['active_mode']);
}
if (!defined('STRIPE_ENABLED')) {
    define('STRIPE_ENABLED', (bool) $stripeConfig['is_enabled']);
}
if (!defined('STRIPE_IS_CONNECTED')) {
    define('STRIPE_IS_CONNECTED', (bool) $stripeConfig['is_connected']);
}
if (!defined('STRIPE_PUBLISHABLE_KEY')) {
    define('STRIPE_PUBLISHABLE_KEY', (string) $stripeConfig['publishable_key']);
}
if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', (string) $stripeConfig['secret_key']);
}
