<?php
// 라이브러리 함수 파일 인크루드
require_once __DIR__ . '/lib.php';

$id = Request::req('id');
if ($id !== '' && !isalNum($id)) Error("게시판 이름이 올바르지 않습니다", "window.close");
$page = Request::req('page');
$no = (int) Request::req('no');
$select_arrange = Request::req('select_arrange');
$desc = Request::req('desc');
$page_num = Request::req('page_num');
$keyword = Request::req('keyword');
$category = Request::req('category');
$sn = Request::req('sn');
$ss = Request::req('ss');
$sc = Request::req('sc');

// DB 연결
if (!isset($connect)) $connect = dbConn();

// 현재 게시판 설정 읽어 오기
if ($id) {
    $setup = get_table_attrib($id);

    // 설정되지 않은 게시판일때 에러 표시
    if (!$setup['name']) Error("생성되지 않은 게시판입니다.<br><br>게시판을 생성후 사용하십시오", "window.close");
}

// 멤버 정보 구해오기;;; 멤버가 있을때
$member = member_info();

if (!$member['no']) Error("회원 정보가 존재하지 않습니다", "window.close");



// 그룹데이타 읽어오기;;
$group_data = $connect->row("select * from $group_table where no=?", [$member['group_no']]);
$group = $group_data;
$group_no = $group['no'] ?? '';


$referer = Request::header('Referer');

$setup['header'] = "";
$setup['footer'] = "";
$setup['header_url'] = "";
$setup['footer_url'] = "";
$group['header'] = "";
$group['footer'] = "";
$group['header_url'] = "";
$group['footer_url'] = "";
$setup['skinname'] = "";

head();

?>
<div align=center><br>

<script>
 function address_popup(num)                                                                                                      
 {                                                                                                                                
  window.open('zipcode/search_zipcode.php?num='+num,'searchaddress','width=440,height=230,scrollbars=yes');                       
 } 
 function check_submit()
 {
  if(write.password.value!=write.password1.value) {alert("패스워드가 일치하지 않습니다.");write.password.value="";write.password1.value=""; write.password.focus(); return false;}
  if(!write.name.value) { alert("이름을 입력하세요"); write.name.focus(); return false; }

<?php
	if ($group_data['use_birth'] ?? '') {
	    ?>

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

<?php
	}
?>

  return true;
  }

</script>
<table border=0 cellspacing=1 cellpadding=0 width=540>
<form name=write method=post action=member_modify_ok.php enctype=multipart/form-data onsubmit="return check_submit();">
<input type=hidden name=one_page value="<?= e(Request::header('Referer')) ?>">
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
<input type=hidden name=referer value="<?= e($referer) ?>">

  <tr><td colspan=2><img src=images/member_modify.gif></td></tr>
        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="3"></td>
        </tr>
  <tr height=28 align=right>
     <td width=28% style=font-family:Tahoma;font-size:8pt;><b>ID&nbsp;</td>
     <td align=left>&nbsp;<?= e($member['user_id']) ?> &nbsp;(<?= date("Y년 m월 d일 H시 i분", $member['reg_date'])?>에 가입)</td>
  </tr>
        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;><b>Password&nbsp;</td>
     <td align=left>&nbsp;<input type=password name=password size=20 maxlength=20 style=border-color:#d8b3b3 class=input> 확인 : <input type=password name=password1 size=20 maxlength=20 style=border-color:#d8b3b3 class=input></td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Level&nbsp;</td>
     <td align=left>&nbsp;<?= e($member['level']) ?></td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;><b>Name&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=name size=20 maxlength=20 value="<?= e($member['name']) ?>" style=border-color:#d8b3b3 class=input></td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php if ($group_data['use_birth'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;><b>Birthday&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=birth_1 size=4 maxlength=4 value="<?= date("Y", $member['birth'])?>" style=border-color:#d8b3b3 class=input> 년 
                    &nbsp;<input type=text name=birth_2 size=2 maxlength=2 value="<?= date("m", $member['birth'])?>" style=border-color:#d8b3b3 class=input> 월
                    &nbsp;<input type=text name=birth_3 size=2 maxlength=2 value="<?= date("d", $member['birth'])?>" style=border-color:#d8b3b3 class=input> 일 
          <input type=checkbox value=1 name=open_birth <?= $member['open_birth'] ? "checked" : "" ?>> 공개
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;><b>E-mail&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=email size=40 maxlength=255 value="<?= e($member['email']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_email <?= $member['open_email'] ? "checked" : "" ?>> 공개
                          </td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Homepage&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=homepage size=40 maxlength=255 value="<?= e($member['homepage']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_homepage <?= $member['open_homepage'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>

<?php if ($group_data['use_icq'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>ICQ&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=icq size=20 maxlength=20 value="<?= e($member['icq']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_icq <?= $member['open_icq'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_aol'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>AIM&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=aol size=20 maxlength=30 value="<?= e($member['aol']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_aol <?= $member['open_aol'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_msn'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>MSN&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=msn size=20 maxlength=250 value="<?= e($member['msn']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_msn <?= $member['open_msn'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_hobby'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Hobby&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=hobby size=40 maxlength=40 value="<?= e($member['hobby']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_hobby <?= $member['open_hobby'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_job'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Occupation(Job)&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=job size=20 maxlength=20 value="<?= e($member['job']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_job <?= $member['open_job'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_home_address'] ?? '') { ?> 
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Home Address&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=home_address size=40 maxlength=255 value="<?= e($member['home_address']) ?>" style=border-color:#d8b3b3 class=input><input type=button value='검색' class=input style=border-color:#d8b3b3 onclick=address_popup(1)>
                          <input type=checkbox value=1 name=open_home_address <?= $member['open_home_address'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_home_tel'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Home Phone&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=home_tel size=20 maxlength=20 value="<?= e($member['home_tel']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_home_tel <?= $member['open_home_tel'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_office_address'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Office Address&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=office_address size=40 maxlength=255 value="<?= e($member['office_address']) ?>" style=border-color:#d8b3b3 class=input><input type=button value='검색' class=input style=border-color:#d8b3b3 onclick=address_popup(2)>
                          <input type=checkbox value=1 name=open_office_address <?= $member['open_office_address'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_office_tel'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Office Phone&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=office_tel size=20 maxlength=20 value="<?= e($member['office_tel']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_office_tel <?= $member['open_office_tel'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_handphone'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Cellular&nbsp;</td>
     <td align=left>&nbsp;<input type=text name=handphone size=20 maxlength=20 value="<?= e($member['handphone']) ?>" style=border-color:#d8b3b3 class=input>
                          <input type=checkbox value=1 name=open_handphone <?= $member['open_handphone'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_mailing'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;><b>Mailling List</td>
     <td align=left>&nbsp;<input type=checkbox name=mailing value=1 <?= $member['mailing'] ? "checked" : "" ?>> 메일링 가입</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;><b>Disclose Info</td>
     <td align=left>&nbsp;<input type=checkbox name=openinfo value=1 <?= $member['openinfo'] ? "checked" : "" ?>> 정보 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>

<?php if ($group_data['use_picture'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Photo</td>
     <td align=left>&nbsp;<input type=file name=picture size=34 maxlength=255 style=border-color:#d8b3b3 class=input>
                 <?php if ($member['picture']) echo "<br>&nbsp;<img src='" . e($member['picture']) . "' border=0> <input type=checkbox name=del_picture value=1> 삭제"; ?>
                          <input type=checkbox value=1 name=open_picture <?= $member['open_picture'] ? "checked" : "" ?>> 공개
                          
     </td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

<?php if ($group_data['use_comment'] ?? '') { ?>
  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Comments</td>
     <td align=left>&nbsp;<textarea cols=40 rows=4 name=comment style=border-color:#d8b3b3 class=textarea><?= e($member['comment']) ?></textarea><br>&nbsp;
                          <input type=checkbox value=1 name=open_comment <?= $member['open_comment'] ? "checked" : "" ?>> 공개</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<?php } ?>

  <tr height=28 align=right>
     <td style=font-family:Tahoma;font-size:8pt;>Point</td>
     <td align=left>&nbsp;<?= ($member['point1'] * 10 + $member['point2'])?> 점 ( 작성글수 : <?= e($member['point1']) ?>, 코멘트 : <?= e($member['point2']) ?> )</td>
  </tr>        <tr>
          <td colspan="5" bgcolor="#EBD9D9" align="center"><img src="images/t.gif" width="10" height="1"></td>
        </tr>
<tr height=30 bgcolor=#ffffff>
   <td align=center><?php if ($member['no'] > 1) {?><a href="#" onclick="if(confirm('탈퇴하시겠습니까?\n\n탈퇴를 하시면 모든 정보가 DB에서 사라집니다.\n\n탈퇴후 언제라도 재 가입가능합니다\n')){var f=document.createElement('form');f.method='post';f.action='member_out.php';document.body.appendChild(f);f.submit();}return false;"><img src=images/button_out.gif border=0 alt="회원탈퇴"></a><?php }?></td>
   <td align=right ><img src=images/t.gif height=5><br>
   <input type=image border=0 src=images/button_modify.gif> &nbsp;
   <img src=images/memo_close.gif border=0 onClick=window.close() style=cursor:hand>&nbsp;&nbsp;&nbsp;
   </td>
</tr>
  </form>
</table>

<?php
	foot();
?>
