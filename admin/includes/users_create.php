<?php
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<h1 class="page-title"><?php echo $lang['create_user']; ?></h1>
<form method="post" action="<?php echo BASE_URL . '/admin/users.php'; ?>" >
    <?php include(ROOT_PATH . '/includes/errors.php') ?>
    <table class="table-form">
        <tr>
            <th>
                <label for="username"><?php echo $lang['user']; ?></label>
            </th>
            <td>
                <input type="text" name="username" id="username" value="">
            </td>
        </tr>
        <tr>
            <th>
                <label for="password"><?php echo $lang['password']; ?></label>
            </th>
            <td>
                <input type="password" name="password" id="password" value="" style="inline-block">
            </td>
        </tr>
        <tr>
            <th>
                <label for="password_confirmation"><?php echo $lang['repeat_password']; ?></label>
            </th>
            <td>
                <input type="password" name="password_confirmation" value="" style="inline-block">
            </td>
        </tr>
        <tr>
            <th>
                <label for="email"><?php echo $lang['mail']; ?></label>
            </th>
            <td>
                <input type="text" name="email" id="email" value="" style="inline-block">
            </td>
        </tr>
        <tr>
            <th>
                <label for="role"><?php echo $lang['role']; ?></label>
            </th>
            <td>
                <select style="margin:0px" name="role">                                
                    <?php foreach ($roles as $key => $role_each): ?>                
                        <?php if ($role_each['role'] !== 'SuperAdmin') : ?>
                            <option value="<?php echo $role_each['role']; ?>"><?php echo $role_each['role']; ?></option>
                        <?php endif ?>                  
                    <?php endforeach ?>                   
                </select>
            </td>
        </tr>
    </table>
    <button type="submit" class="btn" name="create_user"><?php echo $lang['add_user']; ?></button>
</form>