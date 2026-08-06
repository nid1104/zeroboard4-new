<?php
if (!defined('_ZB_PATH')) exit();

/*********************************************************************
 * 회원 정보 변경에 대한 처리
 *********************************************************************/

$no = Request::req('no');
$member_no = Request::req('member_no');
$board_num = Request::req('board_num');
$movelevel = Request::req('movelevel');
$movegroup = Request::req('movegroup');
$keyword = Request::req('keyword');
$keykind = Request::req('keykind');
$like = Request::req('like');
$level_search = Request::req('level_search');
$cart = Request::postArr('cart');
if (!$cart) $cart = Request::getArr('cart');
$name = Request::req('name');
$password = Request::post('password');
$password1 = Request::post('password1');
$level = Request::req('level');
$is_admin = Request::req('is_admin'); // 회원 관리자등급 폼값 (admin 흐름에서는 페이지 권한용 $is_admin과 충돌 없음)
$birth_1 = Request::req('birth_1');
$birth_2 = Request::req('birth_2');
$birth_3 = Request::req('birth_3');
$email = Request::req('email');
$homepage = Request::req('homepage');
$icq = Request::req('icq');
$aol = Request::req('aol');
$msn = Request::req('msn');
$hobby = Request::req('hobby');
$job = Request::req('job');
$home_address = Request::req('home_address');
$home_tel = Request::req('home_tel');
$office_address = Request::req('office_address');
$office_tel = Request::req('office_tel');
$handphone = Request::req('handphone');
$mailing = Request::req('mailing');
$openinfo = Request::req('openinfo');
$comment = Request::req('comment');
$maxdirsize = Request::req('maxdirsize');
$delete_private_icon = Request::req('delete_private_icon');
$delete_private_name = Request::req('delete_private_name');
$picture = $picture_name = $picture_type = '';
$private_icon = $private_icon_name = $private_icon_type = '';
$private_name = $private_name_name = $private_name_type = '';
$picture_size = $private_icon_size = $private_name_size = 0;

// 대상 회원을 이 관리자가 관리할 수 있는지 판단하는 함수
function canManageMember(array $me, ?array $target): bool {
    if (empty($target['no'])) return false;
    if ($me['is_admin'] == '1') return true;
    if ($me['is_admin'] == '2') {
        if (($target['group_no'] ?? '') != $me['group_no']) return false;
        if ((int)($target['is_admin'] ?? 3) <= (int)$me['is_admin']) return false;
        return true;
    }
    return false;
}

function del_member($no) {
    global $group_no, $member_table, $get_memo_table,  $send_memo_table,$admin_table, $t_board, $t_comment, $connect, $group_table, $member;

    $member_data = $connect->row("select * from $member_table where no = ?", [$no]);
    if (!canManageMember($member, $member_data)) error("선택하신 회원의 정보를 변경할 권한이 없습니다");

    // 멤버 정보 삭제
    $connect->exec("delete from $member_table where no=?", [$no]);

    // 쪽지 테이블에서 멤버 정보 삭제
    $connect->exec("delete from $get_memo_table where member_no=?", [$no]);
    $connect->exec("delete from $send_memo_table where member_no=?", [$no]);

    // 그룹테이블에서 회원수 -1
    $connect->exec("update $group_table set member_num=member_num-1 where no = ?", [$group_no]);

    // 이름 그림, 아이콘, 이미지 박스 사용용량 파일 삭제
    @z_unlink("icon/private_name/" . $no . ".gif");
    @z_unlink("icon/private_icon/" . $no . ".gif");
    @z_unlink("icon/member_image_box/" . $no . "_maxsize.php");
}


// 회원전체 삭제하는 부분

if ($exec2 == "deleteall") {
    for ($i = 0; $i < sizeof($cart); $i++) {
        del_member($cart[$i]);
    }
    Response::redirect(Request::scriptName() . "?exec=view_member&group_no=$group_no&page=$page&keyword=$keyword&keykind=$keykind&like=$like&level_search=$level_search&page_num=$page_num");
}


// 회원 게시판 권한 취소시키는 부분

if ($exec2 == "modify_member_board_manager") {

    $_temp = $connect->row("select * from $member_table where no = ?", [$member_no]);
    if (!canManageMember($member, $_temp)) error("선택하신 회원의 정보를 변경할 권한이 없습니다");

    $__temp = explode(",", $_temp['board_name'] ?? '');

    $_st = "";

    for ($u = 0; $u < count($__temp); $u++) {
        $kk = trim($__temp[$u]);
        if ($kk && $kk != $board_num && isnum($kk)) $_st .= $kk . ",";
    }

    $connect->exec("update $member_table set board_name = ? where no=?", [$_st, $member_no]);

    Response::redirect(Request::scriptName() . "?exec=view_member&exec2=modify&group_no=$group_no&page=$page&keyword=$keyword&level_search=$level_search&page_num=$page_num&no=$member_no&keykind=$keykind&like=$like");
}


// 회원 게시판 권한 추가시키는 부분

if ($exec2 == "add_member_board_manager") {

    $_temp = $connect->row("select * from $member_table where no = ?", [$member_no]);
    if (!canManageMember($member, $_temp)) error("선택하신 회원의 정보를 변경할 권한이 없습니다");

    if (!isnum($board_num)) error("잘못된 게시판입니다");
    if ($member['is_admin'] == '1') $_ok = (int)$connect->value("select count(*) from $admin_table where no=?", [$board_num]);
    elseif ($member['is_admin'] == '2') $_ok = (int)$connect->value("select count(*) from $admin_table where no=? and group_no=?", [$board_num, $member['group_no']]);
    else $_ok = 0;
    if (!$_ok) error("해당 게시판에 대한 권한이 없습니다");

    $_board_name = ($_temp['board_name'] ?? '') . $board_num . ",";

    $connect->exec("update $member_table set board_name = ? where no=?", [$_board_name, $member_no]);

    Response::redirect(Request::scriptName() . "?exec=view_member&exec2=modify&group_no=$group_no&page=$page&keyword=$keyword&level_search=$level_search&page_num=$page_num&no=$member_no&keykind=$keykind&like=$like");
}


// 회원 권한 변경하는 부분

if ($exec2 == "moveall") {
    for ($i = 0; $i < sizeof($cart); $i++) {
        $_t = $connect->row("select no,group_no,is_admin from $member_table where no=?", [$cart[$i]]);
        if (!canManageMember($member, $_t)) error("선택하신 회원의 정보를 변경할 권한이 없습니다");
        $connect->exec("update $member_table set level=? where no=?", [$movelevel, $cart[$i]]);
    }
    Response::redirect(Request::scriptName() . "?exec=view_member&group_no=$group_no&page=$page&keyword=$keyword&level_search=$level_search&page_num=$page_num&keykind=$keykind&like=$like");
}


// 회원 그룹 변경하는 부분

if ($exec2 == "move_group" && $member['is_admin'] == 1) {
    for ($i = 0; $i < sizeof($cart); $i++) {
        $connect->exec("update $member_table set group_no=? where no=?", [$movegroup, $cart[$i]]);
        $connect->exec("update $group_table set member_num=member_num-1 where no=?", [$group_no]);
        $connect->exec("update $group_table set member_num=member_num+1 where no=?", [$movegroup]);
    }
    Response::redirect(Request::scriptName() . "?exec=view_member&group_no=$group_no&page=$page&keyword=$keyword&level_search=$level_search&page_num=$page_num&keykind=$keykind&like=$like");
}


// 회원삭제하는 부분

if ($exec2 == "del") {
    del_member($no);
    Response::redirect(Request::scriptName() . "?exec=view_member&group_no=$group_no&page=$page&keyword=$keyword&level_search=$level_search&page_num=$page_num&keykind=$keykind&like=$like");
}


// 회원정보 변경하는 부분

if ($exec2 == "modify_member_ok") {

    $_target = $connect->row("select no,group_no,is_admin from $member_table where no=?", [$member_no]);
    if (!canManageMember($member, $_target)) error("선택하신 회원의 정보를 변경할 권한이 없습니다");

    if (isblank($name)) Error("이름을 입력하셔야 합니다");
    if (strpos($name, '<') !== false || strpos($name, '>') !== false) Error("이름을 영문, 한글, 숫자등으로 입력하여 주십시오");

    if ($password && $password1 && $password != $password1) Error("비밀번호가 일치하지 않습니다");

    $birth = mktime(0, 0, 0, (int)$birth_2, (int)$birth_3, (int)$birth_1);

    if ($member['no'] == $member_no) {
        $is_admin = $member['is_admin'];
        $level = $member['level'];
    }

    $que = "update $member_table set name=?";
    $params = [$name];

    if ($level) { $que .= ",level=?"; $params[] = $level; }

    if ($password && $password1 && $password == $password) { $que .= " ,password=? "; $params[] = createHash($password); }
    if ($member['is_admin'] == 1) { $que .= ",is_admin=?"; $params[] = $is_admin; }

    if ($birth_1 && $birth_2 && $birth_3) { $que .= ",birth=?"; $params[] = $birth; }
    $que .= ",email=?"; $params[] = $email;
    $que .= ",homepage=?"; $params[] = $homepage;
    $que .= ",icq=?"; $params[] = $icq;
    $que .= ",aol=?"; $params[] = $aol;
    $que .= ",msn=?"; $params[] = $msn;
    $que .= ",hobby=?"; $params[] = $hobby;
    $que .= ",job=?"; $params[] = $job;
    $que .= ",home_address=?"; $params[] = $home_address;
    $que .= ",home_tel=?"; $params[] = $home_tel;
    $que .= ",office_address=?"; $params[] = $office_address;
    $que .= ",office_tel=?"; $params[] = $office_tel;
    $que .= ",handphone=?"; $params[] = $handphone;
    $que .= ",mailing=?"; $params[] = $mailing;
    $que .= ",openinfo=?"; $params[] = $openinfo;
    $que .= ",comment=?"; $params[] = $comment;
    $que .= " where no=?"; $params[] = $member_no;

    $connect->exec($que, $params);

    // 회원의 소개 사진
    if ($_uf = Request::file('picture')) {
        $picture = $_uf->tmpName();
        $picture_name = $_uf->name();
        $picture_type = $_uf->type();
        $picture_size = $_uf->size();
    }
    if ($picture_name) {
        if (!$_uf->isValid()) Error("정상적인 방법으로 업로드하여 주십시요");
        if (!preg_match("/\.(gif|jpe?g)$/i", $picture_name)) Error("사진은 gif 또는 jpg 파일을 올려주세요");
        $size = getimagesize($picture);
        if ($size === false) Error("유효하지 않은 아이콘입니다");
        if ($size[0] > 200 || $size[1] > 200) Error("아이콘의 크기는 200*200이하여야 합니다");
        $kind = array("", "gif", "jpg");
        $n = $size[2];
        $path = "icon/member_" . bin2hex(random_bytes(16)) . '.' . $kind[$n];
        @$_uf->moveTo($path);
        @chmod($path, 0707);
        $connect->exec("update $member_table set picture=? where no=?", [$path, $member_no]);
    }

    // 이미지 박스 용량을 저장
    if ($maxdirsize <> 100) {
        $maxdirsize = (int) $maxdirsize * 1024;
        // icon 디렉토리에 member_image_box 디렉토리가 없을경우 디렉토리 생성
        $path = "icon/member_image_box";
        if (!is_dir($path)) {
            @mkdir($path, 0707);
            @chmod($path, 0707);
            createIndexFile($path);
        }

        zWriteFile("icon/member_image_box/" . $member_no . "_maxsize.php", "<?php exit();/*" . base64_encode((string) $maxdirsize) . "*/?>");
    }

    // 이름앞에 붙는 아이콘 삭제시
    if ($delete_private_icon) @z_unlink("icon/private_icon/" . $member_no . ".gif");

    if ($_uf = Request::file('private_icon')) {
        $private_icon = $_uf->tmpName();
        $private_icon_name = $_uf->name();
        $private_icon_type = $_uf->type();
        $private_icon_size = $_uf->size();
    }
    // 이름앞에 붙는 아이콘 업로드시 처리
    if (@filesize($private_icon)) {
        if (!is_dir("icon/private_icon")) {
            @mkdir("icon/private_icon", 0707);
            @chmod("icon/private_icon", 0707);
            createIndexFile("icon/private_icon");
        }

        if (!$_uf->isValid()) Error("정상적인 방법으로 업로드하여 주십시요");
        if (!preg_match("/\.gif$/i", $private_icon_name)) Error("이름앞의 아이콘은 Gif 파일만 올리실수 있습니다");
        @$_uf->moveTo("icon/private_icon/" . $member_no . ".gif");
        @chmod("icon/private_icon" . $member_no . ".gif", 0707);
        @chmod("icon/private_icon", 0707);
    }

    // 이름을 대신하는 아이콘 삭제시
    if ($delete_private_name) @z_unlink("icon/private_name/" . $member_no . ".gif");

    // 이름을 대신하는 아이콘 업로드시 처리
    if ($_uf = Request::file('private_name')) {
        $private_name = $_uf->tmpName();
        $private_name_name = $_uf->name();
        $private_name_type = $_uf->type();
        $private_name_size = $_uf->size();
    }
    if (@filesize($private_name)) {
        if (!is_dir("icon/private_name")) {
            @mkdir("icon/private_name", 0707);
            @chmod("icon/private_name", 0707);
            createIndexFile("icon/private_name");
        }

        if (!$_uf->isValid()) Error("정상적인 방법으로 업로드하여 주십시요");
        if (!preg_match("/\.gif$/i", $private_name_name)) Error("이름아이콘은 Gif 파일만 올리실수 있습니다");
        @$_uf->moveTo("icon/private_name/" . $member_no . ".gif");
        @chmod("icon/private_name" . $member_no . ".gif", 0707);
        @chmod("icon/private_name", 0707);
    }

    Response::redirect(Request::scriptName() . "?exec=view_member&exec2=modify&no=$member_no&group_no=$group_no&page=$page&keyword=$keyword&level_search=$level_search&page_num=$page_num&keykind=$keykind&like=$like");
}

?>
