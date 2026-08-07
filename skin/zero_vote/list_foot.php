<?php if (!defined('_ZB_PATH')) exit(); ?>
</td>
</tr>
</table>

<!-- 버튼 부분 -->
<table border=0 cellspacing=1 cellpadding=1 width="<?= $width ?>">
<tr>
 <td align=right>
  <?= $a_list ?><img src="<?= e($dir) ?>/list.gif" border=0></a>
  <?= $a_write ?><img src="<?= e($dir) ?>/write.gif" border=0></a>
 </td>
</form>
</tr>
</table>

<table border=0 cellspacing=1 cellpadding=1 width="<?= $width ?>">
<tr align=center>
 <td colspan=2><?= $a_prev_page ?>[prev]</a> <?= $print_page ?> <?= $a_next_page ?>[next]</a></td>
</tr>
<tr>
 <td>
<!-- 검색폼 부분 ---------------------->
<!-- 폼태그 부분;; 수정하지 않는 것이 좋습니다 -->
<form method=post name=search action="<?= Request::scriptName() ?>">
<input type=hidden name=page value="<?= e($page) ?>">
<input type=hidden name=id value="<?= e($id) ?>">
<input type=hidden name=select_arrange value="<?= e($select_arrange) ?>">
<input type=hidden name=desc value="<?= e($desc) ?>">
<input type=hidden name=page_num value="<?= e($page_num) ?>">
<input type=hidden name=selected>
<input type=hidden name=exec>
<input type=hidden name=sn value="<?= e($sn) ?>">
<input type=hidden name=ss value="<?= e($ss) ?>">
<input type=hidden name=sc value="<?= e($sc) ?>">
<input type=hidden name=category value="<?= e($category) ?>">
<!----------------------------------------------->
 </td>
 <td>

<table border=0 width=100% cellspcing=0 cellpadding=0>
<tr>
 <td colspan=2 align=center>
    <a href="javascript:OnOff('sn')"><img src="<?= e($dir) ?>/name_<?= e($sn) ?>.gif" border=0 name=sn></a>
    <a href="javascript:OnOff('ss')"><img src="<?= e($dir) ?>/subject_<?= e($ss) ?>.gif" border=0 name=ss></a>
    <a href="javascript:OnOff('sc')"><img src="<?= e($dir) ?>/content_<?= e($sc) ?>.gif" border=0 name=sc></a><img src=images/t.gif width=35 height=1><br>
   <img src="<?= e($dir) ?>/images/search_left.gif" align=absmiddle><input type=text name=keyword value="<?= e($keyword) ?>" <?= size(15) ?> class=input style="font-size:8pt;font-family:Arial;vertical-align:top;border-left-color:#ffffff;border-right-color:#ffffff;border-top-color:<?= e($sC_search0) ?>;border-bottom-color:<?= e($sC_search0) ?>;height:18px;"><input type=image border=0 align=absmiddle src="<?= e($dir) ?>/images/search_right.gif"><?= $a_cancel ?><img src="<?= e($dir) ?>/images/search_right2.gif" align=absmiddle border=0></a>
 </td>
</form>
</tr>

<!-- 페이지 출력 ---------------------->
</form>
</table>

</td></tr></table>
