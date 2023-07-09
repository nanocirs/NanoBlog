<?php
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<div class="header">
	<div class="logo">
        <ul>
            <li><a href="<?php echo BASE_URL .'/index.php'; ?>"><?php echo $_SERVER['HTTP_HOST']; ?></a></li>
        </ul>    
	</div>
	<div class="user-info">
        <ul>
            <li><a href="profile.php"><?php if ($logged_user) { echo $logged_user['username']; } ?></a></li>
		    <li><a href="<?php echo BASE_URL . '/logout.php'; ?>" class="logout-btn"><?php echo $lang['logout']; ?></a></li>
        </ul>
	</div>
</div>