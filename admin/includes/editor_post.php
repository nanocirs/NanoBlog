<?php

    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }

?>

<?php if ($is_editing_post === true): ?>
    <h1 class="page-title"><?php echo $lang['edit_post']; ?></h1>
    <form method="post" id="editor-form" enctype="multipart/form-data" action="" >
<?php else : ?>
    <h1 class="page-title"><?php echo $lang['create_post']; ?></h1>
    <form method="post" id="editor-form" enctype="multipart/form-data" action="">
<?php endif ?>
    <?php include(ROOT_PATH . '/includes/errors.php') ?>
    <?php if ($is_editing_post === true): ?>
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>">
    <?php endif ?>
    <div style="margin-bottom: 25px">
        <label for="title"><?php echo $lang['title']; ?>: &nbsp<input type="text" name="title" id="title" class="input-title" value="<?php echo $title; ?>"></label>
        <select name="topic_id" id="topic_selector">
            <?php if ($post_topic != ''): ?>
                <option value="<?php echo $topic_id; ?>" selected><?php echo $post_topic; ?></option>
            <?php else: ?>
                <option value="" selected disabled><?php echo $lang['choose_topic']; ?></option>
            <?php endif ?>
            <?php foreach ($topics as $topic) : ?>
                <?php if ($topic['name'] !== $post_topic) : ?>
                    <option value="<?php echo $topic['id']; ?>">
                        <?php echo $topic['name']; ?>
                    </option>
                <?php endif ?>
            <?php endforeach ?>
            <option id="select_new_topic" value=""><?php echo $lang['new_topic']; ?></option>
        </select>
        <input type="text" id="input_topic" name="new_topic" style="display: none" placeholder="Escribir categoría">
        <?php if ($is_editing_post === true): ?> 
            <button type="submit" class="btn" name="update_post"><?php echo $lang['update']; ?></button>
        <?php else: ?>
            <button type="submit" class="btn" name="create_post"><?php echo $lang['save']; ?></button>
        <?php endif ?>
        <?php if (has_privileges(PRIVILEGE_PUBLISH)): ?>
            <?php if ($published == true): ?>
                <label for="publish" style="margin-left: 10px"><?php echo $lang['publish']; ?> &nbsp<input type="checkbox" name="publish" style="float: none" checked="checked"></label>
            <?php else: ?>
                <label for="publish" style="margin-left: 10px"><?php echo $lang['publish']; ?> &nbsp<input type="checkbox" name="publish" style="float: none"></label>
            <?php endif ?>
        <?php endif ?>	
        <br><br>
        <label for="featured_image"><?php echo $lang['featured_image']; ?>: &nbsp<input type="file" name="featured_image" id="featured_image"></label><br>
    </div>

    <div style="margin-bottom: 15px">
        <label for="summary"><?php echo $lang['summary']; ?> (360 <?php echo $lang['characters']; ?>): &nbsp
            <textarea name="summary" id="summary" maxlength="360" style="width: 100%; height: 100px; margin-bottom: 25px"><?php echo $summary; ?></textarea>
        </label>
        <textarea name="body"><?php echo $body; ?></textarea>
    </div>
    <?php if ($is_editing_post === true): ?> 
        <button type="submit" class="btn" name="update_post"><?php echo $lang['update']; ?></button>
    <?php else: ?>
        <button type="submit" class="btn" name="create_post"><?php echo $lang['save']; ?></button>
    <?php endif ?>
</form>
<script>
    const topic_selector = document.getElementById('topic_selector');
    const input_topic = document.getElementById('input_topic');

    topic_selector.selectedIndex = 0;

    topic_selector.addEventListener('change', function() {

        if (topic_selector.options[topic_selector.selectedIndex].id == 'select_new_topic') {

            input_topic.style.display = 'inline-block';

        }
        else {

            input_topic.style.display = 'none';

        }

    })

    const editor_form = document.getElementById('editor-form');

    editor_form.addEventListener('submit', function() {

    })

</script>