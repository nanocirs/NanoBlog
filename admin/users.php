<?php include('../config.php'); ?>
<?php 
    if (empty($logged_user)) {
        header('location: ' . BASE_URL . '/login.php');
        exit();
    }
    else if (!has_privileges(PRIVILEGE_EDIT_USERS)) {  
        header('location: ' . BASE_URL . '/redirect/403.html');
        exit();
    }
?>
<?php include(ROOT_PATH . '/admin/includes/functions/functions_user.php'); ?>
<?php include(ROOT_PATH . '/admin/includes/header.php'); ?>
<?php 
    $roles = get_all_roles();	
    $count_users = count_users(); 
?>
<title><?php echo $lang['users']; ?> - <?php echo $webpage_settings['title']; ?></title>
</head>
<body>
    <div>
        <?php include(ROOT_PATH . '/admin/includes/navbar.php') ?>
        <?php include(ROOT_PATH . '/admin/includes/menu.php') ?>
        <div class="container content">
            <div class="action">
                <?php if (isset($_GET['edit-user'])) :?>
                    <?php include(ROOT_PATH . '/admin/includes/users_edit.php'); ?>
                <?php else : ?>
                    <?php include(ROOT_PATH . '/admin/includes/users_create.php'); ?>
                <?php endif ?>
            </div> 
            <div class="action">
                <h1><?php echo $lang['users']; ?> (<?php echo $count_users; ?>)</h1>
                <form>
                    <input type="text" name="search">
                    <button type="submit" class="btn"><?php echo $lang['search']; ?></button>
                    <?php if (isset($_GET['search']) && $search_query !== '') : ?>
                        <span style="margin-left: 15px"><?php echo $lang['showing']; ?> <?php echo ($search_page - 1) * $search_limit + 1; ?>-<?php echo ($search_limit > $search_count_results) ? ($search_page - 1) * $search_limit + $search_count_results : ($search_page * $search_limit); ?> <?php echo $lang['of']; ?> <?php echo $search_count_users; ?> <?php echo $lang['results']; ?> <?php echo $lang['_from']; ?> "<?php echo $search_query; ?>".</span>
                    <?php else : ?>
                        <span style="margin-left: 15px"><?php echo $lang['showing']; ?> <?php echo ($search_page - 1) * $search_limit + 1; ?>-<?php echo ($search_limit > $search_count_results) ? ($search_page - 1) * $search_limit + $search_count_results : ($search_page * $search_limit); ?> <?php echo $lang['of']; ?> <?php echo $count_users; ?> <?php echo $lang['results']; ?>.</span>
                    <?php endif ?>
                </form>
                <?php if (empty($users)): ?>
                    <h2 style="text-align: center"><?php echo $lang['no_users']; ?></h2>
                <?php else: ?>
                <table class="table-data">
                    <tr>
                        <th style="min-width: 160px">
                            <a href="users.php?order_by=user<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'user' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['user']; ?>
                            </a>    
                        </th>
                        <th style="min-width: 140px">
                            <a href="users.php?order_by=role<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'role' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['role']; ?>
                            </a>
                        </th>
                        <th style="min-width: 200px">
                            <a href="users.php?order_by=email<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'email' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['mail']; ?>
                            </a>
                        </th>
                        <th style="min-width: 270px">
                            <a href="users.php?order_by=last_connection<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'last_connection' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['last_connection']; ?>
                            </a>
                        </th>
                        <th style="min-width: 270px">
                            <a href="users.php?order_by=creation_date<?php if (isset($_GET['search'])) : ?>&search=<?php echo $_GET['search']; ?><?php endif ?>&order=<?php echo (isset($_GET['order_by']) && $_GET['order_by'] === 'creation_date' && $_GET['order'] == 'desc') ? 'asc' : 'desc'; ?>">
                                <?php echo $lang['creation_date']; ?>
                            </a>
                        </th>
                        <th style="width: 130px"><?php echo $lang['actions']; ?></th>
                    </tr>
                    <?php foreach ($users as $key => $user): ?>
                    <tr>
                        <td>
                            <?php echo $user['username']; ?>
                        </td>
                        <td>
                            <?php echo $user['role']; ?>
                        </td>
                        <td>
                            <?php echo $user['email']; ?>
                        </td>
                        <td>
                            <?php echo strftime($lang['complete_datetime'], strtotime($user['last_connection'])); ?>
                        </td>
                        <td>
                            <?php echo strftime($lang['complete_datetime'], strtotime($user['created_at'])); ?>
                        </td>
                        <td>
                            <a href="users.php?edit-user=<?php echo $user['id'] ?>"><?php echo $lang['edit']; ?></a>
                            |
                            <a href="users.php?disable-user=<?php echo $user['id'] ?>"><?php echo $lang['disable']; ?></a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
                <?php 
                    if ($search_count_users > $search_limit) {

                        $items_per_page = $search_limit;
                        $total_items = $search_count_users;
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
</body>
</html>