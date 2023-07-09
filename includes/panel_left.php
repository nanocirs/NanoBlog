<?php 
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<?php require_once(ROOT_PATH . '/includes/functions/functions_public.php'); ?>
<form method="get" action="/filtered_posts.php">
    <div class="form_search">
        <input type="text" name="search" placeholder="Buscar publicación">
        <button class="btn" type="submit"><i class="fa fa-search"></i></button>
    </div>
</form>
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