<?php
include_once 'header.php';

if (isset($_GET['id'])) {
    $message = '<span data-localize="transaction_successfull"></span> <span data-localize="transaction_is"></span> ' . $_GET['id'];
} else {
    $message = '<span data-localize="transaction_unsuccessfull"></span> ';
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