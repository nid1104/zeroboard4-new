<?php
if (!defined('_ZB_PATH')) exit();

$name = Request::req('name');
$del_icon = Request::req('del_icon');
$is_open = Request::req('is_open');
$use_join = Request::req('use_join');
$join_return_url = Request::req('join_return_url');
$use_icon = Request::req('use_icon');
$member_move = Request::req('member_move');
$board_move = Request::req('board_move');
$no = Request::req('no');
$icon = $icon_name = $icon_type = '';
$icon_size = 0;
$icon_sql = '';

function del_member($no) {
    global $group_no, $member_table, $get_memo_table,  $send_memo_table,$admin_table, $t_board, $t_comment, $connect, $group_table, $member;

    if (!isnum($no)) return;

    $member_data = $connect->row("select * from $member_table where no = ?", [$no]);
    if ($member['is_admin'] > 1 && $member['no'] != ($member_data['no'] ?? '') && ($member_data['level'] ?? 99) <= $member['level'] && ($member_data['is_admin'] ?? 9) <= $member['is_admin']) error("선택하신 회원의 정보를 변경할 권한이 없습니다");

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


// 그룹추가
if ($exec == "add_group_ok") {
    if ($member['is_admin'] > 1) Error("그룹생성 권한이 없습니다");
    if (isblank($name = Request::req('name'))) Error("그룹이름은 필수로 지정하셔야 합니다");
    // 중복 이름 검사
    $check = $connect->value("SELECT COUNT(*) FROM {$group_table} WHERE name = ?", [$name]);
    if ($check) Error("$name 이라는 이름의 그룹이 이미 있습니다");

    if ($_uf = Request::file('icon')) {
        $icon = $_uf->tmpName();
        $icon_name = $_uf->name();
        $icon_type = $_uf->type();
        $icon_size = $_uf->size();
    }

    // 아이콘 파일 업로드시
    if ($icon_name) {
        if (!preg_match('/\.(gif|jpe?g)$/i', $icon_name)) Error("아이콘은 gif 또는 jpg 파일을 올려주세요");
        $size = getimagesize($icon);
        if ($size === false) Error("유효하지 않은 아이콘입니다");
        if ($size[0] > 24 || $size[1] > 24) Error("아이콘의 크기는 24*24이하여야 합니다");
        $kind = array("", "gif", "jpg");
        $n = $size[2];
        $icon_name = "group_" . bin2hex(random_bytes(16)) . '.' . $kind[$n];
        @copy($icon, "icon/" . $icon_name);
    }

    // 헤더푸터
    $header = Request::req('header');
    $header_url = Request::req('header_url');
    $footer = Request::req('footer');
    $footer_url = Request::req('footer_url');
    $is_open = Request::req('is_open');
    $use_join = Request::req('use_join');
    $join_return_url = Request::req('join_return_url');
    $use_icon = Request::req('use_icon');

    if ($header_url !== '' && !isValidIncludePath($header_url)) {
        error('게시판 상단에 불러올 파일의 경로가 올바르지 않습니다.');
    }
    if ($footer_url !== '' && !isValidIncludePath($footer_url)) {
        error('게시판 하단에 불러올 파일의 경로가 올바르지 않습니다.');
    }

    //DB에 입력
    $connect->exec("INSERT INTO {$group_table} (name, is_open, icon,use_join, join_return_url, use_icon, header, footer, header_url, footer_url)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$name, $is_open, $icon_name, $use_join, $join_return_url, $use_icon, $header, $footer, $header_url, $footer_url]);
    $group_no = $connect->insertId();
    Response::redirect(Request::scriptName() . "?exec=view_group&group_no=$group_no");
}
// 그룹수정 완료
elseif ($exec == "modify_group_ok") {
    if ($member['is_admin'] > 2) Error("그룹수정 권한이 없습니다");
    if ($member['is_admin'] == 2 && $member['group_no'] != $group_no) Error("그룹수정 권한이 없습니다");
    if (isblank($name)) Error("그룹이름은 필수로 지정하셔야 합니다");
    $icon_params = array();
    if ($del_icon) $icon_sql = ",icon=''";
    // 아이콘 파일 업로드시
    if ($_uf = Request::file('icon')) {
        $icon = $_uf->tmpName();
        $icon_name = $_uf->name();
        $icon_type = $_uf->type();
        $icon_size = $_uf->size();
    }

    if ($icon_name) {
        if (!preg_match('/\.(gif|jpe?g)$/i', $icon_name)) Error("아이콘은 gif 또는 jpg 파일을 올려주세요");
        $size = getimagesize($icon);
        if ($size === false) Error("유효하지 않은 아이콘입니다");
        if ($size[0] > 24 || $size[1] > 24) Error("아이콘의 크기는 24*24이하여야 합니다");
        $kind = array("", "gif", "jpg");
        $n = $size[2];
        $icon_name = "group_" . bin2hex(random_bytes(16)) . '.' . $kind[$n];
        @copy($icon, "icon/" . $icon_name);
        $icon_sql = ",icon=?";
        $icon_params = array($icon_name);
    }
    // 헤더푸터
    $header = Request::req('header');
    $header_url = Request::req('header_url');
    $footer = Request::req('footer');
    $footer_url = Request::req('footer_url');
    $use_hobby = Request::req('use_hobby');

    if ($header_url !== '' && !isValidIncludePath($header_url)) {
        error('게시판 상단에 불러올 파일의 경로가 올바르지 않습니다.');
    }
    if ($footer_url !== '' && !isValidIncludePath($footer_url)) {
        error('게시판 하단에 불러올 파일의 경로가 올바르지 않습니다.');
    }

    //DB에 입력
    $connect->exec("update $group_table set
						use_hobby=?,name=?,is_open=? $icon_sql ,use_join=?,join_return_url=?,use_icon=?,
						header=?,footer=?,footer_url=?,header_url=?
						where no=?", array_merge([$use_hobby, $name, $is_open], $icon_params, [$use_join, $join_return_url, $use_icon, $header, $footer, $footer_url, $header_url, $group_no]));
    Response::redirect(Request::scriptName() . "?exec=view_group&group_no=$group_no&exec=modify_group");
}
// 그룹삭제 완료
elseif ($exec == "del_group_ok") {
    if ($member['is_admin'] > 1) Error("그룹삭제 권한이 없습니다");
    // 삭제할 그룹의 회원수와 게시판 수를 구함
    $num = $connect->row("select member_num, board_num from $group_table where no=?", [$group_no]);
    $num += array('member_num' => 0, 'board_num' => 0);

    // 멤버 이동
    if ($member_move) {
        $connect->exec("update $member_table set group_no=? where group_no=?", [$member_move, $group_no]);
        $connect->exec("update $group_table set member_num=member_num+? where no=?", [$num['member_num'], $member_move]);
    } else {
        $result = $connect->all("select no from $member_table where group_no=?", [$group_no]);
        foreach ($result as $data) {
            $no = $data['no'];
            del_member($no);
        }
    }

    // 게시판이동
    if ($board_move) {
        $connect->exec("update $admin_table set group_no=? where group_no=?", [$board_move, $group_no]);
        $connect->exec("update $group_table set board_num=board_num+? where no=?", [$num['board_num'], $board_move]);
    } else {
        $temp = $connect->all("select name from $admin_table where group_no=?", [$group_no]);
        foreach ($temp as $board_row) {
            $table_name = $board_row['name'];
            if (!isalNum($table_name)) continue;
            $tmpData = $connect->all("select file_name1, file_name2 from {$t_board}_{$table_name}");
            foreach ($tmpData as $data) {
                if ($data['file_name1']) @z_unlink("./" . $data['file_name1']);
                if ($data['file_name2']) @z_unlink("./" . $data['file_name2']);
            }
            if (is_dir("./data/" . $table_name)) zRmDir("./data/" . $table_name);
            $connect->exec("delete from $admin_table where name=?", [$table_name]);
            $connect->exec("drop table {$t_board}_{$table_name}");
            $connect->exec("drop table {$t_division}_{$table_name}");
            $connect->exec("drop table {$t_comment}_{$table_name}");
            $connect->exec("drop table {$t_category}_{$table_name}");
            $connect->exec("update $group_table set board_num=board_num-1 where no=?", [$group_no]);
        }
        $connect->exec("delete from $admin_table where group_no=?", [$group_no]);
    }

    // 그룹삭제
    $connect->exec("delete from $group_table where no=?", [$group_no]);

    Response::redirect(Request::scriptName());
}
// 가입양식 변경
elseif ($exec == "modify_member_join_ok") {
    if ($member['is_admin'] > 2) Error("가입양식 변경 권한이 없습니다");
    if ($member['is_admin'] == 2 && $member['group_no'] != $group_no) Error("가입양식 변경 권한이 없습니다");
    $join_level = Request::req('join_level');
    $use_icq = Request::req('use_icq');
    $use_aol = Request::req('use_aol');
    $use_msn = Request::req('use_msn');
    $use_jumin = Request::req('use_jumin');
    $use_comment = Request::req('use_comment');
    $use_job = Request::req('use_job');
    $use_hobby = Request::req('use_hobby');
    $use_home_address = Request::req('use_home_address');
    $use_home_tel = Request::req('use_home_tel');
    $use_office_address = Request::req('use_office_address');
    $use_office_tel = Request::req('use_office_tel');
    $use_handphone = Request::req('use_handphone');
    $use_mailing = Request::req('use_mailing');
    $use_birth = Request::req('use_birth');
    $use_picture = Request::req('use_picture');

    $connect->exec(
        "update $group_table set join_level=?,use_icq=?,use_aol=?,use_msn=?,
		use_jumin=?,use_comment=?,use_job=?,use_hobby=?,
		use_home_address=?,use_home_tel=?,use_office_address=?,
		use_office_tel=?,use_handphone=?,use_mailing=?,
		use_birth=?,use_picture=? where no=?",
        [$join_level, $use_icq, $use_aol, $use_msn, $use_jumin, $use_comment, $use_job, $use_hobby,
            $use_home_address, $use_home_tel, $use_office_address, $use_office_tel, $use_handphone, $use_mailing,
            $use_birth, $use_picture, $group_no]
    );
    Response::redirect(Request::scriptName() . "?exec=modify_member_join&group_no=$group_no");
}

?>
