<?php
/***************************************************************************
 * 관리자 페이지
 **************************************************************************/

require_once __DIR__ . '/lib.php';

$connect = dbConn();
$member = member_info();

$exec = preg_replace('/\W/', '', Request::req('exec'));
$exec2 = preg_replace('/\W/', '', Request::req('exec2'));
$page = (int) Request::req('page');
$page_num = (int) Request::req('page_num');
$group_no = (int) Request::req('group_no');


if (!$member['no']) Error("로그인후 사용하여주십시요", "admin.php");

if ($member['is_admin'] >= 3 && !$member['board_name']) Error("관리자페이지를 사용할수 있는 권한이 없습니다", "admin.php");

// admin 상태변경 액션은 CSRF 토큰 필수 — POST 폼은 body, GET 링크는 query에 토큰(method 무관 검증)
// 이 리스트의 액션은 폼/링크 어느 쪽으로 와도 토큰이 없으면 차단, display(목록/폼표시)는 통과
$_zb_mut_exec2 = ['deleteall', 'moveall', 'move_group', 'del', 'modify_member_ok',
    'modify_member_board_manager', 'add_member_board_manager',
    'modify_ok', 'add_ok', 'category_add', 'del_category', 'category_modify_ok', 'category_move', 'modify_grant_ok'];
$_zb_mut_exec = ['add_group_ok', 'modify_group_ok', 'del_group_ok', 'modify_member_join_ok', 'uninstall'];
if (in_array($exec2, $_zb_mut_exec2, true) || in_array($exec, $_zb_mut_exec, true)) check_csrf(true);


// 게시판 관리자일때
if ($member['is_admin'] >= 3 && $member['board_name'] && $exec != "logout") $exec = "view_board";

// DB 백업일때
if ($member['is_admin'] == 1 && $exec == "db_dump") {
    check_csrf(true);
    set_time_limit(0);
    require_once __DIR__ . '/admin/dbDump.php';
    $dbname = DB_NAME;
    $filename = $dbname . "_" . date("Ymd") . ".sql";
    zbDB_Header($filename);
    zbDB_All_down($dbname);
    exit();
}

// 각 기능별 파일 호출
if ($exec == "view_board") { include "admin/admin_exec_board.php"; }
elseif ($exec == "view_member") { include "admin/admin_exec_member.php"; }
else { include "admin/admin_exec_group.php"; }

head(" bgcolor=444444 ");
?>

<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#000000" height=100%>
  <tr bgcolor="#FFFFFF"> 
    <td width="195" height="64"><a href="admin.php" target="_top"><img src="images/logo.gif" width="159" height="64" border="0"></a></td>
    <td bgcolor="#6F080A" width=100%> 

      <table width="100%" border="0" cellspacing="0" cellpadding="0" height="64">
        <tr> 
          <td valign="top" colspan="2" align=right>
	      		<img src=images/t.gif border=0 height=5><Br> 

<?php if ($member['is_admin'] == 1) {?>
	      		<a href=admin_setup.php?exec=uninstall&zb_csrf_token=<?= Session::csrfToken() ?>><font color=white style=font-size:9pt onclick="return confirm('제로보드를 제거하시겠습니까?')"><b>Uninstall</b></font></a> &nbsp;|&nbsp;
	      		<a href="admin_setup.php?exec=db_dump&zb_csrf_token=<?= Session::csrfToken() ?>"><font color=white style=font-size:9pt onclick="return confirm('백업하시겠습니까?')"><b>DB 백업</b></font></a> &nbsp;|&nbsp;
	      		<a href=admin_setup.php?exec=db_status><font color=white style=font-size:9pt><b>DB 상태 보기</b></font></a> &nbsp;|&nbsp;
	      		<a href="admin/arrangefile.php?zb_csrf_token=<?= Session::csrfToken() ?>" target=_blank onclick="return confirm('첨부파일 정리시에는 첨부파일의 종류에 따라서 시간이 오래 걸릴수 있습니다\n\n실행하시겠습니까?')"><font color=white style=font-size:9pt><b>첨부파일 정리</b></font></a> &nbsp;|&nbsp;
	      		<a href="admin/delsession.php?zb_csrf_token=<?= Session::csrfToken() ?>" target=_blank onclick="return confirm('세션의 양이 많아지면 전체적인 속도가 느려질수 있습니다.\n\n세션 디렉토리를 정리하시겠습니까?')"><font color=white style=font-size:9pt><b>세션 비우기</b></font></a> &nbsp;|&nbsp;
	      		<a href=admin/trace.php target=_blank><font color=white style=font-size:9pt><b>게시물 추적</b></font></a> &nbsp;|&nbsp;
	      		<a href=manual/index.html target=_blank><font color=white style=font-size:9pt><b>매뉴얼</b></font></a>&nbsp;&nbsp;
<?php }?>

          </td>
        </tr>
        <tr> 
          <td valign="bottom" width="180"><img src="images/admintop.gif" width="180" height="20"></td>
          <td valign="bottom" align="right"> 

            <table border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td><img src="images/adminid.gif" width="100" height="24"></td>
                <td nowrap style="font-family:Tahoma;font-size:8pt;"><font color="#FFFFFF"><b><?= e($member['user_id']) ?></b></font></td>
                <td><img src="images/adminlevel.gif" width="49" height="24"></td>
                <td nowrap style="font-family:Tahoma;font-size:8pt;">

<?php
// 최고관리자일때
	if ($member['is_admin'] == 1)
	    echo "<b><font color=#ffffff>Super Administrator</font></b> <a href=" . Request::scriptName() . "?exec=view_member&exec2=modify&no=" . e($member['no']) . "><font color=#ffffff style=font-family:Tahoma;font-size:8pt;>(Edit information)</font></a>";

// 그룹관리자일때
	elseif ($member['is_admin'] == 2)
	    echo "<b><font color=#ffffff>Group Administrator</font></b> <a href=" . Request::scriptName() . "?exec=view_member&group_no=" . e($member['group_no']) . "&exec2=modify&no=" . e($member['no']) . "><font color=#ffffff style=font-family:Tahoma;font-size:8pt;>(Edit information)</font></a>";

// 게시판 관리자일때
	elseif ($member['board_name'])
	    echo "<b><font color=#ffffff>Board Administrator</font></b>";

// 기타일때;; -_-;;
	else "<b><font color=#ffffff>Normal Member</font></b> <a href=" . Request::scriptName() . "?exec=view_member&group_no=" . e($member['group_no']) . "&exec2=modify&no=" . e($member['no']) . "><font color=#ffffff style=font-family:Tahoma;font-size:8pt;>(Edit information)</font></a>";
?>
              		&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href=logout.php?s_url=admin.php><font color=white style=font-size:9pt><b>Log Out</b></font></a>&nbsp;&nbsp;
                </td>
              </tr>
            </table>

          </td>
        </tr>
      </table>

    </td>
  </tr>
  <tr bgcolor="#FFFFFF"> 

<!-- 그룹을 관리하는 부분 -->
    <td bgcolor="#3F3F3F" valign="top">  

<!-- 그룹관리 -->

<?php

// 최고관리자인경우 모든 그룹 보여주고 추가/삭제 가능

	if ($member['is_admin'] == 1) {

	    // 모든그룹의 데이타 갖고옴;;
	    $group_rows = $connect->all("SELECT * FROM {$group_table} ORDER BY no ");
	    ?>
		<table width=100% border=0 cellspacing=0 cellpadding=0>
		<tr> 
			<td bgcolor=#595959><img src=images/l_group.gif></td>
		</tr>
		</table>
<?php

	    		foreach ($group_rows as $group_data) {
	    		    if ($group_data['no'] == $group_no) $b = "<b>"; else $b = "";

	    		    // 그룹이름 출력
	    		    ?>

		<table width=100% border=0 cellspacing=0 cellpadding=0>
		<tr> 
			<td height=29 background=images/gnamebg.gif align=center><img src=images/t.gif width=10 height=3><br>
				&nbsp;<a href=<?= Request::scriptName() ?>?exec=view_group&group_no=<?= e($group_data['no']) ?>><font color=white><?= $b . e($group_data['name']) ?> (<?= e($group_data['no']) ?>)</b></font></a></td>
		</tr>

<?php

	    		    			// 현재 선택된 그룹과 루핑되는 그룹과 매치될때;;
	    		    			if ($group_no == $group_data['no']) {
	    		    			    ?>

		<tr> 
			<td bgcolor=#868686 style=font-family:Tahoma;font-size:8pt;padding:3px><img src=images/g_top.gif width=38 height=14><br>
				<a href=<?= Request::scriptName() ?>?group_no=<?= e($group_data['no']) ?>&exec=modify_group><img src=images/g_properties.gif border=0 alt="그룹 설정"></a>

<?php
	    		    			    				if ($member['is_admin'] == 1)
	    		    			    				    echo "<a href=" . Request::scriptName() . "?group_no=" . e($group_data['no']) . "&exec=del_group><img src=images/g_delete.gif border=0 alt=\"그룹 삭제\"></a>";
	    		    			    ?>

				<img src=images/t.gif width=10 height=5><br>
				<img src=images/m_top1.gif width=51 height=14 align=absmiddle><b><font color=#FFFFFF><?= e($group_data['member_num']) ?></font></b><img src=images/m_top2.gif width=6 height=14 align=absmiddle><br>
				<a href=<?= Request::scriptName() ?>?exec=view_member&group_no=<?= e($group_data['no']) ?>><img src=images/m_manage.gif border=0 alt="회원 관리"></a><a href=<?= Request::scriptName() ?>?exec=modify_member_join&group_no=<?= e($group_data['no']) ?>><img src=images/m_joinform.gif border=0 alt="가입양식 설정"></a><br>
				<img src=images/t.gif width=10 height=5><br>
				<img src=images/w_top1.gif width=58 height=14 align=absmiddle><b><font color=#FFFFFF><?= e($group_data['board_num']) ?></font></b><img src=images/w_top2.gif width=4 height=14 align=absmiddle> 
				<br>
				<a href=<?= Request::scriptName() ?>?exec=view_board&group_no=<?= e($group_data['no']) ?>&page=<?= e($page) ?>&page_num=<?= e($page_num) ?>><img src=images/w_manage.gif alt="게시판 관리" border=0></a><a href=<?= Request::scriptName() ?>?exec=view_board&exec2=add&group_no=<?= e($group_data['no']) ?>><img src=images/w_add.gif alt="게시판 추가" border=0></a> 
			</td>
		</tr>
<?php
	    		    			}

	    		    echo "</table>";
	    		}
	}


// 최고관리자가아닐때;;

	else {
	    $group_data = $connect->row("SELECT * FROM {$group_table} WHERE no = ?", [$member['group_no']]);
	    $group_no = $group_data['no'];
	    $b = "<b>";
	    ?>

		<table width=100% border=0 cellspacing=0 cellpadding=0>
		<tr>
			<td bgcolor=#595959 align=center><img src=images/l_group.gif></td>
		</tr>
		</table>
		<table width=100% border=0 cellspacing=0 cellpadding=0>
		<tr>
			<td height=29 background=images/gnamebg.gif align=center><img src=images/t.gif width=10 height=3><br>
				<a href=<?= Request::scriptName() ?>?exec=view_group&group_no=<?= e($group_data['no']) ?>><b><font color=white><?= $b . e($group_data['name']) ?> (<?= e($group_data['no']) ?>)</b></font></a></td>
		</tr>

<?php
	    		// 메뉴출력
	    		if ($member['is_admin'] == 2) {
	    		    ?>

		<tr>
			<td bgcolor=#868686 style=font-family:Tahoma;font-size:8pt;padding:3px><img src=images/g_top.gif width=38 height=14><br>
				<a href=<?= Request::scriptName() ?>?group_no=<?= e($group_data['no']) ?>&exec=modify_group><img src=images/g_properties.gif width=60 height=12 border=0 alt="그룹 설정"></a><br>
				<img src=images/t.gif width=10 height=5><br>
				<img src=images/m_top1.gif width=51 height=14 align=absmiddle><b><font color=#FFFFFF><?= e($group_data['member_num']) ?></font></b><img src=images/m_top2.gif width=6 height=14 align=absmiddle><br>
				<a href=<?= Request::scriptName() ?>?exec=view_member&group_no=<?= e($group_data['no']) ?>><img src=images/m_manage.gif width=73 height=12 border=0 alt="회원 관리"></a><a href=<?= Request::scriptName() ?>?exec=modify_member_join&group_no=<?= e($group_data['no']) ?>><img src=images/m_joinform.gif width=55 height=12 border=0 alt="가입폼설정"></a><br>
				<img src=images/t.gif width=10 height=5><br>
				<img src=images/w_top1.gif width=58 height=14 align=absmiddle><b><font color=#FFFFFF><?= e($group_data['board_num']) ?></font></b><img src=images/w_top2.gif width=4 height=14 align=absmiddle>
				<br>
				<a href=<?= Request::scriptName() ?>?exec=view_board&group_no=<?= e($group_data['no']) ?>><img src=images/w_manage.gif width=73 height=12 alt="게시판 관리" border=0></a><a href=<?= Request::scriptName() ?>?exec=view_board&exec2=add&group_no=<?= e($group_data['no']) ?>><img src=images/w_add.gif width=29 height=12 alt="게시판 추가" border=0></a>
			</td>
		</tr>

<?php
	    		}
	    echo "</table>";
	}
?>
<!-- 카피라이트 -->
		<table width=100% border=0 cellspacing=0 cellpadding=0>
		<tr>
			<Td bgcolor=666666 height=1><img src=images/t.gif border=0 height=1></td>
		</tr>
<?php
	if ($member['is_admin'] == 1) {
	    ?>
		<tr>
			<td bgcolor="#3F3F3F" valign="bottom"><img src="images/t.gif" width="10" height="10"><br><a href=<?= Request::scriptName() ?>?exec=add_group><img src=images/l_addgroup.gif border=0></a></td>
		</tr>
<?php
	}
?>
		</table>
		<table width=100% border=0 cellspacing=0 cellpadding=0>
		<tr>
			<td bgcolor="#3F3F3F" valign="bottom" height="45"><img src="images/t.gif" width="10" height="10"><br>
				<a href="http://www.zeroboard.com" target="_top"><img src="images/admincopyright.gif" width="159" height="35" border="0"></a>
			</td>
		</tr>
		</table>

	</td>

 <!-- 직접적인 활동무대~~ 냐하핫~ -->
   <td width=100% align=center valign=top bgcolor=#3d3d3d>

<?php
// 최고관리자일때
	if ($member['is_admin'] == 1) {
	    if ($exec == "add_group") { include "admin/admin_add_group.php"; }
	    elseif ($exec == "uninstall") { include "admin/admin_uninstall.php"; }
	    elseif ($exec == "db_status") {include "admin/admin_dbstatus.php"; }
	    elseif ($exec == "modify_group") { include "admin/admin_modify_group.php"; }
	    elseif ($exec == "view_group") { include "admin/admin_view_group.php"; }
	    elseif ($exec == "del_group") { include "admin/admin_del_group.php"; }
	    elseif ($exec == "modify_member_join") { include "admin/admin_modify_member_join.php"; }
	    elseif ($exec == "view_member") {
	        if ($exec2 == "sendmail") {include "admin/admin_sendmail.php"; }
	        elseif ($exec2 == "modify") {include "admin/admin_modify_member.php"; }
	        else {include "admin/admin_view_member.php"; }
	    } elseif ($exec == "view_board") {
	        if ($exec2 == "add") {include "admin/admin_add_board.php"; }
	        elseif ($exec2 == "modify") {include "admin/admin_modify_board.php"; }
	        elseif ($exec2 == "category") {include "admin/admin_category.php"; }
	        elseif ($exec2 == "modify_category") {include "admin/admin_category_modify.php"; }
	        elseif ($exec2 == "grant") {include "admin/admin_modify_grant.php"; }
	        else {include "admin/admin_board_list.php"; }
	    } else {include "admin/readme.php"; }

	    // 그룹 관리자일 경우
	} elseif ($member['is_admin'] == 2) {
	    $group_no = $group_data['no'];
	    if ($exec == "modify_group") { include "admin/admin_modify_group.php"; }
	    elseif ($exec == "modify_group") { include "admin/admin_modify_group.php"; }
	    elseif ($exec == "modify_member_join") { include "admin/admin_modify_member_join.php"; }
	    elseif ($exec == "view_member") {
	        if ($exec2 == "sendmail") {include "admin/admin_sendmail.php"; }
	        elseif ($exec2 == "modify") {include "admin/admin_modify_member.php"; }
	        else {include "admin/admin_view_member.php"; }
	    } elseif ($exec == "view_board") {
	        if ($exec2 == "add") {include "admin/admin_add_board.php"; }
	        elseif ($exec2 == "modify") {include "admin/admin_modify_board.php"; }
	        elseif ($exec2 == "category") {include "admin/admin_category.php"; }
	        elseif ($exec2 == "modify_category") {include "admin/admin_category_modify.php"; }
	        elseif ($exec2 == "grant") {include "admin/admin_modify_grant.php"; }
	        else {include "admin/admin_board_list.php"; }
	    } else {include "admin/admin_view_group.php"; }
	} elseif ($member['board_name']) {
	    if ($exec == "view_board") {
	        if ($exec2 == "modify") {include "admin/admin_modify_board.php"; }
	        elseif ($exec2 == "category") {include "admin/admin_category.php"; }
	        elseif ($exec2 == "modify_category") {include "admin/admin_category_modify.php"; }
	        elseif ($exec2 == "grant") {include "admin/admin_modify_grant.php"; }
	        else {include "admin/admin_board_list.php"; }
	    }

	    // 게시판 관리자일때
	} elseif ($member['board_name'] && $exec == "view_board") {
	    if ($exec2 == "modify") {include "admin/admin_modify_board.php"; }
	    elseif ($exec2 == "category") {include "admin/admin_category.php"; }
	    elseif ($exec2 == "modify_category") {include "admin/admin_category_modify.php"; }
	    else {include "admin/admin_board_list.php"; }
	}
?>

	</td>
</tr>
</table>

<?php
	foot();
?>
