<?php

include_once(ROOT_PATH . '/includes/functions/functions_global.php');

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

$page_id = 0;
$is_editing_page = false;
$published = 0;
$title = '';
$page_slug = '';
$body = '';

$search_count_results = 0;
$search_count_pages = 0;
$search_query = '';
$search_page = 1;
$search_limit = 10;

$order_by = 'title';
$order_type = 'desc';

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_POST['create_page'])) {

    create_page($_POST);

}

if (isset($_GET['edit-page'])) {

    $is_editing_page = true;
    $page_id = $_GET['edit-page'];
    
    edit_page($page_id);

}

if (isset($_POST['update_page'])) {

    update_page($_POST);

}

if (isset($_GET['delete-page'])) {

    $page_id = $_GET['delete-page'];

    delete_page($page_id);

}

if (isset($_GET['publish'])) {

    $page_id = $_GET['publish'];

    page_publish_set($page_id, true);

}

if (isset($_GET['unpublish'])) {

    $page_id = $_GET['unpublish'];

    page_publish_set($page_id, false);
    
}

if (isset($_GET['search']) ) {

    if ($_GET['search'] === '') {

        header('location: ' . BASE_URL . '/admin/pages.php');
        exit(0);

    }

    $pag = isset($_GET['p']) ? esc($_GET['p']) : 1;
    
    $search = $_GET['search'];

    if (isset($_GET['order_by'])) {

        $order_by = $_GET['order_by'];
        $order = $_GET['order'];

    }
    else {

        $order_by = 'publish_date';
        $order = 'desc';

    }

    $pages = get_pages_by_search($search, max($pag, 1), 10, $order_by, $order);

}
else {

    $pag = isset($_GET['p']) ? esc($_GET['p']) : 1;

    if (isset($_GET['order_by'])) {

        $order_by = $_GET['order_by'];
        $order = $_GET['order'];

    }
    else {

        $order_by = 'publish_date';
        $order = 'desc';

    }

    $pages = get_pages(max($pag, 1), 10, $order_by, $order);

}

function count_pages() {

    global $conn;

    $query = "SELECT COUNT(*) AS total FROM nn_pages";
    $result = mysqli_query($conn, $query);

    if (!$result) {

        return null;
        
    }

    return mysqli_fetch_assoc($result)['total'];

}

function create_page($request_values) {

    global $conn, $logged_user, $title, $body, $published, $errors, $lang;

    $title = esc($request_values['title']);
    $body = htmlentities($request_values['body']);
    
    $page_slug = make_slug($title);

    if (empty($title)) { array_push($errors, $lang['error_title_needed']); }
    if (empty($body)) { array_push($errors, $lang['error_body_needed']); }

    $query = "SELECT slug FROM nn_posts WHERE slug = ? UNION SELECT slug FROM nn_pages WHERE slug = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $page_slug, $page_slug);
    $stmt->execute();

    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {

        array_push($errors, $lang['error_title_taken']);

    }

    if (isset($request_values['publish'])) {

        $published = 1;

        $query = "INSERT INTO nn_pages (title, slug, body, published) VALUES (?, ?, ?, 1)";

    }
    else {

        $published = 0;

        $query = "INSERT INTO nn_pages (title, slug, body, published) VALUES (?, ?, ?, 0)";

    }

    if (count($errors) == 0) {

        $stmt = $conn->prepare($query);
        $stmt->bind_param('sss', $title, $page_slug, $body);
        $stmt->execute();

        header('location: ' . BASE_URL . '/admin/pages.php');
        exit(0);

    }

}

function edit_page($id) {

    global $conn, $title, $page_slug, $page_id, $body, $published, $is_editing_page, $errors;

    $page_id = esc($id);
    $query = "SELECT * FROM nn_pages WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $page_id);
    $stmt->execute();

    $page = mysqli_fetch_assoc($stmt->get_result());
    
    $title = $page['title'] ?? '';
    $body = $page['body'] ?? '';
    $published = $page['published'] ?? '';

}

function update_page($request_values) {

    global $conn, $errors, $page_id, $title, $body, $published, $lang;

    $title = esc($request_values['title']);
    $body = esc($request_values['body']);
    $page_id = esc($request_values['page_id']);

    $page_slug = make_slug($title);

    if (empty($title)) { array_push($errors, $lang['error_title_needed']); }
    if (empty($body)) { array_push($errors, $lang['error_body_needed']); }

    $query = "SELECT slug FROM nn_posts WHERE slug = ? UNION SELECT slug FROM nn_pages WHERE slug = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $page_slug, $page_slug);
    $stmt->execute();

    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {

        array_push($errors, $lang['error_title_taken']);

    }
    
    if (isset($request_values['publish'])) {

        $published = 1;

        $query = "UPDATE nn_pages SET title = ?, slug = ?, body = ?, published = ? WHERE id = ?";

    }
    else {

        $published = 0;

        $query = "UPDATE nn_pages SET title = ?, slug = ?, body = ?, published = ? WHERE id = ?";

    }

    if (count($errors) == 0) {

        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssii', $title, $page_slug, $body, $published, $page_id);
        $stmt->execute();

        header('location: ' . BASE_URL . '/admin/pages.php');
        exit(0);
        
    }

}

function delete_page($id) {

    global $conn;

    $page_id = esc($id);

    $query = "DELETE FROM nn_pages WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $page_id);
    
    if ($stmt->execute()) {

        header('location: ' . BASE_URL . '/admin/pages.php');
        exit(0);

    }

}

function page_publish_set($id, $publish) {

    global $conn;

    $page_id = esc($id);

    if ($publish == true) {

        $query = "UPDATE nn_pages SET published=1 WHERE id = ?";

    }
    else {

        $query = "UPDATE nn_pages SET published=0 WHERE id = ?";

    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $page_id);
    
    if ($stmt->execute()) {

        header('location: ' . BASE_URL . '/admin/pages.php');
        exit();

    }

}

function get_pages($page, $limit, $order_by, $order) {

    global $conn, $search_count_results, $search_count_pages, $search_page;

    $limit = esc($limit);
    $page = esc($page);

    $offset = ($page - 1) * $limit;
    $search_page = $page;

    $sql_order = ($order == 'asc') ? 'ASC' : 'DESC';

    switch ($order_by) {

        case 'title':
            $query = "SELECT * FROM nn_pages ORDER BY title " . $sql_order . ", published DESC LIMIT ? OFFSET ?";
            break;

        case 'published':
            $query = "SELECT * FROM nn_pages ORDER BY published " . $sql_order . ", title DESC LIMIT ? OFFSET ?";
            break;
            
        default:
            $query = "SELECT * FROM nn_pages ORDER BY title DESC, published DESC LIMIT ? OFFSET ?";

    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    $search_count_pages = count_pages();
    $search_count_results = $result->num_rows;

    return mysqli_fetch_all($result, MYSQLI_ASSOC);

}

function get_pages_by_search($search, $page, $limit, $order_by, $order) {

    global $conn, $search_count_results, $search_count_pages, $search_query, $search_page;

    $search = esc($search);
    $limit = esc($limit);
    $page = esc($page);
    
    $search_query = $search;
    $search = '%' . $search . '%';
    $offset = ($page - 1) * $limit;
    $search_page = $page;

    $query = "SELECT COUNT(*) AS total FROM nn_pages WHERE title LIKE ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $search);
    $stmt->execute();

    $result = $stmt->get_result();

    $search_count_pages = mysqli_fetch_assoc($result)['total'];

    $sql_order = ($order == 'asc') ? 'ASC' : 'DESC';

    switch ($order_by) {

        case 'title':
            $query = "SELECT * FROM nn_pages WHERE title LIKE ? ORDER BY title " . $sql_order . ", published DESC LIMIT ? OFFSET ?";
            break;

        case 'published':
            $query = "SELECT * FROM nn_pages WHERE title LIKE ? ORDER BY published " . $sql_order . ", title DESC LIMIT ? OFFSET ?";
            break;
            
        default:
            $query = "SELECT * FROM nn_pages WHERE title LIKE ? ORDER BY title DESC, published DESC LIMIT ? OFFSET ?";

    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param('sii', $search, $limit, $offset);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $search_count_results = $result->num_rows;

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
    
}