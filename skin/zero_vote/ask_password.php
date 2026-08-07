<?php
if (!defined('_ZB_PATH')) exit();

if (strpos($dir, "://") !== false || strpos($dir, "..") !== false) $dir = "./";
include "$dir/value.php";

/*
글을 삭제하거나 할때 비밀번호를 물어보는 부분입니다
 
<?=$target?> : 실행파일을 가리킵니다. 수정하지 마세요;;;
<?=$title?> : 타이틀을 출력합니다

<?=$a_list?> : 목록보기 링크
<?=$a_view?> : 내용보기 링크

<?=$invisible?> : 멤버나 관리자가 삭제시 삭제 버튼만 보입니다;;

<?=$input_password?> : 비밀번호를 물어보는 input=text 출력 
*/
?>

<br><br><br>
<div align=center>
<table border=0 width=300 cellpadding=0 cellspacing=0>
<tr>
  <td colspan=2 height=15 background="<?= e($dir) ?>/images/lh_bg.gif"><img src=images/t.gif height=1></td>
</tr>
<form method=post name=delete action="<?= e($target) ?>">
<input type=hidden name=page value="<?= e($page)?>">
<input type=hidden name=id value="<?= e($id)?>">
<input type=hidden name=no value="<?= e($no)?>">
<input type=hidden name=select_arrange value="<?= e($select_arrange)?>">
<input type=hidden name=desc value="<?= e($desc)?>">
<input type=hidden name=page_num value="<?= e($page_num)?>">
<input type=hidden name=keyword value="<?= e($keyword)?>">
<input type=hidden name=category value="<?= e($category)?>">
<input type=hidden name=sn value="<?= e($sn)?>">
<input type=hidden name=ss value="<?= e($ss)?>">
<input type=hidden name=sc value="<?= e($sc)?>">
<input type=hidden name=mode value="<?= e($mode)?>">
<input type=hidden name=c_no value="<?= e($c_no)?>">
<tr>
  <td colspan=2 height=30>&nbsp;&nbsp;<span style="font-family:Arial;font-size:8pt;font-weight:bold;"><font color=#333333>Enter</font> <span style=font-size:15px;letter-spacing:-1px;>Password</span></span></td>
</tr>
<tr height=1><td colspan=2 bgcolor="<?= e($sC_dark0) ?>"><img src=images/t.gif height=1></td></tr>
<tr height=25 bgcolor="<?= e($sC_light1) ?>" style=padding:5px;>
   <td align=center><?= $title ?></td>
</tr>
<tr bgcolor="<?= e($sC_light1) ?>">
   <td align=center><?= $input_password ?></td>
</tr>
<tr height=1><td colspan=2 bgcolor="<?= e($sC_dark1) ?>"><img src=images/t.gif height=1></td></tr>

<tr height=30>
  <td colspan=2 align=right>
     <input type=image align=absmiddle border=0 src="<?= e($dir) ?>/images/btn_confirm.gif"> <?= $a_list ?><img src="<?= e($dir) ?>/images/btn_list.gif" border=0 align=absmiddle></a> <?= $a_view ?><img src="<?= e($dir) ?>/images/btn_back.gif" align=absmiddle border=0></a>
  </td>
</tr>
</table>

