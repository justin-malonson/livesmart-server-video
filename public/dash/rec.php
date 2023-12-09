<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>LiveSmart Agent Dashboard</title>

        <!-- Custom fonts for this template-->
        <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

        <!-- Custom styles for this template-->
        <link href="css/sb-admin-2.min.css" rel="stylesheet">

    </head>

    <body>


        <?php
        if (!isset($_GET['code']) || !$_GET['code']) {
            $html = false;
        } else {
            include_once '../server/connect.php';
            global $dbPrefix, $pdo;
            $token = $_GET['code'];
            $array = [md5($token), date('Y-m-d H:i:s')];
            $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "agents WHERE `recovery_token`= ? and `date_expired` > ?");
            $stmt->execute($array);
            $user = $stmt->fetch();

            if ($user) {
                $html = true;
            } else {
                $html = false;
            }
        }

        if (!$html) {
            ?>
            <div class="container">
                <!-- Outer Row -->
                <div class="row justify-content-center">

                    <div class="col-xl-10 col-lg-12 col-md-9">

                        <div class="card o-hidden border-0 shadow-lg my-5">
                            <div class="card-body p-0">
                                <!-- Nested Row within Card Body -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="p-5">
                                            The provided code is not valid or has expired!
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
            <?php
        } else {
            ?>
            <div class="container">
                <!-- Outer Row -->
                <div class="row justify-content-center">

                    <div class="col-xl-10 col-lg-12 col-md-9">

                        <div class="card o-hidden border-0 shadow-lg my-5">
                            <div class="card-body p-0">
                                <!-- Nested Row within Card Body -->
                                <div class="row">
                                    <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                                    <div class="col-lg-6">
                                        <div class="p-5">
                                            <h4>Fill in your new password</h4>
                                            <div class="text-center">
                                                <div id="error" style="display:none;" class="alert alert-danger"></div><br/>
                                            </div>
                                            <div class="text-center">
                                                <div id="message" style="display:none;" class="alert alert-success"></div><br/>
                                            </div>
                                            <form class="user" method="post">
                                                <div class="form-group">
                                                    <input type="password" class="form-control" id="password" aria-describedby="password" placeholder="Password">
                                                </div>
                                                <div class="form-group">
                                                    <input type="password" class="form-control" id="password_again" aria-describedby="password" placeholder="Password again">
                                                    <input type="hidden" id="token" value="<?php echo $token; ?>">
                                                </div>
                                                <a href="javascript:void(0);" id="recoverbutton" class="btn btn-primary btn-user btn-block">
                                                    Change
                                                </a>
                                                <hr>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- Bootstrap core JavaScript-->
            <script src="vendor/jquery/jquery.min.js"></script>
            <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

            <!-- Core plugin JavaScript-->
            <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

            <!-- Custom scripts for all pages-->
            <script src="js/sb-admin-2.min.js"></script>
            <script>
                $('#recoverbutton').click(function (event) {
                    event.preventDefault();
                    $("#error").hide();
                    $("#message").hide();
                    if (!$('#password').val() || !$('#password_again').val()) {
                        $("#message").hide();
                        $("#error").show();
                        $("#error").html("Passwords are mandatory fields");
                        return false;
                    }
                    if ($('#password').val() !== $('#password_again').val()) {
                        $("#message").hide();
                        $("#error").show();
                        $("#error").html("Passwords do not match");
                        return false;
                    }
                    var dataObj = {'type': 'resetpassword', 'token': $('#token').val(), 'password': $('#password').val()};
                    $.ajax({
                        url: "../server/script.php",
                        type: "POST",
                        data: dataObj,
                        success: function (data) {
                            if (data) {
                                $("#message").show();
                                $("#error").hide();
                                $("#message").html("Your password was successfully changed. Please go to <a href='loginform.php'>login page</a> to authorize.");
                            } else {
                                $("#message").hide();
                                $("#error").show();
                                $("#error").html("Invalid Data");
                            }
                        },
                        error: function (e) {
                            $("#message").hide();
                            $("#error").show();
                            $("#error").html("Invalid Data");
                        }
                    });
                });
            </script>
        <?php } ?>
    </body>

</html>
