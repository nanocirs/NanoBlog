<?php require_once('config.php'); ?>
<?php 
    if ($logged_user !== null) {

        header('location: ' . BASE_URL . '/index.php');
        exit();
        
    }
?>
<?php require_once(ROOT_PATH . '/includes/functions/functions_register.php') ?>
<?php require_once(ROOT_PATH . '/includes/header.php'); ?>
<title><?php echo $lang['sign_up']; ?> - <?php echo $webpage_settings['title']; ?></title>
</head>
<body>
<div>
    <?php include(ROOT_PATH . '/includes/navbar.php'); ?>
    <?php include(ROOT_PATH . '/includes/front.php'); ?>
    <?php include(ROOT_PATH . '/includes/panel_slide.php'); ?>
    <div class="content">
        <div class="container">
            <div class="main_panel main_panel_left">
                <?php include(ROOT_PATH . '/includes/panel_left.php'); ?>
            </div>
            <div class="main_panel main_panel_posts">
                <div class="post_container">
                    <?php if ($register_fulfilled) : ?>
                        <h1><?php echo $lang['sign_up_completed']; ?></h1>
                        <p><?php echo $lang['thank_you_for_signing_up']; ?></p>
                    <?php else: ?>
                    <div class="register_form">
                        <h1><?php echo $lang['sign_up']; ?></h1>
                        <?php include(ROOT_PATH . '/includes/errors.php') ?>
                        <form method="post" action="">
                            <table class="table_form">
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
                                        <label for="email"><?php echo $lang['email']; ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="email" id="email" value="" style="inline-block">
                                    </td>
                                </tr>
                            </table>
                            <button type="submit" class="btn" name="create_user"><?php echo $lang['sign_up']; ?></button>
                        </form>
                    </div>
                    <?php endif ?>
                </div>
            </div>
        <?php if (!$socials_empty) : ?>
            <div class="main_panel main_panel_socials">
                <?php include(ROOT_PATH . '/includes/panel_socials.php'); ?>
            </div>
        <?php endif ?>
        </div>
    </div>
<?php include(ROOT_PATH . '/includes/footer.php'); ?>
<?php include(ROOT_PATH . '/includes/cookies_banner.php'); ?>