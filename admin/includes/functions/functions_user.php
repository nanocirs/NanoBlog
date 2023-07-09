<?php

include_once(ROOT_PATH . '/includes/functions/functions_global.php');

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

$user_id = 0;
$username = '';
$role = '';
$email = '';

$search_count_results = 0;
$search_count_users = 0;
$search_query = '';
$search_page = 1;
$search_limit = 10;

$order_by = 'creation_date';
$order_type = 'desc';

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_POST['create_user'])) {

    create_user($_POST);

}

if (isset($_GET['edit-user'])) {

    $user_id = $_GET['edit-user'];

    edit_user($user_id);

}

if (isset($_POST['update_user'])) {

    update_user($_POST);

}

if (isset($_GET['disable-user'])) {
    
    $user_id = $_GET['disable-user'];
    disable_user($user_id);

}

if (isset($_GET['search']) ) {

    if ($_GET['search'] === '') {

        header('location: ' . BASE_URL . '/admin/users.php');
        exit(0);

    }

    $page = isset($_GET['p']) ? esc($_GET['p']) : 1;
    
    $search = $_GET['search'];

    if (isset($_GET['order_by'])) {

        $order_by = $_GET['order_by'];
        $order = $_GET['order'];

    }
    else {

        $order_by = 'creation_date';
        $order = 'desc';
        
    }

    $users = get_users_by_search($search, max($page, 1), 10, $order_by, $order);

}
else {

    $page = isset($_GET['p']) ? esc($_GET['p']) : 1;

    if (isset($_GET['order_by'])) {

        $order_by = $_GET['order_by'];
        $order = $_GET['order'];

    }
    else {

        $order_by = 'creation_date';
        $order = 'desc';

    }

    $users = get_users(max($page, 1), 10, $order_by, $order);

}

function get_all_roles() {

    global $conn;

    $query = "SELECT * FROM nn_roles";
    $result = mysqli_query($conn, $query);

    if (!$result) {

        return null;
        
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
    
}

function count_users() {

    global $conn;

    $query = "SELECT COUNT(*) AS total FROM nn_users";

    $result = mysqli_query($conn, $query);

    if (!$result) {

        return null;
        
    }
    
    return mysqli_fetch_assoc($result)['total'];

}


function create_user($request_values) {

    global $conn, $errors, $role, $username, $email, $lang;

    $username = esc($request_values['username']);
    $password = esc($request_values['password']);
    $email = esc($request_values['email']);
    $password_confirmation = esc($request_values['password_confirmation']);
    $role = esc($request_values['role']);

    if (strlen($username) < 3) { array_push($errors, $lang['error_username_short']); }
    else if (strlen($username) > 20) { array_push($errors, $lang['error_username_long']); }

    if (empty($role)) { array_push($errors, $lang['error_role_needed']); }
    if (empty($password)) { array_push($errors, $lang['error_password_needed']); }

    if (empty($email)) { array_push($errors, $lang['error_email_needed']); }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { array_push($errors, $lang['error_email_invalid_format']); }

    if (strlen($password) < 8) { array_push($errors, $lang['error_password_short']); }
    if ($password !== $password_confirmation) { array_push($errors, $lang['error_passwords_dont_match']); }

    $query = "SELECT * FROM nn_users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $user = mysqli_fetch_assoc($result);

    if ($user) { array_push($errors, $lang['error_username_taken']); }

    if (!empty($email)) {

        $query = "SELECT * FROM nn_users WHERE email = ? AND (active=1 OR (active=0 AND activation_expire > NOW())) LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $user = mysqli_fetch_assoc($result);
        
        if ($user) { array_push($errors, $lang['error_email_taken']); }
        
    }
    
    if (count($errors) === 0) {

        $password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO nn_users (username, role, password, email, created_at, updated_at, active) VALUES (?, ?, ?, ?, NOW(), NOW(), 1)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssss', $username, $role, $password, $email);
        $stmt->execute();

        header('location: ' . BASE_URL . '/admin/users.php');   
        exit();
        
    }

}

function edit_user($id) {

    global $conn, $username, $role, $email, $user_id;

    $user_id = esc($id);

    $query = "SELECT * FROM nn_users WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $user = mysqli_fetch_assoc($result);

    $username = $user['username'];
    $role = $user['role'];
    $email = $user['email'];

}

function update_user($request_values) {

    global $conn, $errors, $username, $role, $email, $user_id, $lang;

    $user_id = esc($request_values['user_id']);

    if (isset($request_values['role'])) {

        if (esc($request_values['role']) != 'SuperAdmin') {

            $role = esc($request_values['role']);
            
        }

    }

    $email = esc($request_values['email']);

    if (empty($email)) {

        array_push($errors, $lang['error_email_needed']);

    }
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL) ) {

        array_push($errors, $lang['error_email_invalid_format']);

    }

    if (!empty($email)) {

        $query = "SELECT * FROM nn_users WHERE email = ? AND (active=1 OR (active=0 AND activation_expire > NOW())) LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $user = mysqli_fetch_assoc($result);
        
        if ($user && $user['username'] != $username ) { array_push($errors, $lang['error_email_taken']); }
        
    }

    $username = esc($request_values['username']);

    if (strlen($username) < 3) { array_push($errors, $lang['error_username_short']); }
    else if (strlen($username) > 20) { array_push($errors, $lang['error_username_long']); }

    if (isset($request_values['password'])) {

        $password = esc($request_values['password']);
        if (strlen($password) < 8) { array_push($errors, $lang['error_password_short']); }

        if (count($errors) == 0) {

            $password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "UPDATE nn_users SET username = ?, email = ?, role = IF((SELECT COUNT(*) FROM nn_roles WHERE role = ?) > 0, ?, role), password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sssssi', $username, $email, $role, $role, $password, $user_id);      
            $stmt->execute();

            header('location: ' . BASE_URL . '/admin/users.php');
            exit();

        }

    }
    else {

        if (count($errors) == 0) {

            $query = "UPDATE nn_users SET username = ?, email = ?, role = IF((SELECT COUNT(*) FROM nn_roles WHERE role = ?) > 0, ?, role), updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ssssi', $username, $email, $role, $role, $user_id);
            $stmt->execute();

            header('location: ' . BASE_URL . '/admin/users.php');
            exit();

        }

    }

    if (isset($request_values['role_bk'])) {

        $role = esc($request_values['role_bk']);

    }

}

function disable_user($id) {

    global $conn, $errors, $lang;

    $user_id = esc($id);

    $query = "UPDATE nn_users SET active=0 WHERE id = ? AND role != 'SuperAdmin'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    if ($stmt->affected_rows == 0) {

        array_push($errors, $lang['error_disable_superadmin']);

    }
    else {

        header('location: ' . BASE_URL . '/admin/users.php');
        exit();
        
    }    
}

function get_users($page, $limit, $order_by, $order) {

    global $conn, $search_count_results, $search_count_users, $search_page;

    $limit = esc($limit);
    $page = esc($page);

    $offset = ($page - 1) * $limit;
    $search_page = $page;

    $sql_order = ($order == 'asc') ? 'ASC' : 'DESC';

    switch ($order_by) {

        case 'user':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users ORDER BY username " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        case 'role':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users ORDER BY role " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        case 'email':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users ORDER BY email " . $sql_order . " LIMIT ? OFFSET ?";
            break;
        
        case 'last_connection':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users ORDER BY last_connection " . $sql_order . " LIMIT ? OFFSET ?";
            break; 

        case 'creation_date':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users ORDER BY created_at " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        default:
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users ORDER BY username ASC LIMIT ? OFFSET ?";

    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    $search_count_users = count_users();
    $search_count_results = $result->num_rows;

    return mysqli_fetch_all($result, MYSQLI_ASSOC);

}

function get_users_by_search($search, $page, $limit, $order_by, $order) {

    global $conn, $search_count_results, $search_count_users, $search_query, $search_page;

    $search = esc($search);
    $limit = esc($limit);
    $page = esc($page);
    
    $search_query = $search;
    $search = '%' . $search . '%';
    $offset = ($page - 1) * $limit;
    $search_page = $page;

    $query = "SELECT COUNT(*) AS total FROM nn_users WHERE username LIKE ? OR role LIKE ? OR email LIKE ? ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $search, $search, $search);
    $stmt->execute();

    $result = $stmt->get_result();

    $search_count_users = mysqli_fetch_assoc($result)['total'];

    $sql_order = ($order == 'asc') ? 'ASC' : 'DESC';

    switch ($order_by) {

        case 'user':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users WHERE username LIKE ? OR role LIKE ? OR email LIKE ? ORDER BY username " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        case 'role':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users WHERE username LIKE ? OR role LIKE ? OR email LIKE ? ORDER BY role " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        case 'email':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users WHERE username LIKE ? OR role LIKE ? OR email LIKE ? ORDER BY email " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        case 'last_connection':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users WHERE username LIKE ? OR role LIKE ? OR email LIKE ? ORDER BY last_connection " . $sql_order . " LIMIT ? OFFSET ?";
            break; 

        case 'creation_date':
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users WHERE username LIKE ? OR role LIKE ? OR email LIKE ? ORDER BY created_at " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        default:
            $query = "SELECT id, username, role, email, created_at, last_connection FROM nn_users WHERE username LIKE ? OR role LIKE ? OR email LIKE ? ORDER BY username ASC LIMIT ? OFFSET ?";

    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssii', $search, $search, $search, $limit, $offset);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $search_count_results = $result->num_rows;

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
    
}