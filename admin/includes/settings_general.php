<?php 
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<?php include(ROOT_PATH . '/includes/errors.php') ?>
<form method="post" action="" enctype="multipart/form-data">
    <table class="table-form">
        <tr>
            <th>
                <label for="web_title"><?php echo $lang['website_title']; ?></label>
            </th>
            <td>
                <input type="text" name="web_title" id="web_title" value="<?php echo $webpage_settings['title']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="web_header_title"><?php echo $lang['front_title']; ?></label>
            </th>
            <td>
                <input type="text" name="web_header_title" id="web_header_title" value="<?php echo $webpage_settings['header_title']; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="web_header_image"><?php echo $lang['front_image']; ?></label>
            </th>
            <td>
                <input type="file" name="web_header_image" id="web_header_image">
            </td>
        </tr>
        <tr>
            <th>
                <label for="web_language"><?php echo $lang['language']; ?></label>
            </th>
            <td>
                <select name="web_language">
                        <option value="<?php echo $webpage_settings['language']; ?>" selected><?php echo $lang[$webpage_settings['language']]; ?></option>
                    <?php foreach ($lang_index as $key => $lang_item) : ?>
                    <?php if ($key !== $webpage_settings['language']) : ?>
                        <option value="<?php echo $key; ?>"><?php echo $lang[$key]; ?></option>
                    <?php endif ?>
                    <?php endforeach ?>
                </select>
            </td>
        </tr>
    </table>
    <button type="submit" class="btn" name="apply_general_settings"><?php echo $lang['save_changes']; ?></button>
</form>