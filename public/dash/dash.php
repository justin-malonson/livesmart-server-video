<?php
include_once 'header.php';
?>


<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" data-localize="dashboard"></h1>

</div>

<!-- Content Row -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><a class="text-xs font-weight-bold text-success text-uppercase mb-1" href="rooms.php" data-localize="rooms"></a></div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="roomsCount"></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-video fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="row no-gutters align-items-center">
                    <div class="col mr-1">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><a class="text-xs font-weight-bold text-success text-uppercase mb-1" href="room.php" data-localize="room_management"></a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><a class="text-xs font-weight-bold text-info text-uppercase mb-1" href="agents.php" data-localize="agents"></a></div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="agentsCount"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><a class="text-xs font-weight-bold text-info text-uppercase mb-1" href="agent.php" data-localize="add_agent"></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><a class="text-xs font-weight-bold text-warning text-uppercase mb-1" href="users.php" data-localize="users"></a></div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="usersCount"></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><a class="text-xs font-weight-bold text-warning text-uppercase mb-1" href="user.php" data-localize="add_user"></a></div>
                    </div>
                </div>
            </div>
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
                if (@$_SESSION["agent"]['subscription']) {
                    $infospan = 'secondary';
                    $message = '<span data-localize="subscribed_till"></span>' . date('F j, Y G:i', strtotime($_SESSION["agent"]['subscription']));
                } else {
                    $infospan = 'danger';
                    $message = '<span data-localize="need_subscribe"></span>';
                }
    ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?php echo $infospan;?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-<?php echo $infospan;?> text-uppercase mb-1"><a class="text-xs font-weight-bold text-<?php echo $infospan;?> text-uppercase mb-1" href="history.php" data-localize="subscription"></a></div>
                            <div class="h7 mb-0 text-gray-800"><?php echo $message;?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-<?php echo $infospan;?> text-uppercase mb-1"><a class="text-xs font-weight-bold text-<?php echo $infospan;?> text-uppercase mb-1" href="history.php" data-localize="payment_history"></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php }
    } ?>
</div>
<!-- Content Row -->

<div class="row">
    <div class="col-xl col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary" data-localize="news_livesmart"></h6>

            </div>
            <div class="card-body">
                <?php
                $versionFile = fopen('../pages/version.txt', 'r') or die("Unable to open file!");
                $currentVersion = fread($versionFile, filesize('../pages/version.txt'));
                echo '<span data-localize="version"></span>: ' . $currentVersion;
                echo '<br/>';
                echo '<br/>';
                $curNumber = explode('.', $currentVersion);
                fclose($versionFile);
                ?>
                <span id="remoteVersion"></span>
            </div>
        </div>
    </div>

</div>


<?php
include_once 'footer.php';
?>