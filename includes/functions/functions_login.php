<?php 

include_once(ROOT_PATH . '/includes/functions/functions_global.php');
 
if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_POST['login_btn'])) {

    $entered_username = esc($_POST['username']);
    $entered_password = esc($_POST['password']);

    if (empty($entered_username)) { array_push($errors, $lang['error_username_needed']); }
    if (empty($entered_password)) { array_push($errors, $lang['error_password_needed']); }
    if (!$can_store_cookies) { array_push($errors, $lang['error_cookies_needed']); }
    
    if (count($errors) === 0) {

        $query = "SELECT password, active FROM nn_users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $entered_username);
        $stmt->execute();
        
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $user = mysqli_fetch_assoc($result);

            $stored_password = $user['password'];
            
            if ($user['active'] === 0) {
                
                array_push($errors, $lang['error_account_inactive']);

            }
            
        }
        else {

            array_push($errors, $lang['error_credentials']);

        }

        if (count($errors) === 0) {

            if(password_verify($entered_password, $stored_password)) {
                
                $session_token = bin2hex(random_bytes(32));
                
                $query = "UPDATE nn_users SET session_token = ?, session_expire = DATE_ADD(NOW(), INTERVAL ? HOUR) WHERE username = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('sss', $session_token, $login_persistent_time, $entered_username);
                $stmt->execute();
                
                setcookie('session_token', $session_token, time() + 3600 * $login_persistent_time, '/', $_SERVER['SERVER_NAME'], false, true);
                
                header('location: ' . BASE_URL . '/index.php');
                exit();
                
            }
            else {
                
                array_push($errors, $lang['error_credentials']);
                
            }
            
        }

    }

}