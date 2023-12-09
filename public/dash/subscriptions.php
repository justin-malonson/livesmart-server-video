<?php
include_once 'header.php';
?>



<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left" data-localize="subscriptions"></h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">

            <table class="table table-bordered" id="subscriptions_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center" data-localize="payer_name"></th>
                        <th class="text-center" data-localize="payer_email"></th>
                        <th class="text-center" data-localize="period"></th>
                        <th class="text-center" data-localize="payment_status"></th>
                        <th class="text-center" data-localize="tenant"></th>
                        <th class="text-center" data-localize="action"></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>

            </table>
        </div>

    </div>

</div>

<?php
include_once 'footer.php';
?>
