<?php include('config.php'); ?>
<?php include(ROOT_PATH . '/includes/functions/functions_public.php'); ?>
<?php 
    if ($post == null) {
        header('location: ' . BASE_URL . '/index.php');
        exit();      
    }
    $topics = get_all_topics(); 
    $current_topic = get_post_topic($post['id']);
?>
<?php include('includes/header.php'); ?>
<title><?php echo $post['title'] ?> - <?php echo $webpage_settings['title']; ?></title>
</head>
<body>
<div>
    <?php include(ROOT_PATH . '/includes/navbar.php'); ?>
    <?php include(ROOT_PATH . '/includes/front.php'); ?>
    <?php include(ROOT_PATH . '/includes/panel_slide.php'); ?>
    <div class="content">
        <div class="container">
            <div class="main_panel main_panel_left">
                <?php include(ROOT_PATH . '/includes/panel_left.php'); ?>
            </div>
            <div class="main_panel main_panel_posts">
                <div class="post_container">
                    <div class="full_post">
                        <div class="reduced_max767">
                        <?php if (isset($current_topic)) : ?>
                            <h2><?php echo $lang['topic']; ?>: <a href="filtered_posts.php?topic=<?php echo $current_topic['slug']; ?>"><?php echo $current_topic['name']; ?></a></h2>
                        <?php endif ?>
                        </div>
                    <?php if (!$is_page) : ?>
                        <div class="post_image">
                            <img src="<?php echo BASE_URL . '/images/posts/' . $post['image']; ?>" alt="">
                        </div>
                    <?php endif ?>
                        <div class="post_body">
                            <h1 class="post-title"><?php echo $post['title']; ?></h1>
                            <?php if (!$is_page && $post['published_at'] !== null) : ?>
                                <h2 class="post_info"><?php echo $lang['published_on']; ?> <?php echo strftime($lang['datetime'], strtotime($post['published_at'])); ?> <?php echo $lang['by']; ?> <a href="/filtered_posts.php?author=<?php echo $post['author']; ?>"><?php echo $post['author']; ?></a>.</h2> 
                            <?php endif ?>
                            <?php echo html_entity_decode($post['body']); ?>
                            <?php if (isset($post['updated_at'])) : ?>
                                <br><p class="last_update"><i><?php echo $lang['last_modification']; ?>: <?php echo strftime($lang['datetime'], strtotime($post['updated_at'])); ?></i></p>
                            <?php endif ?>
                        </div>
                        <?php if (!$is_page && $webpage_settings['show_comments']) : ?>
                            <?php include(ROOT_PATH . '/includes/comments_area.php'); ?>
                        <?php endif ?>      
                    </div>
                </div>
            </div>
        <?php if (!$socials_empty) : ?>
            <div class="main_panel main_panel_socials">
                <?php include(ROOT_PATH . '/includes/panel_socials.php'); ?>
            </div>
        <?php endif ?>  
        </div>
    </div>
<?php include(ROOT_PATH . '/includes/footer.php'); ?>
<?php include(ROOT_PATH . '/includes/cookies_banner.php'); ?>