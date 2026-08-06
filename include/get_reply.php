<?php
if (!defined('_ZB_PATH')) exit();

if (strpos($dir, "://") !== false || strpos($dir, "..") !== false) $dir = "./";

$reply_result = $connect->all("select * from {$t_board}_{$id} where headnum=? and depth>0 order by arrangenum", [$data['headnum']]);

foreach ($reply_result as $reply_data) {
    include "include/reply_check.php";
    include "$dir/list_reply.php";
}

?>

