<?php

include_once(ROOT_PATH . '/includes/functions/functions_global.php');

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

$post_id = 0;
$is_editing_post = false;
$published = 0;
$title = '';
$post_slug = '';
$body = '';
$image_name = '';
$topic_id = 0;
$post_topic = '';
$summary = '';

$search_count_results = 0;
$search_count_posts = 0;
$search_query = '';
$search_page = 1;
$search_limit = 10;

$order_by = 'publish_date';
$order_type = 'desc';

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_POST['create_post'])) {

    create_post($_POST);

}

if (isset($_GET['edit-post'])) {

    $is_editing_post = true;
    $post_id = $_GET['edit-post'];
    
    edit_post($post_id);

}

if (isset($_POST['update_post'])) {

    update_post($_POST);

}

if (isset($_GET['delete-post'])) {

    $post_id = $_GET['delete-post'];

    delete_post($post_id);

}

if (isset($_GET['publish'])) {

    $post_id = $_GET['publish'];

    post_publish_set($post_id, true);

}

if (isset($_GET['unpublish'])) {

    $post_id = $_GET['unpublish'];

    post_publish_set($post_id, false);
    
}

if (isset($_GET['search']) ) {

    if ($_GET['search'] === '') {

        header('location: ' . BASE_URL . '/admin/posts.php');
        exit(0);

    }

    $page = isset($_GET['p']) ? esc($_GET['p']) : 1;
    
    $search = $_GET['search'];

    if (isset($_GET['order_by'])) {

        $order_by = $_GET['order_by'];
        $order = $_GET['order'];

    }
    else {

        $order_by = 'publish_date';
        $order = 'desc';

    }

    $posts = get_posts_by_search($search, max($page, 1), 10, $order_by, $order);

}
else {

    $page = isset($_GET['p']) ? esc($_GET['p']) : 1;

    if (isset($_GET['order_by'])) {

        $order_by = $_GET['order_by'];
        $order = $_GET['order'];

    }
    else {

        $order_by = 'publish_date';
        $order = 'desc';

    }

    $posts = get_posts(max($page, 1), 10, $order_by, $order);

}

function count_posts() {

    global $conn;

    if (has_privileges(PRIVILEGE_MODERATE_POSTS)) { 

        $query = "SELECT COUNT(*) AS total FROM nn_posts";
        $result = mysqli_query($conn, $query);

    }
    else if (has_privileges(PRIVILEGE_EDIT_POSTS)) {

        global $logged_user;

        $query = "SELECT COUNT(*) AS total FROM nn_posts WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $logged_user['id']);
        $stmt->execute();

        $result = $stmt->get_result();

    }

    return mysqli_fetch_assoc($result)['total'];

}

function get_post_author_by_id($user_id) {

    global $conn;

    $query = "SELECT username FROM nn_users WHERE id= ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        return mysqli_fetch_assoc($result)['username'];

    }
    else {

        return null;
        
    }

}

function create_post($request_values) {

    global $conn, $logged_user, $errors, $title, $post_topic, $summary, $image_name, $topic_id, $body, $published, $lang;

    $title = esc($request_values['title']);
    $body = htmlentities($request_values['body']);
    $summary = nl2br(stripcslashes(esc($request_values['summary'])));
    $summary = str_replace('<br />', "\n", strip_tags($summary));

    if ($topic_id) {

        $topic_id = esc($request_values['topic_id']);

    }

    if ($summary === '') {

        array_push($errors, $lang['error_summary_needed']);

    }

    if (isset($request_values['topic_id']) && $request_values['topic_id'] !== '') {

        $topic_id = esc($request_values['topic_id']);

    }
    else if (isset($request_values['new_topic'])) {

        $topic_name = esc($request_values['new_topic']);
        $topic_slug = make_slug($topic_name);

        if (empty($topic_name)) {

            array_push($errors, $lang['error_topic_name_needed']);
            
        }

        $query = "SELECT * FROM nn_topics WHERE slug = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $topic_slug);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if (mysqli_num_rows($result) > 0) {
            
            array_push($errors, $lang['error_topic_exists']);
        
        }
        
        if (count($errors) == 0) {
            
            $query = "INSERT INTO nn_topics (name, slug) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $topic_name, $topic_slug);
            $stmt->execute();

            $topic_id = mysqli_insert_id($conn);
                        
        }
        
    }
    
    $post_slug = make_slug($title);

    if (empty($title)) { array_push($errors, $lang['error_title_needed']); }
    if (empty($body)) { array_push($errors, $lang['error_body_needed']); }
    if (empty($topic_id)) { array_push($errors, $lang['error_topic_needed']); }

    $featured_image = $_FILES['featured_image']['name'];

    if (empty($featured_image)) { array_push($errors, $lang['error_featured_image_needed']); }

    $extension = pathinfo($featured_image, PATHINFO_EXTENSION);

    $image_name = $post_slug . '.' . $extension;
    $target = '../images/posts/' . $image_name;


    if (!upload_thumbnail_image($_FILES['featured_image']['tmp_name'], $target)) {

        array_push($errors, $lang['error_image_upload']);

    }

    $query = "SELECT slug FROM nn_posts WHERE slug = ? UNION SELECT slug FROM nn_pages WHERE slug = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $post_slug, $post_slug);
    $stmt->execute();

    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {

    array_push($errors, $lang['error_title_taken']);

    }

    if (isset($request_values['publish'])) {

        $published = 1;

        $query = "INSERT INTO nn_posts (user_id, title, slug, image, body, summary, published, created_at, updated_at, published_at) VALUES (?, ?, ?, ?, ?, ?, 1, now(), NULL, now())";

    }
    else {

        $published = 0;

        $query = "INSERT INTO nn_posts (user_id, title, slug, image, body, summary, published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 0, now(), NULL)";

    }

    if (count($errors) == 0) {

        $stmt = $conn->prepare($query);
        $stmt->bind_param('isssss', $logged_user['id'], $title, $post_slug, $image_name, $body, $summary);
        
        if ($stmt->execute()) {

            $inserted_post_id = mysqli_insert_id($conn);

            $query = "INSERT INTO nn_post_topic (post_id, topic_id) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ii', $inserted_post_id, $topic_id);
            $stmt->execute();

            header('location: ' . BASE_URL . '/admin/posts.php');
            exit(0);

        }

    }

}

function edit_post($id) {

    global $conn, $title, $body, $summary, $published, $is_editing_post, $post_id, $image_name, $post_topic, $topic_id, $errors;

    $post_id = esc($id);

    $query = "SELECT * FROM nn_posts WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $post = mysqli_fetch_assoc($result);

    $query = "SELECT * FROM nn_post_topic JOIN nn_topics ON nn_post_topic.topic_id = nn_topics.id WHERE post_id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $post_topics = mysqli_fetch_assoc($result);

    if (isset($post['summary'])) {
        
        $summary = nl2br(stripcslashes(esc($post['summary'])));
        $summary = str_replace('<br />', "\n", strip_tags($summary));

    }

    $title = $post['title'] ?? '';
    $body = $post['body'] ?? '';
    $published = $post['published'] ?? '';
    $image_name = $post['image'];
    $post_topic = $post_topics['name'] ?? '';
    $topic_id = $post_topics['topic_id'] ?? 0;

}

function update_post($request_values) {

    global $conn, $errors, $post_id, $title, $topic_id, $body, $image_name, $published, $lang;

    $title = esc($request_values['title']);
    $body = htmlentities($request_values['body']);
    $summary = esc($request_values['summary']);
    $summary = nl2br(stripcslashes(esc($request_values['summary'])));
    $summary = str_replace('<br />', "\n", strip_tags($summary));
    $post_id = esc($request_values['post_id']);

    if ($summary === '') {

        array_push($errors, $lang['error_summary_needed']);

    }

    if (isset($request_values['topic_id']) && $request_values['topic_id'] !== '') {

        $topic_id = esc($request_values['topic_id']);

    }
    else if (isset($request_values['new_topic']) ) {

        $topic_name = esc($request_values['new_topic']);

        if ($topic_name !== '') {

            $topic_slug = make_slug($topic_name);

            $query = "SELECT * FROM nn_topics WHERE slug = ? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $topic_slug);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if (mysqli_num_rows($result) > 0) {
                
                array_push($errors, $lang['error_topic_exists']);
            
            }
        }
        else {

            array_push($errors, $lang['error_topic_name_needed']);
            
        }
        if (count($errors) == 0) {
            
            $query = "INSERT INTO nn_topics (name, slug) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $topic_name, $topic_slug);
            $stmt->execute();

            $topic_id = mysqli_insert_id($conn);
                        
        }
        
    }

    $post_slug = make_slug($title);

    if (empty($title)) { array_push($errors, $lang['error_title_needed']); }
    if (empty($body)) { array_push($errors, $lang['error_body_needed']); }
    
    if (!empty($_FILES['featured_image']['tmp_name'])) {

        $query = "SELECT * FROM nn_posts WHERE slug = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $post_slug);
        $stmt->execute();

        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {

            $old_image = mysqli_fetch_assoc($result)['image'];

            if ($old_image !== '') {

                if (file_exists('../images/posts/' . $old_image)) {
                    
                    unlink('../images/posts/' . $old_image);
                    
                }

            }

        }
        
        $featured_image = $_FILES['featured_image']['name'];
        $extension = pathinfo($featured_image, PATHINFO_EXTENSION);

        $image_name = $post_slug . '.' . $extension;

        $target = '../images/posts/' . $image_name;

        if (!upload_thumbnail_image($_FILES['featured_image']['tmp_name'], $target)) {

            array_push($errors, $lang['error_image_upload']);
    
        }

    }

    $query = "SELECT slug, id AS post_id FROM nn_posts WHERE slug = ? UNION SELECT slug, id AS page_id FROM nn_pages WHERE slug = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $post_slug, $post_slug);
    $stmt->execute();

    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {

        if (mysqli_fetch_assoc($result)['post_id'] != $post_id) {

            array_push($errors, $lang['error_title_taken']);

        }

    }

    if (isset($request_values['publish'])) {

        $published = 1;

        $query = "UPDATE nn_posts SET title = ?, slug = ?, views = 0, image = ?, body = ?, summary = ?, published = ?, updated_at = NOW(), published_at = IFNULL(published_at, NOW()) WHERE id = ?";

    }
    else {

        $published = 0;

        $query = "UPDATE nn_posts SET title = ?, slug = ?, views = 0, image = ?, body = ?, summary = ?, published = ?, updated_at = NOW() WHERE id = ?";


    }

    if (count($errors) == 0) {

        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssssii', $title, $post_slug, $image_name, $body, $summary, $published, $post_id);
        
        if ($stmt->execute()) {

            if (isset($topic_id)) {   

                $query = "UPDATE nn_post_topic SET topic_id = ? WHERE post_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('ii', $topic_id, $post_id);
                $stmt->execute();

                if ($stmt->affected_rows === 0) {

                    $query = "INSERT INTO nn_post_topic (post_id, topic_id) VALUES (?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('ii', $post_id, $topic_id);
                    $stmt->execute();
        
                }
                
            }
            
            header('location: ' . BASE_URL . '/admin/posts.php');
            exit(0);
            
        }
        
    }

}

function delete_post($id) {

    global $conn;

    $post_id = esc($id);

    $query = "SELECT * FROM nn_posts WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {

        $post_image = mysqli_fetch_assoc($result)['image'];

        if (file_exists('../images/posts/' . $post_image)) {

            unlink('../images/posts/' . $post_image);

        }

    }

    $query = "DELETE FROM nn_posts WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $post_id);
    
    if ($stmt->execute()) {

        header('location: ' . BASE_URL . '/admin/posts.php');
        exit(0);

    }
}

function post_publish_set($id, $publish) {

    global $conn;

    $post_id = esc($id);

    if ($publish == true) {

        $query = "UPDATE nn_posts SET published=1, published_at=now() WHERE id = ?";

    }
    else {

        $query = "UPDATE nn_posts SET published=0 WHERE id = ?";

    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $post_id);
    
    if ($stmt->execute()) {

        header('location: ' . BASE_URL . '/admin/posts.php');
        exit(0);

    }

}

function get_posts($page, $limit, $order_by, $order) {

    global $conn, $search_count_results, $search_count_posts, $search_page;

    $limit = esc($limit);
    $page = esc($page);

    $offset = ($page - 1) * $limit;
    $search_page = $page;

    $sql_order = ($order == 'asc') ? 'ASC' : 'DESC';

    switch ($order_by) {

        case 'title':
            $query = "SELECT * FROM nn_posts ORDER BY title " . $sql_order . ", views DESC, published_at DESC LIMIT ? OFFSET ?";
            break;

        case 'author':
            $query = "SELECT nn_posts.user_id, title, published_at, username, views, comments, nn_posts.updated_at, published_at, published FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id ORDER BY nn_users.username " . $sql_order . ", views DESC, published_at DESC LIMIT ? OFFSET ?";
            break;

        case 'views':
            $query = "SELECT * FROM nn_posts ORDER BY views " . $sql_order . ", published_at DESC LIMIT ? OFFSET ?";
            break;

        case 'last_update':
            $query = "SELECT * FROM nn_posts ORDER BY updated_at " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        case 'publish_date':
            $query = "SELECT * FROM nn_posts ORDER BY published_at " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        case 'published':
            $query = "SELECT * FROM nn_posts ORDER BY published " . $sql_order . ", views DESC, published_at DESC LIMIT ? OFFSET ?";
            break;

        case 'comments':
            $query = "SELECT * FROM nn_posts ORDER BY comments " . $sql_order . ", views DESC, published_at DESC LIMIT ? OFFSET ?";
            break;
            
        default:
            $query = "SELECT * FROM nn_posts ORDER BY published_at DESC, views DESC LIMIT ? OFFSET ?";

    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    $search_count_posts = count_posts();
    $search_count_results = $result->num_rows;

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();
    foreach ($posts as $post) {

        $post['author'] = get_post_author_by_id($post['user_id']);
        
        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_posts_by_search($search, $page, $limit, $order_by, $order) {

    global $conn, $search_count_results, $search_count_posts, $search_query, $search_page;

    $search = esc($search);
    $limit = esc($limit);
    $page = esc($page);
    
    $search_query = $search;
    $search = '%' . $search . '%';
    $offset = ($page - 1) * $limit;
    $search_page = $page;

    $query = "SELECT COUNT(*) AS total FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE title LIKE ? OR nn_users.username LIKE ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $search, $search);
    $stmt->execute();

    $result = $stmt->get_result();

    $search_count_posts = mysqli_fetch_assoc($result)['total'];

    $sql_order = ($order == 'asc') ? 'ASC' : 'DESC';

    switch ($order_by) {

        case 'title':
            $query = "SELECT nn_posts.user_id, title, published_at, username, views, comments, nn_posts.updated_at, published_at, published FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE title LIKE ? OR nn_users.username LIKE ? ORDER BY title " . $sql_order . ", views DESC, published_at DESC LIMIT ? OFFSET ?";
            break;

        case 'author':
            $query = "SELECT nn_posts.user_id, title, published_at, username, views, comments, nn_posts.updated_at, published_at, published FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE title LIKE ? OR nn_users.username LIKE ? ORDER BY nn_users.username " . $sql_order . ", views DESC, published_at DESC LIMIT ? OFFSET ?";
            break;

        case 'views':
            $query = "SELECT nn_posts.user_id, title, published_at, username, views, comments, nn_posts.updated_at, published_at, published FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE title LIKE ? OR nn_users.username LIKE ? ORDER BY views " . $sql_order . ", published_at DESC LIMIT ? OFFSET ?";
            break;

        case 'last_update':
            $query = "SELECT nn_posts.user_id, title, published_at, username, views, comments, nn_posts.updated_at, published_at, published FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE title LIKE ? OR nn_users.username LIKE ? ORDER BY updated_at " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        case 'publish_date':
            $query = "SELECT nn_posts.user_id, title, published_at, username, views, comments, nn_posts.updated_at, published_at, published FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE title LIKE ? OR nn_users.username LIKE ? ORDER BY published_at " . $sql_order . " LIMIT ? OFFSET ?";
            break;

        case 'published':
            $query = "SELECT nn_posts.user_id, title, published_at, username, views, comments, nn_posts.updated_at, published_at, published FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE title LIKE ? OR nn_users.username LIKE ? ORDER BY published " . $sql_order . ", views DESC, published_at DESC LIMIT ? OFFSET ?";
            break;

        case 'comments':
            $query = "SELECT nn_posts.user_id, title, published_at, username, views, comments, nn_posts.updated_at, published_at, published FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE title LIKE ? OR nn_users.username LIKE ? ORDER BY comments " . $sql_order . ", views DESC, published_at DESC LIMIT ? OFFSET ?";
            break;
            
        default:
            $query = "SELECT nn_posts.user_id, title, published_at, username, views, comments, nn_posts.updated_at, published_at, published FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE title LIKE ? OR nn_users.username LIKE ? ORDER BY published_at DESC, views DESC LIMIT ? OFFSET ?";

    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssii', $search, $search, $limit, $offset);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $search_count_results = $result->num_rows;

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['author'] = get_post_author_by_id($post['user_id']);
        
        array_push($final_posts, $post);

    }

    return $final_posts;
    
}

function upload_thumbnail_image($temp_file, $path) {

    if (empty($temp_file)) {

        return false;

    }

    $image_data = getimagesize($temp_file);

    if ($image_data !== false) {

        $image_type = $image_data[2];

        if ($image_type === IMAGETYPE_JPEG) {

            $image = imagecreatefromjpeg($temp_file);

        } 
        elseif ($image_type === IMAGETYPE_PNG) {

            $image = imagecreatefrompng($temp_file);

        } 
        else {

            return false;

        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === 'jpg' || $extension === 'jpeg') {

            if (!imagejpeg($image, $path)) {

                imagedestroy($image);

                return false;

            }

        } 
        elseif ($extension === 'png') {

            if (!imagepng($image, $path)) {

                imagedestroy($image);

                return false;

            }

        } 
        else {

            imagedestroy($image);

            return false;

        }

        imagedestroy($image);

        return true;

    }

    return false;

}