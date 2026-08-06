<?php
require_once __DIR__ . '/lib.php';
$connect = dbConn();

set_time_limit(0);

function thisError($message) {
    print ("<script>\nalert('$message');\nwindow.close();\n</script>\n");
    exit();
}

$member = member_info();
if (!$member['no']) thisError("로그인후 사용하여주십시요");

if ($member['is_admin'] > 3 || $member['is_admin'] < 1) thisError("관리자페이지를 사용할수 있는 권한이 없습니다");

check_csrf();

$s_comment = Request::req('s_comment');
$comment = Request::req('comment');
$from = Request::req('from');
$name = Request::req('name');
$subject = Request::req('subject');
$page = (int) Request::req('page');
$fault = (int) Request::req('fault');
$true = (int) Request::req('true');
$nomailing = (int) Request::req('nomailing');
$sendnum = (int) Request::req('sendnum');
$group_no = (int) Request::req('group_no');
$total_member_num = (int) Request::req('total_member_num');
$total_member = (int) Request::req('total_member');
$totalpage = (int) Request::req('totalpage');
$cart = Request::post('cart');
$html = Request::req('html');
$target_srls = array();

if ($s_comment) $comment = $s_comment;
else $s_comment = $comment;

if (isblank($from)) thisError("보내는 이의 mail을 적어주십시요");
if (isblank($name)) thisError("보내시는 분의 이름을 적어주십시요");
if (isblank($subject)) thisError("제목을 적어주십시요");
if (isblank($comment)) thisError("내용을 적어주십시요");

// 페이지 이동 할때 페이지를 구함
if (!$page) $page = 1; else $page++;
if (!$sendnum) $sendnum = 100;
$s_que = '';
if (!$total_member_num) {
    $total_member_num = (int)$connect->value("select count(*) from $member_table where group_no=?", [$group_no]);
}

if ($cart && strpos($cart, '||') !== false) {
    $temp = explode('||', $cart);
    $_in_marks = array();
    foreach ($temp as $v) {
        $_in_marks[] = '?';
        $target_srls[] = (int)$v; // IN 절 바인딩 파라미터 (정수)
    }
    if ($_in_marks) $s_que = ' AND ( no IN (' . implode(',', $_in_marks) . ') )';
}

$startnum = ($page - 1) * $sendnum;

if (!$total_member) {
    $total_member = (int)$connect->value("select count(*) from $member_table where group_no=? $s_que", array_merge(array($group_no), $target_srls));
}

if (!$totalpage) $totalpage = (int)(($total_member - 1) / $sendnum) + 1;

if ($total_member == 0) thisError("메일을 보낼 회원이 없습니다");

$result = $connect->all("select name, email, mailing from $member_table where group_no=? $s_que order by no limit ?, ?", array_merge(array($group_no), $target_srls, array((int)$startnum, (int)$sendnum)));

head( "onload=window.resizeTo(550,420); bgcolor=white");
?>

<br>
<center><b>메일링 발송</b></center><br>

<table border=0 cellpadding=4 cellspacing=1 width=100% bgcolor=white height=30>
<form action=<?= Request::scriptName() ?> method=post>
<tr>
	<td>
		전체 그룹 회원 수 : <?= number_format($total_member_num)?> 명<br>
		<img src=images/t.gif border=0 height=5><br>
		메일링 발송 대상 회원 수 : <?= number_format($total_member)?> 명<br>
		<img src=images/t.gif border=0 height=5><br>
		메일 발송 단위  : <?= e($sendnum) ?> 명 단위로 잘라서 발송<br>
		<img src=images/t.gif border=0 height=5><br>
		메일 발송 페이지 : <?= e($page) ?> / <?= e($totalpage) ?><br>

<?php
	$fault = 0;
$i = 1;
foreach ($result as $data) {
    if ($data['mailing']) {

        $temp = zb_sendmail($html, $data['email'], $data['name'], $from, $name, $subject, $comment);

        if (!$temp) $fault++;
        else $true++;

        echo ".";

    } else {

        $nomailing++;

    }

    flush();

}
?>

		<img src=images/t.gif border=0 height=5><br>
		메일 발송 결과 : <?= e($true) ?>명 발송 성공 (<?= e($nomailing) ?>명은 메일링 수신 거부)<br>
		<img src=images/t.gif border=0 height=5><br>
		<font color=white>메일 발송 결과 : </font><?= e($fault) ?>명 발송 실패<br>
		<img src=images/t.gif border=0 height=5><br>
		<center>
<?php
	if ($page == $totalpage) {
	    ?>
		<input type=button value="메일링 발송 완료하였습니다" onclick=window.close() class=submit style=width:100%>
<?php
	} else {
	    ?>
		<input type=submit value="다음 <?= e($sendnum) ?>명 에게 메일 발송" class=submit style=width:100%>
<?php
	}
?>
		</center>
		<textarea name="s_comment" cols=1 rows=1 style=width:1px;height:1px><?= e($s_comment) ?></textarea>
	</td>
</tr>
<input type=hidden name="from" value="<?= e($from) ?>">
<input type=hidden name="name" value="<?= e($name) ?>">
<input type=hidden name="subject" value="<?= e($subject) ?>">
<input type=hidden name="page" value="<?= e($page) ?>">
<input type=hidden name="totalpage" value="<?= e($totalpage) ?>">
<input type=hidden name="total_member_num" value="<?= e($total_member_num) ?>">
<input type=hidden name="total_member" value="<?= e($total_member) ?>">
<input type=hidden name="sendnum" value="<?= e($sendnum) ?>">
<input type=hidden name="fault" value="<?= e($fault) ?>">
<input type=hidden name="true" value="<?= e($true) ?>">
<input type=hidden name="cart" value="<?= e($cart) ?>">
<input type=hidden name="html" value="<?= e($html) ?>">
<input type=hidden name="nomailing" value="<?= e($nomailing) ?>">
<?php /* <input type=hidden name="s_que" value="<?= e($s_que) ?>"> */ ?>
<input type=hidden name="group_no" value="<?= e($group_no) ?>">
</form>
</table>
<?php
	foot();
?>
