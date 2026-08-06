<?php
require_once __DIR__ . '/lib.php';

if (Request::method() == 'GET') error('잘못된 접근입니다.');
check_csrf();

$email = Request::req('email');
$jumin1 = Request::post('jumin1');
$jumin2 = Request::post('jumin2');

// 웹마스터 E-mail
$_from = $_zbDefaultSetup['email'];

// 사이트 주소
$_homepage = $_zbDefaultSetup['url'];

// 사이트 이름
$_sitename = $_zbDefaultSetup['sitename'];

if (!$_from || !$_homepage || !$_sitename) error("관리자 정보가 입력되어 있지 않습니다.<br>setup.php 파일을 관리자가 수정하여야 합니다");

$connect = dbconn();

if (isblank($email)) Error("E-Mail을 입력하여 주세요");
if (!ismail($email)) Error("E-Mail을 제대로 입력하여 주세요");

if (isblank($jumin1) || !isnum($jumin1)) Error("주민등록번호를 제대로 입력하여 주세요");
if (isblank($jumin2) || !isnum($jumin2)) Error("주민등록번호를 제대로 입력하여 주세요");

$data = $connect->row("select * from zetyx_member_table where email=? and jumin=?", [$email, hash('sha256', $jumin1 . $jumin2)]);

if (empty($data['no'])) Error("입력하신 정보에 해당하는 회원이 없습니다.<br><br>다시 한번확인하여 주시기 바랍니다");
else {
    $temp = '';

    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < 10; $i++) {
        $temp .= $chars[random_int(0, $max)];
    }


    $connect->exec("update $member_table set password=? where no=?", [createHash($temp), $data['no']]);

    $name = $data['name'];
    $to = $data['email'];


    $subject = "안녕하세요, $_sitename 입니다";

    $comment = "안녕하세요.\n" . "$_sitename 입니다.\n" . "$name 님의 회원 아이디와 새롭게 변경된 비밀번호입니다. \n확인후 곧 바로 $_sitename ($_homepage) 에 로그인 하셔서 비밀번호를 변경하여 주시기 바랍니다.\n\nID : $data[user_id]\nPassword : $temp \n\n * 위의 비밀번호를 타이핑하기 힘들때 마우스로 더블클릭한후 Ctrl-C 를 눌러서 복사한후,\n 비밀번호 입력칸에서 Ctrl-V를 눌러서 복사하세요.";

    if (!zb_sendmail(0, $to, $name, $_from, "", $subject, $comment)) Error("메일 발송 에러");
}
?>
<script>
	alert('변경된 비밀번호가 <?= e($email) ?>로 발송되었습니다.\n\n메일을 확인하신후 곧 바로 로그인하여\n\n비밀번호를 변경하여 주시기 바라겠습니다');
	window.close();
</script>
