<?php 
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
    $roles = get_all_roles(); 
    $role_order = array();
?>
<?php include(ROOT_PATH . '/includes/errors.php') ?>
<form method="post" action="settings.php?page=roles">
    <button type="button" class="btn" id="btn_add_role"><?php echo $lang['add_role']; ?></button>
    <input type="text" name="role_name" id="input_new_role" style="display: none">
    <button type="submit" class="btn" name="create_role" id="btn_confirm_role" style="display: none"><?php echo $lang['add']; ?></button>
    <input type="hidden" name="updated-privileges" id="privileges_holder">
    <button type="submit" class="btn" name="update_privileges"><?php echo $lang['update_privileges']; ?></button>
    <table class="table-check" style="width: auto">
        <th></th>
        <th><?php echo $lang['settings']; ?></th>
        <th><?php echo $lang['edit_pages']; ?></th>
        <th><?php echo $lang['moderation']; ?></th>
        <th><?php echo $lang['edit_users']; ?></th>
        <th><?php echo $lang['edit_topics']; ?></th>
        <th><?php echo $lang['publish']; ?></th>
        <th><?php echo $lang['write']; ?></th>
        <th><?php echo $lang['panel_access']; ?></th>
        <th width="60px" colspan="1" style="border-color: transparent"></th>
    <?php foreach ($roles as $key => $role) : ?>
        <?php array_push($role_order, $role['role']) ?>
        <?php $locked = ($role['role'] == 'SuperAdmin' || $role['role'] == 'Admin' || $role['role'] == 'Default') ? true : false; ?>
        <tr>
            <th>
                <a><?php echo $role['role']; ?></a>
            </th>
            <td style="text-align:center">
                <input type="checkbox" style="float:none" class="checkbox_privilege<?php echo $key; ?>" name="privilege_settings"<?php if (role_has_privilege($role, PRIVILEGE_SETTINGS)) : ?> checked<?php endif ?><?php if ($locked) : ?> disabled<?php endif ?>>
            </td>
            <td style="text-align:center">
                <input type="checkbox" style="float:none" class="checkbox_privilege<?php echo $key; ?>" name="privilege_edit_pages"<?php if (role_has_privilege($role, PRIVILEGE_EDIT_PAGES)) : ?> checked<?php endif ?><?php if ($locked) : ?> disabled<?php endif ?>>
            </td>
            <td style="text-align:center">
                <input type="checkbox" style="float:none" class="checkbox_privilege<?php echo $key; ?>" name="privilege_moderate_posts"<?php if (role_has_privilege($role, PRIVILEGE_MODERATE_POSTS)) : ?> checked<?php endif ?><?php if ($locked) : ?> disabled<?php endif ?>>
            </td>
            <td style="text-align:center">
                <input type="checkbox" style="float:none" class="checkbox_privilege<?php echo $key; ?>" name="privilege_edit_users"<?php if (role_has_privilege($role, PRIVILEGE_EDIT_USERS)) : ?> checked<?php endif ?><?php if ($locked) : ?> disabled<?php endif ?>>
            </td>
            <td style="text-align:center">
                <input type="checkbox" style="float:none" class="checkbox_privilege<?php echo $key; ?>" name="privilege_edit_topics"<?php if (role_has_privilege($role, PRIVILEGE_EDIT_TOPICS)) : ?> checked<?php endif ?><?php if ($locked) : ?> disabled<?php endif ?>>
            </td>
            <td style="text-align:center">
                <input type="checkbox" style="float:none" class="checkbox_privilege<?php echo $key; ?>" name="privilege_publish"<?php if (role_has_privilege($role, PRIVILEGE_PUBLISH)) : ?> checked<?php endif ?><?php if ($locked) : ?> disabled<?php endif ?>>
            </td>    
            <td style="text-align:center">
                <input type="checkbox" style="float:none" class="checkbox_privilege<?php echo $key; ?>" name="privilege_edit_posts"<?php if (role_has_privilege($role, PRIVILEGE_EDIT_POSTS)) : ?> checked<?php endif ?><?php if ($locked) : ?> disabled<?php endif ?>>
            </td>
            <td style="text-align:center">
                <input type="checkbox" style="float:none" class="checkbox_privilege<?php echo $key; ?>" name="privilege_dashboard"<?php if (role_has_privilege($role, PRIVILEGE_DASHBOARD)) : ?> checked<?php endif ?><?php if ($locked) : ?> disabled<?php endif ?>>
            </td>
            <td style="border-color: transparent">
                <?php if (!$locked) : ?>
                <a href="settings.php?page=roles&delete-role=<?php echo $role['role'] ?>"><?php echo $lang['delete']; ?></a>
                <?php endif ?>
            </td>
        </tr>
    <?php endforeach ?>
    </table>
</form>
<script>

    const privileges_holder = document.getElementById('privileges_holder');

    let privileges = {
        <?php foreach ($roles as $role) : ?>
            <?php echo $role['role']; ?> : 0,
        <?php endforeach ?>
    };

    document.addEventListener('DOMContentLoaded', function() { 

        <?php foreach ($roles as $key => $role) : ?>

            const checkboxes<?php echo $key; ?> = document.getElementsByClassName('checkbox_privilege<?php echo $key; ?>');

            populate_privileges_array('<?php echo $role['role']; ?>', Array.from(checkboxes<?php echo $key; ?>));

            Array.from(checkboxes<?php echo $key; ?>).forEach(function(checkbox) {

                checkbox.addEventListener('change', function() {
                    
                    populate_privileges_array('<?php echo $role['role']; ?>', Array.from(checkboxes<?php echo $key; ?>));
            
                });

            });

        <?php endforeach ?>

        privileges_holder.value = JSON.stringify(privileges);

    });

    const input_role = document.getElementById('input_new_role');
    const button_add_role = document.getElementById('btn_add_role');
    const button_confirm_role = document.getElementById('btn_confirm_role');

    button_add_role.addEventListener('click', function() {

        button_add_role.style.display = 'none';

        input_role.style.display = 'inline-block';
        button_confirm_role.style.display = 'inline-block';

    });

    function populate_privileges_array(role, array) {

        let privilege = 0;

        array.forEach(function(element) {

            if (element.checked) {

                switch(element.name) {

                    case 'privilege_settings':
                        privilege += 1;
                        break;

                    case 'privilege_edit_users':
                        privilege += 2;
                        break;

                    case 'privilege_edit_posts':
                        privilege += 4;
                        break;

                    case 'privilege_dashboard':
                        privilege += 8;
                        break;
                        
                    case 'privilege_publish':
                        privilege += 16;
                        break;

                    case 'privilege_moderate_posts':
                        privilege += 32;
                        break;

                    case 'privilege_edit_topics':
                        privilege += 64;
                        break;

                    case 'privilege_edit_pages':
                        privilege += 128;
                        break;

                }
            }
        });

        privileges[role] = privilege;
        
        privileges_holder.value = JSON.stringify(privileges);

    }

</script>