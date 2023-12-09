<?php
include_once "vendor/autoload.php";
use Omnipay\Omnipay;

$gateway = Omnipay::create('Stripe');
$gateway->setApiKey(STRIPE_CLIENT_SECRET);