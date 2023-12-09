<?php
include_once 'header.php';
include_once 'paypal.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Once the transaction has been approved, we need to complete it.
if (array_key_exists('paymentId', $_GET) && array_key_exists('PayerID', $_GET)) {
    $transaction = $gateway->completePurchase(array(
        'payer_id'             => $_GET['PayerID'],
        'transactionReference' => $_GET['paymentId'],
    ));
    $response = $transaction->send();
    $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'subscriptions WHERE payment_id = ?');
    $stmt->execute([$_GET['paymentId']]);
    $sub = $stmt->fetch();
    if ($sub) {
        $message = '<span data-localize="transaction_unsuccessfull"></span>';
    } else {
        if ($response->isSuccessful()) {

            // The customer has successfully paid.
            $arr_body = $response->getData();

            $payment_id = $arr_body['id'];
            $payer_id = $arr_body['payer']['payer_info']['first_name'] . ' ' . $arr_body['payer']['payer_info']['last_name'];
            $payer_email = $arr_body['payer']['payer_info']['email'];
            $amount = $arr_body['transactions'][0]['amount']['total'];
            $plan_id = $arr_body['transactions'][0]['item_list']['items'][0]['name'];
            $agent_id = $arr_body['transactions'][0]['item_list']['items'][0]['description'];
            $tenant = $arr_body['transactions'][0]['description'];
            $string = print_r($arr_body, true);
            // file_put_contents('log.txt', $string);
            $currency = 'USD';
            $payment_status = $arr_body['state'];

            $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'plans WHERE plan_id = ?');
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch();
            $plan_id = $plan['plan_id'];
            $interval = $plan['interval'];
            $interval_count = $plan['interval_count'];

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
            $sql = 'INSERT INTO ' . $dbPrefix . 'subscriptions(`payment_id`, `payment_method`, `payer_name`, `payer_email`, `amount`, `currency`, `payment_status`, `agent_id`, `valid_from`, `valid_to`, `tenant`, `plan_id`, `subscr_interval_count`, `subscr_interval`) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $pdo->prepare($sql)->execute([$payment_id, 'paypal', $payer_id, $payer_email, $amount, $currency, $payment_status, $agent_id, $valid_from, $valid_to, $tenant, $plan_id, $interval_count, $interval]);
            //print_r($_SESSION);
            $_SESSION['agent']['subscription'] = $valid_to;
            $message = '<span data-localize="transaction_successfull"></span> <span data-localize="transaction_is"></span> ' . $payment_id;
        } else {
            $message = '<span data-localize="transaction_unsuccessfull"></span> ' . $response->getMessage();
        }
    }
} else {
    $message = '<span data-localize="transaction_unsuccessfull"></span>';
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left" data-localize="subscription"></h6>
    </div>
    <div class="card-body"><?php echo $message;?></div>

</div>

<?php
include_once 'footer.php';
?>