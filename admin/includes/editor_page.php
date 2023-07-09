<?php
if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}
?>
<?php if ($is_editing_page === true): ?>
    <h1 class="page-title"><?php echo $lang['edit_page']; ?></h1>
    <form method="post" id="editor-form" enctype="multipart/form-data" action="" >
<?php else : ?>
    <h1 class="page-title"><?php echo $lang['create_page']; ?></h1>
    <form method="post" id="editor-form" enctype="multipart/form-data" action="">
<?php endif ?>
        <?php include(ROOT_PATH . '/includes/errors.php') ?>
        <?php if ($is_editing_page === true): ?>
            <input type="hidden" name="page_id" value="<?php echo $page_id; ?>">
        <?php endif ?>
        <div style="margin-bottom: 25px">
            <label for="title"><?php echo $lang['title']; ?>: &nbsp<input type="text" name="title" id="title" class="input-title" value="<?php echo $title; ?>"></label>
            <?php if ($is_editing_page === true): ?> 
                <button type="submit" class="btn" name="update_page"><?php echo $lang['update']; ?></button>
            <?php else: ?>
                <button type="submit" class="btn" name="create_page"><?php echo $lang['save']; ?></button>
            <?php endif ?>
            <?php if ($published == true): ?>                    
                <label for="publish" style="margin-left: 10px"><?php echo $lang['publish']; ?> &nbsp<input type="checkbox" name="publish" style="float: none" checked="checked"></label>                
            <?php else: ?>
                <label for="publish" style="margin-left: 10px"><?php echo $lang['publish']; ?> &nbsp<input type="checkbox" name="publish" style="float: none"></label>
            <?php endif ?>
        </div>
        <div style="margin-bottom: 15px">
            <textarea name="body"><?php echo $body; ?></textarea>
        </div>
        <?php if ($is_editing_page === true): ?> 
            <button type="submit" class="btn" name="update_page"><?php echo $lang['update']; ?></button>
        <?php else: ?>
            <button type="submit" class="btn" name="create_page"><?php echo $lang['save']; ?></button>
        <?php endif ?>              
    </form>