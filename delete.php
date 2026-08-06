<?php
/***************************************************************************
 * 공통 파일 include
 **************************************************************************/
require_once __DIR__ . '/_head.php';

$no = Request::req('no');
$c_no = Request::req('c_no');
$mode = Request::req('mode');
$input_password = '';

$referer_hdr = Request::header('Referer');
if ($referer_hdr === '' || stripos($referer_hdr, Request::header('Host')) === false) Error("정상적으로 글을 삭제하여 주시기 바랍니다.");

/***************************************************************************
 * 게시물 삭제 처리
 **************************************************************************/

// 원본글을 가져옴
$s_data = $connect->row("select * from {$t_board}_{$id} where no=?", [$no]);
$s_data += array('ismember' => '', 'name' => '');

if ($s_data['ismember'] || $is_admin || $member['level'] <= $setup['grant_delete']) {
    if ($s_data['ismember'] != $member['no'] && !$is_admin && $member['level'] > $setup['grant_delete']) Error("삭제할 권한이 없습니다");
    $title = "글을 삭제하시겠습니까?";
} else {
    $title = e($s_data['name']) . "님의 글을 삭제합니다.<br>비밀번호를 입력하여 주십시요";
    $input_password = "<input type=password name=password size=20 maxlength=20 class=input>";
}

$target = "delete_ok.php";

$a_list = "<a href=zboard.php?$href$sort>";

$a_view = "<a href=# onclick=history.back()>";

head();

include $dir . "/ask_password.php";

foot();

include "_foot.php";
?>
