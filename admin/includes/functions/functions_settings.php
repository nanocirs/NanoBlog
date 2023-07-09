<?php

include_once(ROOT_PATH . '/includes/functions/functions_global.php');

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

$role_name = '';
$roles = array();

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_POST['apply_general_settings'])) {

    apply_general_settings($_POST);

}

if (isset($_POST['apply_page_settings'])) {

    apply_page_settings($_POST);

}

if (isset($_POST['apply_socials_settings'])) {

    apply_socials_settings($_POST);

}

if (isset($_POST['create_role'])) {

    create_role($_POST);

}

if (isset($_GET['delete-role'])) {

    $role_name = $_GET['delete-role'];

    delete_role($role_name);

}

if (isset($_POST['update_privileges'])) {

    update_privileges($_POST);
    
}

function apply_general_settings($request_values) {

    global $conn, $errors, $lang, $lang_index;

    $title = esc($request_values['web_title']);
    $header_title = esc($request_values['web_header_title']);
    $language = esc($request_values['web_language']);

    if (!array_key_exists($language, $lang_index)) {

        $language = $webpage_settings['language'];

    }

    $header_image = 0;
    if (!empty($_FILES['web_header_image']['tmp_name'])) {
        
        $header_image = $_FILES['web_header_image']['tmp_name'];
        $target = '../images/public/header_image.png';

        if (!convert_to_jpg($header_image, $target)) {

            array_push($errors, $lang['error_image_upload']);
    
        }
        else {

            $query = "UPDATE nn_settings SET header_image = 1";
            mysqli_query($conn, $query);

        }

    }

    if (count($errors) === 0) {

        $query = "UPDATE nn_settings SET title = ?, header_title = ?, language = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sss', $title, $header_title, $language);
        $stmt->execute();
        
        header('location: ' . BASE_URL . '/admin/settings.php');
        exit();
        
    }
    
}

function apply_page_settings($request_values) {

    global $conn, $errors;

    $page_slugs = json_decode($request_values['apply_page_settings'], true);

    $query = "DELETE FROM nn_pages_navbar";
    mysqli_query($conn, $query);

    if (count($page_slugs) > 0) {

        $query = "INSERT INTO nn_pages_navbar (page_id, position, name, url) VALUES ";
        
        foreach ($page_slugs as $key => $page_slug) {
            
            if ($page_slug['type'] == 'page') {

                $page_id = get_page_id_from_slug($page_slug['slug']);
                
                $query .= "(? , ?, '','') ";
                $bindParams[] = $page_id;
                $bindParams[] = $key;
                
                if ($key < count($page_slugs) - 1) {
                    
                    $query .= ", ";
                    
                }
            }

        }
        
        $stmt = $conn->prepare($query);
        
        if (!$stmt){
            
            echo $conn->error;
        }
        
        $stmt->bind_param(str_repeat('ii', count($page_slugs)), ...$bindParams);
        $stmt->execute();
        
    }

    header('location: ' . BASE_URL . '/admin/settings.php?page=pages');
    exit();


}

function apply_socials_settings($request_values) {

    global $conn;

    $socials_twitter = esc($request_values['socials_twitter']);
    $socials_reddit = esc($request_values['socials_reddit']);
    $socials_github = esc($request_values['socials_github']);
    $socials_facebook = esc($request_values['socials_facebook']);
    $socials_instagram = esc($request_values['socials_instagram']);
    $socials_youtube = esc($request_values['socials_youtube']);
    $socials_twitch = esc($request_values['socials_twitch']);
    $socials_linkedin = esc($request_values['socials_linkedin']);
    $socials_pinterest = esc($request_values['socials_pinterest']);
    $socials_tumblr = esc($request_values['socials_tumblr']);

    $query = "UPDATE nn_social SET twitter = ?, reddit = ?, github = ?, facebook = ?, instagram = ?, youtube = ?, twitch = ?, linkedin = ?, pinterest = ?, tumblr = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssssss', $socials_twitter, $socials_reddit, $socials_github, $socials_facebook, $socials_instagram, $socials_youtube, $socials_twitch, $socials_linkedin, $socials_pinterest, $socials_tumblr);
    $stmt->execute();

    header('location: ' . BASE_URL . '/admin/settings.php?page=social');
    exit();

}

function role_has_privilege($role, $privilege) {

    global $errors;

    $role_privileges = $role['privileges'];

    array_push($errors, $role['privileges']);

    if ((($role_privileges >> $privilege) & 1) == 1) {
        
        return true;
        
    }
    
    return false;
    
}

function create_role($request_values) {

    global $conn, $errors, $lang;

    $role = esc($request_values['role_name']);

    if (strlen($role) < 1) {

        array_push($errors, $lang['error_role_empty']);

    }

    $query = "SELECT * FROM nn_roles WHERE role = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $role);
    $stmt->execute();

    $results = $stmt->get_result();

    if (mysqli_num_rows($results) > 0) {

        array_push($errors, $lang['error_role_taken']);

    }

    if (count($errors) == 0) {

        $query = "INSERT INTO nn_roles (role, privileges) VALUES (?, 0)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $role);
        $stmt->execute();

        header('location: ' . BASE_URL . '/admin/settings.php?page=roles');
        exit(0);

    }

}

function delete_role($role_name) {

    global $conn, $errors, $lang;

    if ($role_name == 'SuperAdmin' || $role_name == 'Admin' || $role_name == 'Default') { array_push($errors, $lang['error_delete_protected']); }

    if (count($errors) == 0) {

        $query = "DELETE FROM nn_roles WHERE role = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $role_name);
        
        if ($stmt->execute()) {
            
            header('location: ' . BASE_URL . '/admin/settings.php?page=roles');
            exit(0);
            
        }
        
    }

}

function update_privileges($request_values) {

    global $conn, $roles;

    $json_privileges = $request_values['updated-privileges'];

    $privileges = json_decode($json_privileges, true);

    $role_names = array_keys($privileges);
    $role_privileges = array_values($privileges);

    foreach ($role_names as $key => $role_name) {

        if ($role_name == 'SuperAdmin' || $role_name == 'Admin' || $role_name == 'Default') {

            unset($role_names[$key]);
            unset($role_privileges[$key]);

        }

    }

    $bind_types = '';
    $bind_values = array();

    $placeholders = implode(', ', array_fill(0, count($role_names), '?'));

    $query = "UPDATE nn_roles SET privileges = CASE ";

    foreach ($role_names as $key => $role_name) {

        $query .= "WHEN role = ? THEN ? ";

        array_push($bind_values, $role_name);
        array_push($bind_values, $role_privileges[$key]);

        $bind_types .= 'si';

    }

    $query .= "END WHERE role IN (" . $placeholders . ")";

    foreach($role_names as $role_name) {
        
        array_push($bind_values, $role_name);

        $bind_types .= 's';

    }

    $stmt = $conn->prepare($query);

    array_unshift($bind_values, $stmt, $bind_types);

    $bind_params = array();

    foreach ($bind_values as $key => &$value) {

        $bind_params[$key] = &$value;

    };

    call_user_func_array('mysqli_stmt_bind_param', $bind_params);

    $stmt->execute();

}

function get_all_pages() {

    global $conn;

    $query = "SELECT title, slug FROM nn_pages";
    $result = mysqli_query($conn, $query);

    if (!$result) {

        return null;

    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);

}

function get_page_id_from_slug($slug) {

    global $conn;

    $slug = esc($slug);

    $query = "SELECT id FROM nn_pages WHERE slug = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $slug);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        return mysqli_fetch_assoc($result)['id'];

    }
    else {

        return null;

    }

}