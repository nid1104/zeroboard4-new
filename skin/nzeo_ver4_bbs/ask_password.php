<?php if (!defined('_ZB_PATH')) exit(); ?>
<br><br><br>

<form method=post name=delete action="<?= e($target) ?>">

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

<input type=hidden name=c_no value="<?= e($c_no) ?>">

<table border=0 width=250 cellspacing=1 cellpadding=0>

<tr class=title>

   <td align=center class=title_han><b><?= $title?></b></td>

</tr>

<?php

	if (!$member['no']) {

	    ?>

<tr height=60>

   <td align=center class=list0>

     <font class=list_eng><b>Password</b> :</font><?= $input_password ?> 

   </td>

</tr>

<?php

	}

?>

<tr class=list0 height=30>

	<td align=center>

	    <input type=submit class=submit value=" 확  인 " border=0 accesskey="s">

	    <input type=button class=button value="이전화면" onclick=history.back()>

   </td>

</tr>

</table>

</form>



