<?php require_once('config.php'); ?>
<?php 
    if ($logged_user !== null) {

        header('location: ' . BASE_URL . '/index.php');
        exit();
        
    }
?>
<?php require_once(ROOT_PATH . '/includes/functions/functions_login.php') ?>
<?php require_once(ROOT_PATH . '/includes/header.php'); ?>
<title><?php echo $lang['login']; ?> - <?php echo $webpage_settings['title']; ?></title>
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
                    <div class="login_form">
                        <form method="post" action="login.php">
                            <h1><?php echo $lang['login']; ?></h1>
                            <?php include(ROOT_PATH . '/includes/errors.php') ?>
                            <input type="text" name="username" value="" value="" placeholder="Usuario">
                            <input type="password" name="password" placeholder="Contraseña">
                            <button type="submit" class="btn" name="login_btn"><?php echo $lang['sign_in']; ?></button>
                        </form>
                        <?php if ($webpage_settings['register_enabled']) : ?>
                            <p><?php echo $lang['if_you_dont_have_an_account']; ?> <a href="/register.php"><?php echo $lang['signup']; ?></a>.</p>
                            <p><a href="/recovery.php"><?php echo $lang['forgot_your_password']; ?></a></p>
                        <?php endif ?>
                    </div>
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