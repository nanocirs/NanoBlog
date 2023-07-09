<?php 

include_once(ROOT_PATH . '/includes/functions/functions_global.php');

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

$comments_nest = null;
$comments_container = null;

if (!isset($errors)) {

    $errors = [];
    
}

if (isset($_POST['comment'])) {

    post_comment($_POST);

}
else if (isset($_POST['reply'])) {

    post_reply($_POST);

}
else if (isset($_POST['delete_id'])) {
    
    delete_comment($_POST);
    
}

function post_comment($request_values) {

    global $conn, $logged_user, $errors, $lang;

    $body = nl2br(stripcslashes(esc($request_values['comment'])));
    $post_id = esc($request_values['post_id']);
    $user_id = esc($logged_user['id']);

    if ($body === '') {
        
        array_push($errors, $lang['error_comment_needed']);

    }

    if (count($errors) === 0) {

        $query = "INSERT INTO nn_comments (post_id, user_id, body, created_at) VALUES (?, ?, ?, now())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iis', $post_id, $user_id, $body);
        $stmt->execute();
        
    }

}

function post_reply($request_values) {

    global $conn, $logged_user, $errors, $lang;

    if ($logged_user) {

        $user_id = esc($logged_user['id']);

    }
    else {

        array_push($errors, $lang['error_session_expired_login_again']);
        
    }
    $body = nl2br(stripcslashes(esc($request_values['reply'])));
    $post_id = esc($request_values['post_id']);
    $parent_id = esc($request_values['parent_id']);

    if ($body === '') {
        
        array_push($errors, $lang['error_reply_needed']);

    }

    if (count($errors) === 0) {

        $query = "INSERT INTO nn_comments (post_id, user_id, body, parent_id, created_at) VALUES (?, ?, ?, ?, now())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iisi', $post_id, $user_id, $body, $parent_id);
        $stmt->execute();
        
    }

}

function delete_comment($request_values) {

    global $conn, $errors;

    $comment_id = esc($request_values['delete_id']);

    if (count($errors) === 0) {

        $query = "DELETE FROM nn_comments WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $comment_id);
        $stmt->execute();

    }

}

function load_comments($post_id) {

    global $conn, $comments_container, $comments_nest;

    $post_id = esc($post_id);

    $query = "SELECT nn_comments.*, nn_users.username FROM nn_comments JOIN nn_users ON nn_users.id=nn_comments.user_id WHERE post_id = ? ORDER BY id ASC, parent_id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $comments_container = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $comments_nest = array();

    foreach ($comments_container as $key => $comment) {

        if ($comment['parent_id'] === null) {

            $comments_nest[$comment['id']] = array(

                'comment' => $comment,
                'replies' => array()

            );

        }
        else {

            $comments_nest[$comment['parent_id']]['replies'][$comment['id']] = build_nest($comment);     

        }

    }

    return $comments_nest;
    
}

function build_nest($comment) {

    global $comments_container;

    $return_reply = array(

        'comment' => $comment,
        'replies' => array()

    );

    foreach($comments_container as $key => $reply) {

        if($reply['parent_id'] === $comment['id']) {

            $return_reply['replies'][$reply['id']] = build_nest($reply);

        }

    }

    return $return_reply;

}

function build_comments($comment) {

    global $logged_user, $lang;

    $output = '';
    $output .= '<div class="main_reply_container">';
    $output .= '    <h3 id="comment_' . $comment['comment']['id'] . '">' . $comment['comment']['username'] . '</h3>';
    $output .= '    <h4> ' . strftime($lang['complete_datetime'], strtotime($comment['comment']['created_at'])) . '</h4>';
    $output .= '    <p>' . $comment['comment']['body'] .'</p>';


    if ($logged_user) {

        if (has_privileges(PRIVILEGE_MODERATE_POSTS)) {

            $output .=  '<form method="post" id="delete_' . $comment['comment']['id'] . '" class="delete" action="">';

        }
        
        $output .=      '<p class="reply_options"><a class="reply_text" data-reply_id="reply_' . $comment['comment']['id'] . '">' . $lang['reply'] . '</a></p>';

        if (has_privileges(PRIVILEGE_MODERATE_POSTS)) {

            $output .=      '<p class="reply_options"><a class="delete_text" data-delete_id="delete_' . $comment['comment']['id'] . '">' . $lang['delete'] . '</a></p>';
            $output .=      '<input type="hidden" name="delete_id" value="' . $comment['comment']['id'] . '">';
            $output .=  '</form>';

        }

        $output .=      '<div class="reply_container reply_border" id="reply_' . $comment['comment']['id'] . '" style="display: none">';

        $output .=      '<form method="post" class="reply" action="">';
        $output .=          '<input type="hidden" name="post_id" value="' . $comment['comment']['post_id'] . '">';
        $output .=          '<input type="hidden" name="parent_id" value="' . $comment['comment']['id'] .'">';
        $output .=          '<label for="reply">' . $lang['reply'] . ':</label>';
        $output .=          '<textarea id="reply" name="reply" maxlength="960" required></textarea>';
        $output .=          '<button type="submit" class="btn">' . $lang['send_reply'] . '</button>';
        $output .=      '</form>';
        $output .=      '</div>';
        $output .= '</div>';

    }
    else {
        $output .= '</div>';

    }

    foreach ($comment['replies'] as $reply) {
        
        $output .= '<div class="reply_border">';

        $output .= build_comments($reply);
        $output .= '</div>';

    }

    return $output;

}