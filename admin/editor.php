<?php include('../config.php'); ?>
<?php 
    if (empty($logged_user)) {
        header('location: ' . BASE_URL . '/login.php');
        exit();
    }
    else if (!has_privileges(PRIVILEGE_EDIT_POSTS)) {    
        header('location: ' . BASE_URL . '/redirect/403.html');
        exit();
    }
?>
<?php include(ROOT_PATH . '/admin/includes/header.php'); ?>
<?php if (isset($_GET['mode-post'])) : ?>
    <?php include(ROOT_PATH . '/admin/includes/functions/functions_post.php'); ?>
    <?php include(ROOT_PATH . '/admin/includes/functions/functions_topic.php'); ?>
    <?php $topics = get_all_topics(); ?>
    <?php if ($is_editing_post === true): ?>
        <title><?php echo $lang['edit_post']; ?> - <?php echo $webpage_settings['title']; ?></title>
    <?php else : ?>
        <title><?php echo $lang['create_post']; ?> - <?php echo $webpage_settings['title']; ?></title>
    <?php endif ?>
<?php elseif (isset($_GET['mode-page'])) : ?>
    <?php include(ROOT_PATH . '/admin/includes/functions/functions_page.php'); ?>
    <?php if ($is_editing_page === true): ?>
        <title><?php echo $lang['edit_page']; ?> - <?php echo $webpage_settings['title']; ?></title>
    <?php else : ?>
        <title><?php echo $lang['create_page']; ?> - <?php echo $webpage_settings['title']; ?></title>
    <?php endif ?>
<?php else : ?>
<?php 
    header('location: ' . BASE_URL . '/index.php');
    exit();
?>
<?php endif ?>
</head>
<body>
<div>
    <?php include(ROOT_PATH . '/admin/includes/navbar.php') ?>
    <?php include(ROOT_PATH . '/admin/includes/menu.php') ?>
	<div class="container content">
		<div class="action create-post-div">     
        <?php if (isset($_GET['mode-post'])) : ?>
            <?php include(ROOT_PATH . '/admin/includes/editor_post.php') ?>
        <?php elseif (isset($_GET['mode-page'])) : ?>
            <?php include(ROOT_PATH . '/admin/includes/editor_page.php') ?>
        <?php endif ?>    
		</div>
	</div>
</div>
</body>
</html>
<script>
    CKEDITOR.replace('body');
</script>