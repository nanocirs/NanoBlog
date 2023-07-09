<?php
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<div class="container menu">
    <div class="menu">
        <div class="card">
            <div class="card-content">
                <?php if (has_privileges(PRIVILEGE_SETTINGS)) : ?>
                    <a href="<?php echo BASE_URL . '/admin/settings.php' ?>"<?php if ($_SERVER['SCRIPT_NAME'] == '/admin/settings.php') : ?> class="selected" <?php endif ?>><?php echo $lang['settings']; ?></a>
                <?php endif ?>
                <?php if (has_privileges(PRIVILEGE_EDIT_PAGES)) : ?>
                    <a href="<?php echo BASE_URL . '/admin/pages.php' ?>"<?php if ($_SERVER['SCRIPT_NAME'] == '/admin/pages.php' || $_SERVER['SCRIPT_NAME'] == '/admin/editor.php' && isset($_GET['mode-page'])) : ?> class="selected" <?php endif ?>><?php echo $lang['pages']; ?></a>
                <?php endif ?>                
                <?php if (has_privileges(PRIVILEGE_EDIT_POSTS)) : ?>
                    <a href="<?php echo BASE_URL . '/admin/posts.php' ?>"<?php if ($_SERVER['SCRIPT_NAME'] == '/admin/posts.php' || $_SERVER['SCRIPT_NAME'] == '/admin/editor.php' && isset($_GET['mode-post'])) : ?> class="selected" <?php endif ?>><?php echo $lang['posts']; ?></a>
                <?php endif ?>
                <?php if (has_privileges(PRIVILEGE_EDIT_USERS)) : ?>
                    <a href="<?php echo BASE_URL . '/admin/users.php' ?>"<?php if ($_SERVER['SCRIPT_NAME'] == '/admin/users.php') : ?> class="selected" <?php endif ?>><?php echo $lang['users']; ?></a>
                <?php endif ?>
                <?php if (has_privileges(PRIVILEGE_EDIT_TOPICS)) : ?>
                    <a href="<?php echo BASE_URL . '/admin/topics.php' ?>"<?php if ($_SERVER['SCRIPT_NAME'] == '/admin/topics.php') : ?> class="selected" <?php endif ?>><?php echo $lang['topics']; ?></a>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>