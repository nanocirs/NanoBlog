<?php 

include_once(ROOT_PATH . '/includes/functions/functions_global.php');
 
if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

$register_fulfilled = false;

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_POST['create_user'])) {

    create_user($_POST);

}
if (isset($_GET['token'])) {

    activate_user($_GET['token']);

}

function create_user($request_values) {

    global $conn, $errors, $can_store_cookies, $webpage_settings, $register_fulfilled, $lang;

    $entered_username = esc($request_values['username']);
    $entered_password = esc($request_values['password']);
    $entered_password2 = esc($request_values['password_confirmation']);
    $entered_email = esc($request_values['email']);
            
    if (strlen($entered_username) < 3) { array_push($errors, $lang['error_username_short']); }
    else if (strlen($entered_username) > 20) { array_push($errors, $lang['error_username_long']); }

    if (strlen($entered_password) < 8) { array_push($errors, $lang['error_password_short']); }
    if ($entered_password !== $entered_password2) { array_push($errors, $lang['error_passwords_dont_match']); }

    if (empty($entered_email)) { array_push($errors, $lang['error_email_needed']); }
    else if (!filter_var($entered_email, FILTER_VALIDATE_EMAIL)) { array_push($errors, $lang['error_email_invalid_format']); }

    if (!$can_store_cookies) { array_push($errors, $lang['error_cookies_needed']); }

    $query = "SELECT * FROM nn_users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $entered_username);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $user = mysqli_fetch_assoc($result);

    if ($user) { array_push($errors, $lang['error_username_taken']); }

    if (!empty($entered_email)) {

        $query = "SELECT * FROM nn_users WHERE email = ? AND (active=1 OR (active=0 AND activation_expire > now())) LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $entered_email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $user = mysqli_fetch_assoc($result);
        
        if ($user) { array_push($errors, $lang['error_email_taken']); }
        
    }

    if (count($errors) === 0) {

        $password = password_hash($entered_password, PASSWORD_DEFAULT);

        $activation_token = generate_token();
        
        $query = "INSERT INTO nn_users (username, role, password, email, activation_token, created_at, updated_at, activation_expire) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssss', $entered_username, $webpage_settings['register_role'], $password, $entered_email, $activation_token);
        $stmt->execute();

        if (mysqli_affected_rows($conn) > 0) {

            $user_id = mysqli_insert_id($conn);

            send_activation_email($entered_email, $activation_token);

            $register_fulfilled = true;

        }

    }

}

function activate_user($token) {

    global $conn, $login_persistent_time, $logged_user;

    $query = "SELECT * FROM nn_users WHERE activation_token = ? AND activation_expire > now() AND active=0 LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $token);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = mysqli_fetch_assoc($result);

        $session_token = bin2hex(random_bytes(32));

        $query = "UPDATE nn_users SET active=1, activation_token='', activation_expire=NULL, session_token = ?, session_expire = DATE_ADD(NOW(), INTERVAL ? HOUR) WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sii', $session_token, $login_persistent_time, $user['id']);
        $stmt->execute();

        $logged_user = $user;
            
        $query = "UPDATE nn_users SET last_connection = now() WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $logged_user['username']);
        $stmt->execute();

        setcookie('session_token', $session_token, time() + 3600 * $login_persistent_time, '/', $_SERVER['SERVER_NAME'], false, true);
        
        header('location: ' . BASE_URL . '/register.php');  

    }

}

function send_activation_email($email, $activation_code) {

    global $webpage_settings, $lang;

    $sender_email = "no-reply@" . $_SERVER['SERVER_NAME'];
    $activation_link = BASE_URL . "/register.php?token=" . $activation_code;

    $title = $lang['url_account_activation'];

    $body  = "<p>" . $lang['welcome_to'] . " " . $webpage_settings['title'] . ". " . $lang['click_url_to_activate_account'] . "</p>";
    $body .= $activation_link;
    $body .= "<p>" . $lang['do_not_reply'] . "</p>";

    $header  = "From:" . $sender_email . "\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-type: text/html\r\n";

    $r = mail($email, $title, $body, $header);

}