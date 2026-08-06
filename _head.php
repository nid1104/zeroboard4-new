<?php
/***************************************************************************
 * 여러번 호출시 에러 발생 금지
 **************************************************************************/
if (isset($_head_php_excuted) && $_head_php_excuted) return;
$_head_php_excuted = true;

/***************************************************************************
 * 기본 라이브러리 include 
 **************************************************************************/

// 라이브러리 함수 파일 include
require_once __DIR__ . '/lib.php';

/***************************************************************************
 * 현재 _head.php를 호출하는 파일이 게시판 관련 파일인지 검사
 **************************************************************************/
$_zb_file_list = ["apply_vote.php", "comment_ok.php", "del_comment.php", "del_comment_ok.php", "delete.php", "download.php", "list_all.php", "view.php", "vote.php", "write.php", "write_ok.php", "zboard.php", "image_box.php"];
$_zb_c = count($_zb_file_list);
for ($i = 0; $i < $_zb_c; $i++) {
    if (in_array(strtolower(basename(Request::scriptName())), $_zb_file_list, true)) {
        $_zboardis = true;
        break;
    }
    else $_zboardis = false;
}


// 리스트 체크 함수 파일 include
if ($_zboardis) include "include/list_check.php";

/***************************************************************************
 * 요청 변수 추출
 **************************************************************************/
$id = strtolower(Request::req('id'));
$page = (int) Request::req('page');
$divpage = (int) Request::req('divpage');
$prev_num = (int) Request::req('prev_num');
$select_arrange = preg_replace('/\W/', '', Request::req('select_arrange'));
$desc = preg_replace('/\W/', '', Request::req('desc'));
$category = Request::req('category');
$keyword = Request::req('keyword');
$sn = Request::req('sn');
$ss = Request::req('ss');
$sc = Request::req('sc');
$sn1 = Request::req('sn1');

if (!in_array($sn, ['on', 'off'])) $sn = 'off';
if (!in_array($ss, ['on', 'off'])) $ss = 'off';
if (!in_array($sc, ['on', 'off'])) $sc = 'off';

$s_que = '';
$t_s_que = '';
$s_params = array();
$t_s_params = array();
$href = '';
$sort = '';
$use_division = false;
$prevdivpage = 0;
$nextdivpage = 0;
$is_admin = '';
$a_category = '';
$category_num_c = array();
$category_name_c = array();
$category_n_c = array();
$category_data = array();
$_category_data = array();
$hide_category_start = $hide_category_end = '';
$hide_cart_start = $hide_cart_end = '';
$member_memo_icon = '';
$memo_on_sound = '';
$delete_all = '';

/***************************************************************************
 * 기본 설정 체크
 **************************************************************************/

// 게시판 $id 체크
if ($id !== '' && !isalNum($id)) error("게시판 이름이 올바르지 않습니다");
if ($id === '' && $_zboardis) error("게시판 이름을 지정해 주셔야 합니다.<br><br>예) zboard.php?id=이름");


/***************************************************************************
 * DB 연결하여 기본 데이타 추출
 **************************************************************************/
// DB 연결
if (!isset($connect)) $connect = dbConn();

// 멤버 정보 구해오기;;; 멤버가 있을때
$_dbTimeStart = getmicrotime();
$member = member_info();

$_dbTime += getmicrotime() - $_dbTimeStart;

/***************************************************************************
 * 현재 _head.php를 불러오는 파일이 게시판일경우에 체크 하는 항목들
 **************************************************************************/
if ($_zboardis) {

    // 게시판 설정 읽어 오기
    $_dbTimeStart = getmicrotime();
    $setup = get_table_attrib($id);
    if (!$setup['name']) Error("생성되지 않은 게시판입니다.<br><br>게시판을 생성후 사용하십시오"); // 설정되지 않은 게시판

    // 현재 게시판의 그룹의 설정 읽어 오기
    if ($_zboardis) $group = group_info($setup['group_no']);
    $_dbTime += getmicrotime() - $_dbTimeStart;

    // 현재 로그인되어 있는 멤버가 전체, 그룹관리자, 게시판관리자인지 검사
    if ($member['is_admin'] == 1 || ($member['is_admin'] == 2 && $member['group_no'] == $setup['group_no']) || check_board_master($member, $setup['no'])) $is_admin = 1;
    else $is_admin = "";

    // 현재 그룹이 폐쇄그룹이고 로그인한 멤버가 비멤버일때 에러표시
    if ($group['is_open'] == 0 && !$is_admin && $member['group_no'] != $setup['group_no']) Error("공개 되어 있지 않습니다");

    // 접근 금지 아이피인 경우 금지하기;;;
    if (!$is_admin) check_blockip();

    // 관리자일경우에는 무조건 바구니 기능 활성화 시킴 (게시물 정리를 위해서)
    if ($is_admin) $setup['use_cart'] = 1;

    // 스킨 디렉토리 : $dir 이라는 변수는 계속해서 스킨경로 파일로
    $dir = "skin/" . sanitizePathComponent($setup['skinname']);

    // 게시판의 가로크기 설정
    $width = $setup['table_width'];

    // 카테고리 읽어오기
    if ($setup['use_category']) {
        if ($category !== '' && !isnum($category)) $category = '';
        $_dbTimeStart = getmicrotime();
        $result = $connect->all("select * from {$t_category}_{$id} order by no");
        $_dbTime += getmicrotime() - $_dbTimeStart;
        $a_category = "<select name=category onchange=category_change(this)><option value=''>Category</option>";
        foreach ($result as $data) {
            $category_num_c[] = $data['no'];
            $category_name_c[] = $data['name'];
            $category_n_c[] = $data['num'];
            $category_data[$data['no']] = $data['name'];
            $_category_data[$data['no']] = $data['num'];
            if ($category == $data['no']) $a_category .= "<option value={$data['no']} selected>{$data['name']}</option>";
            else $a_category .= "<option value={$data['no']}>{$data['name']}</option>";
        }
        $a_category .= "</select>";
    } else {
        $category = "";
    }
    if (!$browser) $a_category = "Category";

    /////////////////////////////////////////////
    // write.php가 아닐때 검색개수 및 query 정리
    /////////////////////////////////////////////
    if (strtolower(basename(Request::scriptName()) !== 'write.php')) {

        // Division의 현황을 체크
        $_dbTimeStart = getmicrotime();
        $division_result = $connect->all("select * from {$t_division}_{$id} where num>0 order by division desc");
        $_dbTime += getmicrotime() - $_dbTimeStart;
        $total_division = count($division_result);
        $sum = 0;
        $division = 0;

        // division 페이지가 없으면 설정 (검색시 사용하는 단위페이지)
        if (!$divpage) $divpage = $total_division;
        if ($divpage < $total_division) $prevdivpage = $divpage + 1;
        if ($divpage > 1) $nextdivpage = $divpage - 1;

        if (!in_array($select_arrange, ['headnum', 'subject', 'name', 'hit', 'vote', 'reg_date', 'download1', 'download2'], true)) $select_arrange = "headnum";
        if ($desc !== 'desc' && $desc !== 'asc') $desc = "asc";

        // 답글 목록에 나타나지 않게 설정하였을때 (게시판 설정시 use_showreply가 체크 되었을때)
        if (!$setup['use_showreply']) {
            if (!$s_que) $s_que = " arrangenum=0 ";
            else $s_que .= " and arrangenum=0 ";
        }

        // 카테고리 : 카테고리가 있을때 category를 검색 조건에 넣음
        if ($category) {
            if (!$s_que) $s_que = " category=? ";
            else $s_que .= " and category=? ";
            $s_params[] = $category;
        }

        // 검색 기능 체크, $sn 이름 $ss 제목 $sc 내용 검사, $keyword 내용;;
        if (!$sn) $sn = "off";
        if (!$ss) $ss = "off";
        if (!$sc) $sc = "off";
        if ($sc == "off" && $sn == "off" && $ss == "off") {
            $sc = "on";
            $ss = "on";
        }
        if (!isblank($keyword)) {
            $kw_like = '%' . $connect->escapeLike($keyword) . '%';
            if (!$sn1) {
                if ($sn == "on" && $t_s_que) { $t_s_que .= " or name like ? "; $t_s_params[] = $kw_like; }
                elseif ($sn == "on") { $t_s_que .= " name like ? "; $t_s_params[] = $kw_like; }
            } else {
                if ($sn == "on" && $t_s_que) { $t_s_que .= " or name = ? "; $t_s_params[] = $keyword; }
                elseif ($sn == "on") { $t_s_que .= " name = ? "; $t_s_params[] = $keyword; }
            }
            if ($ss == "on" && $t_s_que) { $t_s_que .= " or subject like ? "; $t_s_params[] = $kw_like; } elseif ($ss == "on") { $t_s_que .= " subject like ? "; $t_s_params[] = $kw_like; }
            if ($sc == "on" && $t_s_que) { $t_s_que .= " or memo like ? "; $t_s_params[] = $kw_like; } elseif ($sc == "on") { $t_s_que .= " memo like ? "; $t_s_params[] = $kw_like; }
            if ($s_que) $s_que .= " and ( " . $t_s_que . " ) ";
            else $s_que .= " ( " . $t_s_que . " ) ";
            $s_params = array_merge($s_params, $t_s_params); // 카테고리 파라미터 뒤에 키워드 파라미터 순서대로 이어붙임
        }

        // 검색 조건이 있을때 앞에 where 문 추가
        if ($s_que) $s_que = " where " . $s_que;

        // 전체개수를 구함 : 검색어가 있을때는 따로 전체 개수를 구함, 아니면 게시판에 있는것으로
        if ($s_que) {
            // 카테고리만 있을 경우
            if (!$keyword && $setup['use_showreply']) {
                $total = (int) ($_category_data[$category] ?? 0);

                // 검색어나 답글없음이 체크되어 있을경우
            } else {
                $use_division = true;
                // WHERE 맨 앞에 division 조건 삽입 -> division ? 파라미터를 배열 맨 앞에 unshift (플레이스홀더 순서 일치)
                $s_que = str_replace("where", "where division=? and ", $s_que);
                array_unshift($s_params, $divpage);
                $_dbTimeStart = getmicrotime();
                $total = (int) $connect->value("select count(*) from {$t_board}_{$id} $s_que ", $s_params);
                $_dbTime += getmicrotime() - $_dbTimeStart;
            }
        } else $total = $setup['total_article'];

        // 페이지 관련 변수값 정함
        $page_num = $setup['memo_num'];
        if (!$page) $page = 1; // 만약 $page라는 변수에 값이 없으면 임의로 1 페이지 입력

        $total_page = (int)(($total - 1) / $page_num) + 1; // 전체 페이지 구함

        if ($page > $total_page) $page = $total_page; // 페이지가 전체 페이지보다 크면 페이지 번호 바꿈

        $start_num = ($page - 1) * $page_num; // 페이지 수에 따른 출력시 첫번째가 될 글의 번호 구함
    }


    // 링크 설정
    unset($href);

    $href = "id=$id&page=$page&sn1=" . urlencode($sn1) . "&divpage=$divpage";
    if ($category) $href .= "&category=" . urlencode($category);
    if ($sn) $href .= "&sn=" . urlencode($sn);
    if ($ss) $href .= "&ss=" . urlencode($ss);
    if ($sc) $href .= "&sc=" . urlencode($sc);
    if ($prev_num) $href .= "&prev_num=$prev_num";
    if ($keyword) $href .= "&keyword=" . urlencode($keyword);

    $sort = '';
    if ($select_arrange) $sort .= "&select_arrange=$select_arrange";
    if ($desc) $sort .= "&desc=$desc";

    // 카테고리를 나타나게 하는 변수
    if (!$setup['use_category']) {
        $hide_category_start = "<!--";
        $hide_category_end = "-->";
    }

    // 바구니를 나타나게 하는 변수
    if ($is_admin || $setup['use_cart']) {
        $a_cart = "<a onfocus=blur() href='javascript:reverse()'>";
    } else {
        $hide_cart_start = "<!--";
        $hide_cart_end = "-->";
        $a_cart = "";
    }

    // 모두삭제 버튼
    if ($is_admin) $a_delete_all = "<a onfocus=blur() href='javascript:delete_all()'>"; else $a_delete_all = "<Zeroboard ";

    // 통계버튼
    if ($setup['use_status']) $a_status = "<a onfocus=blur() href=javascript:void(window.open('stat.php?id=$id','status','width=400,height=400,statusbar=no,toolbar=no,resizable=no'))>"; else $a_status = "<Zeroboard ";
    $a_status = "<Zeroboard ";

    // Setup 버튼
    if ($is_admin) $a_setup = "<a onfocus=blur() href='admin_setup.php?exec=view_board&no=$setup[no]&group_no=$setup[group_no]&exec2=modify' target=_blank>"; else $a_setup = "<Zeroboard ";

    // 현재 멤버의 새 쪽지가 있을때 아이콘 변경;;
    if ($member['no']) {
        if ($member['new_memo']) {
            $member_memo_icon = "<img name=memozzz src=$dir/member_memo_on.gif border=0 align=absmiddle>";
            $memo_on_sound = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0' width='0' height='0'><param name=menu value=false><param name=wmode value=transparent><param name=movie value='$dir/memo_on.swf'><param name=quality value=low><param name='LOOP' value='false'><embed src='$dir/memo_on.swf' quality=low pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash' type='application/x-shockwave-flash' width='0' height='0' loop='false' wmode=transparent menu='false'></embed></object>";
        } else $member_memo_icon = "<img src=$dir/member_memo_off.gif border=0 align=absmiddle>";
    } else $member_memo_icon = "";

}


/***************************************************************************
 * 각종 기본 버튼 설정
 **************************************************************************/

// 로그인, 아웃, 회원 정보 수정, 쪽지 메뉴 버튼

$s_url = Request::uri();
if ($id && !preg_match('/' . preg_quote($id, '/') . '/i', $s_url)) {
    if (preg_match('/\?/', $s_url)) $s_url .= "&id=$id";
    else $s_url = $s_url . "?id=$id";
}
$s_url = urlencode($s_url);

if (!$member['no']) {
    $a_login = "<a onfocus=blur() href='" . _ZB_URL . "login.php?$href$sort&s_url=$s_url'>";
    $a_logout = "<Zeroboard ";
    $a_member_modify = "<Zeroboard ";
    $a_member_memo = "<Zeroboard ";
} else {
    $a_login = "<Zeroboard ";
    $a_logout = "<a onfocus=blur() href='" . _ZB_URL . "logout.php?$href$sort&s_url=$s_url'>";
    $a_member_modify = "<a onfocus=blur() href=# onclick=\"window.open('" . _ZB_URL . "member_modify.php?group_no=$member[group_no]','zbMemberModify','width=560,height=590,toolbars=no,resizable=yes,scrollbars=yes')\">";
    $a_member_memo = "<a onfocus=blur() href=\"javascript:void(window.open('" . _ZB_URL . "member_memo.php','member_memo','width=450,height=500,status=no,toolbar=no,resizable=yes,scrollbars=yes'))\">";
}


// 회원가입버튼;;
if (!$member['no'] && $group['use_join']) $a_member_join = "<a onfocus=blur() href=# onclick=\"window.open('" . _ZB_URL . "member_join.php?group_no=$setup[group_no]','zbMemberJoin','width=560,height=590,toolbars=no,resizable=yes,scrollbars=yes')\">";
else $a_member_join = "<Zeroboard ";

?>
