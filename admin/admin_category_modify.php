<?php
if (!defined('_ZB_PATH')) exit();

// 카테고리 수정 //////////////////////////////////////////////////////////////////////
$table_data = $connect->row("select name from $admin_table where no=?", [$no]);
if (empty($table_data) || !isalNum($table_data['name'] ?? '')) Error("게시판 정보를 찾을 수 없습니다");
$category_data = $connect->row("select * from {$t_category}_{$table_data['name']} where no=?", [$category_no]);
if (empty($category_data)) error('지정된 카테고리가 존재하지 않습니다');
?>
<table border=0 cellspacing=1 cellpadding=0 width=100% bgcolor=#b0b0b0>
  <tr height=30><td bgcolor=#3d3d3d colspan=10><img src=images/admin_webboard.gif></td></tr>
  <tr height=1><td bgcolor=#000000 style=padding:0px; colspan=10><img src=images/t.gif height=1></td></tr>
<form method=post action="<?= Request::scriptName() ?>">
<input type=hidden name=group_no value="<?= e($group_no) ?>">
<input type=hidden name=exec value="view_board">
<input type=hidden name=exec2 value="category_modify_ok">
<input type=hidden name=page value="<?= e($page) ?>">
<input type=hidden name=page_num value="<?= e($page_num) ?>">
<input type=hidden name=no value="<?= e($no) ?>">
<input type=hidden name=category_no value="<?= e($category_no) ?>">
<tr height=30>
 <td align=center>
 	<table border=0 cellspacing=0 cellpadding=2>
	<tr>
 		<td align=center style=font-family:Tahoma;font-size:8pt;font-weight:bold>카테고리 이름 변경 </td>
 		<Td>&nbsp;<input type=text name=name value="<?= e($category_data['name']) ?>"></td>
 		<td><input type=submit value=' 이름 변경 ' style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px;> &nbsp; <input type=button value=" 이전화면 " style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px; onclick=history.back()></td>
	</tr>
	</table>
</tr>
</table>
</form>
<br>
