<?php
if (!defined('_ZB_PATH')) exit();

/***************************************************************************
 * 게시판 기능 설정 실행
 **************************************************************************/

$no = (int) Request::req('no');
$name = Request::req('name');
$skinname = Request::req('skinname');
$header = Request::req('header');
$footer = Request::req('footer');
$header_url = Request::req('header_url');
$footer_url = Request::req('footer_url');
$bg_image = Request::req('bg_image');
$bg_color = Request::req('bg_color');
$table_width = Request::req('table_width');
$memo_num = Request::req('memo_num');
$cut_length = Request::req('cut_length');
$max_upload_size = Request::req('max_upload_size');
$title = Request::req('title');
$pds_ext1 = Request::req('pds_ext1');
$pds_ext2 = Request::req('pds_ext2');
$filter = Request::req('filter');
$avoid_tag = Request::req('avoid_tag');
$avoid_ip = Request::req('avoid_ip');
$only_board = Request::req('only_board');
$use_category = Request::req('use_category');
$use_html = Request::req('use_html');
$use_filter = Request::req('use_filter');
$use_status = Request::req('use_status');
$use_pds = Request::req('use_pds');
$use_homelink = Request::req('use_homelink');
$use_filelink = Request::req('use_filelink');
$use_cart = Request::req('use_cart');
$use_autolink = Request::req('use_autolink');
$use_showip = Request::req('use_showip');
$use_comment = Request::req('use_comment');
$use_formmail = Request::req('use_formmail');
$use_showreply = Request::req('use_showreply');
$use_secret = Request::req('use_secret');
$use_alllist = Request::req('use_alllist');
$applyall_filter = Request::req('applyall_filter');
$applyall_tag = Request::req('applyall_tag');
$applyall_ip = Request::req('applyall_ip');
$s_page_num = Request::req('s_page_num');
$category_no = Request::req('category_no');
$movename = Request::req('movename');
$c = Request::postArr('c');
if (!$c) $c = Request::getArr('c');
if ($name !== '') $name = strtolower($name);

// allowed_boards: 이 관리자가 편집 가능한 게시판 번호 목록
if ($member['is_admin'] == '1') {
    $allowed_boards = array_column($connect->all("SELECT no FROM {$admin_table}"), 'no');
} elseif ($member['is_admin'] == '2') {
    $allowed_boards = array_column($connect->all("SELECT no FROM {$admin_table} WHERE group_no = ?", [$member['group_no']]), 'no');
} else {
    // 게시판 관리자: board_name 은 "1,2,3," 형태 — 빈 원소 제거
    $allowed_boards = array_filter(array_map('trim', explode(',', $member['board_name'] ?? '')), 'strlen');
}
$allowed_boards = array_map('strval', $allowed_boards);

// 자신이 관리하는 게시판만 대상 가능
$_board_actions = ['modify_ok', 'del', 'category_add', 'del_category', 'category_modify_ok', 'category_move', 'modify_grant_ok'];
if (in_array($exec2, $_board_actions, true) && !in_array((string)$no, $allowed_boards, true)) {
    error('해당 게시판에 대한 권한이 없습니다');
}

// 게시판 수정
if ($exec2 == "modify_ok") {
    if (Request::method() !== 'POST') error('잘못된 접근입니다.');

    // 입력된 테이블 값이 빈값인지, 한글이 들어갔는지를 검사
    if (isBlank($name)) Error("게시판 이름을 입력하셔야 합니다", "");
    if (!isAlNum($name)) Error("게시판 이름은 영문과 숫자로만 하셔야 합니다", "");
    if (strtolower($name) == '__zbsessiontmp') error('사용하실 수 없는 게시판 이름입니다.');

    if ($skinname !== '' && (!isAlNum($skinname) || !is_dir(__DIR__ . '/../skin/' . $skinname))) Error('유효하지 않은 스킨입니다');

    $temp = $connect->value("select count(*) from $admin_table where no=?", [$no]);
    if ($temp == 0) Error("게시판 정보를 찾을 수 없습니다");

    $ba_image = $bg_image;
    $pds_ext1 = str_replace(" ", "", $pds_ext1);
    $pds_ext2 = str_replace(" ", "", $pds_ext2);

    if ($header_url !== '' && !isValidIncludePath($header_url)) {
        error('게시판 상단에 불러올 파일의 경로가 올바르지 않습니다.');
    }
    if ($footer_url !== '' && !isValidIncludePath($footer_url)) {
        error('게시판 하단에 불러올 파일의 경로가 올바르지 않습니다.');
    }

    $connect->exec(
        "update $admin_table set
				only_board=?,skinname=?,header=?,footer=?,header_url=?,footer_url=?,
				bg_image=?,bg_color=?,table_width=?,memo_num=?, page_num=?, cut_length=?, use_category=?, use_html=?,max_upload_size=?,
				use_filter=?,use_status=?,use_pds=?,use_homelink=?,
				title=?,pds_ext1=?,pds_ext2=?,
				use_filelink=?,use_cart=?,use_autolink=?,use_showip=?,
				use_comment=?,use_formmail=?,use_showreply=?, use_secret=?, filter=?, avoid_tag=?, avoid_ip=?, use_alllist=? where no=?",
        [$only_board, $skinname, $header, $footer, $header_url, $footer_url,
            $bg_image, $bg_color, $table_width, $memo_num, $page_num, $cut_length, $use_category, $use_html, $max_upload_size,
            $use_filter, $use_status, $use_pds, $use_homelink,
            $title, $pds_ext1, $pds_ext2,
            $use_filelink, $use_cart, $use_autolink, $use_showip,
            $use_comment, $use_formmail, $use_showreply, $use_secret, $filter, $avoid_tag, $avoid_ip, $use_alllist, $no]
    );

    // '모든 게시판에 적용' 권한 체크
    if ($member['is_admin'] == '1') {
        if ($applyall_filter) $connect->exec("update $admin_table set filter=?", [$filter]);
        if ($applyall_tag) $connect->exec("update $admin_table set avoid_tag=?", [$avoid_tag]);
        if ($applyall_ip) $connect->exec("update $admin_table set avoid_ip=?", [$avoid_ip]);
    } elseif ($member['is_admin'] == '2') {
        if ($applyall_filter) $connect->exec("update $admin_table set filter=? where group_no=?", [$filter, $member['group_no']]);
        if ($applyall_tag) $connect->exec("update $admin_table set avoid_tag=? where group_no=?", [$avoid_tag, $member['group_no']]);
        if ($applyall_ip) $connect->exec("update $admin_table set avoid_ip=? where group_no=?", [$avoid_ip, $member['group_no']]);
    }

    Response::redirect(Request::scriptName() . "?group_no=$group_no&exec=view_board&no=$no&exec2=modify&page=$page&page_num=$s_page_num");
}

// 게시판 추가
elseif ($exec2 == "add_ok") {
    if (Request::method() !== 'POST') error('잘못된 접근입니다.');

    if (!in_array($member['is_admin'], ['1', '2'])
    	|| $member['is_admin'] == 2 && $group_no != $member['group_no']) error('게시판을 추가하실 권한이 없습니다');

    // 입력된 테이블 값이 빈값인지, 한글이 들어갔는지를 검사
    if (isBlank($name)) Error("게시판 이름을 입력하셔야 합니다", "");
    if (!isAlNum($name)) Error("게시판 이름은 영문과 숫자로만 하셔야 합니다", "");
    if (strtolower($name) == '__zbsessiontmp') error('사용하실 수 없는 게시판 이름입니다.');

    if ($skinname !== '' && (!isAlNum($skinname) || !is_dir(__DIR__ . '/../skin/' . $skinname))) Error('유효하지 않은 스킨입니다');


    // 같은 이름의 게시판이 이미 생성되었는지를 검사
    $temp = $connect->value("select count(*) from $admin_table where name=?", [$name]);
    if ($temp > 0) Error("이미 등록되어 있는 게시판입니다.<br>다른 이름으로 생성하십시오", "");

    $ba_image = $bg_image;
    $pds_ext1 = str_replace(" ", "", $pds_ext1);
    $pds_ext2 = str_replace(" ", "", $pds_ext2);

    if ($header_url !== '' && !isValidIncludePath($header_url)) {
        error('게시판 상단에 불러올 파일의 경로가 올바르지 않습니다.');
    }
    if ($footer_url !== '' && !isValidIncludePath($footer_url)) {
        error('게시판 하단에 불러올 파일의 경로가 올바르지 않습니다.');
    }

    // 관리자 테이블 생성
    $connect->exec(
        "insert into $admin_table
					(group_no,name,skinname,header,footer,header_url,footer_url,bg_image,bg_color,table_width,
					memo_num,page_num,cut_length,use_category,use_html,use_filter,use_status,use_pds,use_homelink,
					use_filelink,use_cart,use_autolink,use_showip,use_comment,use_formmail,use_showreply,use_secret,filter,avoid_tag, avoid_ip, use_alllist, max_upload_size,title,pds_ext1,pds_ext2,only_board)
				values
					(?,?,?,?,?,?,?,?,?,?,
					?,?,?,?,?,?,?,?,?,
					?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [$group_no, $name, $skinname, $header, $footer, $header_url, $footer_url, $bg_image, $bg_color, $table_width,
            $memo_num, $page_num, $cut_length, $use_category, $use_html, $use_filter, $use_status, $use_pds, $use_homelink,
            $use_filelink, $use_cart, $use_autolink, $use_showip, $use_comment, $use_formmail, $use_showreply, $use_secret, $filter, $avoid_tag, $avoid_ip, $use_alllist, $max_upload_size, $title, $pds_ext1, $pds_ext2, $only_board]
    );

    $table_name = $name;

    require_once __DIR__ . '/../schema.sql';

    // 게시판 본체 테이블 생성
    $connect->exec($board_table_main_schema);

    // Division 테이블 생성
    $connect->exec($division_table_schema);
    $connect->exec("insert into {$t_division}_{$table_name} (division,num) values ('1','0')");

    // 코멘트 테이블 생성
    $connect->exec($board_comment_schema);

    // 카테고리 테이블 생성
    $connect->exec($board_category_table);

    // 기본 카테고리 필드 입력
    $connect->exec("insert into {$t_category}_{$table_name} (num, name) values ('0','일반')");
    $connect->exec("insert into {$t_category}_{$table_name} (num, name) values ('0','질문')");
    $connect->exec("insert into {$t_category}_{$table_name} (num, name) values ('0','답변')");

    $connect->exec("update $group_table set board_num=board_num+1 where no=?", [$group_no]);

    Response::redirect(Request::scriptName() . "?exec=view_board&group_no=$group_no&page=$page&page_num=$page_num");
}

// 게시판 삭제
elseif ($exec2 == "del") {
    $data = $connect->row("select name from $admin_table where no=?", [$no]);

    $table_name = $data['name'] ?? '';
    if (!isalNum($table_name) || $table_name === '') Error("게시판 정보를 찾을 수 없습니다");

    $tmpData = $connect->all("select file_name1, file_name2 from {$t_board}_{$table_name}");
    foreach ($tmpData as $data) {
        if ($data['file_name1']) @z_unlink("./" . $data['file_name1']);
        if ($data['file_name2']) @z_unlink("./" . $data['file_name2']);
    }

    if (is_dir("./data/" . $table_name)) zRmDir("./data/" . $table_name);

    $connect->exec("delete from $admin_table where no=?", [$no]);
    $connect->exec("drop table {$t_board}_{$table_name}");
    $connect->exec("drop table {$t_division}_{$table_name}");
    $connect->exec("drop table {$t_comment}_{$table_name}");
    $connect->exec("drop table {$t_category}_{$table_name}");

    $connect->exec("update $group_table set board_num=board_num-1 where no=?", [$group_no]);

    Response::redirect(Request::scriptName() . "?exec=view_board&group_no=$group_no&page=$page&page_num=$page_num");
}

// 카테고리 부분
if ($exec2 == "category_add") {
    if (Request::method() !== 'POST') error('잘못된 접근입니다.');

    if (!$name) error("생성할 카테고리 이름을 입력하여 주십시요");
    $table_data = $connect->row("select name from $admin_table where no=?", [$no]);
    if (!isalNum($table_data['name'] ?? '')) Error("게시판 정보를 찾을 수 없습니다");
    $check = $connect->value("select count(*) from {$t_category}_{$table_data['name']} where name=?", [$name]);
    if ($check > 0) Error("동일한 이름의 카테고리가 있습니다");
    $connect->exec("insert into {$t_category}_{$table_data['name']} (name) values (?)", [$name]);
    Response::redirect(Request::scriptName() . "?exec=view_board&exec2=category&no=$no&page=$page&page_num=$page_num&group_no=$group_no");
} elseif ($exec2 == "del_category") {
    $table_data = $connect->row("select name from $admin_table where no=?", [$no]);
    if (!isalNum($table_data['name'] ?? '')) Error("게시판 정보를 찾을 수 없습니다");
    $connect->exec("delete from {$t_category}_{$table_data['name']} where no=?", [$category_no]);
    Response::redirect(Request::scriptName() . "?exec=view_board&exec2=category&no=$no&page=$page&page_num=$page_num&group_no=$group_no");
} elseif ($exec2 == "category_modify_ok") {
    if (!$name) error("수정할 카테고리 이름을 입력하여 주십시요");
    $table_data = $connect->row("select name from $admin_table where no=?", [$no]);
    if (!isalNum($table_data['name'] ?? '')) Error("게시판 정보를 찾을 수 없습니다");
    $connect->exec("update {$t_category}_{$table_data['name']} set name=? where no=?", [$name, $category_no]);

    Response::redirect(Request::scriptName() . "?exec=view_board&exec2=category&no=$no&page=$page&page_num=$page_num&group_no=$group_no");
}

// 카테고리 내용 이동
elseif ($exec2 == "category_move") {
    if (Request::method() !== 'POST') error('잘못된 접근입니다.');

    $table_data = $connect->row("select name from $admin_table where no=?", [$no]);
    if (!isalNum($table_data['name'] ?? '')) Error("게시판 정보를 찾을 수 없습니다");
    for ($i = 0; $i < count($c); $i++) {
        $connect->exec("update {$t_board}_{$table_data['name']} set category=? where category=?", [$movename, $c[$i]]);
    }

    $result = $connect->all("select * from {$t_category}_{$table_data['name']}");
    foreach ($result as $data) {
        $num = $connect->value("select count(*) from {$t_board}_{$table_data['name']} where category=?", [$data['no']]);
        $connect->exec("update {$t_category}_{$table_data['name']} set num=? where no = ?", [$num, $data['no']]);
    }

    Response::redirect(Request::scriptName() . "?exec=view_board&exec2=category&no=$no&page=$page&page_num=$page_num&group_no=$group_no");
}

// 권한 설정
elseif ($exec2 == "modify_grant_ok") {
    if (Request::method() !== 'POST') error('잘못된 접근입니다.');

    $temp = $connect->value("select count(*) from $admin_table where no=?", [$no]);
    if ($temp == 0) Error("게시판 정보를 찾을 수 없습니다");

    $grant_html = Request::req('grant_html');
    $grant_list = Request::req('grant_list');
    $grant_view = Request::req('grant_view');
    $grant_comment = Request::req('grant_comment');
    $grant_write = Request::req('grant_write');
    $grant_reply = Request::req('grant_reply');
    $grant_delete = Request::req('grant_delete');
    $grant_notice = Request::req('grant_notice');
    $grant_view_secret = Request::req('grant_view_secret');
    $grant_imagebox = Request::req('grant_imagebox');
    $connect->exec(
        "update $admin_table set grant_html=?, grant_list=?,
				grant_view=?, grant_comment=?, grant_write=?,
				grant_reply=?, grant_delete=?, grant_notice=?,
				grant_view_secret=?, use_showip = ? where no=?",
        [$grant_html, $grant_list, $grant_view, $grant_comment, $grant_write,
            $grant_reply, $grant_delete, $grant_notice, $grant_view_secret, $grant_imagebox, $no]
    );
    Response::redirect(Request::scriptName() . "?exec=view_board&exec=view_board&exec2=grant&no=$no&page=$page&page_num=$page_num&group_no=$group_no");
}
?>
