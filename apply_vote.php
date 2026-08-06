<?php
/***************************************************************************
 * 공통파일 include
 **************************************************************************/
require_once __DIR__ . '/_head.php';

$no = (int) Request::req('no');
$sub_no = (int) Request::req('sub_no');
$des = Request::req('des');

check_csrf(true);

// 사용권한 체크
if ($setup['grant_view'] < $member['level'] && !$is_admin) Error("사용권한이 없습니다", "login.php?id=$id&page=$page&page_num=$page_num&category=$category&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&no=$no&file=zboard.php");

// 현재글의 Vote수 올림;;
if (strpos(Session::get('zb_vote', ''), $setup['no'] . "_" . $no) === false) {
    $connect->exec("update {$t_board}_{$id} set vote=vote+1 where no=?", [$sub_no]);
    $connect->exec("update {$t_board}_{$id} set vote=vote+1 where no=?", [$no]);

    Session::set('zb_vote', Session::get('zb_vote', '') . "," . $setup['no'] . "_" . $no);
}

// 페이지 이동
if ($setup['use_alllist']) Response::redirect("zboard.php?id=$id&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$des&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&category=$category&no=$no");
else Response::redirect("view.php?id=$id&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$des&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&category=$category&no=$no");

?>
