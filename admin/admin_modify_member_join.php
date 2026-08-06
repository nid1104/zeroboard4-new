<?php
if (!defined('_ZB_PATH')) exit();

$data = $connect->row("select * from $group_table where no = ?", [$group_no]);
if (empty($data)) error("지정된 그룹이 존재하지 않습니다");
?>
<table border=0 cellspacing=1 cellpadding=3 width=100% bgcolor=#b0b0b0>
  <tr height=30><td bgcolor=#3d3d3d colspan=2><img src=images/admin_memberjoin.gif></td></tr>
  <tr height=1><td bgcolor=#000000 style=padding:0px; colspan=2><img src=images/t.gif height=1></td></tr>
<form name=write method=post action=<?= Request::scriptName() ?>>
<input type=hidden name=exec value=modify_member_join_ok>
<input type=hidden name=group_no value=<?= e($group_no) ?>>
  <tr align=center bgcolor=#e0e0e0>
     <td colspan=2 bgcolor=#e0e0e0 style=line-height:180%>
         이 그룹의 회원가입시 나타나는 가입양식을 조절할수 있습니다.<br>
         가장 기본적인 아이디, 비밀번호, 이름, E-Mail은 조절 불가능합니다;;;
     </td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td width=20% align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>가입시 기본 레벨</td>
     <td align=left>&nbsp;<select name=join_level>
<?php
 for ($i = $member['level']; $i <= 10; $i++)
 {
     if ($i == $data['join_level']) echo "<option value=$i selected>$i</option>";
     else echo "<option value=$i>$i</option>";
 }
?></select>
     기본레벨을 정할수 있습니다. 1~10까지입니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>ICQ </td>
     <td align=left>&nbsp;<input type=checkbox name=use_icq value=1 <?= $data['use_icq'] ? "checked" : "" ?>> 아씨큐번호를 입력받을수 있습니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>AIM(AOL) </td>
     <td align=left>&nbsp;<input type=checkbox name=use_aol value=1 <?= $data['use_aol'] ? "checked" : "" ?>> AOL번호를 입력받을수 있습니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>MSN </td>
     <td align=left>&nbsp;<input type=checkbox name=use_msn value=1 <?= $data['use_msn'] ? "checked" : "" ?>> MSN 번호를 입력받을수 있습니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>주민등록번호 </td>
     <td align=left>&nbsp;<input type=checkbox name=use_jumin value=1 <?= $data['use_jumin'] ? "checked" : "" ?>> 주민등록번호를 입력받을수 있습니다. 허위주민등록번호는 자동으로 체크합니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>자기소개서 작성 </td>
     <td align=left>&nbsp;<input type=checkbox name=use_comment value=1 <?= $data['use_comment'] ? "checked" : "" ?>> 자기소개서를 작성할수 있게 합니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>취미</td>
     <td align=left>&nbsp;<input type=checkbox name=use_hobby value=1 <?= $data['use_hobby'] ? "checked" : "" ?>> 취미를 입력받을수 있습니다.</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>직업</td>
     <td align=left>&nbsp;<input type=checkbox name=use_job value=1 <?= $data['use_job'] ? "checked" : "" ?>> 직업을 입력받을수 있습니다.</td>
  </tr>

  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>집 주소</td>
     <td align=left>&nbsp;<input type=checkbox name=use_home_address value=1 <?= $data['use_home_address'] ? "checked" : "" ?>> 집주소를 입력받을수 있습니다.</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>집 전화번호</td>
     <td align=left>&nbsp;<input type=checkbox name=use_home_tel value=1 <?= $data['use_home_tel'] ? "checked" : "" ?>> 집전화번호를 입력할수 있습니다.</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>회사 주소</td>
     <td align=left>&nbsp;<input type=checkbox name=use_office_address value=1 <?= $data['use_office_address'] ? "checked" : "" ?>> 회사주소를 입력할수 있습니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>회사 전화번호</td>
     <td align=left>&nbsp;<input type=checkbox name=use_office_tel value=1 <?= $data['use_office_tel'] ? "checked" : "" ?>> 회사전화번호를 입력할수 있습니다.</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>핸드폰</td>
     <td align=left>&nbsp;<input type=checkbox name=use_handphone value=1 <?= $data['use_handphone'] ? "checked" : "" ?>> 핸드폰 번호를 입력할수 있습니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>메일링 가입</td>
     <td align=left>&nbsp;<input type=checkbox name=use_mailing value=1 <?= $data['use_mailing'] ? "checked" : "" ?>> 메일링 리스트 받기 유무를 선택할수 있습니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>생일 </td>
     <td align=left>&nbsp;<input type=checkbox name=use_birth value=1 <?= $data['use_birth'] ? "checked" : "" ?>> 생일을 입력받게 할수 있습니다</td>
  </tr>
  <tr align=center bgcolor=#e0e0e0>
     <td align=right style=font-family:Tahoma;font-size:8pt;font-weight:bold>사진</td>
     <td align=left>&nbsp;<input type=checkbox name=use_picture value=1 <?= $data['use_picture'] ? "checked" : "" ?>> 사진파일을 입력할수 있습니다</td>
  </tr>
<tr align=right bgcolor=#ffffff><td colspan=2><img src=images/t.gif height=5><br><input type=image border=0 src=images/button_confirm.gif> &nbsp;<img style=cursor:hand onclick=reset() border=0 src=images/button_cancel.gif>&nbsp;&nbsp;</td></tr>

  </form>
</table>
