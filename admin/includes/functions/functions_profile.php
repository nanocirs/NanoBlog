<?php

include_once(ROOT_PATH . '/includes/functions/functions_global.php');

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

if (!isset($errors)) {

    $errors = [];
    
}


if (isset($_POST['username'])) {

    update_username($_POST['username']);

}

if (isset($_POST['password'])) {
 
    update_password();

}

if (isset($_POST['email'])) {

    update_email($_POST['email']);

}


function update_username($new_username) {

    global $conn, $logged_user, $errors, $lang;

    $username = esc($new_username);

    if (strlen($username) < 3) { array_push($errors, $lang['error_username_short']); }
    else if (strlen($username) > 20) { array_push($errors, $lang['error_username_long']); }

    $query = "SELECT * FROM nn_users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $user = mysqli_fetch_assoc($result);

    if ($user) { array_push($errors, $lang['error_username_taken']); }

    if (count($errors) == 0) {

        $query = "UPDATE nn_users SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $username, $logged_user['id']);
        $stmt->execute();

        header('location: ' . BASE_URL . '/admin/profile.php');
        exit();
        
    }

}

function update_password() {

    global $conn, $logged_user, $errors, $lang;

    $password = esc($_POST['password']);
    $password_confirmation = esc($_POST['password2']);

    if ($password !== $password_confirmation) {

        array_push($errors, $lang['error_passwords_dont_match']);

    }

    if (strlen($password) < 8) { array_push($errors, $lang['error_password_short']); }

    if (count($errors) == 0) {

        $password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "UPDATE nn_users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $password, $logged_user['id']);      
        $stmt->execute();

        header('location: ' . BASE_URL . '/admin/profile.php');
        exit();

    }
}

function update_email($new_email) {

    global $conn, $logged_user, $errors, $lang;

    $email = esc($new_email);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && $email !== '') {

        array_push($errors, $lang['error_email_invalid_format']);

    }

    if ($email == '') {

        array_push($errors, $lang['error_email_needed']);

    }
    else {

        $query = "SELECT * FROM nn_users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $user = mysqli_fetch_assoc($result);
            
        if ($user) { array_push($errors, $lang['error_email_taken']); }
    
    }

    if (count($errors) === 0) {

        $query = "UPDATE nn_users SET email = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $email, $logged_user['id']);
        $stmt->execute();

        header('location: ' . BASE_URL . '/admin/profile.php');   
        exit();

    }

}

function get_posts_by_user($user_id) {

    global $conn;

    $query = "SELECT * FROM nn_posts WHERE user_id = ? ORDER BY published_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    $result = $stmt->get_result();

    return mysqli_fetch_all($result, MYSQLI_ASSOC);

}