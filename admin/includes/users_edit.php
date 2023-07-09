<?php
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<h1><?php echo $lang['edit_user']; ?></h1>
<form method="post" action="<?php echo BASE_URL . '/admin/users.php?edit-user=' . $user_id; ?>" >
    <?php include(ROOT_PATH . '/includes/errors.php') ?>
    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
    <input type="hidden" name="role_bk" value="<?php echo $role; ?>">
    <table class="table-form">
        <tr>
            <th>
                <label for="username"><?php echo $lang['user']; ?></label>
            </th>
            <td>
                <input type="text" name="username" id="username" value="<?php echo $username; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="password"><?php echo $lang['password']; ?></label>
            </th>
            <td>
                <div>
                    <button id="btn_set_password" type="button" class="btn btn-form"><?php echo $lang['set_new_password']; ?></button>
                    <input type="password" name="password" id="password" value="" style="display: none" disabled>
                    <button id="btn_cancel_password" type="button" class="btn btn-form" style="display: none"><?php echo $lang['cancel']; ?></button>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                <label for="email"><?php echo $lang['mail']; ?></label>
            </th>
            <td>
                <input type="text" name="email" id="email" value="<?php echo $email; ?>" style="inline-block">
            </td>
        </tr>
        <tr>
            <th>
                <label for="role"><?php echo $lang['role']; ?></label>
            </th>
            <td>
                <?php if($role == 'SuperAdmin') : ?>
                    <input type="hidden" name="role" value="<?php echo $role; ?>">
                    <select style="margin:0px" name="role" disabled>
                        <option value="<?php echo $role; ?>" selected><?php echo $role; ?></option>
                    </select>
                <?php else : ?>
                    <select style="margin:0px" name="role">
                        <option value="" selected><?php echo $role; ?></option>
                        <?php foreach ($roles as $key => $role_each): ?>                            
                            <?php if ($role !== $role_each['role'] && $role_each['role'] !== 'SuperAdmin') : ?>
                                <option value="<?php echo $role_each['role']; ?>"><?php echo $role_each['role']; ?></option>
                            <?php endif ?>                                
                        <?php endforeach ?>
                    </select>
                <?php endif ?>
            </td>
        </tr>
    </table>
    <button type="submit" class="btn" name="update_user"><?php echo $lang['update']; ?></button>
</form>
<script>

    const btn_set_password = document.getElementById('btn_set_password');
    const btn_cancel_password = document.getElementById('btn_cancel_password');
    const field_password = document.getElementById('password');

    btn_set_password.addEventListener('click', function() { 

        btn_set_password.style.display = 'none';
        btn_cancel_password.style.display = 'inline-block';
        field_password.style.display = 'inline-block';
        field_password.disabled = false;

    });

    btn_cancel_password.addEventListener('click', function() { 

        btn_set_password.style.display = 'inline-block';
        btn_cancel_password.style.display = 'none';
        field_password.style.display = 'none';
        field_password.disabled = true;
        
    });


</script>