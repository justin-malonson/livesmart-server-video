<?php
include_once 'header.php';
?>


<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left" data-localize="active_meetings"></h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">

            <table class="table table-bordered" id="rooms_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="15%" class="text-center" data-localize="room"></th>
                        <th class="text-center" data-localize="attendees"></th>
                        <th width="20%" class="text-center" data-localize="action"></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>

            </table>
            <span data-localize="total_meetings"></span> <span id="total_meetings"></span>
        </div>

    </div>

</div>

<?php
include_once 'footer.php';
?>
