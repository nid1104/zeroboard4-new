<?php
require_once __DIR__ . '/lib.php';

$user_id = trim(Request::get('user_id'));

$canHangulId = strtolower(getDefaultSetup()['enable_hangul_id'] ?? '') == 'true';

if (($canHangulId && !preg_match('/^[a-zA-Z0-9_가-힣]*$/u', $user_id))
	|| (!$canHangulId && !preg_match('/^[a-zA-Z0-9_]*$/', $user_id))) Error("ID를 제대로 입력하여 주세요");
if (mb_strlen($user_id, 'UTF-8') < 4 || mb_strlen($user_id, 'UTF-8') > 40) Error("ID를 제대로 입력하여 주세요");

$connect = dbconn();
$check = $connect->value("select count(*) from $member_table where user_id=?", [$user_id]);
head();
?>
<table border=0 width=100% height=100%>
<tr>
  <td align=center>
<?php
  if ($check) echo e($user_id) . " 는 이미 등록된<br> 아이디입니다";
  else echo e($user_id) . " 는 사용하실수 있습니다";
?>

</td>
</tr>
<form>
<tr>
  <td align=center><input type=button value='Close window' onclick=window.close(); class=submit></td>
</tr>
</form>
</table>

<?php
	 foot();
?>
