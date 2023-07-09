<?php 
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<?php include(ROOT_PATH . '/includes/errors.php') ?>
<form method="post" action="">
    <table class="table-form">
        <tr>
            <th>
                <label for="socials_twitter">Twitter</label>
            </th>
            <td>
                <input type="text" name="socials_twitter" id="socials_twitter" value="<?php echo $socials['twitter']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="socials_reddit">Reddit</label>
            </th>
            <td>
                <input type="text" name="socials_reddit" id="socials_reddit" value="<?php echo $socials['reddit']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="socials_github">GitHub</label>
            </th>
            <td>
                <input type="text" name="socials_github" id="socials_github" value="<?php echo $socials['github']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="socials_facebook">Facebook</label>
            </th>
            <td>
                <input type="text" name="socials_facebook" id="socials_facebook" value="<?php echo $socials['facebook']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="socials_instagram">Instagram</label>
            </th>
            <td>
                <input type="text" name="socials_instagram" id="socials_instagram" value="<?php echo $socials['instagram']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="socials_youtube">Youtube</label>
            </th>
            <td>
                <input type="text" name="socials_youtube" id="socials_youtube" value="<?php echo $socials['youtube']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="socials_twitch">Twitch</label>
            </th>
            <td>
                <input type="text" name="socials_twitch" id="socials_twitch" value="<?php echo $socials['twitch']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="socials_linkedin">LinkedIn</label>
            </th>
            <td>
                <input type="text" name="socials_linkedin" id="socials_linkedin" value="<?php echo $socials['linkedin']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="socials_pinterest">Pinterest</label>
            </th>
            <td>
                <input type="text" name="socials_pinterest" id="socials_pinterest" value="<?php echo $socials['pinterest']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="socials_tumblr">Tumblr</label>
            </th>
            <td>
                <input type="text" name="socials_tumblr" id="socials_tumblr" value="<?php echo $socials['tumblr']; ?>">
            </td>
        </tr>
    </table>
    <button type="submit" class="btn" name="apply_socials_settings"><?php echo $lang['save_changes']; ?></button>
</form>
