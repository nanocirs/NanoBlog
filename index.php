<?php require_once('config.php'); ?>
<?php require_once(ROOT_PATH . '/includes/functions/functions_login.php'); ?>
<?php require_once(ROOT_PATH . '/includes/functions/functions_public.php'); ?>
<?php require_once(ROOT_PATH . '/includes/header.php'); ?>
<?php
    $page = isset($_GET['p']) ? max(esc($_GET['p']), 1) : 1;
    $posts = get_published_posts_by_page($page, $page_limit);
?>
<title><?php echo $webpage_settings['title']; ?></title>
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
                <?php if (isset($posts) && count($posts) > 0) : ?>
                <?php if ($page == 1) : ?>
                    <div class="featured_post">         
                <?php else : ?>
                    <div class="post">
                <?php endif ?>
                        <div class="post_image">
                            <a href="<?php echo $posts[0]['slug']; ?>"><img src="<?php echo BASE_URL . '/images/posts/' . $posts[0]['image']; ?>" alt=""></a>
                        </div>
                        <div class="post_summary">
                        <?php if (count($posts) > 0) : ?>
                            <h1><a href="<?php echo $posts[0]['slug']; ?>"><?php echo $posts[0]['title'] ?></a></h1>
                        <?php else :?>
                            <h1><?php echo $lang['no_post_yet']; ?></h1>
                        <?php endif ?>
                        <?php if ($posts[0]['published_at'] !== null) : ?>
                            <h2><?php echo $lang['published_on']; ?> <?php echo strftime($lang['datetime'], strtotime($posts[0]['published_at'])); ?> <?php echo $lang['by']; ?> <a href="/filtered_posts.php?author=<?php echo $posts[0]['author']; ?>"><?php echo $posts[0]['author']; ?></a></h2>
                        <?php endif ?>
                            <p><?php echo $posts[0]['summary']; ?></p>
                        </div>  
                    </div>         
                    <div class="reduced_max767">
                        <div class="post_title_container">
                            <div class="post_image">
                                <a href="<?php echo $posts[0]['slug']; ?>"><img src="<?php echo BASE_URL . '/images/posts/' . $posts[0]['image']; ?>" alt=""></a>
                            </div>
                            <div class="post_title">
                                <?php if (count($posts) > 0) : ?>
                                <h1><a href="<?php echo $posts[0]['slug']; ?>"><?php echo $posts[0]['title'] ?></a></h1>
                                <?php else :?>
                                    <h1><?php echo $lang['no_post_yet']; ?></h1>
                                <?php endif ?>

                                <?php if ($posts[0]['published_at'] !== null) : ?>
                                    <h2><?php echo $lang['published_on']; ?> <?php echo strftime($lang['datetime'], strtotime($posts[0]['published_at'])); ?> <?php echo $lang['by']; ?> <a href="/filtered_posts.php?author=<?php echo $posts[0]['author']; ?>"><?php echo $posts[0]['author']; ?></a></h2>
                                <?php endif ?>
                            </div>
                        </div>
                        <div class="post_summary">
                            <p><?php echo $posts[0]['summary']; ?></p>
                        </div>  
                    </div>
                <?php foreach ($posts as $key => $post): ?>
                <?php if ($key > 0) : ?>
                <div class="post">
                    <div class="post_image">
                        <a href="<?php echo $post['slug']; ?>"><img src="<?php echo BASE_URL . '/images/posts/' . $post['image']; ?>" alt=""></a>
                    </div>         
                    <div class="post_summary">
                        <h1><a href="<?php echo $post['slug']; ?>"><?php echo $post['title'] ?></a></h1>
                        <?php if ($post['published_at'] !== null) : ?>
                        <h2><?php echo $lang['published_on']; ?> <?php echo strftime($lang['datetime'], strtotime($post['published_at'])); ?> <?php echo $lang['by']; ?> <a href="/filtered_posts.php?author=<?php echo $post['author']; ?>"><?php echo $post['author']; ?></a></h2>
                        <?php endif ?>
                        <p><?php echo $post['summary']; ?></p>
                    </div>
                </div>
                <div class="reduced_max767">
                    <div class="post_title_container">
                        <div class="post_image">
                            <a href="<?php echo $post['slug']; ?>"><img src="<?php echo BASE_URL . '/images/posts/' . $post['image']; ?>" alt=""></a>
                        </div>
                        <div class="post_title">
                            <?php if (count($posts) > 0) : ?>
                            <h1><a href="<?php echo $posts[0]['slug']; ?>"><?php echo $posts[0]['title'] ?></a></h1>
                            <?php else :?>
                                <h1><?php echo $lang['no_post_yet']; ?></h1>
                            <?php endif ?>
                            <?php if ($posts[0]['published_at'] !== null) : ?>
                                <h2><?php echo $lang['published_on']; ?> <?php echo strftime($lang['datetime'], strtotime($posts[0]['published_at'])); ?> <?php echo $lang['by']; ?> <a href="/filtered_posts.php?author=<?php echo $posts[0]['author']; ?>"><?php echo $posts[0]['author']; ?></a></h2>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="post_summary">
                        <p><?php echo $post['summary']; ?></p>
                    </div>
                </div>
                <?php endif ?>
                <?php endforeach ?> 
            <?php elseif (!isset($posts)) : ?>
                <h1><?php echo $lang['no_post_yet']; ?></h1>
            <?php endif ?>     
            </div>
        <?php if ($total_posts > $page_limit) : ?>
            <div class="navigation">
                <p>
                <?php if ($page > 1) :?>
                    <a href="index.php?p=<?php echo $pag = $page - 1; ?>"><?php echo $lang['return']; ?></a>
                <?php endif ?>
                </p>
                <p>
                <?php if (($page * $page_limit) < $total_posts) :?>
                    <a href="index.php?p=<?php echo $pag = $page + 1; ?>"><?php echo $lang['see_more']; ?></a>
                <?php endif ?>
                </p>
            </div>
            <?php endif ?>
        <?php if (!$socials_empty) : ?>
            <div class="main_panel main_panel_socials">
                <?php include(ROOT_PATH . '/includes/panel_socials.php'); ?>
            </div>
        <?php endif ?>   
        </div>
    </div>
<?php include(ROOT_PATH . '/includes/footer.php'); ?>
<?php include(ROOT_PATH . '/includes/cookies_banner.php'); ?>