<?php 

if (!defined('INTERNAL_ACCESS')) {
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

/* CÓMO APLICAR 

if ($count_posts > $search_limit) {

    $items_per_page = $search_limit;
    $total_items = $search_count_posts;
    $page = isset($_GET['p']) ? esc($_GET['p']) : 1;

    $link_extra = [];

    if(isset($_GET['search']) && $var1 = $_GET['search']) { 

        $link_extra[] = "search=" . urlencode($var1);

    }

    include(ROOT_PATH . '/admin/includes/pagination.php'); 

}

*/
                        
$link_extra = implode("&amp;", $link_extra);

if($link_extra) {

    $link_extra .= "&amp;";

}

$tmp = [];

for($p=1, $i=0; $i < $total_items; $p++, $i += $items_per_page) {

    if($page == $p) {

        $tmp[] = "<b>{$p}</b>";

    } 
    else {

        $tmp[] = "<a href=\"{$_SERVER['SCRIPT_NAME']}?{$link_extra}p={$p}\">{$p}</a>";

    }
}

for($i = count($tmp) - 3; $i > 1; $i--) {

    if(abs($page - $i - 1) > 2) {

        unset($tmp[$i]);

    }
}

if(count($tmp) > 1) {

    echo "<p>";

    if($page > 1) {

        echo "<a href=\"{$_SERVER['SCRIPT_NAME']}?{$link_extra}p=" . ($page - 1) . "\">&laquo; " . $lang['prev'] . "</a> | ";
    } 
    else {

        echo "Page ";

    }

    $last_link = 0;
    foreach($tmp as $i => $link) {

        if($i > $last_link + 1) {

            echo " ... "; 

        } 
        else if($i) {

            echo " | ";

        }

        echo $link;
        $last_link = $i;

    }

    if($page <= $last_link) {

        echo " | <a href=\"{$_SERVER['SCRIPT_NAME']}?{$link_extra}p=" . ($page + 1) . "\">" . $lang['next'] . " &raquo;</a>";
        
    }

    echo "</p>\n\n";
}
