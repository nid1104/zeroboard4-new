<?php
require_once __DIR__ . '/lib.php';

// DB 연결
if (!isset($connect)) $connect = dbConn();

// 멤버 정보 구해오기
$member = member_info();

if (!$member['no']) Error("로그인 상태가 아닙니다");

$s_url = Request::req('s_url');
$referer = Request::req('referer');
$page = (int) Request::req('page');
$page_num = (int) Request::req('page_num');
$select_arrange = Request::req('select_arrange');
$des = Request::req('des');
$sn = Request::req('sn');
$ss = Request::req('ss');
$sc = Request::req('sc');
$keyword = Request::req('keyword');
$category = Request::req('category');
$no = (int) Request::req('no');

if (!($group_no = Request::req('group_no'))) $group_no = $member['group_no'];

$id = Request::req('id');
if ($id !== '' && !isalNum($id)) Error("게시판 이름이 올바르지 않습니다");
if ($id) $setup = get_table_attrib($id);

if (!empty($setup['group_no']) && !$group_no) $group_no = $setup['group_no'];

destroyZBSessionID($member['no']);

Session::destroy();
Session::start();

if ($s_url && isSafeRedirect($s_url)) Response::redirect($s_url);
if ($id) Response::redirect("zboard.php?id=$id&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$des&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&category=$category&no=$no");
elseif (!empty($group['join_return_url']) && zb_url_scheme_ok($group['join_return_url'])) Response::redirect($group['join_return_url']);
elseif ($referer && isSafeRedirect($referer)) Response::redirect($referer);
else echo "<script>history.go(-2);</script>";
?>
