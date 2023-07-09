<?php 
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<?php include_once(ROOT_PATH . '/includes/functions/functions_global.php'); ?>
<div class="navbar">
    <div class="navbar_container">
        <div class="navbar_menu">
            <a id="menu_icon"><i class="fa fa-bars"></i></a>
        </div>
        <div class="navbar_title">
            <h1><a href="index.php"><?php echo $webpage_settings['header_title']; ?></a></h1>
        </div>
        <div class="navbar_pages">
            <ul>
            <?php if (has_privileges(PRIVILEGE_DASHBOARD)): ?>
                <li><a href="admin/posts.php"><?php echo $lang['panel']; ?></a></li>
            <?php endif ?>
            <?php if (empty($logged_user)) : ?>
                <li><a href="login.php"><?php echo $lang['log_in']; ?></a></li>
            <?php endif ?>
            <?php $navbar_pages = get_ordered_navbar_pages(); ?>
            <?php foreach ($navbar_pages as $navbar_page) : ?>
                <li><a href="<?php echo $navbar_page['slug']; ?>"><?php echo $navbar_page['title'] ?></a></li>
            <?php endforeach ?>
            <?php if (!empty($logged_user)) : ?>
                <li><a href="logout.php"><?php echo $lang['logout']; ?></a></li>
            <?php endif ?>
            </ul>
        </div>
    </div>
</div>