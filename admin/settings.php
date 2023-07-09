<?php include('../config.php'); ?>
<?php 
    if (empty($logged_user)) {

        header('location: ' . BASE_URL . '/login.php');
        exit();

    }
    else if (!has_privileges(PRIVILEGE_SETTINGS)) { 
    
        header('location: ' . BASE_URL . '/redirect/403.html');
        exit();

    }
?>
	<?php include(ROOT_PATH . '/admin/includes/functions/functions_user.php'); ?>
	<?php include(ROOT_PATH . '/admin/includes/functions/functions_settings.php'); ?>
	<?php include(ROOT_PATH . '/admin/includes/header.php'); ?>
	<title><?php echo $lang['settings']; ?> - <?php echo $webpage_settings['title']; ?></title>
</head>
<body>
    <div>
        <?php include(ROOT_PATH . '/admin/includes/navbar.php') ?>
        <?php include(ROOT_PATH . '/admin/includes/menu.php') ?>
        <div class="container content">
            <div class="action">
                <h1><?php echo $lang['settings']; ?></h1>
                <div class="settings-bar">
                    <a href="settings.php"><?php echo $lang['general']; ?></a> |
                    <a href="settings.php?page=pages"><?php echo $lang['pages']; ?></a> |
                    <a href="settings.php?page=roles"><?php echo $lang['roles']; ?></a> |
                    <a href="settings.php?page=social"><?php echo $lang['social']; ?></a>
                </div>
                <?php if (!isset($_GET['page'])) : ?>
                    <?php include(ROOT_PATH . '/admin/includes/settings_general.php'); ?>                 
                <?php elseif ((isset($_GET['page']) && $_GET['page'] == 'pages')) : ?>
                    <?php include(ROOT_PATH . '/admin/includes/settings_pages.php'); ?>                   
                <?php elseif ((isset($_GET['page']) && $_GET['page'] == 'roles')) : ?>
                    <?php include(ROOT_PATH . '/admin/includes/settings_roles.php'); ?>       
                <?php elseif ((isset($_GET['page']) && $_GET['page'] == 'social')) : ?>
                    <?php include(ROOT_PATH . '/admin/includes/settings_social.php'); ?>
                <?php endif ?>
            </div>
        </div>
    </div>
</body>
</html>