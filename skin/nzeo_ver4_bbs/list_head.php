<?php
if (!defined('_ZB_PATH')) exit();
$coloring = 0;
?>
<table border=0 cellspacing=1 cellpadding=4 width="<?= e($width) ?>" style=table-layout:fixed>
<form method=post name=list action=list_all.php>
<input type=hidden name=page value="<?= e($page) ?>">
<input type=hidden name=id value="<?= e($id) ?>">
<input type=hidden name=select_arrange value="<?= e($select_arrange) ?>">
<input type=hidden name=desc value="<?= e($desc) ?>">
<input type=hidden name=page_num value="<?= e($page_num) ?>">
<input type=hidden name=selected>
<input type=hidden name=exec>
<input type=hidden name=keyword value="<?= e($keyword) ?>">
<input type=hidden name=sn value="<?= e($sn) ?>">
<input type=hidden name=ss value="<?= e($ss) ?>">
<input type=hidden name=sc value="<?= e($sc) ?>">
<col width=50></col><?= $hide_category_start?><col width=80></col><?= $hide_category_end?><col width=></col><col width=100></col><col width=70></col><col width=50></col><col width=50></col>
<tr align=center class=title>
	<td class=title_han height=30>번호</td>
	<?= $hide_category_start?><td class=title_han nowrap>분류</td><?= $hide_category_end?>
	<td class=title_han>제목</td>
	<td class=title_han>작성자</td>
	<td class=title_han>작성일</td>
	<td class=title_han><?= $a_vote?><font class=title_han>추천</font></a></td>
	<td class=title_han><?= $a_hit?><font class=title_han>조회</font></a></td>
</tr>

