<?php if (!$can_store_cookies) : ?>
    <?php 
        if (!defined('INTERNAL_ACCESS')) {
            header('location: ' . BASE_URL . '/index.php');
            exit();
        }
    ?>
    <div id="cookie_banner">
        <p><?php echo $lang['cookie_banner']; ?></p>
        <button class="btn" id="accept-cookies"><?php echo $lang['accept']; ?></button>
    </div>
    <script>
        function setCookie(name, value, expiration, path, domain, secure) {
            let cookieString = name + '=' + encodeURIComponent(value);

            if (expiration) {

                const date = new Date();

                date.setTime(date.getTime() + (expiration * 1000));

                cookieString += '; expires=' + date.toUTCString();

            }

            if (path) {

                cookieString += '; path=' + path;

            }

            if (domain) {

                cookieString += '; domain=' + domain;

            }

            if (secure) {

                cookieString += '; secure';

            }

            document.cookie = cookieString;
        }

        function acceptCookies() {

            setCookie('can_store_cookies', 'true', 180 * 24 * 3600, window.location.hostname, false, false);

            const banner = document.getElementById('cookie_banner');
            banner.style.display = 'none';
            
        }

        var acceptButton = document.getElementById('accept-cookies');

        acceptButton.addEventListener('click', function () {

            acceptCookies();

        });

    </script>
<?php endif ?>