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
<?php include(ROOT_PATH . '/admin/includes/functions/functions_post.php'); ?>
<?php include(ROOT_PATH . '/admin/includes/header.php'); ?>
<?php $count_posts = count_posts(); ?>
    <title><?php echo $lang['posts']; ?> - <?php echo $webpage_settings['title']; ?></title>
</head>
<body>
    <div>
        <?php include(ROOT_PATH . '/admin/includes/navbar.php') ?>
        <?php include(ROOT_PATH . '/admin/includes/menu.php') ?>
        <div class="container content">
            <div class="action">
            <?php if (has_privileges(PRIVILEGE_MODERATE_POSTS)) : ?>
                <h1><?php echo $lang['posts']; ?> (<?php echo $count_posts; ?>)</h1>
            <?php else : ?>
                <h1><?php echo $lang['my_posts']; ?></h1>
            <?php endif ?>          
            <form>
                <button class="btn" type="button" onclick="window.location.href='editor.php?mode-post'"><?php echo $lang['new_post']; ?></button>
                <input type="text" name="search">
                <button type="submit" class="btn"><?php echo $lang['search']; ?></button>

                <?php if (isset($_GET['search']) && $search_query !== '') : ?>
                    <span style="margin-left: 15px"><?php echo $lang['showing']; ?> <?php echo ($search_page - 1) * $search_limit + 1; ?>-<?php echo ($search_limit > $search_count_results) ? ($search_page - 1) * $search_limit + $search_count_results : ($search_page * $search_limit); ?> <?php echo $lang['of']; ?> <?php echo $search_count_posts; ?> <?php echo $lang['results']; ?> <?php echo $lang['_from']; ?> "<?php echo $search_query; ?>".</span>
                <?php else : ?>
                    <span style="margin-left: 15px"><?php echo $lang['showing']; ?> <?php echo ($search_page - 1) * $search_limit + 1; ?>-<?php echo ($search_limit > $search_count_results) ? ($search_page - 1) * $search_limit + $search_count_results : ($search_page * $search_limit); ?> <?php echo $lang['of']; ?> <?php echo $count_posts; ?> <?php echo $lang['results']; ?>.</span>
                <?php endif ?>
            </form>
            <?php if (empty($posts)): ?>
                <h2 style="text-align: center; margin-top: 20px;"><?php echo $lang['no_posts']; ?></h2>
            <?php else: ?>
                <table class="table-data">
                    <tr>
                        <th style="width: 20%">    
                            <a href="posts.php?order_by=title<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'title' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['title']; ?>
                            </a>
                        </th>
                        <th style="min-width: 120px">
                            <a href="posts.php?order_by=author<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'author' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['author']; ?>
                            </a>
                        </th>
                        <th style="min-width: 60px">
                            <a href="posts.php?order_by=views<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'views' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['views']; ?>
                            </a>
                        </th>                       
                        <th style="min-width: 270px">
                            <a href="posts.php?order_by=last_update<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'last_update' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['last_modification']; ?>
                            </a>
                        </th>
                        <th style="min-width: 270px">
                            <a href="posts.php?order_by=publish_date<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'publish_date' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['publish_date']; ?>
                            </a>
                        </th>
                    <?php if (has_privileges(PRIVILEGE_PUBLISH)): ?>
                        <th style="width: 220px">
                            <a><?php echo $lang['actions']; ?></a>
                        </th>
                    </tr>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><a target="_blank" href="<?php echo BASE_URL; ?>/<?php echo $post['slug'] ?>"><?php echo $post['title']; ?></a></td>
                        <td><?php echo $post['author']; ?></td>
                        <td><?php echo $post['views']; ?></td>
                        <td><?php if ($post['updated_at'] != '') echo strftime($lang['complete_datetime'], strtotime($post['updated_at'])); ?></td>
                        <td><?php if ($post['published_at'] != '') echo strftime($lang['complete_datetime'], strtotime($post['published_at'])); ?></td>
                        <?php if (has_privileges(PRIVILEGE_PUBLISH)): ?>
                            <td>
                            <?php if ($post['published'] == true): ?>
                                <a href="posts.php?unpublish=<?php echo $post['id'] ?>"><?php echo $lang['unpublish']; ?></a>
                            <?php else: ?>
                                <a href="posts.php?publish=<?php echo $post['id'] ?>"><?php echo $lang['publish']; ?></a>
                            <?php endif ?>                       
                                 | 
                                <a href="editor.php?mode-post&edit-post=<?php echo $post['id'] ?>"><?php echo $lang['edit']; ?></a>
                                 | 
                                <a href="posts.php?delete-post=<?php echo $post['id'] ?>"><?php echo $lang['delete']; ?></a>
                            </td>
                        <?php endif ?>
                    </tr>
                    <?php endforeach ?>
                </table>
            <?php endif ?>
                <?php 
                    if ($count_posts > $search_limit) {

                        $items_per_page = $search_limit;
                        $total_items = $search_count_posts;
                        $page = isset($_GET['p']) ? esc($_GET['p']) : 1;
                        $link_extra = [];
         
                        if(isset($_GET['search']) && $var1 = $_GET['search']) { 
                            $link_extra[] = "search=" . urlencode($var1);
                        }
                        if(isset($_GET['order_by']) && $var2 = $_GET['order_by']) { 
                            $link_extra[] = "order_by=" . urlencode($var2);
                        }
                        if(isset($_GET['order']) && $var3 = $_GET['order']) { 
                            $link_extra[] = "order=" . urlencode($var3);
                        }                    
                        include(ROOT_PATH . '/admin/includes/pagination.php'); 
                    }
                ?>
            <?php endif ?>
            </div>
        </div>
    </div>
</body>
</html>