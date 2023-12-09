<?php
include_once "vendor/autoload.php";
include_once "../server/connect.php";
use Omnipay\Omnipay;

$gateway = Omnipay::create('PayPal_Rest');
$gateway->setClientId(PAYPAL_CLIENT_ID);
$gateway->setSecret(PAYPAL_CLIENT_SECRET);
if ($is_test_mode) {
    $gateway->setTestMode(true);
}