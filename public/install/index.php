<?php
$success = '';
$divErrors = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serverFolder = realpath(dirname(dirname(__FILE__)));
    $publicFolder = basename(dirname(dirname(__FILE__)));
    $installFolderPath = realpath(dirname(dirname(dirname(__FILE__))));

    $filenameCert = $_POST['filenameCert'];
    $filenameKey = $_POST['filenameKey'];
    $secretApiKey = $_POST['apisecret'];

    $servername = 'localhost';
    $database = $_POST['database'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $charset = 'utf8mb4';
    $dbPrefix = '';
    $errors = array();

    $output = shell_exec('node -v');
    $curNumber = str_replace('v', '', $output);
    $curNumber = explode('.', $curNumber);
    if (!isset($curNumber[0]) || $curNumber[0] < 16) {
        array_push($errors, 'Node version not suitable or Node is missing. You need to install it.<br/>');
    }

    if (!is_dir($serverFolder) && !is_dir($serverFolder . '/config')) {
        array_push($errors, 'Server folder is not correct. Make sure the folder exists and your Server files are there.<br/>');
    }

    $compare = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $explodeUrl = explode('/install', $compare);

    $serverURL = $explodeUrl[0];


//    if (!file_exists($filenameCert)) {
//        array_push($errors, 'Certificate file you provided is missing. Please make sure file location is properly provided.<br/>');
//    }
//
//    if (!file_exists($filenameKey)) {
//        array_push($errors, 'Key file you provided is missing. Please make sure file location is properly provided.<br/>');
//    }

    $dsn = "mysql:host=$servername;dbname=$database;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (\PDOException $e) {
        if ($e->getCode() == 1045) {
            array_push($errors, 'Provided MySQL credentials are not proper.<br/>');
        } else if ($e->getCode() == 1049) {
            array_push($errors, 'Provided MySQL database is not existing.<br/>');
        } else if ($e->getCode() == 2002) {
            array_push($errors, 'Cannot connect to the host. Make sure your DB can access remote connections.<br/>');
        } else {
            array_push($errors, $e->getMessage() . '<br/>');
        }
    }

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $divErrors .= $error;
        }
    } else {

        $serverURL = str_replace('www.', '', $serverURL);
        $stripUrlArr = explode('/', str_replace('https://', '', $serverURL));
        $stripUrl = $stripUrlArr[0];
        $serverIP = gethostbyname($stripUrl);

        $sql = file_get_contents('../server/dump.sql');
        $sql .= file_get_contents('../server/dump_1.0.2.sql');
        $sql .= file_get_contents('../server/dump_1.0.3.sql');
        $sql .= file_get_contents('../server/dump_1.0.4.sql');
        $sql .= file_get_contents('../server/dump_1.0.11.sql');
        $sql .= file_get_contents('../server/dump_1.0.12.sql');
        $pdo->exec($sql);

        //server/connect.php file
        $phpContent = file_get_contents($serverFolder . '/server/connect.php');
        $str = str_replace('$database = \'\';', '$database = \'' . $database . '\';', $phpContent);
        $str = str_replace('$username = \'\';', '$username = \'' . $username . '\';', $str);
        $str = str_replace('$password = \'\';', '$password = \'' . $password . '\';', $str);
        $str = str_replace('$apiKey = \'\';', '$apiKey = \'' . $secretApiKey . '\';', $str);
        if (file_put_contents($serverFolder . '/server/connect.php', $str) == false) {
            array_push($errors, 'Error writing to connect.php file. Make sure files in Server folder are not with root ownershi.<br/>');
        }

        if (count($errors) == 0) {
            $success .= 'Database setup successfully finished!<br/>';
        }

        $phpContent = file_get_contents($installFolderPath . '/src/config.js');
        $str = str_replace("/SSL_CERT_PATH", $filenameCert, $phpContent);
        $str = str_replace("/SSL_KEY_PATH", $filenameKey, $str);
        $str = str_replace("API_KEY_SECRET", $secretApiKey, $str);
        $str = str_replace("'public'", "'".$publicFolder."'", $str);
        $str = str_replace("announcedIp: getLocalIp()", "announcedIp: '".$_SERVER['SERVER_ADDR']."'", $str);
        if (file_put_contents($installFolderPath . '/src/config.js', $str) == false) {
            array_push($errors, 'Error writing to src/config.js file. Make sure files in this folder are not with root ownership.<br/>');
        }
        copy('script_ubuntu.sh', $installFolderPath.'/script_ubuntu.sh');

        $phpContent = file_get_contents($serverFolder . '/client.html');
        $str = str_replace('YOUR_DOMAIN', $stripUrl, $phpContent);
        if (file_put_contents($serverFolder . '/client.html', $str) == false) {
            array_push($errors, 'Error writing to sample HTML client.html file. Make sure files in LiveSmart folder are not with root ownership.<br/>');
        }
        if (count($errors) == 0) {
            $success .= 'Configuration file successfully generated!<br/>';
        }

        if (count($errors) == 0) {
            $success .= 'Now you have to login to your console/terminal with root user, go to main folder at ' . $installFolderPath . ' and run the installation script from there <code>sudo bash script_ubuntu.sh</code><br/>';
            $success .= 'Make sure your port 9002 is opened for TCP and port ranges 40000-40100 are opened for TCP and UDP traffic.<br/>';
            $success .= 'Then add the following code to your web server config file:<br/>
                        If you are on Apache, add this for 443 virtual host: <br/>
                        <pre>
                        ProxyRequests off
                        SSLProxyEngine on
                        SSLProxyVerify none
                        SSLProxyCheckPeerCN off
                        SSLProxyCheckPeerName off
                        SSLProxyCheckPeerExpire off
                        ProxyPreserveHost On
                        &lt;Location "/"&gt;
                            Order allow,deny
                            Allow from all
                            ProxyPass https://localhost:9002/
                            ProxyPassReverse https://localhost:9002/
                        &lt;/Location&gt;
                        &lt;Location "/dash"&gt;
                            ProxyPass "!"
                            Order allow,deny
                            Allow from all
                        &lt;/Location&gt;
                        &lt;Location "/server"&gt;
                            ProxyPass "!"
                            Order allow,deny
                            Allow from all
                        &lt;/Location&gt;
                        </pre>
                        and this if you are on Nginx: 
                        <pre>
                        location / {
                            proxy_set_header X-Real-IP $remote_addr;
                            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                            proxy_set_header X-NginX-Proxy true;
                            proxy_pass https://localhost:9002/;
                            proxy_ssl_session_reuse off;
                            proxy_set_header Host $http_host;
                            proxy_cache_bypass $http_upgrade;
                            proxy_redirect off;
                        }
                        location /dash {
                        }
                        location /server {    
                        }</pre> and then restart web server.<br/>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Installation wizard</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <style>
            .bs-example{
                padding-top: 5px;
                margin: auto;
                width: 900px;
            }
        </style>
    </head>
    <body>
        <div class="bs-example">
            <p>
                Welcome to installation wizard! This script applies for Ubuntu/Debian operating systems. In order to fully install this product on your server, you need:<br/>
                - some basic knowledge of Linux administration;<br/>
                - root access;<br/>
                - SSL certificate and key;<br/>
                - MySQL database;<br/>
                When you click on Setup button, the config file will be generated and you will get instructions on how to finalize your installation.
            </p>
            <hr>
            <?php if ($success) { ?>
                <div id="success" class="alert alert-success col-sm-10">
                    <?php echo $success; ?>
                </div>
            <?php } ?>
            <?php if ($divErrors) { ?>
                <div id="errors" class="alert alert-danger col-sm-10">
                    <?php echo $divErrors; ?>
                </div>
            <?php } ?>
            <form method="post">
                <div class="form-group row">
                    <label for="filenameCert" class="col-sm-3 col-form-label">Certificate path</label>

                    <div class="col-sm-8">
                        <input type="input" class="form-control" name="filenameCert" id="filenameCert" value="<?php echo @$_POST['filenameCert'] ?>" placeholder="Certificate absolute path" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="filenameKey" class="col-sm-3 col-form-label">Key path</label>

                    <div class="col-sm-8">
                        <input type="input" class="form-control" name="filenameKey" id="filenameKey" value="<?php echo @$_POST['filenameKey'] ?>" placeholder="Key absolute path" required>
                    </div>
                </div>
                <div class="form-group row">
                    <small>Absolute paths where your certificate and key are. <br/>
                        Contact your host support if you do not know these locations.
                    </small>
                </div>
                <div class="form-group row">
                    <label for="database" class="col-sm-3 col-form-label">Database</label>
                    <div class="col-sm-8">
                        <input type="input" class="form-control" name="database" id="database" placeholder="Database name" value="<?php echo @$_POST['database'] ?>" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="username" class="col-sm-3 col-form-label">Database Username</label>
                    <div class="col-sm-8">
                        <input type="input" class="form-control" name="username" id="username" placeholder="Database username" value="<?php echo @$_POST['username'] ?>" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="password" class="col-sm-3 col-form-label">Database Password</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Database password" required>
                    </div>
                </div>
                <div class="form-group row">
                    <small>You need to provide here your database information, where you need your instance to be installed.
                    </small>
                </div>
                <div class="form-group row">
                    <label for="apisecret" class="col-sm-3 col-form-label">API secret key</label>
                    <div class="col-sm-8">
                        <input type="input" class="form-control" name="apisecret" id="apisecret" placeholder="API Secret Key" value="<?php echo @$_POST['apisecret'] ?>" required>
                    </div>
                </div>
                <div class="form-group row">
                    <small>API secret key. You can generate random string from <a href="https://random.org/strings" target="_blank">here</a>
                    </small>
                </div>
                <div class="form-group row">
                    <div class="col-sm-10 offset-sm-2">
                        <button type="submit" class="btn btn-primary">Setup</button>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>