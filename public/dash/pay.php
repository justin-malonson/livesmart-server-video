<?php
include_once '../server/connect.php';
include_once 'payment_config.php';
function getDates($interval, $interval_count) {
    global $pdo, $dbPrefix;
    $tenant = ($_POST['tenant']) ? $_POST['tenant'] : $_POST['description'];
    $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'subscriptions WHERE tenant = ? order by subscription_id desc limit 1');
    $stmt->execute([$tenant]);
    $subscription = $stmt->fetch();
    $valid_from = ($subscription && strtotime($subscription['valid_to']) >= strtotime(date('Y-m-d H:i:s'))) ? $subscription['valid_to'] : date('Y-m-d H:i:s');
    switch($interval) {
        case 'D':
            $int = 'day';
            break;
        case 'W':
            $int = 'week';
            break;
        case 'M':
            $int = 'month';
            break;
        case 'Y':
            $int = 'year';
            break;
        default:
            $int = 'year';
            break;
    }

    $date = strtotime($valid_from);
    $date = strtotime('+'.$interval_count.' '.$int, $date);
    $valid_to = date('Y-m-d H:i:s', $date);
    $_SESSION["agent"]['subscription'] = $valid_to;
    return array($valid_from, $valid_to);
}

if ($_POST['amount'] && $_POST['currency']) {
    session_start();
    if ($_POST['payment_method'] === 'manual') {
        function random_string($length) {
            $str = random_bytes($length);
            $str = base64_encode($str);
            $str = str_replace(["+", "/", "="], "", $str);
            $str = substr($str, 0, $length);
            return strtoupper($str);
        }
        $plan_id = $_POST['item_name'];
        $tenant = $_POST['tenant'];
        $email = $_SESSION['agent']['email'];
        $name = @$_SESSION['agent']['first_name'] . ' ' . $_SESSION['agent']['last_name'];

        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents WHERE tenant="'.$_POST['tenatnt'].'" AND is_master=1 LIMIT 1;');
        $stmt->execute($array);
        while ($r = $stmt->fetch()) {
            $email = $r['email'];
            $name = $r['first_name'] . ' ' . $r['last_name'];
        }
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'plans WHERE plan_id = ?');
        $stmt->execute([$plan_id]);
        $plan = $stmt->fetch();
        $plan_id = $plan['plan_id'];
        $interval = $plan['interval'];
        $interval_count = $plan['interval_count'];
        $dates = getDates($interval, $interval_count);
        $transaction_id = random_string(20);
        $sql = 'INSERT INTO ' . $dbPrefix . 'subscriptions(`payment_id`, `payment_method`, `payer_name`, `payer_email`, `amount`, `currency`, `payment_status`, `agent_id`, `valid_from`, `valid_to`, `tenant`, `plan_id`, `subscr_interval_count`, `subscr_interval`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$transaction_id, 'manual', $name, $email, $_POST['amount'], $_POST['currency'], 'approved', $_POST['item_value'], $dates[0], $dates[1], $_POST['tenant'], $plan_id, $interval_count, $interval]);
        header('Location: manual_success.php?id='.$transaction_id);
    } else if ($_POST['payment_method'] === 'paypal') {
        include_once 'paypal.php';
        try {
            $order_paramaters = array(
                'amount' => $_POST['amount'],
                'currency' => $_POST['currency'],
                'returnUrl' => PAYPAL_RETURN_URL,
                'cancelUrl' => PAYPAL_CANCEL_URL,
                'description' => $_POST['description'],
                'items' => array(
                    array(
                        'name' => $_POST['item_name'],
                        'price' => $_POST['amount'],
                        'description' => $_POST['item_value'],
                        'quantity' => 1
                    )
                )
            );
            $purchase = $gateway->purchase($order_paramaters);
            $response = $purchase->send();

            if ($response->isRedirect()) {
                $response->redirect(); // this will automatically forward the customer
            } else {
                // not successful
                print_r($response);
                echo $response->getMessage();
            }
        } catch(Exception $e) {
            echo $e->getMessage();
            header('Location: subscribe.php');
        }
    } else if ($_POST['payment_method'] === 'stripe' && $_POST['stripeToken']) {
        include_once 'stripe.php';
        try {
            $token = $_POST['stripeToken'];
            $response = $gateway->authorize([
                'amount' => $_POST['amount'],
                'currency' => $_POST['currency'],
                'description' => $_POST['description'],
                'token' => $token,
                'returnUrl' => STRIPE_RETURN_URL,
                'confirm' => true,
            ])->send();
            if ($response->isSuccessful()) {
                $plan_id = $_POST['item_name'];
                $arr_payment_data = $response->getData();
                $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'plans WHERE plan_id = ?');
                $stmt->execute([$plan_id]);
                $plan = $stmt->fetch();
                $plan_id = $plan['plan_id'];
                $interval = $plan['interval'];
                $interval_count = $plan['interval_count'];
                $email = ($arr_payment_data['billing_details']['email']) ? $arr_payment_data['billing_details']['email'] : $_SESSION['agent']['email'];
                $name = ($arr_payment_data['billing_details']['name']) ? $arr_payment_data['billing_details']['name'] : @$_SESSION['agent']['first_name'] . ' ' . $_SESSION['agent']['last_name'];

                $dates = getDates($interval, $interval_count);
                $sql = 'INSERT INTO ' . $dbPrefix . 'subscriptions(`payment_id`, `payment_method`, `payer_name`, `payer_email`, `amount`, `currency`, `payment_status`, `agent_id`, `valid_from`, `valid_to`, `tenant`, `plan_id`, `subscr_interval_count`, `subscr_interval`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                $pdo->prepare($sql)->execute([$arr_payment_data['id'], 'stripe', $name, $email, $_POST['amount'], $_POST['currency'], $arr_payment_data['status'], $_POST['item_value'], $dates[0], $dates[1], $_POST['description'], $plan_id, $interval_count, $interval]);
                header('Location: ' . STRIPE_RETURN_URL . '?id='.$arr_payment_data['id']);
            } else {
                echo $response->getMessage();
            }
        } catch(Exception $e) {
            echo $e->getMessage();
            header('Location: subscribe.php');
        }
    } else if ($_POST['payment_method'] === 'authorizenet' && isset($_POST['opaqueDataDescriptor']) && isset($_POST['opaqueDataValue'])) {
        require_once 'authorizenet.php';
        try {
            // Generate a unique merchant site transaction ID.
            $transactionId = rand(100000000, 999999999);

            $response = $gateway->authorize([
                'amount' => $_POST['amount'],
                'currency' => $_POST['currency'],
                'description' => $_POST['description'],
                'transactionId' => $transactionId,
                'opaqueDataDescriptor' => $_POST['opaqueDataDescriptor'],
                'opaqueDataValue' => $_POST['opaqueDataValue']
            ])->send();

            if($response->isSuccessful()) {

                // Captured from the authorization response.
                $transactionReference = $response->getTransactionReference();
                $response = $gateway->capture([
                    'amount' => $_POST['amount'],
                    'currency' => $_POST['currency'],
                    'transactionReference' => $transactionReference
                ])->send();

                $transaction_id = $response->getTransactionReference();
                $plan_id = $_POST['item_name'];

                $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'plans WHERE plan_id = ?');
                $stmt->execute([$plan_id]);
                $plan = $stmt->fetch();
                $plan_id = $plan['plan_id'];
                $interval = $plan['interval'];
                $interval_count = $plan['interval_count'];
                $email = (isset($_POST['cardHolderEmail'])) ? $_POST['cardHolderEmail'] : $_SESSION['agent']['email'];
                $name = (isset($_POST['cardHolderName'])) ? $_POST['cardHolderName'] :  $_SESSION['agent']['first_name'] . ' ' . $_SESSION['agent']['last_name'];
                $dates = getDates($interval, $interval_count);
                $sql = 'INSERT INTO ' . $dbPrefix . 'subscriptions(`payment_id`, `payment_method`, `payer_name`, `payer_email`, `amount`, `currency`, `payment_status`, `agent_id`, `valid_from`, `valid_to`, `tenant`, `plan_id`, `subscr_interval_count`, `subscr_interval`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                $pdo->prepare($sql)->execute([$transaction_id, 'authorizenet', $name, $email, $_POST['amount'], $_POST['currency'], 'approved', $_POST['item_value'], $dates[0], $dates[1], $_POST['description'], $plan_id, $interval_count, $interval]);
                header('Location: ' . AUTHORIZENET_RETURN_URL . '?id='.$transaction_id);
            } else {
                // not successful
                echo $response->getMessage();
                header('Location: subscribe.php');
            }
        } catch(Exception $e) {
            echo $e->getMessage();
            header('Location: subscribe.php');
        }
    } else {
        header('Location: subscribe.php');
    }
} else {
    header('Location: subscribe.php');
}