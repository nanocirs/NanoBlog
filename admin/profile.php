<?php include('../config.php'); ?>
<?php 
    if (empty($logged_user)) {
        header('location: ' . BASE_URL . '/login.php');
        exit();
    }
    else if (!has_privileges(PRIVILEGE_DASHBOARD)) {   
        header('location: ' . BASE_URL . '/redirect/403.html');
        exit();
    }
?>
<?php include(ROOT_PATH . '/admin/includes/functions/functions_profile.php'); ?>
<?php include(ROOT_PATH . '/admin/includes/header.php'); ?>
<?php $posts = get_posts_by_user($logged_user['id']); ?>
    <title><?php echo $lang['profile']; ?> - <?php echo $webpage_settings['title']; ?></title>
</head>
<body>
    <div>
        <?php include(ROOT_PATH . '/admin/includes/navbar.php') ?>
        <?php include(ROOT_PATH . '/admin/includes/menu.php') ?>
        <div class="container content">
            <div class="action">
                <h1><?php echo $logged_user['username']; ?></h1>
                <?php include(ROOT_PATH . '/includes/errors.php') ?>
                <form method="post" action="#">
                    <table class="table-form">  
                        <tr>
                            <th>
                                <label for="username"><?php echo $lang['user']; ?></label>
                            </th>
                            <td>
                                <span><?php echo $logged_user['username']; ?></span>
                                <input type="text" name="username" value="<?php echo $logged_user['username']; ?>" style="display: none" disabled>
                                <button type="submit" class="btn btn-form" style="display: none"><?php echo $lang['apply']; ?></button>
                                <a class="cancel_set_field" href="#" style="display: none"><?php echo $lang['cancel']; ?></a>
                                <a class="set_field" href="#"><?php echo $lang['change']; ?></a>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="password"><?php echo $lang['password']; ?></label>
                            </th>
                            <td>
                                <span>****</span>
                                <input type="password" name="password" value="" style="display: none" disabled>
                                <button type="submit" class="btn btn-form" style="display: none"><?php echo $lang['apply']; ?></button>
                                <a class="cancel_set_field" href="#" style="display: none"><?php echo $lang['cancel']; ?></a>
                                <a id="password_field" class="set_field" href="#"><?php echo $lang['change']; ?></a>
                            </td>
                        </tr>
                        <tr id="password_confirmation_field" style="display: none">
                            <th>
                                <label for="password2"><?php echo $lang['repeat_password']; ?></label>
                            </th>
                            <td>
                                <input type="password" id="password_confirmation_input" name="password2" value="" disabled>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="email"><?php echo $lang['mail']; ?></label>
                            </th>
                            <td>
                                <span><?php echo $logged_user['email']; ?></span>
                                <input type="text" name="email" value="" style="display:none" disabled>
                                <button type="submit" class="btn btn-form" style="display: none"><?php echo $lang['apply']; ?></button>
                                <a class="cancel_set_field" href="#" style="display: none"><?php echo $lang['cancel']; ?></a>
                                <a class="set_field" href="#"><?php echo $lang['change']; ?></a>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="role"><?php echo $lang['role']; ?></label>
                            </th>
                            <td>
                                <?php echo $logged_user['role']; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="created_at"><?php echo $lang['account_created']; ?></label>
                            </th>
                            <td>
                                <?php echo strftime($lang['complete_datetime'], strtotime($logged_user['created_at'])); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="created_at"><?php echo $lang['posts']; ?></label>
                            </th>
                            <td>
                                <a <?php if (count($posts) > 0) :?>href="/filtered_posts.php?author=<?php echo $logged_user['username']; ?><?php endif ?>"><?php echo count($posts); ?></a>
                            </td>
                        </tr>
                        <?php if (count($posts) > 0) : ?>
                        <tr>
                            <th>
                                <label for="created_at"><?php echo $lang['last_post']; ?></label>
                            </th>
                            <td>
                                <a href="/<?php echo $posts[0]['slug'] ?>"><i><?php echo $posts[0]['title']; ?></i></a>, el <?php echo strftime("%e de %B de %Y a las %H:%M", strtotime($posts[0]['published_at'])); ?>
                            </td>
                        </tr>
                        <?php endif ?>
                    </table>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<script>
    
    const fields = document.getElementsByClassName('set_field');
    const cancel_fields = document.getElementsByClassName('cancel_set_field');

    const password_confirmation_field = document.getElementById('password_confirmation_field');
    const password_confirmation_input = document.getElementById('password_confirmation_input');

    for (let i = 0; i < fields.length; i++) {

        fields[i].addEventListener('click', function() {

            for (let j = 0; j < fields.length; j++) {

                if (fields[j] != this) {

                    fields[j].style.display = 'inline-block';

                    const cancel_button1 = fields[j].previousElementSibling 
                    cancel_button1.style.display = 'none';

                    const apply_button1 = cancel_button1.previousElementSibling;
                    apply_button1.style.display = 'none';

                    const field_input1 = apply_button1.previousElementSibling;
                    field_input1.style.display = 'none';
                    field_input1.disabled = true;

                    const field_value1 = field_input1.previousElementSibling;
                    field_value1.style.display = 'inline-block';

                }
            }

            this.style.display = 'none';

            const cancel_button = this.previousElementSibling;
            cancel_button.style.display = 'inline-block';

            const apply_button = cancel_button.previousElementSibling;
            apply_button.style.display = 'inline-block';

            const field_input = apply_button.previousElementSibling;
            field_input.style.display = 'inline-block';
            field_input.disabled = false;

            const field_value = field_input.previousElementSibling;
            field_value.style.display = 'none';

            if (this.id == 'password_field') {

                password_confirmation_field.style.display = '';
                password_confirmation_input.disabled = false;

            }
            else {

                password_confirmation_field.style.display = 'none';
                password_confirmation_input.disabled = true;

            }
        });

        cancel_fields[i].addEventListener('click', function() {

            for (let j = 0; j < cancel_fields.length; j++) {

                this.style.display = 'none';

                const edit_button = this.nextElementSibling;
                edit_button.style.display = 'inline-block';

                const apply_button = this.previousElementSibling;
                apply_button.style.display = 'none';

                const field_input = apply_button.previousElementSibling;
                field_input.style.display = 'none';
                field_input.disabled = true;

                const field_value = field_input.previousElementSibling;
                field_value.style.display = 'inline-block';

                if (this.id != 'password_field') {

                    password_confirmation_field.style.display = 'none';

                }
            }
        })
    }

</script>