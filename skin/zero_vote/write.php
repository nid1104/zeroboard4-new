<?php if (!defined('_ZB_PATH')) exit(); ?>
<table border=0 width=600 cellspacing=0 cellpadding=0>
<tr>
  <td><img src="<?= e($dir) ?>/1.gif" border=0></td>
  <td background="<?= e($dir) ?>/2.gif" width=100%><img src="<?= e($dir) ?>/2.gif" border=0></td>
  <td><img src="<?= e($dir) ?>/3.gif" border=0></td>
</tr>

<tr>
  <td background="<?= e($dir) ?>/4.gif"><img src="<?= e($dir) ?>/4.gif" border=0></td>
  <td align=center><b>

<?php
 if (!$mode || $mode == "write") echo "새로운 설문조사 작성";
 elseif ($mode == "reply") echo "설문조사 항목 추가";
 else echo "설문조사 제목 수정";
?>
  </b><br>
<?php
 if ($mode != "modify") $subject = "";
?></b><br><br>

<table border=0 cellspacing=1 cellpadding=0 width=600>
<tr>
 <td width=1>
<!-- 폼태그 부분;; 수정하지 않는 것이 좋습니다 -->
<form method=post name=write action="write_ok.php" onsubmit="return check_submit();" enctype=multipart/form-data>
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
<input type=hidden name=memo value="설문조사<?= time() ?>">
<input type=hidden name=use_html value=1>
<!----------------------------------------------->
 </td>
 <Td>
   <table border=0 width=100% cellsapcing=0 cellpadding=2>
   <tr>
   <td valign=top>
  
  <table border=0 cellsapcing=0 cellpadding=3 width=100% height=100%>

<?= $hide_category_start?>
  <tr>
    <td>설문조사 종류</td><td><?= $category_kind?></td>
  </tr>
<?= $hide_category_end?>

<?= $hide_notice_start?>
  <tr>
    <td> 공지사항 선택 </td>
    <td> <input type=checkbox name=notice <?= $notice?> value=1> </td>
  </tr>
<?= $hide_notice_end?>

  <tr>
    <td>설문조사 제목</td>
    <td> <input type=text name=subject <?= size(70) ?> value="<?= e($subject) ?>" maxlength=200 class=input> </td>
  </tr>

  <?= $hide_start?>
  <tr>
     <td>비밀번호 입력</td>
     <td> <input type=password name=password <?= size(10) ?> maxlength=20 class=input> </td>
  </tr>
  <tr>
     <td width=60 align=center>설문 작성인</td> 
     <td> <input type=text name=name value="<?= e($name) ?>" <?= size(10) ?> maxlength=20 class=input> </td>
  </tr>
  <?= $hide_end?>
  <tr align=center>
    <td colspan=2 height=100% valign=bottom>
      <input type=image src="<?= e($dir) ?>/write.gif" border=0> &nbsp; &nbsp;
      <a href=# onclick=history.back()><img src="<?= e($dir) ?>/list.gif" border=0></a>
  </td>
  </tr>
  </table>

  </td>
</tr>
</table>

</td>
</tr>
</form>
</table>

  </td>
  <td background="<?= e($dir) ?>/6.gif"><img src="<?= e($dir) ?>/6.gif" border=0></td>
</tr>

<tr>
  <td><img src="<?= e($dir) ?>/7.gif" border=0></td>
  <td background="<?= e($dir) ?>/8.gif" width=100%><img src="<?= e($dir) ?>/8.gif" border=0></td>
  <td><img src="<?= e($dir) ?>/9.gif" border=0></td>
</tr>
</table>
