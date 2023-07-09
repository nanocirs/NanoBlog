<?php

include_once(ROOT_PATH . '/includes/functions/functions_global.php');

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

$topic_id = 0;
$is_editing_topic = false;
$topic_name = '';

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_POST['create_topic'])) { 

    create_topic($_POST); 

}

if (isset($_GET['edit-topic'])) {

    $is_editing_topic = true;
    $topic_id = $_GET['edit-topic'];

    edit_topic($topic_id);

}

if (isset($_POST['update_topic'])) {

    update_topic($_POST);

}

if (isset($_GET['delete-topic'])) {

    $topic_id = $_GET['delete-topic'];

    delete_topic($topic_id);

}

function get_all_topics() {

    global $conn;

    $query = "SELECT * FROM nn_topics";
    $result = mysqli_query($conn, $query);
    
    $topics = mysqli_fetch_all($result, MYSQLI_ASSOC);

    if (!$result) {

        return null;
        
    }

    $final_topics = array();

    foreach ($topics as $topic) {

        $topic['count'] = get_posts_count_by_topic($topic['id']);

        array_push($final_topics, $topic);

    }

    return $final_topics;

}

function get_posts_count_by_topic($topic_id) {

    global $conn;

    $query = "SELECT COUNT(*) AS total FROM nn_post_topic WHERE topic_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        return mysqli_fetch_assoc($result)['total'];

    }
    else {

        return 0;
        
    }

}

function create_topic($request_values) {

    global $conn, $errors, $topic_name, $lang;

    $topic_name = esc($request_values['topic_name']);

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
        
        header('location: ' . BASE_URL . '/admin/topics.php');
        
        exit();
        
    }

}

function edit_topic($topic_id) {

    global $conn, $topic_name, $is_editing_topic, $topic_id;

    $query = "SELECT * FROM nn_topics WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $topic = mysqli_fetch_assoc($result);

    $topic_name = $topic['name'];

}

function update_topic($request_values) {

    global $conn, $errors, $topic_name, $topic_id, $lang;

    $topic_name = esc($request_values['topic_name']);
    $topic_id = esc($request_values['topic_id']);

    $topic_slug = make_slug($topic_name);

    if (empty($topic_name)) {

        array_push($errors, $lang['error_topic_name_needed']);

    }

    $query = "SELECT * FROM nn_topics WHERE name = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $topic_name);
    $stmt->execute();

    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {
        
        array_push($errors, $lang['error_topic_exists']);
    
    }
        
    if (count($errors) == 0) {

        $query = "UPDATE nn_topics SET name = ?, slug = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssi', $topic_name, $topic_slug, $topic_id);
        $stmt->execute();

        header('location: ' . BASE_URL . '/admin/topics.php');

        exit();

    }

}

function delete_topic($topic_id) {

    global $conn;

    $query = "DELETE FROM nn_topics WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();

    header('location: ' . BASE_URL . '/admin/topics.php');

    exit();
    
}