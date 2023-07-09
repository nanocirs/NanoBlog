<?php include('../config.php'); ?>
<?php 
    if (empty($logged_user)) {
        header('location: ' . BASE_URL . '/login.php');
        exit();
    }
    else if (!has_privileges(PRIVILEGE_EDIT_TOPICS)) { 
        header('location: ' . BASE_URL . '/redirect/403.html');
        exit();
    }
?>
<?php include(ROOT_PATH . '/admin/includes/functions/functions_topic.php'); ?>
<?php include(ROOT_PATH . '/admin/includes/header.php'); ?>
<?php $topics = get_all_topics(); ?>
    <title><?php echo $lang['topics']; ?> - <?php echo $webpage_settings['title']; ?></title>
</head>
<body>
    <div>
    <?php include(ROOT_PATH . '/admin/includes/navbar.php') ?>
    <?php include(ROOT_PATH . '/admin/includes/menu.php') ?>      
        <div class="container content">
            <div class="action">
                <h1 class="page-title"><?php echo $lang['topics']; ?></h1>
                <form method="post" action="<?php echo BASE_URL . '/admin/topics.php'; ?>" >
                    <?php include(ROOT_PATH . '/includes/errors.php') ?>
                    <?php if ($is_editing_topic === true): ?>
                        <input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>">
                    <?php endif ?>
                    <input type="text" name="topic_name" value="<?php echo $topic_name; ?>">
                    <?php if ($is_editing_topic === true): ?> 
                        <button type="submit" class="btn" name="update_topic"><?php echo $lang['update']; ?></button>
                    <?php else: ?>
                        <button type="submit" class="btn" name="create_topic"><?php echo $lang['add']; ?></button>
                    <?php endif ?>
                </form>
                <?php if (empty($topics)): ?>
                    <h2 style="text-align: center"><?php echo $lang['no_topics']; ?></h2>
                <?php else: ?>
                    <table class="table-data table-short">
                        <th style="width: 150px"><?php echo $lang['topic']; ?></th>
                        <th style="width: 130px"><?php echo $lang['posts']; ?></th>
                        <th style="width: 120px"><?php echo $lang['actions']; ?></th>
                    <?php foreach ($topics as $topic): ?>
                        <tr>
                            <td><?php echo $topic['name']; ?></td>
                            <td><?php echo $topic['count']; ?></td>
                            <td>
                                <a href="topics.php?edit-topic=<?php echo $topic['id']; ?>"><?php echo $lang['edit']; ?></a>
                                |
                                <a href="topics.php?delete-topic=<?php echo $topic['id']; ?>"><?php echo $lang['delete']; ?></a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </table>
                <?php endif ?>
            </div>
        </div>
    </div>
</body>
</html>