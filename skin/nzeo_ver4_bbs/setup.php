<?php
if (!defined('_ZB_PATH')) exit();

if (stripos($a_login, "Zeroboard") === false) $a_login = str_replace(">", "><font class=list_han>", $a_login) . "&nbsp;";

if (stripos($a_logout, "Zeroboard") === false) $a_logout = str_replace(">", "><font class=list_han>", $a_logout) . "&nbsp;";

if (stripos($a_setup, "Zeroboard") === false) $a_setup = str_replace(">", "><font class=list_han>", $a_setup) . "&nbsp;";

if (stripos($a_member_join, "Zeroboard") === false) $a_member_join = str_replace(">", "><font class=list_han>", $a_member_join) . "&nbsp;";

if (stripos($a_member_modify, "Zeroboard") === false) $a_member_modify = str_replace(">", "><font class=list_han>", $a_member_modify) . "&nbsp;";

if (stripos($a_member_memo, "Zeroboard") === false) $a_member_memo = str_replace(">", "><font class=list_han>", $a_member_memo) . "&nbsp;";

?>



<table border=0 cellspacing=0 cellpadding=0 width=<?= e($width) ?>>

<tr>

	<td <?php if (!$setup['use_category']) echo "align=right"; ?>>

		<?= $a_login?>로그인</a>

		<?= $a_member_join?>회원가입</a>

		<?= $a_member_modify?>정보수정</a>

		<?= $a_member_memo?>메모박스</a>

		<?= $a_logout?>로그아웃</a>

		<?= $a_setup?>설정변경</a>

	</td>

<?= $hide_category_start?>

	<td align=right><font class=list_eng><b>Category</b> :</font> <?= $a_category?></td>

<?= $hide_category_end?>

</tr>

</table>

