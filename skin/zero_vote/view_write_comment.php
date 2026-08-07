<?php
/* 간단한 답글 쓰기 표시

 -- 간단한 답글 관련
 <?=$hide_comment_start?> <?=$hide_comment_end?> : 간단한 답글 쓰기 보여주기/ 숨기기
 <?=$hide_c_password_start?> <?=$hide_c_password_end?> : 간단한 답글시 비밀번호 입력 보여주기/ 숨기기;;

 <?=$c_name?> : 코멘트시 이름 입력하는 곳;;

 ** view.php 제일 아래쪽에 간답한 답글이 시작하는 <table>태그 시작부분이 있습니다.
    그리고 간단한 답글이 있으면 view_comment_view.php 파일에서 출력을 합니다.

*/
if (!defined('_ZB_PATH')) exit();
?>


<!-- 간단한 답변글 쓰기 -->
<tr>
<td width=100%>
<table border=0 width=100% cellspacing=0 cellpadding=0 height=30>
<tr>
<td width=0>
<form method=post name=write action="comment_ok.php">
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
</td>
<td align=center>
   <font color=444444 >이름 : </b></font><b> <?= $c_name ?> &nbsp;</b>
   <font color=444444 >의견 : </b></font> <input type=text name=memo <?= size(40) ?> maxlength=100 class=input>
   <?= $hide_c_password_start ?> &nbsp;
   <font color=444444 >비밀번호 : </b></font>  <input type=password name=password <?= size(10) ?> maxlength=20 class=input>
   <?= $hide_c_password_end ?>
   <input type=submit value="입력" class=submit>
 </td>
</tr>
</table>
</form>
</table>
