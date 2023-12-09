<?php
include_once 'header.php';
?>

<h1 class="h3 mb-2 text-gray-800" id="planTitle" data-localize="plan"></h1>
<div id="error" style="display:none;" class="alert alert-danger"></div>

<div class="row">
    <div class="col-lg-5">
        <div class="p-1">

            <form class="plan">
                <div class="form-group">
                    <label for="payment_status"><h6 data-localize="payment_status"></h6></label><br/>
                    <span id="payment_status"></span>
                </div>
                <div class="form-group">
                    <label for="payer_name"><h6 data-localize="payer_name"></h6></label><br/>
                    <span id="payer_name"></span>
                </div>
                <div class="form-group">
                    <label for="payer_email"><h6 data-localize="payer_email"></h6></label><br/>
                    <span id="payer_email"></span>
                </div>
                <div class="form-group">
                    <label for="agent_name"><h6 data-localize="agent"></h6></label><br/>
                    <span id="agent_name"></span>
                </div>
                <div class="form-group">
                    <label for="payment_method"><h6 data-localize="payment_method"></h6></label><br/>
                    <span id="payment_method"></span>
                </div>
                <div class="form-group">
                    <label for="plan_name"><h6 data-localize="plan"></h6></label><br/>
                    <span id="plan_name"></span>
                </div>
                <div class="form-group">
                    <label for="valid_from"><h6 data-localize="valid_from"></h6></label>
                    <input type="text" class="form-control " id="valid_from" aria-describedby="valid_from">
                </div>
                <div class="form-group">
                    <label for="valid_to"><h6 data-localize="valid_to"></h6></label>
                    <input type="text" class="form-control " id="valid_to" aria-describedby="valid_to">
                </div>
                <div class="form-group">
                    <label for="subscr_interval"><h6 data-localize="interval"></h6></label><br/>
                    <span id="subscr_interval"></span>
                </div>
                <div class="form-group">
                    <label for="subscr_interval_count"><h6 data-localize="interval_count"></h6></label><br/>
                    <span id="subscr_interval_count"></span>
                </div>
                <div class="form-group">
                    <label for="payment_id"><h6 data-localize="payment_id"></h6></label><br/>
                    <span id="payment_id"></span>
                </div>
                <div class="form-group">
                    <label for="paid_amount"><h6 data-localize="paid_amount"></h6></label><br/>
                    <span id="paid_amount"></span>
                </div>
                <div class="form-group">
                    <label for="ipn_track_id"><h6 data-localize="ipn_track_id"></h6></label><br/>
                    <span id="ipn_track_id"></span>
                </div>
                <div class="form-group">
                    <label for="txn_id"><h6 data-localize="txn_id"></h6></label><br/>
                    <span id="txn_id"></span>
                </div>
                <hr>
                <a href="javascript:void(0);" id="saveSubscription" class="btn btn-primary btn-subscription btn-block" data-localize="save">
                </a>
                <hr>

            </form>

        </div>
    </div>
</div>
<?php
include_once 'footer.php';
