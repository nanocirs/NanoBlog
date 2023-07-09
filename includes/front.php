<div class="fixed_navbar_offset"></div>
</div>
<div class="banner" <?php if ($webpage_settings['header_image'] == 0) : ?>style="background-image: unset"<?php endif ?>>
    <div class="container">
        <div class="banner_separator">
        </div>
        <div class="main_panel main_panel_top">
            <div class="container">
                <div class="main_panel_left"></div>
                <div class="main_panel_posts">
            <?php if (!$socials_empty) : ?>
                <div class="main_panel_socials"></div>
            <?php endif ?>
                <?php if (isset($is_page) && !$is_page) : ?>
                    <div class="post_container">
                    <?php if (isset($current_topic)) : ?>
                        <h2><?php echo $lang['topic']; ?>: <a href="filtered_posts.php?topic=<?php echo $current_topic['slug']; ?>"><?php echo $current_topic['name']; ?></a></h2>
                    <?php endif ?>
                    </div>
                <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>