<?php
require_once __DIR__ . '/../lib.php';

$connect = dbconn();
$member = member_info();

$keyword = Request::req('keyword');
$keykind = Request::reqArr('keykind');
$s_keyword = $keyword;

if (!$member['no'] || $member['is_admin'] > 1 || $member['level'] > 1) Error("최고 관리자만이 사용할수 있습니다");

if (isset($keykind[5])) {
    $userno = $connect->value("select no from zetyx_member_table where user_id = ?", [$keyword]);
}

// 실제 검색부분
if ($keyword !== '')
{
    $comment_search = 1;
    $s_que = '';
    $s_params = array();
    $kw_like = '%' . $connect->escapeLike($keyword) . '%';
    for ($i = 0; $i < 6; $i++)
    {
        if (isset($keykind[$i])
          && in_array($keykind[$i], ['name', 'email', 'ip', 'subject', 'memo', 'ismember'], true))
        {
            if ($keykind[$i] != "ismember") {
                if (!$s_que) $s_que .= " where $keykind[$i] like ? ";
                else $s_que .= " and $keykind[$i] like ? ";
                $s_params[] = $kw_like;

            } else {
                if ($userno) {
                    if (!$s_que) $s_que .= " where $keykind[$i] = ? ";
                    else $s_que .= " and $keykind[$i] = ? ";
                    $s_params[] = $userno;
                }

            }

            if ($keykind[$i] == "email" || $keykind[$i] == "subject") $comment_search = 0;
        }

        $table_name_result = $connect->all("select name, use_alllist from $admin_table order by name");
    }

}

head(" bgcolor=white");
?>
<div align=center>
<br>
<table border=0 cellspacing=0 cellpadding=0 width=98%>
<tr>
  <td><img src=../images/trace_left.gif border=0></td>
  <td width=100% background=../images/trace_back.gif><img src=../images/trace_back.gif border=0></td>
  <td><img src=../images/trace_right.gif border=0></td>
</tr>
<form action=<?= Request::scriptName() ?> method=post>
<tr>
  <td colspan=3 align=right>

  <Table border=0>
	<tr>
  	<td style=line-height:180% height=40 align=right>
  		<input type=checkbox name=keykind[0] value="name" <?php if (isset($keykind[0])) echo "checked"; ?>> 이름 &nbsp;
  		<input type=checkbox name=keykind[1] value="email" <?php if (isset($keykind[1])) echo "checked"; ?>> E-Mail &nbsp;
  		<input type=checkbox name=keykind[2] value="ip" <?php if (isset($keykind[2])) echo "checked"; ?>> 아이피 &nbsp;
  		<input type=checkbox name=keykind[3] value="subject" <?php if (isset($keykind[3])) echo "checked"; ?>> 제목 &nbsp;
  		<input type=checkbox name=keykind[4] value="memo" <?php if (isset($keykind[4])) echo "checked"; ?>> 내용 &nbsp;
  		<input type=checkbox name=keykind[5] value="ismember" <?php if (isset($keykind[5])) echo "checked"; ?>> 아이디 &nbsp;
  	</td>
  	<td><input type=text name=keyword value="<?= e($s_keyword) ?>" size=20 class=input>&nbsp;</td>
  	<td><input type=image src=../images/trace_search.gif border=0 valign=absmiddle></td>
	</tr>
	<tr>
  	<td colspan=3 align=right>
		<font color=darkred>* 체크된 항목은 AND 연산됩니다, 즉 선택된 항목이 모두 해당될때입니다.</font>
  	</td>
	</tr>
	</form>
	</table>
  </td>
</tr>
</table>
</div>

<?php
if ($keyword && $s_que)
{
    foreach ($table_name_result as $table_data)
    {

        $table_name = $table_data['name'];
        if (!isalNum($table_name)) continue;
        if ($table_data['use_alllist']) $file = "zboard.php";
        else $file = "view.php";

        // 본문
        $result = $connect->all("select * from {$t_board}_{$table_name} $s_que", $s_params);
        ?>

<br><br><br>

&nbsp;&nbsp;<a href=../zboard.php?id=<?= e($table_name) ?> target=_blank><font size=4 style=font-family:tahoma; color=black><?= e($table_name) ?>&nbsp;<b>게시판</b></font></a><br>
<?php
           foreach ($result as $data)
           {
               flush();
               $data['subject'] = str_ireplace(e($keyword), "<font color=red>" . e($keyword) . "</font>", e($data['subject']));
               ?>

&nbsp;&nbsp; [<?= e($data['name']) ?>] &nbsp;
<a href=../<?= $file ?>?id=<?= e($table_name) ?>&no=<?= e($data['no']) ?> target=_blank><?= $data['subject'] ?></a></b> 
&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
<font color=666666>(<font color=blue><?= date("Y-m-d H:i:s", $data['reg_date']) ?></font> / <font color=green><?= e($data['ip']) ?></font>)</font>

<img src=../images/t.gif border=0 height=20><Br>

<?php
           }
        /// 코멘트
        if ($comment_search)
        {
            $result = $connect->all("select * from {$t_comment}_{$table_name} $s_que", $s_params);
            ?>

<br><Br><br>
&nbsp;&nbsp;&nbsp;&nbsp;<a href=../zboard.php?id=<?= e($table_name) ?> target=_blank><font size=3 style=font-family:tahoma;><?= e($table_name) ?><b>게시판</b> 의 간단한 답글</font></a>
<br>
<?php
                foreach ($result as $data)
                {
                    flush();
                    $data['memo'] = str_ireplace(e($keyword), "<font color=red>" . e($keyword) . "</font>", e($data['memo']));
                    ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [ <?= e($data['name']) ?> ]
&nbsp;<a href=../<?= $file?>?id=<?= e($table_name) ?>&no=<?= e($data['parent']) ?> target=_blank><?= $data['memo'] ?></a> &nbsp;&nbsp;
<font color=666666>(<font color=blue><?= date("Y-m-d H:i:s", $data['reg_date']) ?></font> / <font color=green><?= e($data['ip']) ?></font>)</font>
<img src=../images/t.gif border=0 height=20><Br>

<?php
                }
        }
    }

}
?>

<br><Br><Br>

<?php
 foot();
?>
