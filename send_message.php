<?php
// 라이브러리 함수 파일 인크루드
require_once __DIR__ . '/lib.php';

check_csrf();

$member_no = (int) Request::req('member_no');
$kind = Request::req('kind');
$subject = Request::req('subject');
$memo = Request::req('memo');

// DB 연결
if (!isset($connect)) $connect = dbConn();

// 글쓴이의 정보를 갖고옴;;
$data = $connect->row("select * from $member_table where no=?", [$member_no]);
$data += array('no' => '', 'name' => '', 'group_no' => '');

// 멤버정보 구하기
$member = member_info();

if (!$member['no']) Error("회원만이 쪽지보내가기 가능합니다", "window.close");

// 그룹데이타 읽어오기;;
$group_data = $connect->row("select * from $group_table where no=?", [$data['group_no']]);


// 쪽지 보내기일때;;
if ($kind == 1 && $member['no'] && $data['no']) {
    if (isblank($subject)) Error("제목이 없습니다. 제목을 입력해 주십시오.");
    if (isblank($memo)) Error("내용이 없습니다. 내용을 입력해 주십시오.");

    $reg_date = time();
    $connect->exec("insert into $get_memo_table (member_no,member_from,subject,memo,readed,reg_date) values (?,?,?,?,1,?)", [$data['no'], $member['no'], $subject, $memo, $reg_date]);
    $connect->exec("insert into $send_memo_table (member_to,member_no,subject,memo,readed,reg_date) values (?,?,?,?,1,?)", [$data['no'], $member['no'], $subject, $memo, $reg_date]);
    $connect->exec("update $member_table set new_memo=1 where no=?", [$data['no']]);
    echo "<script language=\"javascript\">alert(\"" . e_js($data['name']) . " 님께 쪽지를 보냈습니다\");window.close();</script>";
}
?>
