<?php
$stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "payment_options");
$stmt->execute();
$paymentData = $stmt->fetch();
$actual_link = 'https://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
$is_test_mode = $paymentData['is_test_mode'];
$is_test_mode = ($is_test_mode == 1) ? true : false;

//577UhKn3 378NAeSd739Hv22y SIMON
define('PAYPAL_CLIENT_ID', $paymentData['paypal_client_id']);
define('PAYPAL_CLIENT_SECRET', $paymentData['paypal_secret_id']);
define('PAYPAL_RETURN_URL', $actual_link.'/paypal_success.php');
define('PAYPAL_CANCEL_URL', $actual_link.'/paypal_cancel.php');

define('STRIPE_CLIENT_ID', $paymentData['stripe_client_id']);
define('STRIPE_CLIENT_SECRET', $paymentData['stripe_secret_id']);
define('STRIPE_RETURN_URL', $actual_link.'/stripe_success.php');
define('STRIPE_CANCEL_URL', $actual_link.'/stripe_cancel.php');

define('AUTHORIZENET_API_LOGIN_ID', $paymentData['authorizenet_api_login_id']);
define('AUTHORIZENET_TRANSACTION_KEY', $paymentData['authorizenet_transaction_key']);
define('AUTHORIZENET_PUBLIC_CLIENT_KEY', $paymentData['authorizenet_public_client_key']);
define('AUTHORIZENET_RETURN_URL', $actual_link.'/authorizenet_success.php');
define('AUTHORIZENET_CANCEL_URL', $actual_link.'/authorizenet_cancel.php');