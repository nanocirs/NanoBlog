<?php if (file_exists('install.php')) { header('location: install.php'); exit(); }
define('INTERNAL_ACCESS', true);

$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
define('ROOT_PATH', realpath(dirname(__FILE__)));
define('BASE_URL', $scheme . $_SERVER['HTTP_HOST']);

$db_hostname = '___db_hostname___';
$db_username = '___db_username___';
$db_password = '___db_password___';
$db_database = '___db_database___';

$conn = mysqli_connect($db_hostname, $db_username, $db_password, $db_database);

if (!$conn) { die('Error connecting to the database: ' . mysqli_connect_error()); }

$query = "DELETE FROM nn_users WHERE active=0 AND activation_expire < NOW()";
mysqli_query($conn,$query);

$query = "UPDATE nn_users SET recovery_token='' AND recovery_expire=NULL WHERE recovery_expire < NOW()";
mysqli_query($conn,$query);

$query = "SELECT * FROM nn_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$webpage_settings = mysqli_fetch_assoc($result);

require_once(ROOT_PATH . '/language/lang_index.php');

$lang_file = $lang_index[$webpage_settings['language']];

require_once(ROOT_PATH . '/language/' . $lang_file);

$query = "SELECT * FROM nn_social LIMIT 1";
$result = mysqli_query($conn, $query);
$socials = mysqli_fetch_assoc($result);

$socials_empty = true;
foreach ($socials as $social => $value) {

    if ($value !== '') {

        $socials_empty = false;
        break;

    }
}

$logged_user = null;
$login_persistent_time = 1;
$can_store_cookies = false;

if (isset($_COOKIE['can_store_cookies'])) {

    $can_store_cookies =  $_COOKIE['can_store_cookies'];

}

if ($can_store_cookies) {

    if (isset($_COOKIE['session_token'])) {
        
        $query = "SELECT * FROM nn_users WHERE session_token = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $_COOKIE['session_token']);
        $stmt->execute();
        
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

        $user = mysqli_fetch_assoc($result);
        
        $session_expire_time = $user['session_expire'];
            
            if (new Datetime() < new Datetime($session_expire_time)) {
                
                if ($user['active'] === 1) {

                    $logged_user = $user;
                    
                    $query = "UPDATE nn_users SET last_connection = now() WHERE username = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('s', $logged_user['username']);
                    $stmt->execute();
                    
                }

            }

        }
        
    }

}

define('PRIVILEGE_SETTINGS',       0); //  1
define('PRIVILEGE_EDIT_USERS',     1); //  2
define('PRIVILEGE_EDIT_POSTS',     2); //  4
define('PRIVILEGE_DASHBOARD',      3); //  8
define('PRIVILEGE_PUBLISH',        4); // 16
define('PRIVILEGE_MODERATE_POSTS', 5); // 32
define('PRIVILEGE_EDIT_TOPICS',    6); // 64
define('PRIVILEGE_EDIT_PAGES',     7); // 128

function has_privileges($privilege) {

    global $conn, $logged_user;

    if (empty($logged_user)) {

        return false;

    } 

    $query = "SELECT privileges FROM nn_roles WHERE role = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $logged_user['role']);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user_privileges = mysqli_fetch_assoc($result)['privileges'];
        
        if ((($user_privileges >> $privilege) & 1) == 1) {
            
            return true;
            
        }
                
    }

    return false;

}