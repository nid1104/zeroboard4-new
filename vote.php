<?php
/***************************************************************************
 * 공통파일 include
 **************************************************************************/
require_once __DIR__ . '/_head.php';

$no = Request::req('no');
$des = Request::req('des');

check_csrf(true);

$q_keyword = urlencode($keyword);
$q_category = urlencode($category);
$q_sn = urlencode($sn);
$q_ss = urlencode($ss);
$q_sc = urlencode($sc);

/***************************************************************************
 * 설정 체크
 **************************************************************************/

// 사용권한 체크
if ($setup['grant_view'] < $member['level'] && !$is_admin) Error("사용권한이 없습니다", "login.php?id=$id&page=$page&page_num=$page_num&category=$q_category&sn=$q_sn&ss=$q_ss&sc=$q_sc&keyword=$q_keyword&no=$no&file=zboard.php");

// 현재글의 Vote수 올림;;
if (strpos(Session::get('zb_vote', ''), $setup['no'] . "_" . $no) === false) {
    $connect->exec("update {$t_board}_{$id} set vote=vote+1 where no=?", [$no]);
    $vote_str = "," . $setup['no'] . "_" . $no;

    Session::set('zb_vote', Session::get('zb_vote', '') . $vote_str);
}

// 페이지 이동
if ($setup['use_alllist']) $temp_href = "zboard.php"; else $temp_href = "view.php";
Response::redirect("$temp_href?id=$id&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$des&sn=$q_sn&ss=$q_ss&sc=$q_sc&keyword=$q_keyword&category=$q_category&no=$no");
?>
