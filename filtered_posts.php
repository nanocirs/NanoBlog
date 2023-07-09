<?php include('config.php'); ?>
<?php include(ROOT_PATH . '/includes/functions/functions_public.php'); ?>
<?php  
    $page = isset($_GET['p']) ? max(esc($_GET['p']), 1) : 1;

    if (isset($_GET['search'])) {
        $posts = get_published_posts_by_search_by_page($_GET['search'], $page, $page_limit);
    }
    elseif (isset($_GET['topic'])) {
        $posts = get_published_posts_by_topic_by_page($_GET['topic'], $page, $page_limit); 
    }
    elseif (isset($_GET['author'])) {
        $posts = get_published_posts_by_author_by_page($_GET['author'], $page, $page_limit);   
    }
    elseif (isset($_GET['y']) && isset($_GET['m'])) {
        $year = esc($_GET['y']);
        $month = esc($_GET['m']);    
        $posts = get_published_posts_by_month_year_page($_GET['m'], $_GET['y'], $page, $page_limit);
    }
    else {        
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<?php include(ROOT_PATH . '/includes/header.php'); ?>
	<title><?php echo $lang['posts']; ?> - <?php echo $webpage_settings['title']; ?></title>
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
                <div class="search_title">
                <?php if (isset($_GET['author'])) : ?>
                    <?php if (count($posts) === 0) : ?>
                        <h1><?php echo $lang['no_posts_written_by']; ?> <i><?php echo $_GET['author']; ?></i>.</h1>
                    <?php else : ?>
                        <h1><?php echo $lang['posts_written_by']; ?> <i><?php echo $_GET['author']; ?></i>.</h1>
                    <?php endif ?>
                <?php elseif (isset($_GET['topic'])) : ?>
                <?php $topic_name = get_topic_name_by_slug($_GET['topic']); ?>
                    <?php if (count($posts) === 0) : ?>
                        <h1><?php echo $lang['no_posts_from_topic']; ?> <i><?php echo ($topic_name !== null) ? $topic_name : $_GET['topic']; ?></i>.</h1>
                    <?php else : ?>
                        <h1><?php echo $lang['posts_from_topic']; ?> <i><?php echo ($topic_name !== null) ? $topic_name : $_GET['topic']; ?></i>.</h1>
                    <?php endif ?>
                <?php elseif (isset($_GET['search'])) : ?>
                    <?php if (count($posts) === 0) : ?>
                        <h1><?php echo $lang['no_results_from']; ?> <i><?php echo $_GET['search']; ?></i>.</h1>
                    <?php else : ?>
                        <h1><?php echo $lang['results_from']; ?> <i><?php echo $_GET['search']; ?></i>.</h1>
                    <?php endif ?>
                <?php elseif (isset($_GET['y']) && isset($_GET['m'])) : ?>
                    <?php if (count($posts) === 0) : ?>
                        <h1><?php echo $lang['no_results_from']; ?> <i><?php echo get_month_from_number($_GET['m']) . ' de ' . $_GET['y']; ?></i>.</h1>
                    <?php else : ?>
                        <h1><?php echo $lang['results_from']; ?> <i><?php echo get_month_from_number($_GET['m']) . ' de ' . $_GET['y']; ?></i>.</h1>
                    <?php endif ?>
                <?php else : ?>
                        <?php if ($posts === null) : ?>
                            <h1><?php echo $lang['no_results']; ?></h1>
                        <?php endif ?>
                <?php endif ?>
                </div>
            <?php if (isset($posts)) : ?>
                <?php foreach ($posts as $post): ?>
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
                                <h1><a href="<?php echo $post['slug']; ?>"><?php echo $post['title'] ?></a></h1>
                                <?php if ($post['published_at'] !== null) : ?>
                                    <h2><?php echo $lang['published_on']; ?> <?php echo strftime($lang['datetime'], strtotime($post['published_at'])); ?> <?php echo $lang['by']; ?> <a href="/filtered_posts.php?author=<?php echo $post['author']; ?>"><?php echo $post['author']; ?></a></h2>
                                <?php endif ?>
                            </div>
                        </div>
                        <div class="post_summary">
                            <p><?php echo $post['summary']; ?></p>
                        </div>
                    </div>
                <?php endforeach ?>             
                <?php if ($total_posts > $page_limit) : ?>
                    <div class="navigation">
                        <p>
                        <?php if ($page > 1) :?>
                            <a href="filtered_posts.php?<?php if (isset($_GET['author'])) : ?>author=<?php echo $_GET['author']; ?><?php elseif (isset($_GET['topic'])) : ?>topic=<?php echo $_GET['topic']; ?><?php elseif (isset($_GET['search'])) : ?>search=<?php echo $_GET['search']; ?><?php endif ?>&p=<?php echo $pag = $page - 1; ?>"><?php echo $lang['return']; ?></a>
                        <?php endif ?>
                        </p>
                        <p>
                        <?php if (($page * $page_limit) < $total_posts) :?>
                            <a href="filtered_posts.php?<?php if (isset($_GET['author'])) : ?>author=<?php echo $_GET['author']; ?><?php elseif (isset($_GET['topic'])) : ?>topic=<?php echo $_GET['topic']; ?><?php elseif (isset($_GET['search'])) : ?>search=<?php echo $_GET['search']; ?><?php endif ?>&p=<?php echo $pag = $page + 1; ?>"><?php echo $lang['see_more']; ?></a>
                        <?php endif ?>
                        </p>
                    </div>
                <?php endif ?>
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