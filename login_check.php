<?php
require_once __DIR__ . '/lib.php';

$connect = dbconn();

check_csrf();
$user_id = trim(Request::post('user_id'));
$password = trim(Request::post('password'));

$auto_login = Request::req('auto_login');
$s_url = Request::req('s_url');
$referer = Request::req('referer');
$page = Request::req('page');
$page_num = Request::req('page_num');
$select_arrange = Request::req('select_arrange');
$des = Request::req('des');
$sn = Request::req('sn');
$ss = Request::req('ss');
$sc = Request::req('sc');
$keyword = Request::req('keyword');
$category = Request::req('category');
$no = (int) Request::req('no');

if ($user_id === '') error("아이디를 입력하여 주십시요");
if ($password === '') error("비밀번호를 입력하여 주십시요");

$id = Request::req('id');
if ($id !== '' && !isalNum($id)) error("게시판 이름이 올바르지 않습니다");
if ($id) {
    $setup = get_table_attrib($id);
    $group = group_info($setup['group_no']);
}

if (!empty($setup['group_no'])) $group_no = $setup['group_no'];


// 회원 로그인 체크
$member_data = $connect->row("SELECT * FROM {$member_table} WHERE user_id = ?", [$user_id]);

// 회원로그인이 성공하였을 경우 세션을 생성하고 페이지를 이동함
if (isset($member_data['no']) && verifyHash($password, $member_data['password'])) {
    if (needsRehash($member_data['password'])) {
        $connect->exec(
            "UPDATE {$member_table} SET password = ? WHERE no = ?",
            [createHash($password), $member_data['no']]
        );
    }
    Session::regenerate();

    if (!empty($auto_login)) {
        makeZBSessionID($member_data['no']);
    }

    Session::set('zb_logged_no', $member_data['no']);
    Session::set('zb_logged_time', time());
    Session::set('zb_logged_ip', Request::clientIp());
    Session::set('zb_last_connect_check', '0');

    // 로그인 후 페이지 이동
    $s_url = urldecode($s_url);
    if (!$s_url && $id) $s_url = "zboard.php?id={$id}";
    if ($s_url && isSafeRedirect($s_url)) Response::redirect($s_url);
    elseif ($id) Response::redirect("zboard.php?id=$id&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$des&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&category=$category&no=$no");
    elseif (!empty($group['join_return_url']) && zb_url_scheme_ok($group['join_return_url'])) Response::redirect($group['join_return_url']);
    elseif ($referer && isSafeRedirect($referer)) Response::redirect($referer);
    else echo "<script>history.go(-2);</script>";

    // 회원로그인이 실패하였을 경우 에러 표시
} else {
    head();
    error("로그인을 실패하였습니다");
    foot();
}

?>
