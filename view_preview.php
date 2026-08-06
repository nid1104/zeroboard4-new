<?php
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/include/list_check.php';

$id = Request::req('id');
if ($id !== '' && !isalNum($id)) Error("게시판 이름이 올바르지 않습니다", "window.close");
$subject = Request::req('subject');
$memo = Request::req('memo');
$use_html = Request::req('use_html');

$referer_hdr = Request::header('Referer');
if ($referer_hdr === '' || stripos($referer_hdr, Request::header('Host')) === false) Error("정상적으로 글을 작성하여 주시기 바랍니다.", "window.close");
if (stripos($referer_hdr, "write.php") === false) Error("정상적으로 글을 쓰시기 바랍니다", "window.close");
if (Request::method() == 'GET' ) Error("정상적으로 글을 쓰시기 바랍니다", "window.close");


if (!$subject) Error("제목을 입력하여 주십시요", "window.close");
if (!$memo) Error("내용을 입력하여 주십시요", "window.close");


$connect = dbconn();

// 게시판 설정 읽어 오기
$setup = get_table_attrib($id);

// 설정되지 않은 게시판
if (!$setup['name']) Error("생성되지 않은 게시판입니다.<br><br>게시판을 생성후 사용하십시오", "window.close()");

// 현재 게시판의 그룹의 설정 읽어 오기
$group = group_info($setup['group_no']);

// 회원 데이타 읽어 오기
$member = member_info();

// 현재 로그인되어 있는 멤버가 전체, 또는 그룹관리자인지 검사
if ($member['is_admin'] == 1 || ($member['is_admin'] == 2 && $member['group_no'] == $setup['group_no']) || check_board_master($member, $setup['no'])) $is_admin = 1; else $is_admin = "";


// 가상의 게시물 데이타 제작

if ($use_html < 2) {
    $memo = str_replace("  ", "&nbsp;&nbsp;", $memo);
    $memo = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $memo);
}


// 내용 제작
if (!$is_admin && $setup['grant_html'] < $member['level']) {

    // 내용의 HTML 금지;;
    if ($use_html != 1 || $setup['use_html'] == 0) $memo = del_html($memo);

    // HTML의 부분허용일때;;
    if ($use_html == 1 && $setup['use_html'] == 1) {
        $memo = zb_sanitize_html($memo, explode(",", $setup['avoid_tag']));
    }
} else {
    if (!$use_html) {
        $memo = del_html($memo);
    }
}

$data = array();
$data['memo'] = $memo;

// 제목 제작
$data['subject'] = $subject;

// 기타 데이타 작성
$data['use_html'] = $use_html;
$data['ismember'] = $member['no'];

$data += array('name' => '', 'homepage' => '', 'email' => '', 'is_secret' => 0, 'total_comment' => 0, 'hit' => 0, 'vote' => 0,
    'reg_date' => 0, 'depth' => 0, 'headnum' => 0, 'category' => '', 'sitelink1' => '', 'sitelink2' => '', 'islevel' => '',
    'file_name1' => '', 'file_name2' => '', 's_file_name1' => '', 's_file_name2' => '', 'download1' => '', 'download2' => '', 'ip' => '', 'child' => '', 'no' => '');

// 데이타 가공
list_check($data, 1);
?>
<html>
<head>
	<title><?= e($setup['title']) ?></title>
	<meta http-equiv=Content-Type content=text/html; charset=UTF-8>
	<link rel=StyleSheet HREF="skin/<?= e(sanitizePathComponent($setup['skinname'])) ?>/style.css" type=text/css title=style>
</head>
<body topmargin='10'  leftmargin='10' marginwidth='10' marginheight='10' <?php
	if ($setup['bg_color']) echo " bgcolor=" . e($setup['bg_color']);
if ($setup['bg_image']) echo " background=" . e($setup['bg_image']); ?>>

<table border=0 width=100% cellspacing=0 cellpadding=0 bgcolor=white>
<tr>
	<td align=left><img src=images/pv_title_left.gif border=0></td>
	<td width=100% background=images/pv_title_back.gif><img src=images/pv_title_back.gif></td>
	<td align=right><img src=images/pv_title_right.gif border=0></td>
</tr>
</table>

<table border=0 cellspacing=0 cellpadding=10 width=100% height=100% bgcolor=black>
<Tr bgcolor=white valign=top>
	<td height=20>
		<b><?= e($data['subject']) ?></b><br>
	</td>
</tr>
<Tr bgcolor=white valign=top>
	<td>
		<?= e($memo) ?>
	</td>
</tr>
</table>

</body>
</html>

