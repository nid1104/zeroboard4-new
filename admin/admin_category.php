<?php
if (!defined('_ZB_PATH')) exit();

$group_data = $connect->row("select * from $group_table where no=?", [$group_no]);
if (empty($group_data)) error('지정된 그룹이 존재하지 않습니다');

if ($member['is_admin'] > 2 && strpos($member['board_name'], $no . ",") === false) error("사용 권한이 없습니다");

$table_data = $connect->row("select name from $admin_table where no=?", [$no]);
if (empty($table_data) || !isalNum($table_data['name'] ?? '')) Error("게시판 정보를 찾을 수 없습니다");

$result = $connect->all("select * from {$t_category}_{$table_data['name']} order by no");
$total_category = count($result);
?>
<table border=0 cellspacing=1 cellpadding=3 width=100% bgcolor=#b0b0b0>
  <tr height=30><td bgcolor=#3d3d3d colspan=5><img src=images/admin_webboard.gif></td></tr>
<Tr height=30><td bgcolor=white colspan=5 align=right style=font-family:Tahoma;font-size:8pt;>
그룹 이름 : <b><?= e($group_data['name']) ?></b> , 게시판 이름 : <b><a href=zboard.php?id=<?= e($table_data['name']) ?> target=_blank><?= e($table_data['name']) ?></a></b> &nbsp;&nbsp;&nbsp;
    <input type=button value='게시판관리' class=input style=width=100px onclick="location.href='<?= Request::scriptName() ?>?exec=view_board&group_no=<?= e_js($group_no) ?>&exec2=modify&no=<?= e_js($no) ?>&page=<?= e_js($page) ?>&page_num=<?= e_js($page_num) ?>'">
    <input type=button value='권한설정' class=input style=width=100px onclick="location.href='<?= Request::scriptName() ?>?exec=view_board&group_no=<?= e_js($group_no) ?>&exec2=grant&no=<?= e_js($no) ?>&page=<?= e_js($page) ?>&page_num=<?= e_js($page_num) ?>'">
&nbsp;&nbsp;&nbsp;
</td></tr>
  <tr height=1><td bgcolor=#000000 style=padding:0px; colspan=5><img src=images/t.gif height=1></td></tr>
<form method=post action="<?= Request::scriptName() ?>">
<input type=hidden name=group_no value="<?= e($group_no) ?>">
<input type=hidden name=exec value="view_board">
<input type=hidden name=exec2 value="category_move">
<input type=hidden name=page value="<?= e($page) ?>">
<input type=hidden name=page_num value="<?= e($page_num) ?>">
<input type=hidden name=no value="<?= e($no) ?>">
<tr height=23 align=center bgcolor=#a0a0a0>
    <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>선택</td>
    <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>카테고리명</td>
    <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>등록된 개수</td>
    <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>수정</td>
    <td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>삭제</td>
  </tr>
<?php
  foreach ($result as $data)
  {
      $total_num = (int)$connect->value("select count(*) from {$t_board}_{$table_data['name']} where category=?", [$data['no']]);
      ?>

  <tr height=23 align=center bgcolor=#e0e0e0>
    <td><input type=checkbox name=c[] value=<?= e($data['no']) ?>></td>
    <td><img src=images/t.gif height=3><br><?= e($data['name']) ?></td>
    <td style=font-family:Tahoma;font-size:8pt><?= e($total_num) ?></td>
    <?= "<td style=font-family:Tahoma;font-size:8pt><a href=" . Request::scriptName() . "?exec=view_board&no=" . e($no) . "&exec2=modify_category&group_no=" . e($group_no) . "&page=" . e($page) . "&page_num=" . e($page_num) . "&category_no=" . e($data['no']) . ">Modify</a></td>" ?>
    <td style=font-family:Tahoma;font-size:8pt>
<?php
        if (!$total_num && $total_category > 1)
            echo "<a href=" . Request::scriptName() . "?exec=view_board&no=" . e($no) . "&exec2=del_category&group_no=" . e($group_no) . "&page=" . e($page) . "&page_num=" . e($page_num) . "&category_no=" . e($data['no']) . "&zb_csrf_token=" . Session::csrfToken() . " onclick=\"return confirm('삭제하시겠습니까?')\">Delete</a>"; else echo "&nbsp;";
      ?>
    </td>
  </tr>

<?php
  }
?>
  <tr height=28 align=center>
    <td colspan=5 ><table border=0 cellpadding=2 cellspacing=0><tr><td style=font-family:Tahoma;font-size:8pt;font-weight:bold;>
      선택된 카테고리의 게시물을 일괄 이동 : </td><td><img src=images/t.gif height=2><br><select name=movename class=input> 
<?php
  $temp2 = $connect->all("select * from {$t_category}_{$table_data['name']} order by no desc");
foreach ($temp2 as $data2)
{
    echo '<option value="' . e($data2['no']) . '">' . e($data2['name']) . '</option>';
}
?>
  </select></td><td>
  <input type=submit value=' 이동 ' style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px;> </td></tr></table>
 </td>
</form>
</tr>
</table>
<form method=post action=<?= Request::scriptName() ?>>
<input type=hidden name=group_no value=<?= e($group_no) ?>>
<input type=hidden name=exec value=view_board>
<input type=hidden name=exec2 value=category_add>
<input type=hidden name=no value=<?= e($no) ?>>
<input type=hidden name=page value=<?= e($page) ?>>
<input type=hidden name=page_num value=<?= e($page_num) ?>>
<table border=0 cellpadding=2 cellspacing=0>
<tr><td style=font-size:8pt;font-family:Tahoma;color:#ffffff;font-weight:bold>
      카테고리 추가</td><td><input type=text size=10 name=name></td><td><input type=submit value=' 추가 ' style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px;></td></tr></table><br><br>
 <input type=button value=' 게시판 목록 보기 ' onclick="location.href='<?= Request::scriptName() ?>?exec=view_board&group_no=<?= e_js($group_no) ?>&page=<?= e_js($page) ?>&page_num=<?= e_js($page_num) ?>'" style=border-color:#b0b0b0;background-color:#3d3d3d;color:#ffffff;font-size:8pt;font-family:Tahoma;height:20px;>
  </form>
</div>