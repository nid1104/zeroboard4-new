<?php
/***************************************************************************
 * 공통 파일 include
 **************************************************************************/
require_once __DIR__ . '/_head.php';

$no = (int) Request::req('no');
$c_no = (int) Request::req('c_no');
$password = Request::post('password');
$des = Request::req('des');

check_csrf();
$referer_hdr = Request::header('Referer');
if ($referer_hdr === '' || stripos($referer_hdr, Request::header('Host')) === false) Error("정상적으로 글을 삭제하여 주시기 바랍니다.");
if (Request::method() == 'GET' ) Error("정상적으로 글을 삭제하시기 바랍니다");

/***************************************************************************
* 코멘트 삭제 진행
**************************************************************************/

// 원본글을 가져옴
$s_data = $connect->row("select * from {$t_comment}_{$id} where no=?", [$c_no]);
$s_data += array('ismember' => '', 'password' => '');

// 회원일때를 확인;;
if (!$is_admin && $member['level'] > $setup['grant_delete']) {
    if (!$s_data['ismember']) {
        if (!verifyHash($password, $s_data['password'])) Error("비밀번호가 올바르지 않습니다");
    } else {
        if ($s_data['ismember'] != $member['no']) Error("비밀번호를 입력하여 주십시요");
    }
}

// 코멘트 삭제
$connect->exec("delete from {$t_comment}_{$id} where no=?", [$c_no]);

// 코멘트 개수 정리
$total = $connect->value("select count(*) from {$t_comment}_{$id} where parent=?", [$no]);
$connect->exec("update {$t_board}_{$id} set total_comment=? where no=?", [$total, $no]);

// 회원일 경우 해당 해원의 점수 주기
if ($member['no'] == $s_data['ismember']) $connect->exec("update $member_table set point2=point2-1 where no=?", [$member['no']]);

// 페이지 이동
if ($setup['use_alllist']) Response::redirect("zboard.php?id=$id&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$des&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&no=$no");
else Response::redirect("view.php?id=$id&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$des&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&no=$no");
?>
