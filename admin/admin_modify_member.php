<?php
if (!defined('_ZB_PATH')) exit();


$member_data = $connect->row("SELECT * FROM $member_table WHERE no = ?", [$no]);
if (empty($member_data)) error('지정된 회원이 존재하지 않습니다');

$group_no = $member_data['group_no'];
$group_data = $connect->row("SELECT * FROM $group_table WHERE no = ?", [$group_no]);

if ($member['is_admin'] > 1 && $member['no'] != $member_data['no'] && $member_data['level'] <= $member['level'] && $member_data['is_admin'] <= $member['is_admin']) error("선택하신 회원의 정보를 변경할 권한이 없습니다");

?>

<script>
 function check_submit()
 {
  if(write.password.value!=write.password1.value) {alert("패스워드가 일치하지 않습니다.");write.password.value="";write.password1.value=""; write.password.focus(); return false;}
  if(!write.name.value) { alert("이름을 입력하세요"); write.name.focus(); return false; }

<?php if ($group_data['use_birth'])
{ ?>

    if ( write.birth_1.value < 1000 || write.birth_1.value <= 0 )  {
         alert('생년이 잘못입력되었습니다.');
         write.birth_1.value='';
         write.birth_1.focus();
        return false;
    }
    if ( write.birth_2.value > 12 || write.birth_2.value <= 0 ) {
         alert('생월이 잘못입력되었습니다.');
         write.birth_2.value='';
         write.birth_2.focus();
        return false;
    }
    if ( write.birth_3.value > 31 || write.birth_3.value <= 0 )  {
         alert('생일이 잘못입력되었습니다.');
         write.birth_3.value='';
         write.birth_3.focus();
        return false;
    }
<?php } ?>

  return true;
  }


  function add_board_manager() {

	var myindex=document.write.board_name.selectedIndex;
	var no=document.write.board_name.options[myindex].value;

	if(no) {
		location.href="<?= Request::scriptName() ?>?exec=view_member&exec2=add_member_board_manager&group_no=<?= e_js($group_no) ?>&member_no=<?= e_js($no) ?>&page=<?= e_js($page) ?>&keyword=<?= e_js($keyword) ?>&keykind=<?= e_js($keykind) ?>&like=<?= e_js($like) ?>&board_num="+ no + "&zb_csrf_token=<?= Session::csrfToken() ?>";
	}
  }

</script>
<table border=0 cellspacing=1 cellpadding=3 width=100% bgcolor=#b0b0b0>
  <tr height=30><td bgcolor=#3d3d3d colspan=2><img src=images/admin_webboard.gif></td></tr>
  <tr height=1><td bgcolor=#000000 style=padding:0px; colspan=2><img src=images/t.gif height=1></td></tr>
<form name=write method=post action="<?= Request::scriptName() ?>" enctype=multipart/form-data onsubmit="return check_submit();">
<input type=hidden name=exec value="view_member">
<input type=hidden name=exec2 value="modify_member_ok">
<input type=hidden name=group_no value="<?= e($group_no) ?>">
<input type=hidden name=member_no value="<?= e($no) ?>">
<input type=hidden name=page value="<?= e($page) ?>">
<input type=hidden name=page_num value="<?= e($page_num) ?>">
<input type=hidden name=keykind value="<?= e($keykind) ?>">
<input type=hidden name=keyword value="<?= e($keyword) ?>">
<input type=hidden name=like value="<?= e($like) ?>">

  <tr height=22 align=center><td height=30 colspan=2><b><?= e($member_data['name']) ?></b> 회원 설정 변경</td></tr>

  <tr height=22 align=center bgcolor=#e0e0e0>
     <td width=25% align=right bgcolor=#a0a0a0 style=font-family:Tahoma;font-size:8pt;font-weight:bold;>아이디&nbsp;&nbsp;</td>
     <td align=left>&nbsp;<?= e($member_data['user_id']) ?> &nbsp;(<?= date("Y년 m월 d일 H시 i분", $member_data['reg_date']) ?>에 가입)</td>
  </tr>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>비밀번호&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=password name=password size=20 maxlength=20 class=input style=border-color:#b0b0b0> 확인 : <input type=password name=password1 size=20 maxlength=20 class=input style=border-color:#b0b0b0></td>
  </tr>

<?php
$get_string = '';
$locking = '';
if ($member['no'] == $no) $locking = "disabled";

if ($member['is_admin'] == 1)
{
    $select = array(1 => '', 2 => '', 3 => '');
    $select[$member_data['is_admin']] = "selected";
    ?>
  <tr height=22 align=center>  
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>관리자 레벨&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<select name=is_admin <?= $locking ?>>
                          <option value=3 <?= $select[3] ?>>일반사용자</option>
                          <option value=2 <?= $select[2] ?>>그룹관리자</option>
                          <option value=1 <?= $select[1] ?>>최고관리자</option>
                          </select> (관리자 레벨은 일반 레벨에 우선합니다)</td>
  </tr>
<?php
}
?>

  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>레벨&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<select name=level <?= $locking ?>>
<?php
  for ($i = $member['level']; $i <= 10; $i++) if ($i == $member_data['level']) echo "<option value=$i selected>$i</option>"; else echo "<option value=$i>$i</option>";
?>
                    </select></td>
  </tr>

  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>이름&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=name size=20 maxlength=20 value="<?= e($member_data['name']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>

<?php
  $__manager_board_name = '';

if ($member_data['is_admin'] > 2)
{

    if (trim($member_data['board_name'])) {
        $manager_board_temp = explode(",", $member_data['board_name']);
        $__gs_marks = array();
        $__gs_params = array();
        for ($__k = 0; $__k < count($manager_board_temp); $__k++){
            if (trim($manager_board_temp[$__k]) !== '') { $__gs_marks[] = '?'; $__gs_params[] = trim($manager_board_temp[$__k]); }
        }
        $get_string = $__gs_marks ? (" no in (" . implode(',', $__gs_marks) . ") ") : " 0 ";
        $manager_board_list = $connect->all("select * from $admin_table where $get_string", $__gs_params);
        foreach ($manager_board_list as $__manager_data) {
            $__manager_board_name .= "&nbsp;" . e($__manager_data['name']) . " &nbsp; <a href='" . Request::scriptName() . "?exec=view_member&exec2=modify_member_board_manager&group_no=" . e($group_no) . "&member_no=" . e($no) . "&page=" . e($page) . "&keyword=" . e($keyword) . "&board_num=" . e($__manager_data['no']) . "&zb_csrf_token=" . Session::csrfToken() . "' onclick=\"return confirm('권한을 취소시키시겠습니까?')\">[권한취소]</a><br><img src=images/t.gif border=0 height=4><br>";

        }
    }

    $select[$member_data['board_name']] = "selected";
    $board_list = $connect->all("select no,name from $admin_table where group_no = ?", [$group_data['no']]);
    ?>                                                                                                  
  <tr height=22 align=center>                                                                       
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>게시판 관리자 지정&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>
     <?= $__manager_board_name ?>
     &nbsp;<select name=board_name>
     <option value="">게시판관리자 지정</option>
<?php
foreach ($board_list as $board_data_list)
{
    if (!preg_match('/,' . preg_quote($board_data_list['no'], '/') . ',/i', $member_data['board_name'])) echo "<option value='" . e($board_data_list['no']) . "'>" . e($board_data_list['name']) . "</option>";
}
    ?>
     </select> <input type=button value="게시판 관리 권한 추가" onclick="add_board_manager()" style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px;>
     </td>
  </tr>
<?php
}
?> 

<?php if ($group_data['use_birth']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>생일&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=birth_1 size=4 maxlength=4 value="<?= date("Y", $member_data['birth'])?>" class=input style=border-color:#b0b0b0> 년 
                    &nbsp;<input type=text name=birth_2 size=2 maxlength=2 value="<?= date("m", $member_data['birth'])?>" class=input style=border-color:#b0b0b0> 월
                    &nbsp;<input type=text name=birth_3 size=2 maxlength=2 value="<?= date("d", $member_data['birth'])?>" class=input style=border-color:#b0b0b0> 일 
  </tr>
<?php } ?>

  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>E-mail&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=email size=50 maxlength=255 value="<?= e($member_data['email']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>홈페이지&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=homepage size=50 maxlength=255 value="<?= e($member_data['homepage']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>

<?php if ($group_data['use_icq']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>ICQ&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=icq size=20 maxlength=20 value="<?= e($member_data['icq']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_aol']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>AIM(AOL)&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=aol size=20 maxlength=20 value="<?= e($member_data['aol']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_msn']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>MSN&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=msn size=20 maxlength=20 value="<?= e($member_data['msn']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_hobby']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>취미&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=hobby size=50 maxlength=50 value="<?= e($member_data['hobby']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_job']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>직업&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=job size=20 maxlength=20 value="<?= e($member_data['job']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_home_address']) { ?> 
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>집 주소&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=home_address size=50 maxlength=255 value="<?= e($member_data['home_address']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_home_tel']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>집 전화번호&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=home_tel size=20 maxlength=20 value="<?= e($member_data['home_tel']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_office_address']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>회사 주소&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=office_address size=50 maxlength=255 value="<?= e($member_data['office_address']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_office_tel']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>회사 전화번호&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=office_tel size=20 maxlength=20 value="<?= e($member_data['office_tel']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_handphone']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>핸드폰&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=text name=handphone size=20 maxlength=20 value="<?= e($member_data['handphone']) ?>" class=input style=border-color:#b0b0b0></td>
  </tr>
<?php } ?>

<?php if ($group_data['use_mailing']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>메일링리스트 가입&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=checkbox name=mailing value=1 <?= $member_data['mailing'] ? "checked" : "" ?>></td>
  </tr>
<?php } ?>

  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>정보 공개 여부&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=checkbox name=openinfo value=1 <?= $member_data['openinfo'] ? "checked" : "" ?>></td>
  </tr>

<?php if ($group_data['use_picture']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>사진&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<input type=file name=picture size=37 maxlength=255 class=input style=border-color:#b0b0b0>
                 <?php if ($member_data['picture']) echo "<br>&nbsp;<img src='" . e($member_data['picture']) . "' border=0>"; ?>
     </td>
  </tr>
<?php } ?>

<?php if ($group_data['use_comment']) { ?>
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>소갯말&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<textarea cols=50 rows=4 name=comment class=textarea style=border-color:#b0b0b0><?= e($member_data['comment']) ?></textarea></td>
  </tr>
<?php } ?>

  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>Point&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<?= e($member_data['point1'] * 10 + $member_data['point2']) ?> 점 ( 작성글수 : <?= e($member_data['point1']) ?>, 코멘트 : <?= e($member_data['point2']) ?> )</td>
  </tr>

  <tr height=22 align=center>
     <td colspan=2 bgcolor=#a0a0a0 style=font-family:Tahoma;font-size:8pt;font-weight:bold; align=center>관리자 고유권한</td>
  </tr>

  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>Image Box 용량 지정&nbsp;&nbsp;</td>
	 <td align=left bgcolor=#e0e0e0>&nbsp;<?php
	 	$maxDirSize = zReadFile("icon/member_image_box/" . $no . "_maxsize.php");
if ($maxDirSize) {
    if (strpos($maxDirSize, "<?php exit();/*") === 0) {
      $maxDirSize = str_replace("<?php exit();/*", "", $maxDirSize);
      $maxDirSize = str_replace("*/?>", "", $maxDirSize);
      $maxDirSize = base64_decode($maxDirSize);
      $maxDirSize = (int)($maxDirSize / 1024);
    } else {
      $maxDirSize = str_replace("<?/*", "", $maxDirSize);
      $maxDirSize = str_replace("*/?>", "", $maxDirSize);
      $maxDirSize = (int)($maxDirSize / 1024);
    }
} else {
    $maxDirSize = 100;
}?><input type=input name=maxdirsize value="<?= e($maxDirSize) ?>" size=10 maxlength=20 class=input> KByte &nbsp; 이미지 창고의 사용 용량을 지정해 줄수 있습니다.</td>
  </tr>

  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>마크 그림&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<?php
	$private_icon = get_private_icon($member_data['no'], 1);
if ($private_icon) {
    ?>
		<img src='<?= e($private_icon) ?>' border=1>
		<input type=checkbox value=1 name=delete_private_icon > Delete
<?php
} else echo "<img src=images/t.gif border=1 width=16 height=15>";
?>
			<br>
			&nbsp;<input type=file name=private_icon value="" size=20 maxlength=20 class=input >
			<br>
			<img src=images/t.gif border=0 height=5><br>
	 		* 정해진 회원의 이름 앞에만 나타나는 아이콘입니다. <br>
			<font color=#e0e0e0>* </font>(GIF 파일만 가능합니다. 16x16px 정도로 해주세요)
	 </td>
  </tr>
	
  <tr height=22 align=center>
     <td bgcolor=#a0a0a0 align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold;>이름 그림&nbsp;&nbsp;</td>
     <td align=left bgcolor=#e0e0e0>&nbsp;<?php
	$private_name = get_private_icon($member_data['no'], 2);
if ($private_name) {
    ?>
		<img src='<?= e($private_name) ?>' border=1>
		<input type=checkbox value=1 name=delete_private_name > Delete
<?php
} else echo "<img src=images/t.gif border=1 width=16 height=15>";
?>
			<br>
			&nbsp;<input type=file name=private_name value="" size=20 maxlength=20 class=input >
			<br>
			<img src=images/t.gif border=0 height=5><br>
	 		* 정해진 회원의 이름을 대신해서 나타나는 아이콘입니다. <br>
			<font color=#e0e0e0>* </font>스킨에 따라서 오동작을 일으킬수 있으니 확인을 꼭 하여주세요<br>
			<font color=#e0e0e0>* </font>(GIF 파일만 가능합니다. 세로길이는 16px 정도로 해주세요)
	 </td>
  </tr>

  <tr height=22 align=center><td colspan=2><input type=submit value='  변경 완료  ' style=font-weight:bold;border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:23px;>
                                 <input type=button value='  변경 취소  ' style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:23px; onclick="location.href='<?= Request::scriptName() . "?exec=view_member&group_no=" . e_js($group_no) . "&page=" . e_js($page) . "&keyword=" . e_js($keyword) . "&level_search=" . e_js($level_search) . "&page_num=" . e_js($page_num) . "&keykind=" . e_js($keykind) . "&like=" . e_js($like) ?>'">
  </td></tr>
  </form>
</table>
