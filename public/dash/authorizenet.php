<?php
include_once "vendor/autoload.php";
include_once "../server/connect.php";
use Omnipay\Omnipay;

$gateway = Omnipay::create('AuthorizeNetApi_Api');
$gateway->setAuthName(AUTHORIZENET_API_LOGIN_ID);
$gateway->setTransactionKey(AUTHORIZENET_TRANSACTION_KEY);
if ($is_test_mode) {
    $gateway->setTestMode(true);
}