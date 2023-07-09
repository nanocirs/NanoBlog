<?php

include_once(ROOT_PATH . '/includes/functions/functions_global.php');

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

$page = 1;
$page_limit = 6;
$total_posts = 0;
$posts = null;
$is_page = false;

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_GET['post-slug'])) {

    $post = get_post($_GET['post-slug']);

}

function get_post($slug) {

    global $conn, $logged_user, $is_page;

    $slug = esc($slug);

    if (has_privileges(PRIVILEGE_MODERATE_POSTS)) { 
    
        $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE slug = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $slug);

    }
    else if (has_privileges(PRIVILEGE_EDIT_POSTS)) {

        $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE (slug = ? AND published=1) OR (slug = ? AND published=0 AND user_id = ?) LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssi', $slug, $slug, $logged_user['id']);

    }
    else {

        $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE slug = ? AND published=1 LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $slug);
        
    }

    $stmt->execute();

    $result = $stmt->get_result();
    $num_rows = $result->num_rows;

    if ($num_rows > 0) {

        $post = mysqli_fetch_assoc($result);
        
        if ($post) {
            
            $post['topic'] = get_post_topic($post['id']);
            
            $query = "UPDATE nn_posts SET views=views + 1 WHERE id = ? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $post['id']);
            $stmt->execute();
        
        }
            
        return $post;

    }
    else {

        $is_page = true;
        
        if (has_privileges(PRIVILEGE_EDIT_PAGES)) { 
    
            $query = "SELECT * FROM nn_pages WHERE slug = ? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $slug);
    
        }
        else {
    
            $query = "SELECT * FROM nn_pages WHERE slug = ? AND published=1 LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $slug);
            
        }

        $stmt->execute();

        $result = $stmt->get_result();

        return mysqli_fetch_assoc($result);
        
    }
    
}

function get_all_topics() {

    global $conn;

    $query = "SELECT * FROM nn_topics";
    $result = mysqli_query($conn, $query);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);

}

function get_published_topics() {

    global $conn;

    $query = "SELECT nn_topics.name, nn_topics.slug, COUNT(nn_post_topic.topic_id) AS topic_count FROM nn_topics 
              JOIN nn_post_topic ON nn_topics.id=nn_post_topic.topic_id 
              JOIN nn_posts ON nn_posts.id=nn_post_topic.post_id WHERE nn_posts.published=1 
              GROUP BY nn_topics.name, nn_topics.slug ORDER BY topic_count DESC LIMIT 5";
              
    $result = mysqli_query($conn, $query);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);

}

function get_all_posts() {

    global $conn;

    $query = "SELECT * FROM nn_posts";
    $result = mysqli_query($conn, $query);

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['topic'] = get_post_topic($post['id']);
        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_recent_posts($quantity) {

    global $conn;

    $quantity = esc($quantity);

    $query = "SELECT * FROM nn_posts WHERE published=1 ORDER BY published_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $quantity);
    $stmt->execute();

    $result = $stmt->get_result();

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['topic'] = get_post_topic($post['id']);
        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_published_dates() {
    
    global $conn;

    $query = "SELECT DISTINCT DATE_FORMAT(published_at, '%M %Y') AS date, DATE_FORMAT(published_at, '%m') AS month, DATE_FORMAT(published_at, '%Y') AS year, published_at FROM nn_posts WHERE published=1 ORDER BY published_at DESC";
    $result = mysqli_query($conn, $query);

    if ($result->num_rows > 0) {

        return mysqli_fetch_all($result, MYSQLI_ASSOC);

    }
    else {

        return array();

    }

}

function get_published_posts() {

    global $conn;

    $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE published=1 ORDER BY nn_posts.published_at DESC";
    $result = mysqli_query($conn, $query);

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['topic'] = get_post_topic($post['id']);
        $post['summary'] = nl2br($post['summary']);
        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_published_posts_by_page($page, $limit) {

    global $conn, $total_posts;

    $limit = esc($limit);
    $page = esc($page);

    $offset = ($page - 1) * $limit;

    $query = "SELECT COUNT(*) AS total FROM nn_posts WHERE published=true";
    $result = mysqli_query($conn, $query);

    $total_posts = mysqli_fetch_assoc($result)['total'];

    $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE published=1 ORDER BY nn_posts.published_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();

    if (mysqli_num_rows($result) > 0) {

        $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $final_posts = array();

        foreach ($posts as $post) {

            $post['topic'] = get_post_topic($post['id']);
            $post['summary'] = nl2br(stripcslashes($post['summary']));
            array_push($final_posts, $post);

        }

        return $final_posts;

    } 

}

function get_published_posts_by_search_by_page($search, $page, $limit) {

    global $conn, $topic_id, $total_posts;

    $search = esc($search);

    if ($search === '') {
        
        header('location: ' . BASE_URL . '/index.php');
        exit();

    }

    $limit = esc($limit);
    $page = esc($page);
    $search = '%' . $search . '%';

    $query = "SELECT COUNT(*) AS total FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE nn_posts.title LIKE ? OR nn_users.username LIKE ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $search, $search);
    $stmt->execute();

    $result = $stmt->get_result();

    $total_posts = mysqli_fetch_assoc($result)['total'];

    $offset = ($page - 1) * $limit;
    
    $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE nn_posts.title LIKE ? OR nn_users.username LIKE ? AND published = 1 ORDER BY nn_posts.published_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssii', $search, $search, $limit, $offset);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['topic'] = get_post_topic($post['id']);
        $post['summary'] = nl2br($post['summary']);
        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_published_posts_by_topic($slug) {

    global $conn, $topic_id, $total_posts;
    
    $topic_id = get_topic_id_by_slug(esc($slug));

    $query = "SELECT COUNT(*) AS total FROM
                (SELECT * FROM nn_posts WHERE id IN 
                (SELECT post_id FROM nn_post_topic WHERE topic_id = ? 
                GROUP BY post_id HAVING COUNT(1)=1) AND published=1
             ) AS SUBQUERY";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $total_posts = mysqli_fetch_assoc($result)['total'];

    $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE nn_posts.id IN 
             (SELECT post_id FROM nn_post_topic WHERE topic_id = ? 
              GROUP BY post_id HAVING COUNT(1)=1) AND published=1 ORDER BY nn_posts.published_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['topic'] = get_post_topic($post['id']);
        $post['summary'] = nl2br($post['summary']);
        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_published_posts_by_topic_by_page($slug, $page, $limit) {

    global $conn, $topic_id, $total_posts;
    
    $topic_id = get_topic_id_by_slug(esc($slug));
    $limit = esc($limit);
    $page = esc($page);

    $offset = ($page - 1) * $limit;

    $query = "SELECT COUNT(*) AS total FROM
                (SELECT * FROM nn_posts WHERE id IN 
                (SELECT post_id FROM nn_post_topic WHERE topic_id = ? 
                GROUP BY post_id HAVING COUNT(1)=1) AND published=1
             ) AS SUBQUERY";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $total_posts = mysqli_fetch_assoc($result)['total'];

    $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE nn_posts.id IN 
             (SELECT post_id FROM nn_post_topic WHERE topic_id = ? 
              GROUP BY post_id HAVING COUNT(1)=1) AND published=1 ORDER BY nn_posts.published_at DESC LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iii', $topic_id, $limit, $offset);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['topic'] = get_post_topic($post['id']);
        $post['summary'] = nl2br($post['summary']);

        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_published_posts_by_author($author) {

    global $conn, $topic_id, $total_posts;

    $author = esc($author);

    $query = "SELECT COUNT(*) AS total FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE username = ? AND published=1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $author);
    $stmt->execute();

    $result = $stmt->get_result();

    $total_posts = mysqli_fetch_assoc($result)['total'];
    
    $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE username = ? AND published = 1 ORDER BY nn_posts.published_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $author);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['topic'] = get_post_topic($post['id']);
        $post['summary'] = nl2br($post['summary']);

        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_published_posts_by_author_by_page($author, $page, $limit) {

    global $conn, $topic_id, $total_posts;

    $author = esc($author);
    $limit = esc($limit);
    $page = esc($page);

    $offset = ($page - 1) * $limit;

    $query = "SELECT COUNT(*) AS total FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE username = ? AND published=1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $author);
    $stmt->execute();

    $result = $stmt->get_result();

    $total_posts = mysqli_fetch_assoc($result)['total'];
    
    $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE username = ? AND published = 1 ORDER BY nn_posts.published_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sii', $author, $limit, $offset);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['topic'] = get_post_topic($post['id']);
        $post['summary'] = nl2br($post['summary']);

        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_published_posts_by_month_year_page($month, $year, $page, $limit) {

    global $conn, $topic_id, $total_posts;

    $date = esc($month) . ' ' . esc($year);
    $limit = esc($limit);
    $page = esc($page);

    $offset = ($page - 1) * $limit;

    $query = "SELECT COUNT(*) AS total FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE DATE_FORMAT(published_at, '%m %Y') = ? AND published=1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $date);
    $stmt->execute();

    $result = $stmt->get_result();

    $total_posts = mysqli_fetch_assoc($result)['total'];
    
    $query = "SELECT nn_posts.*, nn_users.username AS author FROM nn_posts JOIN nn_users ON nn_posts.user_id = nn_users.id WHERE DATE_FORMAT(published_at, '%m %Y') = ? AND published=1 ORDER BY nn_posts.published_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sii', $date, $limit, $offset);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = array();

    foreach ($posts as $post) {

        $post['topic'] = get_post_topic($post['id']);
        $post['summary'] = nl2br($post['summary']);
        
        array_push($final_posts, $post);

    }

    return $final_posts;

}

function get_topic_name_by_id($id) {

    global $conn;

    $id = esc($id);
    
    $query = "SELECT name FROM nn_topics WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        $topic = mysqli_fetch_assoc($result);
        return $topic['name'];

    }
    else {

        return null;

    }

}

function get_topic_name_by_slug($slug) {

    global $conn;

    $slug = esc($slug);
    
    $query = "SELECT name FROM nn_topics WHERE slug = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        $topic = mysqli_fetch_assoc($result);
        return $topic['name'];

    }
    else {

        return null;

    }

}

function get_topic_id_by_slug($slug) {

    global $conn;
    
    $slug = esc($slug);

    $query = "SELECT * FROM nn_topics WHERE slug = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        $topic = mysqli_fetch_assoc($result);
        return $topic['id'];

    }
    else {

        header('location: ' . BASE_URL . '/index.php');
        exit();

    }


}

function get_post_topic($post_id) {

    global $conn;

    $query = "SELECT * FROM nn_topics WHERE id = (SELECT topic_id FROM nn_post_topic WHERE post_id = ?) LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    
    $result = $stmt->get_result();

    return mysqli_fetch_assoc($result);

}