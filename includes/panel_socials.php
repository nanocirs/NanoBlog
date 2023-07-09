<?php 
    if (!defined('INTERNAL_ACCESS')) {
        header('location: ' . BASE_URL . '/index.php');
        exit();
    }
?>
<?php if ($socials['twitter']) : ?>
    <a href="https://www.twitter.com/<?php echo $socials['twitter']; ?>" target="_blank"><i class="fa fa-twitter"></i></a>
<?php endif ?>
<?php if ($socials['github']) : ?>
    <a href="https://www.github.com/<?php echo $socials['github']; ?>" target="_blank"><i class="fa fa-github-square"></i></a>
<?php endif ?>
<?php if ($socials['reddit']) : ?>
    <a href="https://www.reddit.com/u/<?php echo $socials['reddit']; ?>" target="_blank"><i class="fa fa-reddit"></i></a>
<?php endif ?>
<?php if ($socials['facebook']) : ?>
    <a href="https://www.facebook.com/<?php echo $socials['facebook']; ?>" target="_blank"><i class="fa fa-facebook"></i></a>
<?php endif ?>
<?php if ($socials['instagram']) : ?>
    <a href="https://www.instagram.com/<?php echo $socials['instagram']; ?>" target="_blank"><i class="fa fa-instagram"></i></a>
<?php endif ?>
<?php if ($socials['youtube']) : ?>
    <a href="https://www.youtube.com/@<?php echo $socials['youtube']; ?>" target="_blank"><i class="fa fa-youtube"></i></a>
<?php endif ?>
<?php if ($socials['twitch']) : ?>
    <a href="https://www.twitch.tv/<?php echo $socials['twitch']; ?>" target="_blank"><i class="fa fa-twitch"></i></a>
<?php endif ?>
<?php if ($socials['linkedin']) : ?>
    <a href="https://www.linkedin.com/in/<?php echo $socials['linkedin']; ?>" target="_blank"><i class="fa fa-linkedin"></i></a>
<?php endif ?>
<?php if ($socials['pinterest']) : ?>
    <a href="https://www.pinterest.com/<?php echo $socials['pinterest']; ?>" target="_blank"><i class="fa fa-pinterest"></i></a>
<?php endif ?>
<?php if ($socials['tumblr']) : ?>
    <a href="https://<?php echo $socials['tumblr']; ?>.tumblr.com/" target="_blank"><i class="fa fa-tumblr"></i></a>
<?php endif ?>