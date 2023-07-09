<?php
define('INTERNAL_ACCESS', true);

$language = 'EN';
require_once('language/lang_index.php');

$errors = array();
$conn = null;

$db_host = null;
$db_user = null;
$db_password = null;
$db_database = null;

$installation_phase = 1;

if (file_exists('tmp_install.php')) {

    require_once('tmp_install.php');

    $a = file_get_contents('tmp_install.php');

    $language = $tmp_language;
    $db_host = $tmp_host;
    $db_user = $tmp_user;
    $db_password = $tmp_password;
    $db_database = $tmp_database;
    
    $conn = mysqli_connect($tmp_host, $tmp_user, $tmp_password, $tmp_database);

    if ($conn) {

        if (check_installation()) {

            if (check_superadmin_created()) {

                finish_installation();

            }
            else {

                $installation_phase = 3;

            }
                
        }

    }

}

if (isset($_POST['language'])) {

    $language = $_POST['language'];

    $installation_phase = 2;

}

if (isset($_POST['install'])) {

    require_once('language/' . $lang_index[$language]);

    $db_host = $_POST['database_host'];
    $db_user = $_POST['database_user'];
    $db_password = $_POST['database_password'];
    $db_database = $_POST['database_db'];

    if (empty($db_host)) { array_push($errors, $lang['error_db_host_needed']); }
    if (empty($db_user)) { array_push($errors, $lang['error_db_user_needed']); }
    if (empty($db_password)) { array_push($errors, $lang['error_db_pass_needed']); }
    if (empty($db_database)) { array_push($errors, $lang['error_db_base_needed']); }
    
    if (count($errors) === 0) {

        $conn = mysqli_connect($db_host, $db_user, $db_password, $db_database);
        
        if ($conn) {
            
            database_config($db_host, $db_user, $db_password, $db_database);

            $title = $_POST['webpage_title'];  
            
            install_nanoblog($title);
            
        }
        else {

            array_push($errors, $lang['error_credentials']);

        }

    }

}
else if (isset($_POST['create_superadmin'])) {

    create_superadmin($_POST);

}

require_once('language/' . $lang_index[$language]);

function esc(String $value) {

    global $conn;

    $val = trim($value);

    return mysqli_real_escape_string($conn, $val);
    
}

function database_config($db_host, $db_user, $db_password, $db_database) {

    $file_tmp = 'tmp_install.php';
    $file_contents = "<?php\n";
    $file_contents .= "\$tmp_host = '$db_host';\n";
    $file_contents .= "\$tmp_user = '$db_user';\n";
    $file_contents .= "\$tmp_password = '$db_password';\n";
    $file_contents .= "\$tmp_database = '$db_database';\n";

    file_put_contents($file_tmp, $file_contents);

    require_once('tmp_install.php');
    
}

function install_nanoblog($webpage_title) {

    global $conn, $errors, $language, $installation_phase;

    if (check_installation()) { 
        
        $installation_phase = 3;
        return;
    
    }

    if (count($errors) === 0) {

        // Create table settings
        $query = "CREATE TABLE nn_settings (
                    title varchar(255) DEFAULT '',
                    header_title varchar(255) DEFAULT '',
                    header_image tinyint(1) DEFAULT '0',
                    enable_comments tinyint(1) DEFAULT '1',
                    show_comments tinyint(1) DEFAULT '1',
                    register_enabled tinyint(1) DEFAULT '1',
                    register_role varchar(255) DEFAULT 'Default',
                    language varchar(255) DEFAULT 'EN'
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        $result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM nn_settings");

        if (mysqli_fetch_assoc($result)['count'] == 0) { 
    
            $query = "INSERT INTO nn_settings (title, header_title, language) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sss', $webpage_title, $webpage_title, $language);
            $stmt->execute();
            
        }

        // Create table users
        $query = "CREATE TABLE IF NOT EXISTS nn_users (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    username varchar(255) NOT NULL,
                    created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at timestamp NULL DEFAULT NULL,
                    password varchar(255) NOT NULL,
                    session_token varchar(255) DEFAULT NULL,
                    role varchar(255) DEFAULT NULL,
                    session_expire datetime DEFAULT NULL,
                    email varchar(255) DEFAULT '',
                    last_connection timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    active tinyint(1) DEFAULT '0',
                    activation_token varchar(255) DEFAULT '',
                    activation_expire datetime DEFAULT NULL,
                    recovery_token varchar(255) DEFAULT '',
                    recovery_expire datetime DEFAULT NULL,
                    PRIMARY KEY (id),
                    UNIQUE (username, email)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        // Create table roles
        $query = "CREATE TABLE IF NOT EXISTS nn_roles (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    role varchar(255) NOT NULL,
                    privileges int(11) NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE (role)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        // Populate table roles
        $query = "INSERT IGNORE INTO nn_roles (role, privileges) VALUES ('SuperAdmin', 255)";
        mysqli_query($conn, $query);

        $query = "INSERT IGNORE INTO nn_roles (role, privileges) VALUES ('Admin', 254)";
        mysqli_query($conn, $query);

        $query = "INSERT IGNORE INTO nn_roles (role, privileges) VALUES ('Default', 0)";
        mysqli_query($conn, $query);

        // Create table posts
        $query = "CREATE TABLE IF NOT EXISTS nn_posts (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) DEFAULT NULL,
                    title varchar(255) NOT NULL,
                    slug varchar(255) NOT NULL,
                    views int(11) NOT NULL DEFAULT '0',
                    image varchar(255) NOT NULL,
                    body text NOT NULL,
                    published tinyint(1) NOT NULL,
                    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    updated_at timestamp NULL DEFAULT NULL,
                    published_at timestamp NULL DEFAULT NULL,
                    comments int(11) DEFAULT '0',
                    summary text,
                    PRIMARY KEY (id),
                    UNIQUE KEY slug (slug),
                    KEY user_id (user_id),
                    CONSTRAINT posts_ibfk_1 FOREIGN KEY (user_id) REFERENCES nn_users (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
                    UNIQUE (title, slug)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        // Create table comments
        $query = "CREATE TABLE IF NOT EXISTS nn_comments (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    body text NOT NULL,
                    parent_id int(11) DEFAULT NULL,
                    post_id int(11) DEFAULT NULL,
                    PRIMARY KEY (id),
                    KEY parent_id (parent_id),
                    CONSTRAINT comments_ibfk_1 FOREIGN KEY (parent_id) REFERENCES nn_comments (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        // Create table topics
        $query = "CREATE TABLE IF NOT EXISTS nn_topics (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    name varchar(255) NOT NULL,
                    slug varchar(255) NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE (name, slug)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        // Create table post_topic
        $query = "CREATE TABLE IF NOT EXISTS nn_post_topic (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    post_id int(11) DEFAULT NULL,
                    topic_id int(11) DEFAULT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY post_id (post_id),
                    KEY post_topic_ibfk_2 (topic_id),
                    CONSTRAINT post_topic_ibfk_1 FOREIGN KEY (post_id) REFERENCES nn_posts (id) ON DELETE CASCADE ON UPDATE NO ACTION,
                    CONSTRAINT post_topic_ibfk_2 FOREIGN KEY (topic_id) REFERENCES nn_topics (id) ON DELETE CASCADE ON UPDATE NO ACTION
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        // Create table pages
        $query = "CREATE TABLE IF NOT EXISTS nn_pages (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    title varchar(255) NOT NULL UNIQUE,
                    slug varchar(255) NOT NULL UNIQUE,
                    body text NOT NULL,
                    published tinyint(1) NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE (title, slug)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        // Create table pages_navbar
        $query = "CREATE TABLE IF NOT EXISTS nn_pages_navbar (
                    page_id int(11) DEFAULT NULL,
                    position int(11) DEFAULT NULL,
                    name varchar(255) DEFAULT '',
                    url varchar(255) DEFAULT '',
                    KEY pages_navbar_ibfk_1 (page_id),
                    CONSTRAINT pages_navbar_ibfk_1 FOREIGN KEY (page_id) REFERENCES nn_pages (id) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        // Create table social
        $query = "CREATE TABLE IF NOT EXISTS nn_social (
                    twitter varchar(255) DEFAULT '',
                    facebook varchar(255) DEFAULT '',
                    instagram varchar(255) DEFAULT '',
                    youtube varchar(255) DEFAULT '',
                    twitch varchar(255) DEFAULT '',
                    linkedin varchar(255) DEFAULT '',
                    pinterest varchar(255) DEFAULT '',
                    tumblr varchar(255) DEFAULT '',
                    github varchar(255) DEFAULT '',
                    reddit varchar(255) DEFAULT ''
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        mysqli_query($conn, $query);

        $result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM nn_social");

        if (mysqli_fetch_assoc($result)['count'] == 0) { 
    
            mysqli_query($conn, "INSERT INTO nn_social () VALUES ()");
            
        }

        if (check_installation()) {
            
            $installation_phase = 3;
            
        }

    }
    else {
        
        $installation_phase = 2;

    }

    mysqli_close($conn);

}

function check_installation() {

    global $conn, $language;

    $file_tmp = file_get_contents('tmp_install.php');

    if (!strpos($file_tmp, '$tmp_language')) {

        file_put_contents('tmp_install.php', '$tmp_language = \'' . $language . '\';' . PHP_EOL, FILE_APPEND);

    }

    // Check for table collisions.
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_settings'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM nn_settings");
    if (mysqli_fetch_assoc($result)['count'] == 0) { return false; }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_users'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_roles'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SELECT * FROM nn_roles WHERE role='SuperAdmin' LIMIT 1");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SELECT * FROM nn_roles WHERE role='Admin' LIMIT 1");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SELECT * FROM nn_roles WHERE role='Default' LIMIT 1");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_posts'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_comments'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_topics'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_post_topic'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_pages'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_pages_navbar'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SHOW TABLES LIKE 'nn_social'");
    if (empty(mysqli_num_rows($result))) { return false; }

    $result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM nn_social");
    if (mysqli_fetch_assoc($result)['count'] == 0) { return false; }        

    return true;

}

function create_superadmin($request_values) {

    global $conn, $errors, $lang;

    $entered_username = esc($request_values['username']);
    $entered_password = esc($request_values['password']);
    $entered_password2 = esc($request_values['password_confirmation']);
    $entered_email = esc($request_values['email']);
            
    if (strlen($entered_username) < 3) { array_push($errors, $lang['error_username_short']); }
    else if (strlen($entered_username) > 20) { array_push($errors, $lang['error_username_long']); }

    if (strlen($entered_password) < 8) { array_push($errors, $lang['error_password_short']); }
    if ($entered_password !== $entered_password2) { array_push($errors, $lang['error_passwords_dont_match']); }

    if (empty($entered_email)) { array_push($errors, $lang['error_email_needed']); }
    else if (!filter_var($entered_email, FILTER_VALIDATE_EMAIL)) { array_push($errors, $lang['error_email_invalid_format']); }

    $query = "SELECT * FROM nn_users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $entered_username);
    $stmt->execute();
    
    $result = $stmt->get_result();

    $user = mysqli_fetch_assoc($result);

    if ($user) { array_push($errors, $lang['error_username_taken']); }

    if (!empty($entered_email)) {

        $query = "SELECT * FROM nn_users WHERE email = ? AND (active=1 OR (active=0 AND activation_expire > now())) LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $entered_email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $user = mysqli_fetch_assoc($result);
        
        if ($user) { array_push($errors, $lang['error_email_taken']); }
        
    }

    if (count($errors) === 0) {

        $password = password_hash($entered_password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO nn_users (active, username, role, password, email, created_at, updated_at) VALUES (1, ?, 'SuperAdmin', ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sss', $entered_username, $password, $entered_email);
        $stmt->execute();

        if (mysqli_affected_rows($conn) > 0) {

            $user_id = mysqli_insert_id($conn);

        }

    }

}

function check_superadmin_created() {

    global $conn;

    $query = "SELECT COUNT(*) AS count FROM nn_users WHERE role = 'SuperAdmin'";
    $result = mysqli_query($conn, $query);
    if (mysqli_fetch_assoc($result)['count'] == 0) { return false; }

    return true;

}

function finish_installation() {

    global $db_host, $db_user, $db_password, $db_database;
    
    $file_config = 'config.php';
    $file_contents = file_get_contents($file_config);

    $file_contents = str_replace('___db_hostname___', $db_host, $file_contents);
    $file_contents = str_replace('___db_username___', $db_user, $file_contents);
    $file_contents = str_replace('___db_password___', $db_password, $file_contents);
    $file_contents = str_replace('___db_database___', $db_database, $file_contents);

    file_put_contents($file_config, $file_contents);

    $file_contents = file_get_contents($file_config);
    $file_contents = str_replace('if (file_exists(\'install.php\')) { header(\'location: install.php\'); exit(); }', '', $file_contents);

    file_put_contents($file_config, $file_contents);

    $file_htaccess = '.htaccess';
    $htaccess_content = '
# NANOBLOG_START
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^/]+)$ single_post.php?post-slug=$1 [QSA,L]
# NANOBLOG_END';

    if (!file_exists($file_htaccess)) {

        touch($file_htaccess);

    }

    file_put_contents($file_htaccess, $htaccess_content . PHP_EOL, FILE_APPEND);

    if (file_exists('install.php')) { unlink('install.php'); }
    if (file_exists('tmp_install.php')) { unlink('tmp_install.php'); }

    header('location: index.php');
    exit();

}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/install.css">
    <?php if ($installation_phase === 1) : ?>
        <title>Installation - NanoBlog</title>
    <?php else : ?>
        <title><?php echo $lang['install_title']; ?></title>
    <?php endif ?>
</head>
    <body>
        <div class="panel">
            <?php if ($installation_phase === 1) :?>
                <h1>NanoBlog Installation</h1> 
                <p>Select a language:</p>
                <form method="POST" action="">
                    <div class="center">
                        <select id="website_language" name="language" multiple>
                            <option value="ES">Spanish</option>
                            <option value="EN">English</option>
                        </select>
                    </div>
                    <div class="btn_container">
                        <button type="submit" class="btn">Continue</button>
                    </div>
                </form>
            <?php elseif ($installation_phase === 2) :?>
                <h1><?php echo $lang['install_header']; ?></h1>
                <p><?php echo $lang['install_intro']; ?></p>
                <h2><?php echo $lang['general']; ?></h2>
                <form method="POST" action="">
                    <table class="table_form">
                        <tr>
                            <th>
                                <label for="webpage_title"><?php echo $lang['website_title']; ?></label>
                            </th>
                            <td>
                                <input type="text" id="webpage_title" name="webpage_title">
                            </td>
                        </tr>
                    </table>
                    <h2><?php echo $lang['database']; ?></h2>
                    <table class="table_form">
                        <tr>
                            <th>
                                <label for="database_host"><?php echo $lang['host']; ?></label>
                            </th>
                            <td>
                                <input type="text" id="database_host" name="database_host">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="database_user"><?php echo $lang['user']; ?></label>
                            </th>
                            <td>
                                <input type="text" id="database_user" name="database_user">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="database_password"><?php echo $lang['password']; ?></label>
                            </th>
                            <td>
                                <input type="password" id="database_password" name="database_password">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="database_db"><?php echo $lang['database']; ?></label>
                            </th>
                            <td>
                                <input type="text" id="database_db" name="database_db">
                            </td>
                        </tr>
                    </table>
                    <?php include('includes/errors.php'); ?>
                    <div class="btn_container">
                        <input type="hidden" name="language" value="<?php echo $language; ?>">
                        <button type="submit" class="btn" name="install"><?php echo $lang['continue']; ?></button>
                    </div>
                </form>
            <?php elseif ($installation_phase === 3) :?>
                <h1><?php echo $lang['install_header']; ?></h1>
                <h2><?php echo $lang['create_user']; ?></h2>
                <p><?php echo $lang['install_create_user_info']; ?></p>
                <form method="POST" action="">
                    <table class="table_form">
                        <tr>
                            <th>
                                <label for="username"><?php echo $lang['user']; ?></label>
                            </th>
                            <td>
                                <input type="text" id="username" name="username">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="password"><?php echo $lang['password']; ?></label>
                            </th>
                            <td>
                                <input type="password" id="password" name="password">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="password_confirmation"><?php echo $lang['repeat_password']; ?></label>
                            </th>
                            <td>
                                <input type="password" id="password_confirmation" name="password_confirmation">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="email"><?php echo $lang['email']; ?></label>
                            </th>
                            <td>
                                <input type="text" id="email" name="email">
                            </td>
                        </tr>
                    </table>
                    <?php include('includes/errors.php'); ?>
                    <div class="btn_container">
                        <button type="submit" class="btn" name="create_superadmin"><?php echo $lang['continue']; ?></button>
                    </div>
                </form>
            <?php endif ?>
        </div>
    </body>
</html>
