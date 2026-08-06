<?php
if (!defined('_ZB_PATH')) exit();

if (strpos($dir, "://") !== false || strpos($dir, "..") !== false) $dir = "./";

$des = $des ?? Request::req('des');

if (!$data['vote']) $data['vote'] = 1;

$reply_result = $connect->all("select * from {$t_board}_{$id} where headnum=? and depth>0 order by arrangenum", [$data['headnum']]);

$q_keyword = urlencode($keyword); $q_category = urlencode($category);
$q_sn = urlencode($sn); $q_ss = urlencode($ss); $q_sc = urlencode($sc); $q_des = urlencode($des);

foreach ($reply_result as $reply_data) {
    include "include/reply_check.php";
    $subject = e($reply_data['subject']);
    $a_vote = "<a href=apply_vote.php?id=$id&no=$data[no]&sub_no=$reply_data[no]&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$q_des&cn=$q_sn&ss=$q_ss&sc=$q_sc&keyword=$q_keyword&category=$q_category&zb_csrf_token=" . Session::csrfToken() . ">";
    $bar_size = (int)(($reply_data['vote'] / $data['vote']) * 100);
    $vote = $reply_data['vote'];
    include "$dir/vote_list.php";
}

?>

