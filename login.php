<?php
require_once __DIR__ . '/lib.php';

// 단락 평가로 $group_no가 미할당되지 않도록 할당과 검사를 분리
$id = Request::req('id');
if ($id !== '' && !isalNum($id)) Error("게시판 이름이 올바르지 않습니다", "");
$group_no = Request::req('group_no');
$page = Request::req('page');
$no = Request::req('no');
$select_arrange = Request::req('select_arrange');
$desc = Request::req('desc');
$page_num = Request::req('page_num');
$keyword = Request::req('keyword');
$category = Request::req('category');
$sn = Request::req('sn');
$ss = Request::req('ss');
$sc = Request::req('sc');
$mode = Request::req('mode');
$s_url = Request::req('s_url');
$referer = Request::req('referer');
$autologin = array('ok' => '');

if (!$id && !$group_no) Error("게시판 이름이나 그룹번호를 지정하여 주셔야 합니다.<br><br>(login.php?id=게시판이름   또는  login.php?group_no=번호)", "");

$connect = dbConn();

// 현재 게시판 설정 읽어 오기
if ($id) {
    $setup = get_table_attrib($id);

    // 설정되지 않은 게시판일때 에러 표시
    if (!$setup['name']) Error("생성되지 않은 게시판입니다.<br><br>게시판을 생성후 사용하십시오");

    // 현재 게시판의 그룹의 설정 읽어 오기
    $group = group_info($setup['group_no']);
    $dir = "skin/" . sanitizePathComponent($setup['skinname']);
    $file = "skin/" . sanitizePathComponent($setup['skinname']) . "/login.php";

} else {
    if ($group_no) $group = group_info($group_no);
    if (!$group['no']) error("지정된 그룹이 존재하지 않습니다");
}

head();
?>

<script>
 function check_submit()
 {
  if(!login.user_id.value)
  {
   alert("아이디를 입력하여 주세요");
   login.user_id.focus();
   return false;
  }
  if(!login.password.value)
  {
   alert("비밀번호를 입력하여 주세요");
   login.password.focus();
   return false;
  }
  check=confirm("자동 로그인 기능을 사용하시겠습니까?\n\n자동 로그인 사용시 다음 접속부터는 로그인을 하실필요가 없습니다.\n\n단, 게임방, 학교등 공공장소에서 이용시 개인정보가 유출될수 있으니 조심하여 주십시요");
  if(check) {login.auto_login.value=1;}
  return true;
 }
</script>

<form method=post action=login_check.php onsubmit="return check_submit();" name=login>
<input type=hidden name=auto_login value="<?= $autologin['ok'] ? "1" : "0" ?>">
<input type=hidden name=page value="<?= e($page) ?>">
<input type=hidden name=id value="<?= e($id) ?>">
<input type=hidden name=no value="<?= e($no) ?>">
<input type=hidden name=select_arrange value="<?= e($select_arrange) ?>">
<input type=hidden name=desc value="<?= e($desc) ?>">
<input type=hidden name=page_num value="<?= e($page_num) ?>">
<input type=hidden name=keyword value="<?= e($keyword) ?>">
<input type=hidden name=category value="<?= e($category) ?>">
<input type=hidden name=sn value="<?= e($sn) ?>">
<input type=hidden name=ss value="<?= e($ss) ?>">
<input type=hidden name=sc value="<?= e($sc) ?>">
<input type=hidden name=mode value="<?= e($mode) ?>">
<input type=hidden name=s_url value="<?= e($s_url) ?>">
<input type=hidden name=referer value="<?= e($referer) ?>">

<?php
	if ($id) include $file;
?>

</form>

<?php
	foot();
?>

