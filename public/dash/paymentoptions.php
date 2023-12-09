<?php
include_once 'header.php';
?>

<h1 class="h3 mb-2 text-gray-800" id="optionTitle" data-localize="payment_options"></h1>
<div id="error" style="display:none;" class="alert alert-danger"></div>
<?php if ($_SESSION["tenant"] == 'lsv_mastertenant' || @$_GET['id'] == $_SESSION["agent"]['agent_id']) { ?>

    <div class="row">
        <div class="col-lg-5">
            <div class="p-1">

                <form class="user">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_enabled">
                            <label class="custom-control-label" for="is_enabled" data-localize="is_enabled"></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_test_mode">
                            <label class="custom-control-label" for="is_test_mode" data-localize="is_test_mode"></label>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="paypal_enabled">
                            <label class="custom-control-label" for="paypal_enabled" data-localize="paypal_enabled"></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="paypal_client_id"><h6 data-localize="paypal_client_id"></h6></label>
                        <input type="text" class="form-control" id="paypal_client_id" aria-describedby="paypal_client_id">
                    </div>
                    <div class="form-group">
                        <label for="paypal_secret_id"><h6 data-localize="paypal_secret_id"></h6></label>
                        <input type="text" class="form-control" id="paypal_secret_id" aria-describedby="paypal_secret_id">
                    </div>
                    <hr>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="stripe_enabled">
                            <label class="custom-control-label" for="stripe_enabled" data-localize="stripe_enabled"></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="stripe_client_id"><h6 data-localize="stripe_client_id"></h6></label>
                        <input type="text" class="form-control" id="stripe_client_id" aria-describedby="stripe_client_id">
                    </div>
                    <div class="form-group">
                        <label for="stripe_secret_id"><h6 data-localize="stripe_secret_id"></h6></label>
                        <input type="text" class="form-control" id="stripe_secret_id" aria-describedby="stripe_secret_id">
                    </div>
                    <hr>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="authorizenet_enabled">
                            <label class="custom-control-label" for="authorizenet_enabled" data-localize="authorizenet_enabled"></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="authorizenet_api_login_id"><h6 data-localize="authorizenet_api_login_id"></h6></label>
                        <input type="text" class="form-control" id="authorizenet_api_login_id" aria-describedby="authorizenet_api_login_id">
                    </div>
                    <div class="form-group">
                        <label for="authorizenet_transaction_key"><h6 data-localize="authorizenet_transaction_key"></h6></label>
                        <input type="text" class="form-control" id="authorizenet_transaction_key" aria-describedby="authorizenet_transaction_key">
                    </div>
                    <div class="form-group">
                        <label for="authorizenet_public_client_key"><h6 data-localize="authorizenet_public_client_key"></h6></label>
                        <input type="text" class="form-control" id="authorizenet_public_client_key" aria-describedby="authorizenet_public_client_key">
                    </div>
                    <hr>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="email_notification">
                            <label class="custom-control-label" for="email_notification" data-localize="email_notification"></label>
                        </div>
                    </div>
                    <div id="email_templates">
                        <div class="form-group">
                            <label for="email_subject"><h6 data-localize="email_subject"></h6></label>
                            <input type="text" class="form-control" id="email_subject" aria-describedby="email_subject">
                        </div>
                        <div class="form-group">
                            <label for="email_body"><h6 data-localize="email_body"></h6></label>
                            <textarea rows="6" class="form-control" id="email_body"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="email_from"><h6 data-localize="email_from"></h6></label>
                            <input type="text" class="form-control" id="email_from" aria-describedby="email_from">
                        </div>
                        <div class="form-group">
                            <label for="email_day_notify"><h6 data-localize="email_day_notify"></h6></label>
                            <select class="form-control" name="email_day_notify" id="email_day_notify" name="email_day_notify">
                                <option value="1">1</option>
                                <option value="3">3</option>
                                <option value="7">7</option>
                                <option value="14">14</option>
                            </select>
                        </div>
                    </div>
                    <a href="javascript:void(0);" id="saveOptions" class="btn btn-primary btn-user btn-block" data-localize="save">
                    </a>
                    <hr>
                </form>

            </div>
        </div>
    </div>
<?php } ?>
<?php
include_once 'footer.php';
