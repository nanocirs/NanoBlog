<?php require_once(ROOT_PATH . '/includes/functions/functions_comment.php'); ?>
<?php $comments = load_comments($post['id']); ?>
<div class="comments_area">
    <div>
        <?php if ($webpage_settings['enable_comments']) : ?>
            <hr>
            <h1><?php echo $lang['comments']; ?></h1>
        <?php endif ?>
        <?php if ($webpage_settings['enable_comments']) : ?>
            <?php include(ROOT_PATH . '/includes/errors.php') ?>
            <?php if ($logged_user) : ?>

                <form method="post" action="">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <label for="comment"><?php echo $lang['add_a_comment']; ?>:</label>
                    <textarea id="comment" name="comment" maxlength="960" required></textarea>
                    <button type="submit" class="btn"><?php echo $lang['send_comment']; ?></button>
                </form>
            <?php else : ?>
                <p><i><?php echo $lang['must_be_logged_in_to_comment']; ?></i> <a href="/login.php"><?php echo $lang['login']; ?>.</a></p>
            <?php endif ?>
        <?php endif ?>
    </div>
    <div class="comments_section">
    <?php foreach ($comments as $comment) : ?> 
        <?php if (isset($comment['comment'])) :?>
        <?php if ($comment['comment']['parent_id'] === null) : ?>
            <?php echo build_comments($comment); ?>
        <?php endif ?>
        <?php endif ?>
    <?php endforeach ?>
    </div>
</div>
<script>

const reply_text = document.querySelectorAll('.reply_text');
const delete_text = document.querySelectorAll('.delete_text');
const reply_containers = document.querySelectorAll('.reply_container');

reply_containers.forEach(function(element) {
    
    element.style.display = 'none';

})

reply_text.forEach(function(element) {

    element.addEventListener('click', function() {

        document.getElementById(element.dataset.reply_id).style.display = 'block';
        element.style.display = 'none';

    })

});

delete_text.forEach(function(element) {

element.addEventListener('click', function(event) {

    event.preventDefault();

    const form_delete = document.getElementById(element.dataset.delete_id);

    document.getElementById(element.dataset.delete_id).style.display = 'block';
    element.style.display = 'none';

    form_delete.submit();

})

});

</script>