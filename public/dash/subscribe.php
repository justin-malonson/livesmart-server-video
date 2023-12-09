<?php
include_once 'header.php';
include_once 'stripe.php';
?>

<div class="row">
    <div class="card-deck mb-1">
        <?php
        $array = [];
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'plans');
        $stmt->execute($array);
        while ($r = $stmt->fetch()) {
        ?>

            <div class="card mb-3 border-left-info shadow m-3">
                <div class="card-header">
                    <h4 class="my-0 font-weight-normal"><a href="plan.php?id=<?php echo $r['plan_id']; ?>"><?php echo $r['name']; ?></a></h4>
                </div>
                <div class="card-body d-flex flex-column">
                    <h4 class="card-title pricing-card-title"><?php echo $r['price']; ?> <?php echo $r['currency']; ?> / <small class="text-muted"> <?php echo $r['interval_count']; ?> <span data-localize="<?php echo $r['interval']; ?>"></span></small></h4>
                    <div class="text-info" style="overflow-y : auto;">
                        <?php echo nl2br($r['description']); ?>
                    </div>
                </div>
                <div class="card-footer">
                    <input class="form-control mt-auto" type="radio" data-item_name="<?php echo $r['plan_id']; ?>" data-price="<?php echo $r['price']; ?>" data-currency="<?php echo $r['currency']; ?>" value="<?php echo $r['plan_id']; ?>" name="plan" id="plan<?php echo $r['plan_id']; ?>" style="height: calc(1.5em + 2px) !important;">
                </div>
            </div>

        <?php
        }
        ?>
    </div>
</div>

<form action="pay.php" method="post" name="payment-form" id="payment-form">
    <div class="row">
        <div class="col-lg-5">
            <div class="p-1">
                <div class="form-group">
                    <label for="payment_method">
                        <h6 data-localize="payment_method"></h6>
                    </label>
                    <select class="form-control" name="payment_method" id="payment_method" name="payment_method">
                        <?php if ($paymentData['paypal_enabled']) { ?>
                            <option data-localize="paypal" value="paypal"></option>
                        <?php } ?>
                        <?php if ($paymentData['stripe_enabled']) { ?>
                            <option data-localize="stripe" value="stripe"></option>
                        <?php } ?>
                        <?php if ($paymentData['authorizenet_enabled']) { ?>
                            <option data-localize="authorizenet" value="authorizenet"></option>
                        <?php } ?>
                        <?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>
                            <option data-localize="manual" value="manual"></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div id="stripe_payment" style="display:none">
        <div class="row">
            <div class="col-lg-5">
                <div class="p-1">
                    <div class="form-group">
                        <div id="card-element">
                            <!-- A Stripe Element will be inserted here. -->
                        </div>
                        <!-- Used to display form errors. -->
                        <div id="card-errors" role="alert"></div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Create a Stripe client.
            var publishable_key = '<?php echo STRIPE_CLIENT_ID; ?>';
            var stripe = Stripe(publishable_key);

            // Create an instance of Elements.
            var elements = stripe.elements();

            // Custom styling can be passed to options when creating an Element.
            // (Note that this demo uses a wider set of styles than the guide below.)
            var style = {
                base: {
                    color: '#32325d',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };

            // Create an instance of the card Element.
            var card = elements.create('card', {
                style: style
            });

            // Add an instance of the card Element into the `card-element` <div>.
            card.mount('#card-element');

            // Handle real-time validation errors from the card Element.
            card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
            // Handle form submission.
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                if ($("#payment_method option:selected").val() === 'stripe') {
                    event.preventDefault();
                    stripe.createToken(card).then(function(result) {
                        if (result.error) {
                            // Inform the user if there was an error.
                            var errorElement = document.getElementById('card-errors');
                            errorElement.textContent = result.error.message;
                        } else {
                            // Send the token to your server.
                            stripeTokenHandler(result.token);
                        }
                    });
                }
            });
            // Submit the form with the token ID.
            function stripeTokenHandler(token) {
                // Insert the token ID into the form so it gets submitted to the server
                var form = document.getElementById('payment-form');
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);

                // Submit the form
                form.submit();
            }
        </script>
    </div>
    <div id="authorizenet_payment" style="display:none">
        <?php if ($paymentData['is_test_mode']) { ?>
            <script type="text/javascript" src="https://jstest.authorize.net/v1/Accept.js" charset="utf-8"></script>
        <?php } else { ?>
            <script type="text/javascript" src=" https://js.authorize.net/v1/Accept.js" charset="utf-8"></script>
        <?php } ?>
        <script type="text/javascript">
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                $('#errorAuth').hide();
                if ($("#payment_method option:selected").val() === 'authorizenet') {
                    event.preventDefault();
                    $('#submitButton').prop("disabled", true);
                    // add spinner to button
                    // $('#submitButton').html(
                    //     `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`
                    // );
                    sendPaymentDataToAnet();
                }
            });

            function sendPaymentDataToAnet() {
                // Set up authorisation to access the gateway.
                var authData = {};
                authData.clientKey = "<?php echo AUTHORIZENET_PUBLIC_CLIENT_KEY; ?>";
                authData.apiLoginID = "<?php echo AUTHORIZENET_API_LOGIN_ID; ?>";

                var cardData = {};
                cardData.cardNumber = document.getElementById("cardNumber").value;
                cardData.month = document.getElementById("expMonth").value;
                cardData.year = document.getElementById("expYear").value;
                cardData.cardCode = document.getElementById("cardCode").value;

                // Now send the card data to the gateway for tokenisation.
                // The responseHandler function will handle the response.
                var secureData = {};
                secureData.authData = authData;
                secureData.cardData = cardData;
                Accept.dispatchData(secureData, responseHandler);
            }

            function responseHandler(response) {
                // add spinner to button
                if (!$('#cardHolderName').val()) {
                    $('#errorAuth').show();
                    $('#errorAuth').html('Please fill in card holder name');
                    $('#submitButton').prop("disabled", false);
                } else if (!$('#cardHolderEmail').val()) {
                    $('#errorAuth').show();
                    $('#errorAuth').html('Please fill in card holder email');
                    $('#submitButton').prop("disabled", false);
                } else if (!$('#item_name').val()) {
                    $('#errorAuth').show();
                    $('#errorAuth').html('Please choose a plan');
                    $('#submitButton').prop("disabled", false);
                } else {
                    if (response.messages.resultCode === "Error") {
                        var i = 0;
                        var err = '';
                        while (i < response.messages.message.length) {
                            err += response.messages.message[i].text;
                            i = i + 1;
                        }
                        $('#errorAuth').show();
                        $('#errorAuth').html(err);
                        $('#submitButton').prop("disabled", false);
                    } else {
                        paymentFormUpdate(response.opaqueData);
                    }
                }
            }

            function paymentFormUpdate(opaqueData) {
                document.getElementById("opaqueDataDescriptor").value = opaqueData.dataDescriptor;
                document.getElementById("opaqueDataValue").value = opaqueData.dataValue;
                document.getElementById("payment-form").submit();
            }
        </script>
        <div id="errorAuth" style="display:none;" class="alert alert-danger"></div>
        <div class="row form-group col-lg-5">
            <label for="cardNumber">
                <h6 data-localize="cardNumber"></h6>
            </label>
            <input type="text" class="form-control" id="cardNumber" />
        </div>
        <div class="row">
            <div class="form-group col-lg-1">
                <label for="expMonth">
                    <h6 data-localize="expMonth"></h6>
                </label>
                <input type="text" class="form-control" id="expMonth" />
            </div>
            <div class="form-group col-lg-1">
                <label for="expYear">
                    <h6 data-localize="expYear"></h6>
                </label>
                <input type="text" class="form-control" id="expYear" />
            </div>
            <div class="form-group col-lg-1">
                <label for="cardCode">
                    <h6 data-localize="cardCode"></h6>
                </label>
                <input type="text" class="form-control" id="cardCode" />
            </div>
        </div>
        <div class="row form-group col-lg-5">
            <label for="cardHolderName">
                <h6 data-localize="cardHolderName"></h6>
            </label>
            <input type="text" class="form-control" id="cardHolderName" name="cardHolderName" />
        </div>
        <div class="row form-group col-lg-5">
            <label for="cardHolderEmail">
                <h6 data-localize="cardHolderEmail"></h6>
            </label>
            <input type="text" class="form-control" id="cardHolderEmail" name="cardHolderEmail" />
        </div>
    </div>
    <div id="manual" style="display:none">
        <?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>
            <div class="row">
                <div class="col-lg-5">
                    <div class="p-1">
                        <div class="form-group">
                            <label for="tenant">
                                <h6 data-localize="tenant"></h6>
                            </label>
                            <select class="form-control" name="tenant" id="tenant">
                                <?php
                                $array = [];
                                $stmt = $pdo->prepare('SELECT tenant, agent_id FROM ' . $dbPrefix . 'agents WHERE agent_id IN (SELECT MAX(agent_id) from ' . $dbPrefix . 'agents group by tenant) order by tenant asc;');
                                $stmt->execute($array);
                                while ($r = $stmt->fetch()) {
                                    $str = '';
                                    $stmt1 = $pdo->prepare('SELECT valid_to FROM ' . $dbPrefix . 'subscriptions WHERE tenant = "' . $r['tenant'] . '" order by subscription_id desc limit 1;');
                                    $stmt1->execute($array);
                                    while ($r1 = $stmt1->fetch()) {
                                        $str = ($r1['valid_to']) ? ', valid to ' . date('F j, Y G:i', strtotime($r1['valid_to'])) : '';
                                    }
                                ?>
                                    <option value="<?php echo $r['tenant']; ?>"><?php echo $r['tenant']; ?> <?php echo $str; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <input type="hidden" id="price" name="amount">
    <input type="hidden" name="currency" id="currency">
    <input type="hidden" name="item_name" id="item_name">
    <input type="hidden" name="description" id="description" value="<?php echo @$_SESSION['agent']['tenant'] ?>">
    <input type="hidden" name="item_value" id="item_value" value="<?php echo @$_SESSION['agent']['agent_id'] ?>">
    <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" />
    <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" />
    <button type="submit" id="submitButton" class="btn btn-primary" data-localize="subscribe"></button>
</form>



<?php
include_once 'footer.php';
?>