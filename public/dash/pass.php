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
                                        <h4>Password recovery</h4>
                                        <div class="text-center">
                                            <div id="error" style="display:none;" class="alert alert-danger"></div><br/>
                                        </div>
                                        <div class="text-center">
                                            <div id="message" style="display:none;" class="alert alert-success"></div><br/>
                                        </div>
                                        <form class="user" method="post">
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="username" aria-describedby="username" placeholder="Username">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" class="form-control" id="email" aria-describedby="email" placeholder="Email">
                                            </div>
                                            <a href="javascript:void(0);" id="recoverbutton" class="btn btn-primary btn-user btn-block">
                                                Recover
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
                if (!$('#email').val() || !$('#username').val()) {
                    $("#message").hide();
                    $("#error").show();
                    $("#error").html("Email and username are mandatory fields");
                    return false;
                }
                var dataObj = {'type': 'recoverpassword', 'email': $('#email').val(), 'username': $('#username').val()};
                $.ajax({
                    url: "../server/script.php",
                    type: "POST",
                    data: dataObj,
                    success: function (data) {
                        if (data) {
                            $("#message").show();
                            $("#error").hide();
                            $("#message").html("We have sent instructions to your email how to reset your account");
                        } else {
                            $("#message").hide();
                            $("#error").show();
                            $("#error").html("Invalid Data");
                        }
                    },
                    error: function (e) {
                        $("#message").hide();
                        $("#error").show();
                        $("#error").html("Invalid Data");                    }
                });
            });
        </script>
    </body>

</html>

