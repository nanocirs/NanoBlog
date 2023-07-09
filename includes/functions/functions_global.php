<?php 

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

function get_ordered_navbar_pages() {
    
    global $conn;

    $query = "SELECT title, name, slug, url FROM nn_pages_navbar JOIN nn_pages ON nn_pages_navbar.page_id=nn_pages.id ORDER BY position ASC";
    $result = mysqli_query($conn, $query);

    if (!$result) {

        return null;
        
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);

}

function esc(String $value) {

    global $conn;

    $val = trim($value);

    return mysqli_real_escape_string($conn, $val);
    
}

function make_slug(String $string) {

	$string = strtolower($string);

    $replacements = array(
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ñ' => 'n'
    );
    
    $string = strtr($string, $replacements);
	
    return preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	
}

function convert_to_jpg($source, $target) {

    $image = imagecreatefromstring(file_get_contents($source));

    $new_image = imagecreatetruecolor(imagesx($image), imagesy($image));

    imagecopy($new_image, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
    imagejpeg($new_image, $target, 100);

    imagedestroy($image);
    imagedestroy($new_image);

    return true;
    
}

function convert_to_png($source, $target) {

    $image = imagecreatefromstring(file_get_contents($source));

    $new_image = imagecreatetruecolor(imagesx($image), imagesy($image));

    imagecopy($new_image, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
    imagepng($new_image, $target);

    imagedestroy($image);
    imagedestroy($new_image);

    return true;
    
}

function get_month_from_number($month) {

    $date = mktime(0, 0, 0, $month, 1);
    $month_name = strftime('%B', $date);

    return ucfirst($month_name);

}

function generate_token() {

    return bin2hex(random_bytes(16));

}