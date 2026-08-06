<?php
if (!defined('_ZB_PATH')) exit();


if ($member['is_admin'] > 2 && !preg_match('/' . preg_quote($no, '/') . ',/i', $member['board_name'])) error("사용 권한이 없습니다");

$board_data = $connect->row("SELECT * FROM $admin_table WHERE no = ?", [$no]);
if (empty($board_data)) error("생성되지 않은 게시판입니다.<br><br>게시판을 생성후 사용하십시오");

$group_no = $board_data['group_no'];
$group_data = $connect->row("SELECT * FROM $group_table WHERE no = ?", [$group_no]);
?>
<table border=0 cellspacing=1 cellpadding=0 width=100% bgcolor=#b0b0b0>
  <tr height=30><td bgcolor=#3d3d3d colspan=10><img src=images/admin_webboard.gif></td></tr>
<Tr height=30><td bgcolor=white colspan=10 align=right style=font-family:Tahoma;font-size:8pt;>
그룹 이름 : <b><?= e($group_data['name']) ?></b> , 게시판 이름 : <a href="zboard.php?id=<?= e($board_data['name']) ?>" target=_blank><b><?= e($board_data['name']) ?></a></b> &nbsp;&nbsp;&nbsp;
    <input type=button value='게시판관리' class=input style=width=100px onclick="location.href='<?= Request::scriptName()?>?exec=view_board&group_no=<?= e_js($group_no) ?>&exec2=modify&no=<?= e_js($no) ?>&page=<?= e_js($page) ?>&page_num=<?= e_js($page_num) ?>'">
    <input type=button value='카테고리 관리' class=input style=width=100px onclick="location.href='<?= Request::scriptName()?>?exec=view_board&group_no=<?= e_js($group_no) ?>&exec2=category&no=<?= e_js($no) ?>&page=<?= e_js($page) ?>&page_num=<?= e_js($page_num) ?>'">&nbsp;&nbsp;&nbsp;
</td></tr>
  <tr height=1><td bgcolor=#000000 style=padding:0px; colspan=10><img src=images/t.gif height=1></td></tr>
<form method=post action="<?= Request::scriptName() ?>">
<input type=hidden name=exec value="<?= e($exec) ?>">
<input type=hidden name=group_no value="<?= e($group_no) ?>">
<input type=hidden name=exec2 value="modify_grant_ok">
<input type=hidden name=page value="<?= e($page) ?>">
<input type=hidden name=page_num value="<?= e($page_num) ?>">
<input type=hidden name=no value="<?= e($no) ?>">
<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;width=30%><b>목록 보기 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_list class=input >
<?php
  for ($i = 1; $i <= 10; $i++)
      if ($i == $board_data['grant_list']) echo "<option value=$i selected>$i</option>";
      else echo "<option value=$i>$i</option>";
?>
     </select> &nbsp;&nbsp;
     글 목록 보기 권한을 레벨별로 지정합니다
  </td>
</tr>

<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;><b>내용 보기 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_view  class=input>
<?php
  for ($i = 1; $i <= 10; $i++)
      if ($i == $board_data['grant_view']) echo "<option value=$i selected>$i</option>";
      else echo "<option value=$i>$i</option>";
?>
     </select> &nbsp;&nbsp;
     글의 내용을 읽을수 있는 권한을 레벨별로 지정합니다
  </td>
</tr>

<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;><b>글쓰기 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_write class=input>
<?php
  for ($i = 1; $i <= 10; $i++)
      if ($i == $board_data['grant_write']) echo "<option value=$i selected>$i</option>";
      else echo "<option value=$i>$i</option>";
?>
     </select> &nbsp;&nbsp;
     글쓰기 권한을 레벨별로 지정합니다.
  </td>
</tr>


<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;><b>간단한 답글 쓰기 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_comment class=input>
<?php
  for ($i = 1; $i <= 10; $i++)
      if ($i == $board_data['grant_comment']) echo "<option value=$i selected>$i</option>";
      else echo "<option value=$i>$i</option>";
?>
     </select> &nbsp;&nbsp;
     간단한 답글 달기 권한을 레벨별로 지정합니다
  </td>
</tr>

<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;><b>답변쓰기 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_reply class=input>
<?php
  for ($i = 1; $i <= 10; $i++)
      if ($i == $board_data['grant_reply']) echo "<option value=$i selected>$i</option>";
      else echo "<option value=$i>$i</option>";
?>
     </select> &nbsp;&nbsp;
     댓글 달기 권한을 레벨별로 지정합니다.
  </td>
</tr>

<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;><b>삭제 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_delete class=input>
<?php
  for ($i = 1; $i <= 10; $i++)
      if ($i == $board_data['grant_delete']) echo "<option value=$i selected>$i</option>";
      else echo "<option value=$i>$i</option>";
?>
     </select> &nbsp;&nbsp;
     글 삭제 권한을 레벨별로 지정합니다.
  </td>
</tr>

<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;><b> HTML 사용 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_html class=input>
<?php
  for ($i = 1; $i <= 10; $i++)
      if ($i == $board_data['grant_html']) echo "<option value=$i selected>$i</option>";
      else echo "<option value=$i>$i</option>";
?>
     </select> &nbsp;&nbsp;
     HTML 모두 사용할수 있는 권한을 레벨별로 지정합니다.
  </td>
</tr>

<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;><b>공지사항 작성 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_notice class=input>
<?php
  for ($i = 1; $i <= 10; $i++)
      if ($i == $board_data['grant_notice']) echo "<option value=$i selected>$i</option>";
      else echo "<option value=$i>$i</option>";
?>
     </select> &nbsp;&nbsp;
    공지사항 작성 권한을 레벨별로 지정합니다.
  </td>
</tr>

<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;><b>비밀글 보기 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_view_secret class=input>
<?php
  for ($i = 1; $i <= 10; $i++)
      if ($i == $board_data['grant_view_secret']) echo "<option value=$i selected>$i</option>";
      else echo "<option value=$i>$i</option>";
?>
     </select> &nbsp;&nbsp;
    비밀 글 보기 권한을 레벨별로 지정합니다.
  </td>
</tr>

<tr height=25 bgcolor=#e0e0e0>
  <td  align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;><b>Image Box 사용 권한 &nbsp;</td>
  <td >&nbsp;&nbsp;
     <select name=grant_imagebox class=input>
<?php
  if (!$board_data['use_showip']) $board_data['use_showip'] = 1;
for ($i = 1; $i < 10; $i++)
    if ($i == $board_data['use_showip']) echo "<option value=$i selected>$i</option>";
    else echo "<option value=$i>$i </option>";
?>
     </select> &nbsp;&nbsp;
	Image Box 사용권한을 레벨별로 지정합니다. (회원만 사용가능합니다)
  </td>
<!-- Submit  -->

<tr align=right bgcolor=#ffffff><td colspan=2><img src=images/t.gif height=5><br><input type=image border=0 src=images/button_confirm.gif accesskey="s"> &nbsp;<img style=cursor:hand onclick=reset() border=0 src=images/button_cancel.gif>&nbsp;&nbsp;</td>
</form>
</tr>
</table>
</div>
