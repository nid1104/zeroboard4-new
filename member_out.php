<?php
// 라이브러리 함수 파일 인크루드
require_once __DIR__ . '/lib.php';

check_csrf();
if (stripos(Request::header('Referer'), "member_modify.php") === false) Error("제대로 된 접근을 하여 주시기 바랍니다");

// DB 연결
if (!isset($connect)) $connect = dbConn();

// 회원 정보를 얻어옴
$member = member_info();
$group_no = $member['group_no'];

// 멤버 정보 삭제
$connect->exec("delete from $member_table where no=?", [$member['no']]);


// 쪽지 테이블에서 멤버 정보 삭제
$connect->exec("delete from $get_memo_table where member_no=?", [$member['no']]);
$connect->exec("delete from $send_memo_table where member_no=?", [$member['no']]);

// 각종 게시판에서 현재 탈퇴한 멤버의 모든 정보를 삭제 (부하 문제로 인해서 주석 처리)
/*
$result=$connect->all("select name from $admin_table");
foreach($result as $data) {
	if(!isalNum($data['name'])) continue;
	// 게시판 테이블에서 삭제
	try { $connect->exec("update {$t_board}_{$data['name']} set ismember='0', password=? where ismember=?", [createHash((string)time()), $member['no']]); } catch (mysqli_sql_exception $e) {}
	// 코멘트 테이블에서 삭제
	try { $connect->exec("update {$t_comment}_{$data['name']} set ismember='0', password=? where ismember=?", [createHash((string)time()), $member['no']]); } catch (mysqli_sql_exception $e) {}
}
*/

// 그룹테이블에서 회원수 -1
$connect->exec("update $group_table set member_num=member_num-1 where no = ?", [$group_no]);

// 로그아웃 시킴
destroyZBSessionID($member['no']);

Session::set('zb_logged_no', '');
Session::set('zb_logged_time', '');
Session::set('zb_logged_ip', '');
Session::set('zb_secret', '');
Session::set('zb_last_connect_check', '0');

head();
?>
<script>
alert("정상적으로 탈퇴가 되었습니다.");
opener.window.history.go(0);
window.close();
</script>
<?php
    foot();
?>
