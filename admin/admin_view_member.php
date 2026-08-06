<?php
if (!defined('_ZB_PATH')) exit();

/**************************************************************************
 * 회원 목록 보는 페이지
 *************************************************************************/

$keykind = Request::req('keykind');
$like = Request::req('like');
$level_search = (int) Request::req('level_search');
$keyword = Request::req('keyword');

// 전체 그룹수와 현재 그룹의 정보를 추출
$total_group_num = $connect->value("select count(*) from {$group_table}");
$group_data = $connect->row("select * from {$group_table} where no = ?", [$group_no]);

if (empty($group_data)) error("지정된 그룹이 존재하지 않습니다");

$total_member = $connect->value("select count(*) from {$member_table} where group_no = ?", [$group_no]);

// 검색어에 대해서 처리
$s_que = '';
$s_params = array();
$href = "&keykind=" . e($keykind) . "&like=" . e($like);

if ($total_group_num > 1) { $s_que = " where group_no = ? "; $s_params[] = $group_no; }

if ($level_search > 0) {
    if ($s_que) $s_que .= " and "; else $s_que = " where ";
    $s_que .= " level = ? ";
    $s_params[] = $level_search;
    $href .= "&level_search={$level_search}";
}
if ($keyword && in_array($keykind, ['user_id', 'name', 'homepage', 'email', 'jumin'], true)) {
    if ($s_que) $s_que .= " and "; else $s_que = " where ";

    if ($keykind != "jumin") {
        if ($like) { $s_que .= " $keykind like ? "; $s_params[] = '%' . $connect->escapeLike($keyword) . '%'; }
        else { $s_que .= " $keykind = ? "; $s_params[] = $keyword; }

    } else {
        $s_que .= " $keykind = ? ";
        $s_params[] = hash('sha256', $keyword);
    }

    $href .= "&keyword=" . e($keyword) . "&keykind=" . e($keykind) . "&like=" . e($like);
}

$temp = $connect->value("select count(*) from $member_table $s_que", $s_params);
$total = $temp;

//페이지 구하는 부분
if (!$page_num)$page_num = 10;
$href .= "&page_num={$page_num}";
if (!$page) $page = 1;
$start_num = ($page - 1) * $page_num;
$total_page = (int)(($total - 1) / $page_num) + 1;


// 멤버정보를 구해옴
$result = $connect->all("select * from $member_table $s_que order by no desc limit ?,?", array_merge($s_params, array((int)$start_num, (int)$page_num)));

//  앞에 붙는 가상번호
$number = $total - ($page - 1) * $page_num;

?>

<table border=0 cellspacing=1 cellpadding=0 width=100% bgcolor=#b0b0b0>
<tr height=30><td bgcolor=#3d3d3d colspan=10><img src=images/admin_webboard.gif></td></tr>
<tr height=1><td bgcolor=#000000 style=padding:0px; colspan=10><img src=images/t.gif height=1></td></tr>
<tr bgcolor=bbbbbb>
	<td align=right colspan=10 height=25 colspan=2 style=font-family:Tahoma;font-size:8pt;>
	그룹이름 : <b><?= e($group_data['name']) ?></b> , 전체 회원수 : <b><?= e($total_member) ?></b> , <b><?= e($total) ?></b> 개 검색&nbsp;&nbsp;&nbsp;</td>
</tr>
<!-- 모두삭제하는 거랑, 변한변경, 그룹이동 버튼 표시 -->
<script>

  function select() {
    var i, chked=0;
    for(i=0;i<document.write.length;i++) {
    if(document.write[i].type=='checkbox') { 
     if(document.write[i].checked) { document.write[i].checked=false; }
     else { document.write[i].checked=true; }
     }
    }
     return false;
   }

  function sendmail()
  {
   var i, chked=0, cart="";
   for(i=0;i<document.write.length;i++) 
   {
    if(document.write[i].type=='checkbox') 
    {
     if(document.write[i].checked)
     { cart = cart + "||" + document.write[i].value; chked=1; }
    }
   }
   if(chked)
   {
    search.cart.value=cart;
   }
   else { search.cart.value=""; }
   search.exec2.value="sendmail";
   search.submit();
   return true;
  }

  function delete_all()
  {
   var i, j=0, k=0;
   for(i=0;i<document.write.length;i++) {
    if(document.write[i].checked)
    k++;
   }
   if(k<1)
   {
    alert("선택을 하여 주세요");
    return false;
   }

   if(confirm("선택된 멤버를 모두 삭제하시겠습니까?"))
   {
    write.exec2.value="deleteall";
    write.submit();
    return true;
   }
   return false;
  }

  function move_all()
  {
   var i, j=0, k=0;
   for(i=0;i<document.write.length;i++) {
    if(document.write[i].checked)
    k++;
   }
   if(k<1)
   {
    alert("선택을 하여 주세요");
    return false;
   }


   if(confirm("선택된 멤버를 모두 "+write.movelevel.value+"로 변경하시겠습니까?"))
   {
    write.exec2.value="moveall";
    write.submit();
    return true;
   }
   return false;
  }

<?php if ($member['is_admin'] == 1)
{
    ?>
  function move_group()
  {
   var i, j=0, k=0;
   for(i=0;i<document.write.length;i++) {
    if(document.write[i].checked)
    k++;
   }
   if(k<1)
   {
    alert("선택을 하여 주세요");
    return false;
   }


   if(confirm("선택된 멤버를 선택된 그룹으로 이동하시겠습니까?"))
   {
    write.exec2.value="move_group";
    write.submit();
    return true;
   }
   return false;
  }
<?php } ?>
</script>

<tr align=center height=25 bgcolor=#a0a0a0>
  <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>번호</td>
  <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;><a href=javascript: onclick="return select();">선택</a></td>
  <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>유저명</td>
  <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>이름</td>
  <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>레벨</td>
  <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>점수</td>
  <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>가입일자</td>
  <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>수정</td>
  <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>삭제</td>
</tr>

   <form method=post action="<?= Request::scriptName() ?>" name="write">
   <input type=hidden name=page value="<?= e($page) ?>">
   <input type=hidden name=keykind value="<?= e($keykind) ?>">
   <input type=hidden name=keyword value="<?= e($keyword) ?>">
   <input type=hidden name=like value="<?= e($like) ?>">
   <input type=hidden name=group_no value="<?= e($group_no) ?>">
   <input type=hidden name=exec value="<?= e($exec) ?>">
   <input type=hidden name=page_num value="<?= e($page_num) ?>">
   <input type=hidden name=exec2 value="">

<?php
      foreach ($result as $data)
      {
          if ($data['level'] == 1) $grant_color = "<font color=red><b>";
          elseif ($data['level'] == 2) $grant_color = "<font color=blue><b>";
          elseif ($data['level'] == 3) $grant_color = "<font color=green><b>";
          else $grant_color = "";

          echo "
        <tr align=center height=23 bgcolor=#e0e0e0>
           <td style=font-family:Tahoma;font-size:7pt;>$number</td>
           <td><input type=checkbox name=cart[] value=$data[no]></td>
           <td style=font-family:Tahoma;font-size:8pt;>" . e($data['user_id']) . "</td>
           <td><img src=images/t.gif height=3><br>" . e($data['name']) . "&nbsp;</td>
           <td style=font-family:Tahoma;font-size:8pt;>$grant_color$data[level]</td>
           <td style=font-family:Tahoma;font-size:8pt;>" . e($data['point1'] * 10 + $data['point2']) . " <font style=font-size:7pt;>(" . e($data['point1'] . "/" . $data['point2']) . ")</font></td>
           <td style=font-family:Tahoma;font-size:8pt;>" . date("Y-m-d", $data['reg_date']) . "</td>
           <td style=font-family:Tahoma;font-size:8pt;><a href=" . Request::scriptName() . "?exec=$exec&group_no=$group_no&exec2=modify&page=$page&no=$data[no]&keyword=" . urlencode($keyword) . "&keykind=" . e($keykind) . "&like=" . e($like) . "&page_num=" . e($page_num) . ">Modify</a></td>
           <td style=font-family:Tahoma;font-size:8pt;>";
          if ($data['no'] > 1) echo "<a href=" . Request::scriptName() . "?exec=" . e($exec) . "&group_no=" . e($group_no) . "&exec2=del&keyword=" . e($keyword) . "&page=" . e($page) . "&no=" . e($data['no'] . $href) . "&zb_csrf_token=" . Session::csrfToken() . " onclick=\"return confirm('삭제하시겠습니까?')\">Delete</a>"; else echo "&nbsp;";
          echo "   </td>
        </tr>
        ";
          $number--;
      }
?>
<tr>
    <td height=30 align=right  colspan=9>

<!-- 삭제, 변경 버튼 부분 -->
    <table border=0 cellspacing=0 cellpadding=1>
    <tr>
       <td width=20>&nbsp;</td>
       <td><img src=images/t.gif height=1><br><select name=movelevel>
  <?php
$select = array_fill(0, 11, '');
$select[0] = " selected ";
for ($i = 1; $i <= 10; $i++)
    echo "<option value=$i $select[$i]>$i Level</option>"; ?></select></td><td><input type=button value='레벨변경' style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px; onclick=move_all()>
       </td><td><input type=button value='선택된 회원 삭제' style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px; onclick=delete_all()></td>
<?php
    if ($member['is_admin'] == 1)
    {
        ?>
       <td width=20>&nbsp;</td>
       <td><img src=images/t.gif height=1><br><select name=movegroup><?php
          $temp_group = $connect->all("select * from {$group_table} where no != ?", [$group_no]);
        $i = 0;
        $select[0] = " selected ";
        foreach ($temp_group as $temp_data)
        {
            echo "<option value=" . e($temp_data['no']) . " $select[$i]>" . e($temp_data['name']) . "</option>";
            $i++;
        }
        ?></select></td>
    <td><input type=button value='그룹 변경' style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px; onclick=move_group()>&nbsp;</td>

<?php
    }
?>

    </tr>
    </table>
    </td>
</form>
</tr>
<tr>
<td colspan=9 align=right bgcolor=666666>
<table border=0 cellpadding=2 cellspacing=0 width=100%>
<!-- 검색하는 부분;;;; -->
<form method=post action="<?= Request::scriptName() ?>" name="search">
<input type=hidden name=exec2 value="">
<?php /* <input type=hidden name=s_que value="<?= e($s_que) ?>"> */ ?>
<input type=hidden name=page value="<?= e($page) ?>">
<input type=hidden name=group_no value="<?= e($group_no) ?>">
<input type=hidden name=exec value="<?= e($exec) ?>">
<input type=hidden name=cart value=''>
<tr>
	<td rowspan=2 align=left>
		<input type=button value="메일링 리스트 발송" style=line-height:150%;border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:50px; onclick="sendmail();">&nbsp;
		<input type=button value="회원 추가" style=line-height:150%;border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:50px; onclick="window.open('member_join.php?mode=admin&group_no=<?= e_js($group_no) ?>','zbMemberJoin','width=560,height=590,toolbars=no,resizable=yes,scrollbars=yes')">&nbsp;
	</td>
	<td align=right height=30 nowrap>
		<img src=images/t.gif height=2><br>
  		<select name=level_search>
  			<option>레벨검색</option>
<?php
$check = array_fill(0, 11, '');
$check[$level_search] = "selected";
for ($i = 1; $i <= 10; $i++) echo "<option value=$i $check[$i]>$i Level</option>";
?>
		</select>
		<select name=keykind>
			<option value="user_id" <?php if ($keykind == "user_id")echo "selected"; ?>>User ID</option>
			<option value="name" <?php if ($keykind == "name")echo "selected"; ?>>Name</option>
			<option value="homepage" <?php if ($keykind == "homepage")echo "selected"; ?>>Homepage</option>
			<option value="email" <?php if ($keykind == "email")echo "selected"; ?>>Email</option>
			<option value="jumin" <?php if ($keykind == "jumin")echo "selected"; ?>>Jumin</option>
			<option value="comment" <?php if ($keykind == "comment")echo "selected"; ?>>Comment</option>
		</select>
		<input type=text name=keyword value='<?= e($keyword) ?>'>
		<input type=checkbox name=like value=1 <?= $like ? "checked" : "" ?> onclick='alert("Include 체크시 검색어를 포함하는 대상을 검색합니다.\n\n체크시 : *검색어*\n\n체크를 하지 않을경우 완전한 대상을 검색하며 더 빠릅니다\n\nComment를 제외하고는 체크하지 않는 것을 권해드립니다")'> <font style=color:#ffffff;font-size:8pt;font-family:Tahoma;>Include</font> &nbsp;
		<input type=submit value=' 검색 '  style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px; >
		<input type=button value=' 처음으로 ' style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px; onclick=location.href="<?= Request::scriptName() ?>?exec=<?= e_js($exec) ?>&group_no=<?= e_js($group_no) ?>">
	</td>
</tr>
<tr>
	<td style=font-family:Tahoma;font-size:8pt;font-weight:bold; align=right>
		한 페이지당 표시될 회원수	
		<input type=text name=page_num value='<?= e($page_num) ?>' style=width:30px;>
    </td>
</tr>
</form>
</table>
</td></tr>
</table>
<br>

<font color=#ffffff style=font-size:8pt;font-family:Tahoma;>
<?php
//페이지 나타내는 부분
$show_page_num = 10;
$start_page = (int)(($page - 1) / $show_page_num) * $show_page_num;
$i = 1;
if ($page > $show_page_num){$prev_page = $start_page - 1; echo "<a href=" . Request::scriptName() . "?exec=" . e($exec) . "&group_no=" . e($group_no) . "&page=" . e($prev_page . $href) . "><font color=#ffffff>[Prev]</font></a>"; }
while ($i + $start_page <= $total_page && $i <= $show_page_num)
{
    $move_page = $i + $start_page;
    if ($page == $move_page) echo "<b>$move_page</b>";
    else echo "<a href=" . Request::scriptName() . "?exec=" . e($exec) . "&group_no=" . e($group_no) . "&page=" . e($move_page . $href) . "><font color=#ffffff>[" . e($move_page) . "]</font></a>";
    $i++;
}
if ($total_page > $move_page){$next_page = $move_page + 1; echo "<a href=" . Request::scriptName() . "?exec=" . e($exec) . "&group_no=" . e($group_no) . "&page=" . e($next_page . $href) . "><font color=#ffffff>[Next]</font></a>"; }
//페이지 나타내는 부분 끝

?></font><br><br>
