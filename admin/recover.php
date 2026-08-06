<?php
require_once __DIR__ . '/../lib.php';

$connect = dbconn();
$member = member_info();

$no = (int) Request::req('no');

if (!$member['no'] || $member['is_admin'] > 1 || $member['level'] > 1) Error("최고 관리자만이 사용할수 있습니다");

check_csrf(true);

$board_info = $connect->row("select * from $admin_table where no = ?", [$no]);

$id = $board_info['name'] ?? false;
if ($id === false || !isAlNum($id)) error('유효하지 않은 게시판입니다.');

head("bgcolor=black")
?>
<img src=../images/t.gif border=0 width=1 height=8><Br>
<u><center><font color=aaaaaa>[<b><?= e($id) ?></b>] 게시판 정리</font></center></u><Br>
<img src=../images/t.gif border=0 width=1 height=8><Br>
<font color=white>&nbsp;&nbsp;&nbsp;&nbsp;Category 정리 :
<?php
  $s_que = '';
$f_cn = '';
$s_params = array();
$temp = $connect->all("select * from {$t_category}_{$id} order by no asc");
foreach ($temp as $cat)
{
    if (!$f_cn)$f_cn = $cat['no'];
    $s_que .= " category != ? and ";
    $s_params[] = $cat['no'];
}
$s_que .= " category != 0";
$check = $connect->exec("update {$t_board}_{$id} set category=? where $s_que", array_merge([$f_cn], $s_params));

$temp = $connect->all("select * from {$t_category}_{$id} order by no asc");
foreach ($temp as $cat)
{
    $c = $connect->value("select count(*) from {$t_board}_{$id} where category = ?", [$cat['no']]);
    $connect->exec("update {$t_category}_{$id} set num = ? where no = ?", [$c, $cat['no']]);
}
echo "<font color=yellow>성공</font>";
?>
<font color=white>&nbsp;&nbsp;&nbsp;&nbsp;Division 정리 :
<?php
  $temp = $connect->all("select * from {$t_division}_{$id} order by no asc");
foreach ($temp as $data)
{
    $c = $connect->value("select count(*) from {$t_board}_{$id} where division = ?", [$data['division']]);
    $connect->exec("update {$t_division}_{$id} set num = ? where division = ?", [$c, $data['division']]);
}
$temp = $connect->value("select count(*) from {$t_board}_{$id}");
$connect->exec("update $admin_table set total_article = ? where no = ?", [$temp, $no]);
echo "<font color=yellow>성공</font>";
?>
<br><br><center><a href=# onclick=window.close()><font color=888888>[close windows]</font></a>
<?php
 foot();
?>
