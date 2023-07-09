<?php 

include_once(ROOT_PATH . '/includes/functions/functions_global.php');
 
if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

$password_recovery_requested = false;
$enable_password_recovery = false;
$password_restart_success = false;

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_POST['password_reset_request'])) {

    handle_password_recovery($_POST);

}

if (isset($_POST['set_new_password'])) {

    set_new_password($_POST);

}

if(isset($_GET['token'])) {

    enable_password_reset($_GET['token']);

}

function handle_password_recovery($request_values) {

    global $conn, $errors, $password_recovery_requested, $lang;

    $entered_email = esc($request_values['email']);

    if (empty($entered_email)) { array_push($errors, $lang['error_email_needed']); }
    else if (!filter_var($entered_email, FILTER_VALIDATE_EMAIL)) { array_push($errors, $lang['error_email_invalid_format']); }

    if (!empty($entered_email)) {

        $query = "SELECT * FROM nn_users WHERE email = ? AND active=1 OR (active=0 AND activation_expire > NOW()) LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $entered_email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $user = mysqli_fetch_assoc($result);
        
        if (!$user) { array_push($errors, $lang['error_email_doesnt_exist']); }
        
    }

    if (count($errors) === 0) {

        $recovery_token = generate_token();

        $query = "UPDATE nn_users SET recovery_token = ?, recovery_expire = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $recovery_token, $entered_email);
        $stmt->execute();

        if (mysqli_affected_rows($conn) > 0) {

            send_recovery_email($entered_email, $recovery_token);

            $password_recovery_requested = true;
            
        }

    }

}

function set_new_password($request_values) {

    global $conn, $errors, $login_persistent_time, $password_restart_success, $lang;

    $entered_password = esc($request_values['password']);
    $entered_password2 = esc($request_values['password_confirmation']);

    if (strlen($entered_password) < 8) { array_push($errors, $lang['error_password_short']); }

    if ($entered_password !== $entered_password2) { array_push($errors, $lang['error_passwords_dont_match']); }

    $query = "SELECT * FROM nn_users WHERE recovery_token = ? AND recovery_expire > now() LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $_GET['token']);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0 && count($errors) === 0) {

        $user = mysqli_fetch_assoc($result);

        $password = password_hash($entered_password, PASSWORD_DEFAULT);
        $session_token = bin2hex(random_bytes(32));
        
        $query = "UPDATE nn_users SET password = ?, session_token = ?, session_expire = DATE_ADD(NOW(), INTERVAL ? HOUR), recovery_token='', recovery_expire=NULL, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssii', $password, $session_token, $login_persistent_time, $user['id']);
        $stmt->execute();

        if (mysqli_affected_rows($conn) > 0) {

            $logged_user = $user;

            $query = "UPDATE nn_users SET last_connection = now() WHERE username = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $logged_user['username']);
            $stmt->execute();
                
            $password_restart_success = true;

            setcookie('session_token', $session_token, time() + 3600 * $login_persistent_time, '/', $_SERVER['SERVER_NAME'], false, true);
         
        }
        
    }

}

function enable_password_reset($token) {

    global $conn, $errors, $enable_password_recovery;

    $query = "SELECT * FROM nn_users WHERE recovery_token = ? AND recovery_expire > NOW() LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $token);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $enable_password_recovery = true;

    }

}

function send_recovery_email($email, $recovery_token) {

    global $lang;

    $sender_email = "no-reply@" . $_SERVER['SERVER_NAME'];
    $recovery_link = BASE_URL . "/recovery.php?token=" . $recovery_token;

    $title = $lang['password_recovery'];

    $body  = "<p>" . $lang['click_url_to_change_password'] . "</p>";
    $body .= $recovery_link;
    $body .= "<p>" . $lang['url_limited_time'] . " " . $lang['do_not_reply'] . "</p>";

    $header  = "From:" . $sender_email . "\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-type: text/html\r\n";

    $r = mail($email, $title, $body, $header);

}