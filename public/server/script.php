<?php

/**
 * REST API for managing agents, users, rooms and chats in LiveSmart Server Video
 *
 * @author  LiveSmart <contact@livesmart.video>
 *
 * @since 1.0
 *
 */
session_start();
include_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(405);
}

function checkHeaders() {
    global $apiSecret, $apiHashMethod;
    $pathInfo = pathinfo($_SERVER['HTTP_REFERER']);
    if (isset($pathInfo) && $pathInfo['basename'] && strpos($pathInfo['basename'], 'integration.php') !== false) {
        return true;
    }
    if ($apiSecret && $apiHashMethod && (!isset($_SESSION["username"]) || !$_SESSION["username"]) ) {
        if (isset(getallheaders()['Authorization'])) {
            $hmac = hash_hmac($apiHashMethod, json_encode($_POST, JSON_UNESCAPED_UNICODE), $apiSecret);
            if ($hmac === getallheaders()['Authorization']) {
                return true;
            }
        }
        http_response_code(401);
        exit;
    }
    return true;
}

function guid() {
    return bin2hex(openssl_random_pseudo_bytes(20));
}

function secondsToTime($inputSeconds) {
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;

    // Extract days
    $days = floor($inputSeconds / $secondsInADay);

    // Extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // Extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // Extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // Format and return
    $timeParts = [];
    $sections = [
        'day' => (int) $days,
        'hour' => (int) $hours,
        'minute' => (int) $minutes,
        'second' => (int) $seconds,
    ];

    foreach ($sections as $name => $value) {
        if ($value > 0) {
            $nm = $name . ($value == 1 ? '' : 's');
            $timeParts[] = $value . ' <span data-localize="' . $nm . '"></span> ';
        }
    }

    return implode(', ', $timeParts);
}

/**
 * Method to check login of an user. Returns 200 code for successful login.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param  String $username
 * @param String $pass
 * @return boolean|int
 */
function checkLogin($username, $pass) {
    global $dbPrefix, $pdo;
    try {
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'users WHERE username = ? AND password = ? AND is_blocked = 0');
        $stmt->execute([$username, md5($pass)]);
        $user = $stmt->fetch();

        if ($user) {
            return 204;
        } else {
            return 403;
        }
    } catch (Exception $e) {
        return 500;
    }
}

/**
 * Method to check the token in a broadcasting session.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $token
 * @param type $roomId
 * @param type $isAdmin
 * @return boolean
 */
function checkLoginToken($token, $roomId, $isAdmin = false) {
    global $dbPrefix, $pdo;
    try {
        if ($isAdmin) {
            $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents WHERE token = ? AND roomId = ?');
            $stmt->execute([$token, $roomId]);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'users WHERE token = ? AND roomId = ? AND is_blocked = 0');
            $stmt->execute([$token, $roomId]);
        }

        $user = $stmt->fetch();

        if ($user) {
            return json_encode($user);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Method to add a room. 
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $agent
 * @param type $visitor
 * @param String $agenturl
 * @param String $visitorurl
 * @param String $pass
 * @param String $session
 * @param String $datetime
 * @param Int $duration
 * @param String $shortagenturl
 * @param String $shortvisitorurl
 * @param String $agentId
 * @param String $agenturl_broadcast
 * @param String $visitorurl_broadcast
 * @param String $shortagenturl_broadcast
 * @param String $shortvisitorurl_broadcast
 * @param Bool $is_active
 * @return string|int
 */
function insertScheduling($agent, $visitor, $agenturl, $visitorurl, $pass, $session, $datetime, $duration, $shortagenturl, $shortvisitorurl, $agentId = null, $agenturl_broadcast = null, $visitorurl_broadcast = null, $shortagenturl_broadcast = null, $shortvisitorurl_broadcast = null, $is_active = true) {
    global $dbPrefix, $pdo;
    checkHeaders();
    $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'rooms WHERE roomId = ? or shortagenturl = ? or shortvisitorurl = ?');
    $stmt->execute([$session, $shortagenturl, $shortvisitorurl]);
    $userName = $stmt->fetch();
    if ($userName) {
        return false;
    }
    $is_active = ($is_active == 'true') ? 1 : 0;

    try {
        $sql = "INSERT INTO " . $dbPrefix . "rooms (agent, visitor, agenturl, visitorurl, password, roomId, datetime, duration, shortagenturl, shortvisitorurl, agent_id, agenturl_broadcast, visitorurl_broadcast, shortagenturl_broadcast, shortvisitorurl_broadcast, is_active) "
                . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$agent, $visitor, $agenturl, $visitorurl, md5($pass), $session, $datetime, $duration, $shortagenturl, $shortvisitorurl, $agentId, $agenturl_broadcast, $visitorurl_broadcast, $shortagenturl_broadcast, $shortvisitorurl_broadcast, (int) $is_active]);
        return 200;
    } catch (Exception $e) {
        return 'Error';
    }
}

/**
 * Add a room and generate URLs from PHP directly. 
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $lsRepUrl
 * @param String $agentId
 * @param String $roomId
 * @param String $agentName
 * @param String $visitorName
 * @param String $agentShortUrl
 * @param String $visitorShortUrl
 * @param String $password
 * @param String $config
 * @param String $dateTime
 * @param String $duration
 * @param Bool $disableVideo
 * @param Bool $disableAudio
 * @param Bool $disableScreenShare
 * @param Bool $disableWhiteboard
 * @param Bool $disableTransfer
 * @param Bool $is_active
 * @return boolean|string|int
 */
function addRoom($lsRepUrl, $agentId = null, $roomId = null, $agentName = null, $visitorName = null, $agentShortUrl = null, $visitorShortUrl = null, $password = null, $config = 'config.json', $dateTime = null, $duration = null, $disableVideo = false, $disableAudio = false, $disableScreenShare = false, $disableWhiteboard = false, $disableTransfer = false, $is_active = true) {
    global $dbPrefix, $pdo;
    
    $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'rooms WHERE roomId = ? or shortagenturl = ? or shortvisitorurl = ?');
    $stmt->execute([$roomId, $agentShortUrl, $visitorShortUrl]);
    $userName = $stmt->fetch();
    if ($userName) {
        return false;
    }
    $is_active = ($is_active == 'true') ? 1 : 0;

    try {

        function generateRand($length) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            return $randomString;
        }

        $roomId = ($roomId) ? $roomId : generateRand(10);
        $str = [];

        if ($agentName) {
            $str['agentName'] = $agentName;
        }
        if ($visitorName) {
            $str['visitorName'] = $visitorName;
        }
        if ($config) {
            $str['config'] = $config;
        }
        if ($agentId) {
            $str['agentId'] = $agentId;
        }
        if ($agentId) {
            $str['agentId'] = $agentId;
        }

        if ($dateTime) {
            $str['datetime'] = $dateTime;
        }
        if ($duration) {
            $str['duration'] = $duration;
        }

        $encodedString = base64_encode(json_encode($str));
        $visitorUrl = $lsRepUrl . '/' . $roomId . '?p=' . $encodedString;

        if ($password) {
            $str['pass'] = $password;
        }
        if (isset($str['vistorName'])) {
            unset($str['vistorName']);
        }
        $str['admin'] = 1;
        $encodedString = base64_encode(json_encode($str));
        $agentUrl = $lsRepUrl . '/' . $roomId . '?p=' . $encodedString;

        $sql = "INSERT INTO " . $dbPrefix . "rooms (agent, visitor, agenturl, visitorurl, password, roomId, datetime, duration, shortagenturl, shortvisitorurl, agent_id, is_active) "
                . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$agentName, $visitorName, $agentUrl, $visitorUrl, md5($password), $roomId, $dateTime, $duration, $agentShortUrl, $visitorShortUrl, $agentId, (int) $is_active]);
        $id = $pdo->lastInsertId();
        return $id;
    } catch (Exception $e) {
        return 'Error';
    }
}

/**
 * Method to edit a room.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $roomId
 * @param String $agent
 * @param String $visitor
 * @param String $agenturl
 * @param String $visitorurl
 * @param String $pass
 * @param String $session
 * @param String $datetime
 * @param String $duration
 * @param String $shortagenturl
 * @param String $shortvisitorurl
 * @param String $agentId
 * @param String $agenturl_broadcast
 * @param String $visitorurl_broadcast
 * @param String $shortagenturl_broadcast
 * @param String $shortvisitorurl_broadcast
 * @param Bool $is_active
 * @return int
 */
function editRoom($roomId, $agent, $visitor, $agenturl, $visitorurl, $pass, $session, $datetime, $duration, $shortagenturl, $shortvisitorurl, $agentId = null, $agenturl_broadcast = null, $visitorurl_broadcast = null, $shortagenturl_broadcast = null, $shortvisitorurl_broadcast = null, $is_active = 1) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $is_active = ($is_active == 'true') ? 1 : 0;
        $sql = "UPDATE " . $dbPrefix . "rooms set agent=?, visitor=?, agenturl=?, visitorurl=?, password=?, roomId=?, datetime=?, duration=?, shortagenturl=?, shortvisitorurl=?, agent_id=?, agenturl_broadcast=?, visitorurl_broadcast=?, shortagenturl_broadcast=?, shortvisitorurl_broadcast=?, is_active=?"
                . " WHERE room_id = ?;";
        $pdo->prepare($sql)->execute([$agent, $visitor, $agenturl, $visitorurl, md5($pass), $session, $datetime, $duration, $shortagenturl, $shortvisitorurl, $agentId, $agenturl_broadcast, $visitorurl_broadcast, $shortagenturl_broadcast, $shortvisitorurl_broadcast, (int) $is_active, $roomId]);
        return 200;
    } catch (Exception $e) {
        return 'Error ' . $e->getMessage();
    }
}

/**
 * Update room state
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $roomId
 * @param type $is_active
 * @return int
 */
function updateRoomState($roomId, $is_active) {
    checkHeaders();
    global $dbPrefix, $pdo;
    try {
        $is_active = ($is_active == 'true') ? 1 : 0;
        $sql = "UPDATE " . $dbPrefix . "rooms set is_active=?"
                . " WHERE room_id = ?;";
        $pdo->prepare($sql)->execute([(int) $is_active, $roomId]);
        return 200;
    } catch (Exception $e) {
        return 'Error ' . $e->getMessage();
    }
}

/**
 * Returns all information about agent by tenant
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $tenant
 * @return boolean
 */
function getAgent($tenant) {

    global $dbPrefix, $pdo;
    
    try {
        $array = [$tenant];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "agents WHERE `tenant`= ?");
        $stmt->execute($array);
        $user = $stmt->fetch();

        if ($user) {
            return json_encode($user);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Reset agent password
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $email
 * @param type $username
 * @return agent object
 */
function recoverPassword($email, $username) {

    global $dbPrefix, $pdo, $fromEmail;
    try {
        $array = [$email, $username];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "agents WHERE `email`= ? and `username` = ?");
        $stmt->execute($array);
        $user = $stmt->fetch();

        if ($user) {
            $to = $user['email'];
            $authToken = guid();
            $expired = date("Y-m-d H:i:s", strtotime("+30 minutes"));

            $compare = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $explodeUrl = explode('/server', $compare);
            $serverURL = $explodeUrl[0];
            $array = [$expired, md5($authToken), $email, $username];
            $sql = 'UPDATE ' . $dbPrefix . 'agents SET date_expired=?, recovery_token=? WHERE email = ? and username = ?';
            $pdo->prepare($sql)->execute($array);

            $subject = 'Password Recovery from LiveSmart';
            $recoveryUrl = $serverURL . '/dash/rec.php?code=' . $authToken;
            $message = 'Hello<br/><br/>In order to change your password, please follow this <a href="' . $recoveryUrl . '">link</a> or copy and paste it into your browser address bar: <br/> ' . $recoveryUrl . '<br/><br/>Sincerely, <br/>LiveSmart Team.';

            $header = "From: LiveSmart Team <" . $fromEmail . "> \r\n";
            $header .= "Reply-To: " . $fromEmail . " \r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-type: text/html\r\n";
            $retval = mail($to, $subject, $message, $header);

            if ($retval == true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Change agent password
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $token
 * @param String $password
 * @return agent object
 */
function resetPassword($token, $password) {

    global $dbPrefix, $pdo, $fromEmail;
    
    try {
        $array = [md5($token)];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "agents WHERE `recovery_token`= ?");
        $stmt->execute($array);
        $user = $stmt->fetch();

        if ($user) {

            $array = [md5($password), md5($token)];

            $sql = 'UPDATE ' . $dbPrefix . 'agents SET password=? WHERE recovery_token=? ';
            $pdo->prepare($sql)->execute($array);
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns agent info by agent_id.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param Int $id
 * @return boolean|type
 */
function getAdmin($id) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $array = [$id];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "agents WHERE `agent_id`= ?");
        $stmt->execute($array);
        $user = $stmt->fetch();

        if ($user) {
            return json_encode($user);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}


/**
 * Returns room information by short URL
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param string $shortUrl
 * @return boolean|type
 */
function getRoomByShort($shortUrl) {

    global $dbPrefix, $pdo;
    $shortUrl = str_replace('/', '', $shortUrl);
    try {
        $array = [$shortUrl];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "rooms WHERE `shortagenturl`= ? or `shortvisitorurl`= ? or `shortagenturl_broadcast`= ? or `shortvisitorurl_broadcast`= ?");
        $stmt->execute([$shortUrl, $shortUrl, $shortUrl, $shortUrl]);
        $row = $stmt->fetch();
        if ($row) {
            if ($row['shortagenturl'] == $shortUrl) {
                return $row['agenturl'];
            }
            if ($row['shortvisitorurl'] == $shortUrl) {
                return $row['visitorurl'];
            }
            if ($row['shortagenturl_broadcast'] == $shortUrl) {
                return $row['agenturl_broadcast'];
            }
            if ($row['shortvisitorurl_broadcast'] == $shortUrl) {
                return $row['visitorurl_broadcast'];
            }
        }
        return '300';
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns room information by room identifier
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $roomId
 * @return boolean|type
 */
function getRoom($roomId) {

    global $dbPrefix, $pdo;
    
    try {
        $array = [$roomId];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "rooms WHERE `roomId`= ? AND `is_active` = 1");
        $stmt->execute($array);
        $room = $stmt->fetch();
        if ($room) {
            return json_encode($room);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns room information by room_id
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $roomId
 * @return boolean|type
 */
function getRoomById($roomId) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $array = [$roomId];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "rooms WHERE `room_id`= ?");
        $stmt->execute($array);
        $room = $stmt->fetch();
        if ($room) {
            return json_encode($room);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns all rooms by agent_id
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $agentId
 * @return boolean|type
 */
function getRooms($draw, $agentId = false, $start = 0, $offset = 10, $order = false, $search = false) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $additional = '';
        $array = [];
        if ($agentId && $agentId != 'false') {
            $additional = ' WHERE agent_id = ? ';
            $array = [$agentId];
        }
        
        if ($search && $search['value']) {
            if (!$additional) {
                $additional = ' WHERE';
            }
            $additional .= ' agent like "%' . $search['value'] . '%" OR shortvisitorurl like "%' . $search['value'] . '%" OR datetime  like "%' . $search['value'] . '%" OR roomId  like "%' . $search['value'] . '%" OR shortagenturl  like "%' . $search['value'] . '%" OR title like "%' . $search['value'] . '%"';
        }
        $start = $start ?? 0;
        $offset = $offset ?? 10;
        $orderBy = array('room_id', 'agent', 'visitor', 'shortagenturl', 'shortvisitorurl', 'datetime', 'is_active');
        $orderBySql = '';
        if (isset($order[0]['column'])) {
            $orderBySql = ' order by ' . $orderBy[$order[0]['column']] . ' ' . $order[0]['dir'] . ' ';
        }

        $total = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'rooms' . $additional);
        $total->execute($array);
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'rooms ' . $additional . $orderBySql . ' LIMIT ' . $start . ',' . $offset);
        $stmt->execute($array);
        if ($draw) {
            $data['draw'] = $draw;
        }
        $data['recordsTotal'] = $total->rowCount();
        $data['recordsFiltered'] = $total->rowCount();

        $rows = array();
        while ($r = $stmt->fetch()) {
            $rows[] = $r;
        }
        $data['data'] = $rows;
        return json_encode($data);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Deletes a room by room_id and agent_id
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $roomId
 * @param String $agentId
 * @return boolean
 */
function deleteRoom($roomId, $agentId = false) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $additional = '';
        $array = [$roomId];
        if ($agentId && $agentId != 'false') {
            $additional = ' AND agent_id = ?';
            $array = [$roomId, $agentId];
        }
        $sql = 'DELETE FROM ' . $dbPrefix . 'rooms WHERE room_id = ?' . $additional;
        $pdo->prepare($sql)->execute($array);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Deletes rooms by agent ID
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $roomId
 * @param type $agentId
 * @return boolean
 */
function deleteRoomByAgent($agentId) {
    global $dbPrefix, $pdo;
    
    try {
        $additional = '';
        $array = [$agentId];
        $sql = 'DELETE FROM ' . $dbPrefix . 'rooms WHERE agent_id = ?';
        $pdo->prepare($sql)->execute($array);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns all agents
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @return boolean|type
 */
function getAgents($draw, $start = 0, $offset = 10, $order = false, $search = false) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {

        $array = [];
        $additional = '';
        if ($_SESSION['tenant'] != 'lsv_mastertenant') {
            $additional = ' WHERE tenant = "'.$_SESSION['tenant'].'"';
        }
        if ($search && $search['value']) {
            if (!$additional) {
                $additional = ' WHERE';
            }
            $additional .= ' first_name like "%' . $search['value'] . '%" OR last_name like "%' . $search['value'] . '%" OR username like "%' . $search['value'] . '%" OR tenant like "%' . $search['value'] . '%" OR email like "%' . $search['value'] . '%"';
        }
        $start = $start ?? 0;
        $offset = $offset ?? 10;
        $orderBy = array('username', 'first_name', 'tenant', 'email');
        $orderBySql = '';
        if (isset($order[0]['column'])) {
            $orderBySql = ' order by ' . $orderBy[$order[0]['column']] . ' ' . $order[0]['dir'] . ' ';
        }
        
        $total = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents' . $additional);
        $total->execute($array);
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents' . $additional . $orderBySql . ' LIMIT ' . $start . ',' . $offset);
        $stmt->execute($array);
        if ($draw) {
            $data['draw'] = $draw;
        }
        $data['recordsTotal'] = $total->rowCount();
        $data['recordsFiltered'] = $total->rowCount();
        
        $rows = array();
        while ($r = $stmt->fetch()) {
            $rows[] = $r;
        }
        $data['data'] = $rows;
        return json_encode($data);
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Deletes an agent
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $agentId
 * @return boolean
 */
function deleteAgent($agentId) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {

        $sql = 'DELETE FROM ' . $dbPrefix . 'agents WHERE agent_id = ?';
        $pdo->prepare($sql)->execute([$agentId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Deletes an agent by username
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $username
 * @return boolean
 */
function deleteAgentByUsername($username) {
    global $dbPrefix, $pdo;
    
    try {

        $sql = 'DELETE FROM ' . $dbPrefix . 'agents WHERE username = ?';
        $pdo->prepare($sql)->execute([$username]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Updates an agent
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $agentId
 * @param String $firstName
 * @param String $lastName
 * @param String $email
 * @param String $tenant
 * @param String $pass
 * @param Bool $is_master
 * @param type $usernamehidden
 * @return boolean
 */
function editAgent($agentId, $firstName, $lastName, $email, $tenant, $pass = null, $usernamehidden = null, $is_master = false) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $is_master = ($is_master == 'true') ? 1 : 0;
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents WHERE email = ? and agent_id <> ?');
        $stmt->execute([$email, $agentId]);
        $userName = $stmt->fetch();
        if ($userName) {
            return false;
        }

        $array = [$firstName, $lastName, $email, $tenant, $is_master, $agentId];
        $additional = '';
        if ($pass) {
            $additional = ', password = ?';
            $array = [$firstName, $lastName, $email, $tenant, $is_master, md5($pass), $agentId];
        }

        $sql = 'UPDATE ' . $dbPrefix . 'agents SET first_name=?, last_name=?, email=?, tenant=?, is_master=? ' . $additional . ' WHERE agent_id = ?';

        if ($_SESSION["username"] == $usernamehidden) {
            $_SESSION["agent"] = array('agent_id' => $agentId, 'first_name' => $firstName, 'last_name' => $lastName, 'tenant' => $tenant, 'email' => $email);
        }

        $pdo->prepare($sql)->execute($array);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Updates an admin agent.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $agentId
 * @param String $firstName
 * @param String $lastName
 * @param String $email
 * @param String $tenant
 * @param String $pass
 * @return boolean
 */
function editAdmin($agentId, $firstName, $lastName, $email, $tenant, $pass = null) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {

        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents WHERE email = ? and agent_id <> ?');
        $stmt->execute([$email, $agentId]);
        $userName = $stmt->fetch();
        if ($userName) {
            return false;
        }

        $array = [$firstName, $lastName, $email, $tenant, $agentId];
        $additional = '';
        if ($pass) {
            $additional = ', password = ?';
            $array = [$firstName, $lastName, $email, $tenant, md5($pass), $agentId];
        }

        $sql = 'UPDATE ' . $dbPrefix . 'agents SET first_name=?, last_name=?, email=?, tenant=? ' . $additional . ' WHERE agent_id = ?';
        $_SESSION["agent"] = array('agent_id' => $agentId, 'first_name' => $firstName, 'last_name' => $lastName, 'tenant' => $tenant, 'email' => $email);
        $pdo->prepare($sql)->execute($array);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Adds an agent.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $user
 * @param String $pass
 * @param String $firstName
 * @param String $lastName
 * @param String $email
 * @param String $tenant
 * @param Bool $is_master
 * @return boolean
 */
function addAgent($user, $pass, $firstName, $lastName, $email, $tenant, $is_master = false) {
    global $dbPrefix, $pdo;
    try {
        $is_master = ($is_master == 'true') ? 1 : 0;
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents WHERE username = ? or email = ?');
        $stmt->execute([$user, $email]);
        $userName = $stmt->fetch();
        if ($userName) {
            return false;
        }

        $sql = 'INSERT INTO ' . $dbPrefix . 'agents (username, password, first_name, last_name, email, tenant, is_master) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$user, md5($pass), $firstName, $lastName, $email, $tenant, $is_master]);
        $fileAgantConfig = '../config/' . $tenant . '.json';
        if (!file_exists($fileAgantConfig)) {
            $jsonString = file_get_contents('../config/config.json');
            file_put_contents($fileAgantConfig, $jsonString);
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Updates a configuration file properties.
 * 
 * @param type $postData
 * @param type $file
 * @return boolean
 */
function updateConfig($postData, $file) {
    checkHeaders();
    try {

        $jsonString = file_get_contents('../config/' . $file . '.json');
        $data = json_decode($jsonString, true);

        foreach ($postData as $key => $value) {
            $val = explode('.', $key);
            if (isset($val[1]) && $value == 'true') {
                $data[$val[0]][$val[1]] = true;
            } else if (isset($val[1]) && $value == 'false') {
                $data[$val[0]][$val[1]] = false;
            } else if (isset($val[1]) && $value) {
                $data[$val[0]][$val[1]] = $value;
            } else if (isset($val[1])) {
                unset($data[$val[0]][$val[1]]);
            } else {
                $data[$key] = $value;
            }
        }
        $newJsonString = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents('../config/' . $file . '.json', $newJsonString);


        $currentVersion = file_get_contents('../pages/version.txt');
        $curNumber = explode('.', $currentVersion);
        if (count($curNumber) == 3) {
            $currentVersion = $currentVersion . '.1';
        } else {
            $currentVersion = $curNumber[0] . '.' . $curNumber[1] . '.' . $curNumber[2] . '.' . ((int) $curNumber[3] + 1);
        }
        file_put_contents('../pages/version.txt', $currentVersion);


        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Add a configuration file
 * 
 * @param String $fileName
 * @return boolean
 */
function addConfig($fileName) {
    checkHeaders();
    try {

        $jsonString = file_get_contents('../config/config.json');
        file_put_contents('../config/' . $fileName . '.json', $jsonString);

        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Updates a locale file.
 * 
 * @param type $postData
 * @param type $file
 * @return boolean
 */
function updateLocale($postData, $file) {
    checkHeaders();
    try {

        $jsonString = file_get_contents('../locales/' . $file . '.json');
        $data = json_decode($jsonString, true);

        foreach ($postData as $key => $value) {
            $val = explode('.', $key);
            if (isset($val[1]) && $value == 'true') {
                $data[$val[0]][$val[1]] = true;
            } else if (isset($val[1]) && $value == 'false') {
                $data[$val[0]][$val[1]] = false;
            } else if (isset($val[1]) && $value) {
                $data[$val[0]][$val[1]] = $value;
            } else if (isset($val[1])) {
                unset($data[$val[0]][$val[1]]);
            } else {
                $data[$key] = $value;
            }
        }
        $newJsonString = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents('../locales/' . $file . '.json', $newJsonString);


        $currentVersion = file_get_contents('../pages/version.txt');
        $curNumber = explode('.', $currentVersion);
        if (count($curNumber) == 3) {
            $currentVersion = $currentVersion . '.1';
        } else {
            $currentVersion = $curNumber[0] . '.' . $curNumber[1] . '.' . $curNumber[2] . '.' . ((int) $curNumber[3] + 1);
        }
        file_put_contents('../pages/version.txt', $currentVersion);


        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Adds a locale file.
 * 
 * @param String $fileName
 * @return boolean
 */
function addLocale($fileName) {
    checkHeaders();
    try {
        if (!preg_match ("/^[a-zA-z_0-9]*$/", $fileName) ) {  
            return false;
        }
        $jsonString = file_get_contents('../locales/en_US.json');
        file_put_contents('../locales/' . $fileName . '.json', $jsonString);

        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Login method for an agent.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $username
 * @param String $pass
 * @return boolean
 */
function loginAgent($username, $pass) {
    global $dbPrefix, $pdo, $fromEmail;
    try {
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents WHERE username = ? AND password=?');
        $stmt->execute([$username, md5($pass)]);
        $user = $stmt->fetch();
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'payment_options WHERE payment_option_id=?');
        $stmt->execute([1]);
        $payment_option = $stmt->fetch();

        if ($user) {
            if ($payment_option['is_enabled']) {
                $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'subscriptions WHERE tenant=? AND (payment_status="approved" OR payment_status="succeeded") order by subscription_id desc limit 1');
                $stmt->execute([$user['tenant']]);
                $subscription = $stmt->fetch();
                $paid = (strtotime($subscription['valid_to']) >= strtotime(date('Y-m-d H:i:s'))) ? $subscription['valid_to'] : false;


                $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'subscriptions WHERE valid_to>"'.date('Y-m-d H:i:s').'" AND (payment_status="approved" OR payment_status="succeeded") AND email_sent=0 ORDER BY subscription_id DESC');
                $stmt->execute();
                while ($r = $stmt->fetch()) {
                    $days = (int)$payment_option['email_day_notify'];
                    if ($payment_option['email_notification'] && (strtotime($r['valid_to']) <= (strtotime(date('Y-m-d H:i:s')) + 86400*$days))) {
                        $subject = $payment_option['email_subject'];
                        $message =  $payment_option['email_body'];
                        $message = str_replace('{{name}}', $r['payer_name'], $message);
                        $message = str_replace('{{date}}', $r['valid_to'], $message);
                        $header = "From: " . $payment_option['email_from'] . " <" . $fromEmail . "> \r\n";
                        $header .= "Reply-To: " . $fromEmail . " \r\n";
                        $header .= "MIME-Version: 1.0\r\n";
                        $header .= "Content-type: text/html\r\n";
                        $retval = mail($r['payer_email'], $subject, nl2br($message), $header);
                        $sql = 'UPDATE ' . $dbPrefix . 'subscriptions SET `email_sent`=1 WHERE subscription_id = ?';
                        $pdo->prepare($sql)->execute([$r['subscription_id']]);
                    }
                }
            }
            $_SESSION["tenant"] = ($user['is_master'] && $user['tenant'] == 'admin') ? 'lsv_mastertenant' : $user['tenant'];
            $_SESSION["tenant_admin"] = ($user['is_master']) ? true : false;
            $_SESSION["username"] = $user['username'];
            $_SESSION["agent"] = array('agent_id' => $user['agent_id'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'tenant' => $user['tenant'], 'email' => $user['email'], 'license' => $payment_option['license'], 'payment_enabled' => $payment_option['is_enabled'], 'subscription' => @$paid);
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Login method for an agent.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $username
 * @param String $pass
 * @return boolean
 */
function loginAgentExt($username, $pass) {
    global $dbPrefix, $pdo;
    try {
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents WHERE username = ? AND password=?');
        $stmt->execute([$username, md5($pass)]);
        $user = $stmt->fetch();

        if ($user) {
            $arr = array('agent_id' => $user['agent_id'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'tenant' => $user['tenant'], 'email' => $user['email'], 'username' => $user['username'], 'is_master' => $user['is_master']);
            return json_encode($arr);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Login method for admin agent
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $email
 * @param String $pass
 * @return boolean|int
 */
function loginAdmin($email, $pass) {
    global $dbPrefix, $pdo;
    try {
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents WHERE email = ? AND password = ?');
        $stmt->execute([$email, md5($pass)]);
        $user = $stmt->fetch();

        if ($user) {
            return 200;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}


/**
 * Return chat messages by roomId and participants.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $roomId
 * @param type $sessionId
 * @param type $agentId
 * @return boolean
 */
function getChat($roomId, $sessionId, $agentId = null) {

    global $dbPrefix, $pdo;
    
    try {

        $additional = '';
        $array = [$roomId, "%$sessionId%"];
        if ($agentId && $agentId != 'false') {
            $additional = ' AND agent_id = ?';
            $array = [$roomId, $agentId, "%$sessionId%"];
        }
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "chats WHERE (`room_id`= ? or `room_id` = 'dashboard') $additional and from like ? order by date_created asc");
        $stmt->execute($array);
        $rows = array();
        while ($r = $stmt->fetch()) {
            $r['date_created'] = strtotime($r['date_created']);
            $rows[] = $r;
        }
        return json_encode($rows);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns all the chats for agent_id
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $agentId
 * @return type
 */
function getChats($draw, $agentId = false, $start = 0, $offset = 10, $order = false, $search = false) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $additional = '';
        $array = [];

        if ($agentId && $agentId != 'false') {
            $additional = ' WHERE agent_id = ? ';
            $array = [$agentId];
        }

        if ($search && $search['value']) {
            if (!$additional) {
                $additional = ' WHERE';
            }
            $additional .= ' `agent` like "%' . $search['value'] . '%" OR `message` like "%' . $search['value'] . '%" OR `date_created` like "%' . $search['value'] . '%" OR `from` like "%' . $search['value'] . '%" OR `to` like "%' . $search['value'] . '%"  OR `room_id` like "%' . $search['value'] . '%"';
        }
        $orderBy = array('date_created', 'room_id');
        $total = $pdo->prepare('SELECT max(room_id) as room_id, max(date_created) as date_created, max(agent) as agent FROM ' . $dbPrefix . 'chats ' . $additional . ' group by room_id');
        $total->execute($array);
        $stmt = $pdo->prepare('SELECT max(room_id) as room_id, max(date_created) as date_created, max(agent) as agent FROM ' . $dbPrefix . 'chats ' . $additional . ' group by room_id order by ' . $orderBy[$order[0]['column']] . ' ' . $order[0]['dir'] . ' LIMIT ' . $start . ',' . $offset);
        $stmt->execute($array);
        if ($draw) {
            $data['draw'] = $draw;
        }
        $data['recordsTotal'] = $total->rowCount();
        $data['recordsFiltered'] = $total->rowCount();

        $data['recordsTotal'] = $total->rowCount();
        $data['recordsFiltered'] = $total->rowCount();
        $rows = array();
        while ($r = $stmt->fetch()) {
            $stmt1 = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'chats where room_id=? order by date_created asc ');
            $stmt1->execute([$r['room_id']]);
            $rows1 = '<table>';
            while ($r1 = $stmt1->fetch()) {
                $rows1 .= '<tr><td><small>' . $r1['date_created'] . '</small></td><td>' . $r1['from'] . ': ' . $r1['message'] . '</td></tr>';
            }
            $rows1 .= '</table>';
//            $r['messages'] = $rows1;

            $r['messages'] = '<div class="modal fade" id="ex' . $r['room_id'] . '" tabindex="-1" role="dialog" aria-labelledby="ex' . $r['room_id'] . '" aria-hidden="true"><div class="modal-dialog modal-lgr" role="document"><button type="button" data-toggle="modal" class="closeDocumentModal" data-target="#ex' . $r['room_id'] . '" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="fa fa-times"></span></button><div class="modal-content">' . $rows1 . '</div>     </div> </div><a href="" class="fas fa-fw fa-list" data-toggle="modal" data-target="#ex' . $r['room_id'] . '"></a>';


            $rows[] = $r;
        }
        $data['data'] = $rows;
        return json_encode($data);
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Adds a chat message.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $roomId
 * @param String $message
 * @param String $agent
 * @param String $from
 * @param String $to
 * @param String $agentId
 * @param String $system
 * @param String $avatar
 * @param String $datetime
 * @return string|int
 */
function insertChat($roomId, $message, $agent, $from, $to, $agentId = null, $system = null, $avatar = null, $datetime = null) {
    global $dbPrefix, $pdo;

    try {
        $sql = "INSERT INTO " . $dbPrefix . "chats (`room_id`, `message`, `agent`, `agent_id`, `from`, `date_created`, `to`, `system`, `avatar`) "
                . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $pdo->prepare($sql)->execute([$roomId, $message, $agent, $agentId, $from, date("Y-m-d H:i:s", strtotime($datetime)), $to, $system, $avatar]);
        return 200;
    } catch (Exception $e) {
        return 500;
    }
}


/**
 * Returns all users.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @return boolean
 */
function getUsers($draw, $start = 0, $offset = 10, $order = false, $search = false) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $additional = '';
        if ($_SESSION["tenant"] !== 'lsv_mastertenant') {
            $additional = ' WHERE agent_id = "' . $_SESSION["tenant"] . '"';
        }
        $array = [];
        if ($search && $search['value']) {
            if (!$additional) {
                $additional = ' WHERE';
            }
            $additional .= ' name like "%' . $search['value'] . '%" OR username like "%' . $search['value'] . '%"';
        }
        $start = $start ?? 0;
        $offset = $offset ?? 10;
        $orderBy = array('name', 'username');
        $orderBySql = '';
        if (isset($order[0]['column'])) {
            $orderBySql = ' order by ' . $orderBy[$order[0]['column']] . ' ' . $order[0]['dir'] . ' ';
        }
        $total = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'users' . $additional);
        $total->execute($array);
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'users' . $additional . $orderBySql . ' LIMIT ' . $start . ',' . $offset);
        $stmt->execute($array);
        if ($draw) {
            $data['draw'] = $draw;
        }
        $data['recordsTotal'] = $total->rowCount();
        $data['recordsFiltered'] = $total->rowCount();
        
        $rows = array();
        while ($r = $stmt->fetch()) {
            $rows[] = $r;
        }
        $data['data'] = $rows;
        return json_encode($data);
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns user info by user_id
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $id
 * @return boolean|type
 */
function getUser($id) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $array = [$id];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "users WHERE `user_id`= ?");
        $stmt->execute($array);
        $user = $stmt->fetch();

        if ($user) {
            return json_encode($user);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Deletes an user by user_id
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $userId
 * @return boolean
 */
function deleteUser($userId) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $sql = 'DELETE FROM ' . $dbPrefix . 'users WHERE user_id = ?';
        $pdo->prepare($sql)->execute([$userId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Updates an user by user_id
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $userId
 * @param String $name
 * @param String $user
 * @param String $pass
 * @param Bool $blocked
 * @return boolean
 */
function editUser($userId, $name, $user, $pass, $blocked, $agentId) {
    global $dbPrefix, $pdo;
    checkHeaders();
    $additional = '';
    $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'users WHERE username = ? and user_id <> ?');
    $stmt->execute([$user, $userId]);
    $userName = $stmt->fetch();
    if ($userName) {
        return false;
    }

    $array = [$user, $name, $agentId, $blocked, $userId];
    if ($pass) {
        $additional = ', password = ?';
        $array = [$user, $name, $agentId, $blocked, md5($pass), $userId];
    }
    try {
        $sql = 'UPDATE ' . $dbPrefix . 'users SET username=?, name=?, agent_id=?, is_blocked=? ' . $additional . ' WHERE user_id = ?';
        $pdo->prepare($sql)->execute($array);
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Adds an user.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $user
 * @param String $name
 * @param String $pass
 * @param String $firstName
 * @param String $lastName
 * @return boolean
 */
function addUser($user, $name, $pass, $firstName = null, $lastName = null, $agentId = null) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {

        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'users WHERE username = ?');
        $stmt->execute([$user]);
        $userName = $stmt->fetch();
        if ($userName) {
            return false;
        }


        $sql = 'INSERT INTO ' . $dbPrefix . 'users (username, name, password, first_name, last_name, agent_id) VALUES (?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$user, $name, md5($pass), $firstName, $lastName, $agentId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Method to add a recording after video session ends.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $roomId
 * @param type $file
 * @param type $agentId
 * @return int
 */
function insertRecording($roomId, $file, $agentId) {
    global $dbPrefix, $pdo;
    
    try {

        $sql = "INSERT INTO " . $dbPrefix . "recordings (`room_id`, `filename`, `agent_id`, `date_created`) "
                . "VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$roomId, $file, $agentId, date("Y-m-d H:i:s")]);
        return 200;
    } catch (Exception $e) {
        return 'Error ' . $e->getMessage();
    }
}

/**
 * Method to delete a recording from the database and delete the file.
 * 
 * @global type $dbPrefix
 * @global type $pdo
 * @param type $recordingId
 * @return boolean
 */
function deleteRecording($recordingId) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {

        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'recordings WHERE recording_id = ?');
        $stmt->execute([$recordingId]);
        $rec = $stmt->fetch();

        if ($rec) {
            @unlink('../server/recordings/' . $rec['filename']);
            @unlink('../server/recordings/' . $rec['filename'] . '.mp4');
        }

        $array = [$recordingId];
        $sql = 'DELETE FROM ' . $dbPrefix . 'recordings WHERE recording_id = ?';
        $pdo->prepare($sql)->execute($array);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns all recordings.
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @return type
 */
function getRecordings($draw, $start = 0, $offset = 10, $order = false, $search = false) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        if ($_SESSION["tenant"] == 'lsv_mastertenant') {
            $additional = '';
        } else {
            $additional = ' WHERE agent_id="' . $_SESSION["tenant"] . '"';
        }
        $array = [];
        if ($search && $search['value']) {
            if (!$additional) {
                $additional = ' WHERE';
            }
            $additional .= ' filename like "%' . $search['value'] . '%" OR room_id like "%' . $search['value'] . '%" OR agent_id like "%' . $search['value'] . '%" OR date_created like "%' . $search['value'] . '%" ';
        }
        $start = $start ?? 0;
        $offset = $offset ?? 10;
        $orderBy = array('filename', 'room_id', 'agent_id', 'date_created');
        $orderBySql = '';
        if (isset($order[0]['column'])) {
            $orderBySql = ' order by ' . $orderBy[$order[0]['column']] . ' ' . $order[0]['dir'] . ' ';
        }

        $total = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'recordings' . $additional);
        $total->execute($array);
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'recordings' . $additional . $orderBySql . ' LIMIT ' . $start . ',' . $offset);
        $stmt->execute($array);
        if ($draw) {
            $data['draw'] = $draw;
        }
        $data['recordsTotal'] = $total->rowCount();
        $data['recordsFiltered'] = $total->rowCount();

        $rows = array();
        while ($r = $stmt->fetch()) {
            if ($r['filename']) {
                if (file_exists('recordings/' . $r['filename'] . '.mp4')) {
                    $r['filename'] = $r['filename'] . '.mp4';
                }
                $rows[] = $r;
            }
        }
        $data['data'] = $rows;
        return json_encode($data);

    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Returns payment options
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @return boolean|type
 */
function getPaymentOptions() {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "payment_options");
        $stmt->execute();
        $data = $stmt->fetch();
        if ($data) {
            return json_encode($data);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Updates payment option
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $paypal_client_id
 * @param String $paypal_secret_id
 * @param String $stripe_client_id
 * @param String $stripe_secret_id
 * @param String $email_day_notify
 * @param Bool $is_enabled
 * @param String $email_notification
 * @param String $email_subject
 * @param String $email_body
 * @param String $email_from
 * @param Bool $is_test_mode
 * @param Bool $paypal_enabled
 * @param Bool $stripe_enabled
 * @param Bool $authorizenet_enabled
 * @param String $authorizenet_api_login_id
 * @param String $authorizenet_transaction_key
 * @param String $authorizenet_public_client_key
 * @param Integer $email_day_notify
 * @return boolean
 */
function updatePaymentOptions($paypal_client_id, $paypal_secret_id, $stripe_client_id, $stripe_secret_id, $is_enabled, $email_notification, $email_subject, $email_body, $email_from, $is_test_mode, $paypal_enabled, $stripe_enabled, $authorizenet_enabled, $authorizenet_api_login_id, $authorizenet_transaction_key, $authorizenet_public_client_key, $email_day_notify) {
    global $dbPrefix, $pdo;
    checkHeaders();
    $is_enabled = ($is_enabled == 'true') ? 1 : 0;
    $email_notification = ($email_notification == 'true') ? 1 : 0;
    $is_test_mode = ($is_test_mode == 'true') ? 1 : 0;
    $paypal_enabled = ($paypal_enabled == 'true') ? 1 : 0;
    $stripe_enabled = ($stripe_enabled == 'true') ? 1 : 0;
    $authorizenet_enabled = ($authorizenet_enabled == 'true') ? 1 : 0;
    $array = [$paypal_client_id, $paypal_secret_id, $stripe_client_id, $stripe_secret_id, $is_enabled, $email_notification, $email_subject, $email_body, $email_from, $is_test_mode, $paypal_enabled, $stripe_enabled, $authorizenet_enabled, $authorizenet_api_login_id, $authorizenet_transaction_key, $authorizenet_public_client_key, $email_day_notify, 1];
    try {
        $sql = 'UPDATE ' . $dbPrefix . 'payment_options SET paypal_client_id=?, paypal_secret_id=?, stripe_client_id=?, stripe_secret_id=?, is_enabled=?, email_notification=?, email_subject=?, email_body=?, email_from=?, is_test_mode=?, paypal_enabled=?, stripe_enabled=?, authorizenet_enabled=?, authorizenet_api_login_id=?, authorizenet_transaction_key=?, authorizenet_public_client_key=?, email_day_notify=? WHERE payment_option_id=?';
        $pdo->prepare($sql)->execute($array);
        $_SESSION['agent']['payment_enabled'] = $is_enabled;
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Returns all plans.
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @return boolean
 */
function getPlans($draw, $start = 0, $offset = 10, $order = false, $search = false) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $array = [];
        if ($search && $search['value']) {
            $additional = 'WHERE name like "%' . $search['value'] . '%" OR price like "%' . $search['value'] . '%"';
        }
        $start = $start ?? 0;
        $offset = $offset ?? 10;
        $orderBy = array('name', 'price');
        $orderBySql = '';
        if (isset($order[0]['column'])) {
            $orderBySql = ' order by ' . $orderBy[$order[0]['column']] . ' ' . $order[0]['dir'] . ' ';
        }

        $total = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'plans' . $additional);
        $total->execute($array);
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'plans' . $additional . $orderBySql . ' LIMIT ' . $start . ',' . $offset);
        $stmt->execute($array);
        if ($draw) {
            $data['draw'] = $draw;
        }
        $data['recordsTotal'] = $total->rowCount();
        $data['recordsFiltered'] = $total->rowCount();

        $rows = array();
        while ($r = $stmt->fetch()) {
            $rows[] = $r;
        }
        $data['data'] = $rows;
        return json_encode($data);

    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns plan info by plan_id
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $id
 * @return boolean|type
 */
function getPlan($id) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $array = [$id];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "plans WHERE `plan_id`= ?");
        $stmt->execute($array);
        $user = $stmt->fetch();

        if ($user) {
            return json_encode($user);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}


/**
 * Updates plan by plan_id
 *
 * @global type $dbPrefix
 * @global user $pdo
 * @param String $planId
 * @param String $name
 * @param String $price
 * @param String $currency
 * @param String $interval
 * @param String $interval_count
 * @param String $description
 * @return boolean
 */
function editPlan($planId, $name, $price, $currency, $interval, $interval_count, $description) {
    global $dbPrefix, $pdo;
    checkHeaders();
    $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'plans WHERE name = ? and plan_id <> ?');
    $stmt->execute([$name, $planId]);
    $planName = $stmt->fetch();
    if ($planName) {
        return false;
    }

    $array = [$name, $price, $currency, $interval, $interval_count, $description, $planId];
    try {
        $sql = 'UPDATE ' . $dbPrefix . 'plans SET `name`=?, `price`=?, `currency`=?, `interval`=?, `interval_count`=?, `description`=? WHERE plan_id = ?';
        $pdo->prepare($sql)->execute($array);
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}


/**
 * Adds a plan.
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $name
 * @param String $price
 * @param String $currency
 * @param String $interval
 * @param String $interval_count
 * @param String $description
 * @return boolean
 */
function addPlan($name, $price, $currency, $interval, $interval_count, $description) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {

        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'plans WHERE name = ?');
        $stmt->execute([$name]);
        $plan = $stmt->fetch();
        if ($plan) {
            return false;
        }

        $sql = 'INSERT INTO ' . $dbPrefix . 'plans (`name`, `price`, `currency`, `interval`, `interval_count`, `description`) VALUES (?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([$name, $price, $currency, $interval, $interval_count, $description]);
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Returns all subscriptions.
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @return boolean
 */
function getSubscriptions($draw, $start = 0, $offset = 10, $order = false, $search = false) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $array = [];
        $additional = 'WHERE subscription_id IN (SELECT MAX(subscription_id) from ' . $dbPrefix . 'subscriptions group by tenant)';
        if ($search && $search['value']) {
            $additional = 'WHERE subscription_id IN (SELECT MAX(subscription_id) from ' . $dbPrefix . 'subscriptions group by tenant) AND payer_name like "%' . $search['value'] . '%" OR payer_email like "%' . $search['value'] . '%"';
        }
        $start = $start ?? 0;
        $offset = $offset ?? 10;
        $orderBy = array('subscription_id', 'valid_to');
        $orderBySql = ' order by subscription_id desc;';
        if (isset($order[0]['column'])) {
            $orderBySql = ' order by ' . $orderBy[$order[0]['column']] . ' ' . $order[0]['dir'] . ' ';
        }

        $total = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'subscriptions ' . $additional);
        $total->execute($array);
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'subscriptions ' . $additional . $orderBySql . ' LIMIT ' . $start . ',' . $offset);
        $stmt->execute($array);
        if ($draw) {
            $data['draw'] = $draw;
        }
        $data['recordsTotal'] = $total->rowCount();
        $data['recordsFiltered'] = $total->rowCount();

        $rows = array();
        while ($r = $stmt->fetch()) {
            $rows[] = $r;
        }
        $data['data'] = $rows;
        return json_encode($data);

    } catch (Exception $e) {
        return false;
    }
}

/**
 * Returns plan info by plan_id
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $id
 * @return boolean|type
 */
function getSubscription($id) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $array = [$id];
        $stmt = $pdo->prepare("SELECT * FROM " . $dbPrefix . "subscriptions LEFT JOIN " . $dbPrefix . "agents ON " . $dbPrefix . "agents.agent_id = " . $dbPrefix . "subscriptions.agent_id LEFT JOIN " . $dbPrefix . "plans ON " . $dbPrefix . "plans.plan_id = " . $dbPrefix . "subscriptions.plan_id WHERE `subscription_id`= ?");
        $stmt->execute($array);
        $user = $stmt->fetch();

        if ($user) {
            return json_encode($user);
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}


/**
 * Updates plan by plan_id
 *
 * @global type $dbPrefix
 * @global user $pdo
 * @param String $subscriptionId
 * @param String $valid_from
 * @param String $valid_to
 * @return boolean
 */
function editSubscription($subscriptionId, $valid_from, $valid_to) {
    global $dbPrefix, $pdo;
    checkHeaders();

    $array = [$valid_from, $valid_to, $subscriptionId];
    try {
        $sql = 'UPDATE ' . $dbPrefix . 'subscriptions SET `valid_from`=?, `valid_to`=? WHERE subscription_id = ?';
        $pdo->prepare($sql)->execute($array);
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

/**
 * Deletes a plan
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $planId
 * @return boolean
 */
function deletePlan($planId) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {

        $sql = 'DELETE FROM ' . $dbPrefix . 'plans WHERE plan_id = ?';
        $pdo->prepare($sql)->execute([$planId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Deletes a plan
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @param String $subscriptionId
 * @return boolean
 */
function deleteSubscription($subscriptionId) {
    global $dbPrefix, $pdo;
    checkHeaders();
    try {

        $sql = 'DELETE FROM ' . $dbPrefix . 'subscriptions WHERE subscription_id = ?';
        $pdo->prepare($sql)->execute([$subscriptionId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function checkPayment($agentId) {
    global $dbPrefix, $pdo;
    $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'agents WHERE tenant=?');
    $stmt->execute([$agentId]);
    $user = $stmt->fetch();
    $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'payment_options WHERE payment_option_id=?');
    $stmt->execute([1]);
    $payment_option = $stmt->fetch();
    if ($payment_option['is_enabled']) {
        $paid = false;
        if ($user) {
            $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'subscriptions WHERE tenant=? AND (payment_status="approved" OR payment_status="succeeded") order by subscription_id desc limit 1');
            $stmt->execute([$user['tenant']]);
            $subscription = $stmt->fetch();
            $paid = (strtotime($subscription['valid_to']) >= strtotime(date('Y-m-d H:i:s'))) ? true : false;
        } else {
            $paid = false;
        }
        return $paid;
    } else {
        return true;
    }
}


/**
 * Returns all subscriptions.
 *
 * @global type $dbPrefix
 * @global type $pdo
 * @return boolean
 */
function getHistory($draw, $tenant = '', $start = 0, $offset = 10, $order = false, $search = false) {

    global $dbPrefix, $pdo;
    checkHeaders();
    try {
        $array = [];
        if (!isset($_SESSION["agent"]['tenant'])) {
            return false;
        }
        if ($_SESSION['tenant'] == 'lsv_mastertenant' && $tenant) {
            $additional = ' WHERE tenant = "'. $tenant . '"';
        } else {
            $additional = ' WHERE tenant = "' . $_SESSION["agent"]['tenant'] . '"';
        }
        if ($search && $search['value']) {
            $additional .= ' AND payer_name LIKE "%' . $search['value'] . '%" OR payer_email LIKE "%' . $search['value'] . '%"';
        }
        $start = $start ?? 0;
        $offset = $offset ?? 10;
        $orderBy = array('subscription_id', 'valid_to');
        $orderBySql = '';
        if (isset($order[0]['column'])) {
            $orderBySql = ' order by ' . $orderBy[$order[0]['column']] . ' ' . $order[0]['dir'] . ' ';
        }
        $total = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'subscriptions ' . $additional);
        $total->execute($array);
        $stmt = $pdo->prepare('SELECT * FROM ' . $dbPrefix . 'subscriptions LEFT JOIN ' . $dbPrefix . 'plans ON ' . $dbPrefix . 'plans.plan_id = ' . $dbPrefix . 'subscriptions.plan_id  ' . $additional . $orderBySql . ' LIMIT ' . $start . ',' . $offset);
        $stmt->execute($array);
        if ($draw) {
            $data['draw'] = $draw;
        }
        $data['recordsTotal'] = $total->rowCount();
        $data['recordsFiltered'] = $total->rowCount();

        $rows = array();
        while ($r = $stmt->fetch()) {
            $rows[] = $r;
        }
        $data['data'] = $rows;
        return json_encode($data);

    } catch (Exception $e) {
        return false;
    }
}

function getPassphrase() {
    global $pasPhrase;
    return $pasPhrase;
}

function getApiKey() {
    global $apiKey;
    return $apiKey;
}



function getPk() {
    global $setVal;
    if (isset($setVal)) {
        return $setVal;
    } else {
        return '';
    }
}

function getVirtualImages() {
    $filenameArray = [];

    $handle = opendir('../img/virtual');
    while ($file = readdir($handle)) {
        if ($file !== '.' && $file !== '..') {
            array_push($filenameArray, $file);
        }
    }

    return json_encode($filenameArray);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'));

    if (isset($data) && $data->type) {
        if ($data->type == 'getpk') {
            echo getPk();
        }
        if ($data->type == 'getchat') {
            echo getChat($data->roomId, $data->sessionId, @$data->agentId);
        }
        if ($data->type == 'addchat') {
            echo insertChat($data->roomId, $data->message, $data->agent, $data->from, $data->to, @$data->agentId, @$data->system, @$data->avatar, @$data->datetime);
        }
        if ($data->type == 'login') {
            echo checkLogin($data->email, $data->password);
        }
        if ($data->type == 'getroom') {
            echo getRoom($data->roomId);
        }
        if ($data->type == 'addrecording') {
            echo insertRecording($data->roomId, $data->filename, $data->agentId);
        }
        if ($data->type == 'getvirtualimages') {
            echo getVirtualImages();
        }
        if ($data->type == 'getroombyshort') {
            echo getRoomByShort($data->shortUrl);
        }
        if ($data->type == 'checkpayment') {
            echo checkPayment($data->agentId);
        }
    } else {
        if (isset($_POST['type']) && $_POST['type'] == 'login') {
            echo checkLogin($_POST['email'], @$_POST['password']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'logintoken') {
            echo checkLoginToken($_POST['token'], $_POST['roomId'], @$_POST['isAdmin']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'scheduling') {
            echo insertScheduling($_POST['agent'], $_POST['visitor'], $_POST['agenturl'], $_POST['visitorurl'], @$_POST['password'], $_POST['session'], $_POST['datetime'], $_POST['duration'], @$_POST['shortAgentUrl'], @$_POST['shortVisitorUrl'], $_POST['agentId'], @$_POST['agenturl_broadcast'], @$_POST['visitorurl_broadcast'], @$_POST['shortAgentUrl_broadcast'], @$_POST['shortVisitorUrl_broadcast'], @$_POST['is_active']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'addroom') {
            echo addRoom($_POST['lsRepUrl'], @$_POST['agentId'], @$_POST['roomId'], @$_POST['agentName'], @$_POST['visitorName'], @$_POST['agentShortUrl'], @$_POST['visitorShortUrl'], @$_POST['password'], @$_POST['config'], @$_POST['dateTime'], @$_POST['duration'], @$_POST['disableVideo'], @$_POST['disableAudio'], @$_POST['disableScreenShare'], @$_POST['disableWhiteboard'], @$_POST['disableTransfer'], @$_POST['is_active']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'editroom') {
            echo editRoom($_POST['room_id'], $_POST['agent'], $_POST['visitor'], $_POST['agenturl'], $_POST['visitorurl'], @$_POST['password'], $_POST['session'], $_POST['datetime'], $_POST['duration'], @$_POST['shortAgentUrl'], @$_POST['shortVisitorUrl'], $_POST['agentId'], @$_POST['agenturl_broadcast'], @$_POST['visitorurl_broadcast'], @$_POST['shortAgentUrl_broadcast'], @$_POST['shortVisitorUrl_broadcast'], @$_POST['is_active']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'changeroomstate') {
            echo updateRoomState($_POST['room_id'], $_POST['is_active']);
        }

        if (isset($_POST['type']) && $_POST['type'] == 'getrooms') {
            echo getRooms(@$_POST['draw'], @$_POST['agentId'], @$_POST['start'], @$_POST['length'], @$_POST['order'], @$_POST['search']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'deleteroom') {
            echo deleteRoom($_POST['roomId'], $_POST['agentId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'deleteroombyagent') {
            echo deleteRoomByAgent($_POST['agentId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getagents') {
            echo getAgents(@$_POST['draw'], @$_POST['start'], @$_POST['length'], @$_POST['order'], @$_POST['search']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'deleteagent') {
            echo deleteAgent($_POST['agentId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'deleteagentbyusername') {
            echo deleteAgentByUsername($_POST['username']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'editagent') {
            echo editAgent($_POST['agentId'], $_POST['firstName'], $_POST['lastName'], $_POST['email'], $_POST['tenant'], $_POST['password'], @$_POST['usernamehidden'], @$_POST['is_master']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'editadmin') {
            echo editAdmin($_POST['agentId'], $_POST['firstName'], $_POST['lastName'], $_POST['email'], $_POST['tenant'], $_POST['password']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'loginagent') {
            echo loginAgent($_POST['username'], $_POST['password']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'loginagentext') {
            echo loginAgentExt($_POST['username'], $_POST['password']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'loginadmin') {
            echo loginAdmin($_POST['email'], $_POST['password']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'addagent') {
            echo addAgent($_POST['username'], $_POST['password'], $_POST['firstName'], $_POST['lastName'], $_POST['email'], $_POST['tenant'], @$_POST['is_master']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'updateconfig') {
            echo updateConfig($_POST['data'], $_POST['fileName']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'addconfig') {
            echo addConfig($_POST['fileName']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'updatelocale') {
            echo updateLocale($_POST['data'], $_POST['fileName']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'addlocale') {
            echo addLocale($_POST['fileName']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getagent') {
            echo getAgent($_POST['tenant']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getadmin') {
            echo getAdmin($_POST['id']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getroom') {
            echo getRoom($_POST['roomId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getroombyid') {
            echo getRoomById($_POST['room_id']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getpassphrase') {
            echo getPassphrase();
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getapikey') {
            echo getApiKey();
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getpk') {
            echo getPk();
        }
        if (isset($_POST['type']) && $_POST['type'] == 'recoverpassword') {
            echo recoverPassword($_POST['email'], $_POST['username']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'resetpassword') {
            echo resetPassword($_POST['token'], $_POST['password']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getchats') {
            echo getChats(@$_POST['draw'], @$_POST['agentId'], @$_POST['start'], @$_POST['length'], @$_POST['order'], @$_POST['search']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getusers') {
            echo getUsers(@$_POST['draw'], @$_POST['start'], @$_POST['length'], @$_POST['order'], @$_POST['search']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'deleteuser') {
            echo deleteUser($_POST['userId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'edituser') {
            echo editUser($_POST['userId'], $_POST['name'], $_POST['username'], @$_POST['password'], @$_POST['isBlocked'], @$_POST['agentId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'adduser') {
            echo addUser($_POST['username'], $_POST['name'], $_POST['password'], @$_POST['firstName'], @$_POST['lastName'], @$_POST['agentId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getuser') {
            echo getUser($_POST['id']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'addrecording') {
            echo insertRecording($_POST['roomId'], $_POST['filename'], $_POST['agentId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getrecordings') {
            echo getRecordings(@$_POST['draw'], @$_POST['start'], @$_POST['length'], @$_POST['order'], @$_POST['search']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'deleterecording') {
            echo deleteRecording($_POST['recordingId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getpaymentoptions') {
            echo getPaymentOptions();
        }
        if (isset($_POST['type']) && $_POST['type'] == 'updatepaymentoption') {
            echo updatePaymentOptions($_POST['paypal_client_id'], $_POST['paypal_secret_id'], $_POST['stripe_client_id'], $_POST['stripe_secret_id'], @$_POST['is_enabled'], @$_POST['email_notification'], @$_POST['email_subject'], @$_POST['email_body'], @$_POST['email_from'], @$_POST['is_test_mode'], $_POST['paypal_enabled'], $_POST['stripe_enabled'], $_POST['authorizenet_enabled'], $_POST['authorizenet_api_login_id'], $_POST['authorizenet_transaction_key'], $_POST['authorizenet_public_client_key'], $_POST['email_day_notify']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getplans') {
            echo getPlans(@$_POST['draw'], @$_POST['start'], @$_POST['length'], @$_POST['order'], @$_POST['search']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getplan') {
            echo getPlan($_POST['id']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'editplan') {
            echo editPlan($_POST['planId'], $_POST['name'], $_POST['price'], $_POST['currency'], @$_POST['interval'], @$_POST['interval_count'], @$_POST['description']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'addplan') {
            echo addPlan($_POST['name'], $_POST['price'], $_POST['currency'], @$_POST['interval'], @$_POST['interval_count'], @$_POST['description']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getsubscriptions') {
            echo getSubscriptions(@$_POST['draw'], @$_POST['start'], @$_POST['length'], @$_POST['order'], @$_POST['search']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'getsubscription') {
            echo getSubscription($_POST['id']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'editsubscription') {
            echo editSubscription($_POST['subscriptionId'], $_POST['valid_from'], $_POST['valid_to']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'deleteplan') {
            echo deletePlan($_POST['planId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'deletesubscription') {
            echo deleteSubscription($_POST['subscriptionId']);
        }
        if (isset($_POST['type']) && $_POST['type'] == 'gethistory') {
            echo getHistory(@$_POST['draw'], @$_POST['tenant'], @$_POST['start'], @$_POST['length'], @$_POST['order'], @$_POST['search']);
        }
    }
}
