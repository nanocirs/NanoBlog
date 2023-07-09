<?php 
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<?php require_once(ROOT_PATH . '/includes/functions/functions_public.php'); ?>
<div id="menu" class="reduced_max1279 panel_slide">
    <div class="slide_content">
        <form method="get" action="/filtered_posts.php">
            <div class="form_search">
                <input type="text" name="search" placeholder="Buscar publicación">
                <button class="btn" type="submit"><i class="fa fa-search"></i></button>
            </div>
        </form>
        <?php if (has_privileges(PRIVILEGE_DASHBOARD)): ?>
            <p><a href="admin/posts.php"><?php echo $lang['panel']; ?></a></p>
        <?php endif ?>
        <?php if (empty($logged_user)) : ?>
            <p><a href="login.php"><?php echo $lang['log_in']; ?></a></p>
        <?php endif ?>
        <?php $navbar_pages = get_ordered_navbar_pages(); ?>
        <?php foreach ($navbar_pages as $navbar_page) : ?>
            <p><a href="<?php echo $navbar_page['slug']; ?>"><?php echo $navbar_page['title'] ?></a></p>
        <?php endforeach ?>
        <?php if (!empty($logged_user)) : ?>
            <p><a href="logout.php"><?php echo $lang['logout']; ?></a></p>
        <?php endif ?>
        <h1><?php echo $lang['recent_posts']; ?></h1>
        <?php foreach (get_recent_posts(5) as $panel_recent_post) : ?>
            <p><a href="<?php echo $panel_recent_post['slug']; ?>"><?php echo $panel_recent_post['title']; ?></p></a>
        <?php endforeach ?>
        <h1><?php echo $lang['history']; ?></h1>
        <?php foreach (get_published_dates() as $panel_date) : ?>
            <p><a href="filtered_posts.php?y=<?php echo $panel_date['year']; ?>&m=<?php echo $panel_date['month']; ?>"><?php echo get_month_from_number($panel_date['month']) . ' ' . $panel_date['year']; ?></a></p>
        <?php endforeach ?>
        <h1><?php echo $lang['topics']; ?></h1>
        <?php foreach (get_published_topics() as $panel_topic) : ?>
            <p><a href="filtered_posts.php?topic=<?php echo $panel_topic['slug']; ?>"><?php echo $panel_topic['name']; ?></a></p>
        <?php endforeach ?>
    </div>
</div>
<script>
    const menu_icon = document.getElementById('menu_icon');
    const menu = document.getElementById('menu');

    menu_icon.addEventListener('click', function() {

    menu.classList.toggle('panel_slide_visible');

    });
</script>