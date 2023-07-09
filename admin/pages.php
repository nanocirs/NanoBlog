<?php include('../config.php'); ?>
<?php 
    if (empty($logged_user)) {
        header('location: ' . BASE_URL . '/login.php');
        exit();
    }
    else if (!has_privileges(PRIVILEGE_EDIT_PAGES)) { 
        header('location: ' . BASE_URL . '/redirect/403.html');
        exit();
    }
?>
<?php include(ROOT_PATH . '/admin/includes/functions/functions_page.php'); ?>
<?php include(ROOT_PATH . '/admin/includes/header.php'); ?>
<?php $count_pages = count_pages(); ?>
<title><?php echo $lang['pages']; ?> - <?php echo $webpage_settings['title']; ?></title>
</head>
<body>
    <div>
        <?php include(ROOT_PATH . '/admin/includes/navbar.php') ?>
        <?php include(ROOT_PATH . '/admin/includes/menu.php') ?>
        <div class="container content">
            <div class="action">            
            <h1><?php echo $lang['pages']; ?> (<?php echo $count_pages; ?>)</h1>             
            <form>
                <button class="btn" type="button" onclick="window.location.href='editor.php?mode-page'"><?php echo $lang['new_page']; ?></button>
                <input type="text" name="search">
                <button type="submit" class="btn"><?php echo $lang['search']; ?></button>
                <?php if (isset($_GET['search']) && $search_query !== '') : ?>
                    <span style="margin-left: 15px"><?php echo $lang['showing']; ?> <?php echo ($search_page - 1) * $search_limit + 1; ?>-<?php echo ($search_limit > $search_count_results) ? ($search_page - 1) * $search_limit + $search_count_results : ($search_page * $search_limit); ?> <?php echo $lang['of']; ?> <?php echo $search_count_pages; ?> <?php echo $lang['results']; ?> <?php echo $lang['_from']; ?> "<?php echo $search_query; ?>".</span>
                <?php else : ?>
                    <span style="margin-left: 15px"><?php echo $lang['showing']; ?> <?php echo ($search_page - 1) * $search_limit + 1; ?>-<?php echo ($search_limit > $search_count_results) ? ($search_page - 1) * $search_limit + $search_count_results : ($search_page * $search_limit); ?> <?php echo $lang['of']; ?> <?php echo $count_pages; ?> <?php echo $lang['results']; ?>.</span>
                <?php endif ?>
                </form>
            <?php if (empty($pages)): ?>
                <h2 style="text-align: center; margin-top: 20px;"><?php echo $lang['no_pages']; ?></h2>          
            <?php else: ?>
                <table class="table-data table-short">
                    <tr>
                        <th style="width: 280px">    
                            <a href="pages.php?order_by=title<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'title' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['title']; ?>
                            </a>
                        </th>
                        <th style="width: 220px">
                            <a><?php echo $lang['actions']; ?></a>
                        </th>
                    </tr>
                    <?php foreach ($pages as $page): ?>
                    <tr>
                        <td>
                            <a target="_blank" href="<?php echo BASE_URL; ?>/<?php echo $page['slug'] ?>"><?php echo $page['title']; ?></a>
                        </td>
                        <td>
                        <?php if ($page['published'] == true): ?>
                            <a href="pages.php?unpublish=<?php echo $page['id'] ?>"><?php echo $lang['unpublish']; ?></a>
                        <?php else: ?>
                            <a href="pages.php?publish=<?php echo $page['id'] ?>"><?php echo $lang['publish']; ?></a>
                        <?php endif ?>                       
                                | 
                            <a href="editor.php?mode-page&edit-page=<?php echo $page['id'] ?>"><?php echo $lang['edit']; ?></a>
                                | 
                            <a href="pages.php?delete-page=<?php echo $page['id'] ?>"><?php echo $lang['delete']; ?></a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </table>
            <?php endif ?>
                <?php 
                    if ($count_pages > $search_limit) {

                        $items_per_page = $search_limit;
                        $total_items = $search_count_pages;
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
            </div>
        </div>
    </div>
</body>
</html>