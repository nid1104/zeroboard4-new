</table>
<?php
    if (!defined('_ZB_PATH')) exit();

if (stripos($a_list, "Zeroboard") === false) $a_list = str_replace(">", "><font class=list_eng>", $a_list) . "&nbsp;&nbsp;";
if (stripos($delete_all, "Zeroboard") === false) $a_delete_all = str_replace(">", "><font class=list_eng>", $a_delete_all) . "&nbsp;&nbsp;";
if (stripos($a_1_prev_page, "Zeroboard") === false) $a_1_prev_page = str_replace(">", "><font class=list_eng>", $a_1_prev_page) . "&nbsp;&nbsp;";
if (stripos($a_1_next_page, "Zeroboard") === false) $a_1_next_page = str_replace(">", "><font class=list_eng>", $a_1_next_page) . "&nbsp;&nbsp;";
if (stripos($a_write, "Zeroboard") === false) $a_write = str_replace(">", "><font class=list_eng>", $a_write) . "&nbsp;&nbsp;";
if (stripos($a_prev_page, "Zeroboard") === false) $a_prev_page = str_replace(">", "><font class=list_eng>", $a_prev_page) . "&nbsp;&nbsp;";
if (stripos($a_next_page, "Zeroboard") === false) $a_next_page = str_replace(">", "><font class=list_eng>", $a_next_page) . "&nbsp;&nbsp;";
$print_page = str_replace("<font style=font-size:8pt>", "<font class=list_eng>", $print_page);
$print_page = str_replace("계속 검색", "<font class=list_han>계속 검색", $print_page);
$print_page = str_replace("이전 검색", "<font class=list_han>계속 검색", $print_page);
?>
<img src=<?= e($dir) ?>/t.gif border=0 height=10><br>

<table border=0 cellpadding=0 cellspacing=0 width=<?= e($width) ?>>
<tr valign=top>
	<td>
		<?= $a_list?>목록보기</a>
		<?= $a_delete_all?>관리자기능</a>
		<?= $a_1_prev_page?>이전페이지</a>
		<?= $a_1_next_page?>다음페이지</a>
		<?= $a_write?>글쓰기</a>
	</td>
	<td align=right>
		<?= $a_prev_page?>[이전 <?= e($setup['page_num']) ?>개]</a></font> <?= $print_page?> <?= $a_next_page?>[다음 <?= e($setup['page_num']) ?>개]</font></a><br>
		<table border=0 cellspacing=0 cellpadding=0>
		</form>
		<form method=get name=search action="<?= e(Request::scriptName()) ?>"><input type=hidden name=id value="<?= e($id) ?>"><input type=hidden name=select_arrange value="<?= e($select_arrange) ?>"><input type=hidden name=desc value="<?= e($desc) ?>"><input type=hidden name=page_num value="<?= e($page_num) ?>"><input type=hidden name=selected><input type=hidden name=exec><input type=hidden name=sn value="<?= e($sn) ?>"><input type=hidden name=ss value="<?= e($ss) ?>"><input type=hidden name=sc value="off"><input type=hidden name=category value="<?= e($category) ?>">
		<tr>
			<td>
				<a href="javascript:OnOff('sn')" onfocus=blur()><img src="<?= e($dir) ?>/name_<?= e($sn) ?>.gif" border=0 name=sn></a>&nbsp;
				<a href="javascript:OnOff('ss')" onfocus=blur()><img src="<?= e($dir) ?>/subject_<?= e($ss) ?>.gif" border=0 name=ss></a>&nbsp;&nbsp;
				<a href="javascript:OnOff('sc')" onfocus=blur()><img src="<?= e($dir) ?>/content_<?= e($sc) ?>.gif" border=0 name=sc></a>&nbsp;&nbsp;
			</td>
			<td><input type=text name=keyword value="<?= e($keyword) ?>" class=input size=10></td>
			<td><input type=submit class=submit value="검색"></td>
			<td><input type=button class=button value="취소" onclick="location.href='zboard.php?id=<?= e_js($id) ?>'"></td>
		</tr>
		</form>
		</table>
	</td>
</tr>
</table>
<br>
