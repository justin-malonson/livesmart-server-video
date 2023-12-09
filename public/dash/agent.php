<?php
include_once 'header.php';
?>

<h1 class="h3 mb-2 text-gray-800" id="agentTitle" data-localize="agent"></h1>
<div id="error" style="display:none;" class="alert alert-danger"></div>
<?php if ($_SESSION["tenant"] == 'lsv_mastertenant' || $_SESSION["tenant_admin"] || @$_GET['id'] == $_SESSION["agent"]['agent_id']) { ?>

    <div class="row">
        <div class="col-lg-5">
            <div class="p-1">

                <form class="user">

                    <div class="form-group">
                        <label for="first_name"><h6 data-localize="first_name"></h6></label>
                        <input type="text" class="form-control" id="first_name" aria-describedby="first_name">
                    </div>
                    <div class="form-group">
                        <label for="last_name"><h6 data-localize="last_name"></h6></label>
                        <input type="text" class="form-control" id="last_name" aria-describedby="last_name">
                    </div>
                    <?php if ($_SESSION["tenant"] == 'lsv_mastertenant' || $_SESSION["tenant_admin"]) { ?>
                        <div class="form-group">
                            <label for="email"><h6 data-localize="email"></h6></label>
                            <input type="text" class="form-control" id="email" aria-describedby="email">
                        </div>
                        <div class="form-group">
                            <label for="tenant"><h6 data-localize="tenant"></h6></label>
                            <input type="text" class="form-control" id="tenant" <?php echo ($_SESSION["tenant"] != 'lsv_mastertenant') ? 'disabled' : '';?> aria-describedby="tenant" value="<?php echo ($_SESSION["tenant"] != 'lsv_mastertenant') ? $_SESSION["tenant"] : '';?>">
                        </div>
                        <div class="form-group" id="usernameDiv">
                            <label for="first_name"><h6 data-localize="username"></h6></label>
                            <input type="text" class="form-control" id="username" aria-describedby="username">
                        </div>
                        <?php if ($_SESSION["tenant"] == 'lsv_mastertenant') {?>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_master">
                                <label class="custom-control-label" for="is_master" data-localize="is_admin"></label>
                            </div>
                        </div>
                        <?php } ?>
                    <?php } else { ?>
                        <input type="hidden" class="form-control" id="email">
                        <input type="hidden" class="form-control" id="tenant">
                        <input type="hidden" class="form-control" id="username">
                    <?php } ?>
                    <div class="form-group">
                        <label for="password"><h6><span data-localize="password"></span> <span id="leftblank"></span></h6></label>
                        <input type="password" class="form-control" id="password" autocomplete="new-password">
                    </div>
                    <input type="hidden" class="form-control" id="usernamehidden">
                    <a href="javascript:void(0);" id="saveAgent" class="btn btn-primary btn-user btn-block" data-localize="save">
                    </a>
                    <hr>

                </form>

            </div>
        </div>
        <?php if (isset($_SESSION['agent']['payment_enabled']) && $_SESSION['agent']['payment_enabled'] && @$_SESSION['tenant_admin']) {
            $configFile = $_SESSION['agent']['tenant'];
            if (!file_exists('../config/' . $configFile . '.json') || !isset($_SESSION['agent']['tenant'])) {
                $configFile = 'config';
            }
            $jsonString = file_get_contents('../config/' . $configFile . '.json');
            $data = json_decode($jsonString);
            $payment_config_enabled = @$data->serverSide->payment_enabled;
            if ($payment_config_enabled) {
            ?>
        <div class="col-sm-6">
            <div class="p-1">
                <h6 data-localize="subscription"></h6>
                <?php if (@$_GET['id'] == @$_SESSION["agent"]['agent_id']) {
                        if (@$_SESSION["agent"]['subscription']) {
                            $message = '<span data-localize="subscribed_till"></span>' . date('F j, Y G:i', strtotime($_SESSION["agent"]['subscription']));
                        } else {
                            $message = '<span data-localize="need_subscribe"></span>';
                        }
                    ?>
                    <hr>
                    <div class="form-group">
                        <h6><?php echo $message;?></h6>
                    </div>
                    <div class="form-group">
                        <h6><span><a href="history.php" data-localize="payment_history"></a></span></h6>
                    </div>
                    <?php } ?>

                <hr>

            </div>
        </div>
        <?php }
        } ?>
    </div>
<?php } ?>
<?php
include_once 'footer.php';
