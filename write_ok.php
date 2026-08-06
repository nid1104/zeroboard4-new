<?php
$del_que1 = $del_que2 = '';

/***************************************************************************
 * 공통 파일 include
 **************************************************************************/
require_once __DIR__ . '/_head.php';

/***************************************************************************
 * 요청 변수 추출
 **************************************************************************/
$mode = Request::post('mode');
$no = Request::post('no');
$name = Request::post('name');
$password = Request::post('password');
$email = Request::post('email');
$homepage = Request::post('homepage');
$subject = Request::post('subject');
$memo = Request::post('memo');
$category = Request::post('category');
$use_html = Request::post('use_html');
$reply_mail = Request::post('reply_mail');
$is_secret = Request::post('is_secret');
$notice = Request::post('notice');
$sitelink1 = Request::post('sitelink1');
$sitelink2 = Request::post('sitelink2');
$del_file1 = Request::post('del_file1');
$del_file2 = Request::post('del_file2');
$zx = Request::post('zx');
$zy = Request::post('zy');
$des = Request::post('des');
$page_num = (int) Request::post('page_num');

$file1 = $file2 = '';
$file1_name = $file2_name = '';
$file1_size = $file2_size = 0;
$file1_type = $file2_type = '';
$file_name1 = $file_name2 = '';
$s_file_name1 = $s_file_name2 = '';

$q_keyword = urlencode($keyword);
$q_category = urlencode($category);
$q_sn = urlencode($sn);
$q_ss = urlencode($ss);
$q_sc = urlencode($sc);

/***************************************************************************
 * 게시판 설정 체크
 **************************************************************************/

// 편법을 이용한 글쓰기 방지
check_csrf();
$referer_hdr = Request::header('Referer');
if ($referer_hdr === '' || stripos($referer_hdr, Request::header('Host')) === false) Error("정상적으로 글을 작성하여 주시기 바랍니다.");
if (Request::method() == 'GET' ) Error("정상적으로 글을 쓰시기 바랍니다", "");
if (!$mode) $mode = "write";

// 사용권한 체크
if ($mode == "reply" && $setup['grant_reply'] < $member['level'] && !$is_admin) Error("사용권한이 없습니다", "login.php?id=$id&page=$page&page_num=$page_num&category=$q_category&sn=$q_sn&ss=$q_ss&sc=$q_sc&keyword=$q_keyword&no=$no&file=zboard.php");
elseif ($setup['grant_write'] < $member['level'] && !$is_admin) Error("사용권한이 없습니다", "login.php?id=$id&page=$page&page_num=$page_num&category=$q_category&sn=$q_sn&ss=$q_ss&sc=$q_sc&keyword=$q_keyword&no=$no&file=zboard.php");

if (!$is_admin && $setup['grant_notice'] < $member['level']) $notice = 0;

// 각종 변수 검사;;
if (!$member['no']) {
    if (isblank($name)) Error("이름을 입력하셔야 합니다");
    if (isblank($password)) Error("비밀번호를 입력하셔야 합니다");
} else {
    $password = $member['password'];
}

$subject = str_replace("ㅤ", "", $subject);
$memo = str_replace("ㅤ", "", $memo);
$name = str_replace("ㅤ", "", $name);

if (isblank($subject)) Error("제목을 입력하셔야 합니다");
if (isblank($memo)) Error("내용을 입력하셔야 합니다");

if (!$category) {
    $category = $connect->value("select min(no) from {$t_category}_{$id}");
}


// 필터링;; 관리자가 아닐때;;
if (!$is_admin && $setup['use_filter']) {
    $filter = explode(",", $setup['filter']);
    $f_memo = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($memo));
    $f_name = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($name));
    $f_subject = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($subject));
    $f_email = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($email));
    $f_homepage = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($homepage));
    for ($i = 0; $i < count($filter); $i++) {
        if (!isblank($filter[$i])) {
            if (stripos($f_memo, $filter[$i]) !== false) Error("<b>$filter[$i]</b> 은(는) 등록하기에 적합한 단어가 아닙니다");
            if (stripos($f_name, $filter[$i]) !== false) Error("<b>$filter[$i]</b> 은(는) 등록하기에 적합한 단어가 아닙니다");
            if (stripos($f_subject, $filter[$i]) !== false) Error("<b>$filter[$i]</b> 은(는) 등록하기에 적합한 단어가 아닙니다");
            if (stripos($f_email, $filter[$i]) !== false) Error("<b>$filter[$i]</b> 은(는) 등록하기에 적합한 단어가 아닙니다");
            if (stripos($f_homepage, $filter[$i]) !== false) Error("<b>$filter[$i]</b> 은(는) 등록하기에 적합한 단어가 아닙니다");
        }
    }
}

//패스워드를 암호화
$password_plain = $password;
if (strlen($password) && !$member['no']) {
    $password = createHash($password);
}

// 관리자이거나 HTML허용레벨이 낮을때 태그의 금지유무를 체크
if (!$is_admin && $setup['grant_html'] < $member['level']) {

    // 내용의 HTML 금지;;
    if (!$use_html || $setup['use_html'] == 0) $memo = del_html($memo);

    // HTML의 부분허용일때;;
    if ($use_html && $setup['use_html'] == 1) {
        $memo = zb_sanitize_html($memo, explode(",", $setup['avoid_tag']));
    }
} else {
    if (!$use_html) {
        $memo = del_html($memo);
    }
}


// 원본글을 가져옴
unset($s_data);
$s_data = $connect->row("select * from {$t_board}_{$id} where no=?", [$no]);

// 원본글을 이용한 비교
if ($mode == "modify" || $mode == "reply") {
    if (empty($s_data['no'])) Error("원본글이 존재하지 않습니다");
}

// 공지글에는 답글이 안 달리게 처리
if ($mode == "reply" && ($s_data['headnum'] ?? 0) <= -2000000000) Error("공지글에는 답글을 달수 없습니다");


// 회원등록이 되어 있을때 이름등을 가져옴;;
if ($member['no']) {
    if ($mode == "modify" && $member['no'] != $s_data['ismember']) {
        $name = $s_data['name'];
        $email = $s_data['email'];
        $homepage = $s_data['homepage'];
    } else {
        $name = $member['name'];
        $email = $member['email'];
        $homepage = $member['homepage'];
    }
}

// 내용 공백/탭 처리
if ($use_html < 2) {
    $memo = str_replace("  ", "&nbsp;&nbsp;", $memo);
    $memo = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $memo);
}

// 홈페이지 주소의 경우 http:// 가 없으면 붙임
if ((stripos($homepage, "http://") !== 0 && stripos($homepage, "https://") !== 0) && $homepage) $homepage = "http://" . $homepage;

// 각종 변수 설정
$ip = Request::clientIp(); // 아이피값 구함;;
$reg_date = time(); // 현재의 시간구함;;

$x = $zx;
$y = $zy;

// 도배인지 아닌지 검사;; 우선 같은 아이피대에 30초이내의 글은 도배로 간주;;
if (!$is_admin && $mode != "modify") {
    $max_no = $connect->value("select max(no) from {$t_board}_{$id}");
    $temp = $connect->value("select count(*) from {$t_board}_{$id} where ip=? and $reg_date - reg_date <30 and no=?", [$ip, $max_no]);
    if ($temp > 0) {Error("글등록은 30초이상이 지나야 가능합니다"); exit; }
}

// 같은 내용이 있는지 검사;;
if (!$is_admin && $mode != "modify") {
    $max_no = $connect->value("select max(no) from {$t_board}_{$id}");
    $temp = $connect->value("select count(*) from {$t_board}_{$id} where memo=? and no=?", [$memo, $max_no]);
    if ($temp > 0) {Error("같은 내용의 글은 등록할수가 없습니다"); exit; }
}


// 쿠키 설정;;
if ($mode != "modify") {
    if ($name) Session::set('zb_writer_name', $name);
    if ($email) Session::set('zb_writer_email', $email);
    if ($homepage) Session::set('zb_writer_homepage', $homepage);
}


/***************************************************************************
 * 업로드가 있을때
 **************************************************************************/
$image_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp');

$uf1 = Request::file('file1');
if ($uf1) {
    $file1 = $uf1->tmpName();
    $file1_name = $uf1->name();
    $file1_size = $uf1->size();
    $file1_type = $uf1->type();
}
$uf2 = Request::file('file2');
if ($uf2) {
    $file2 = $uf2->tmpName();
    $file2_name = $uf2->name();
    $file2_size = $uf2->size();
    $file2_type = $uf2->type();
}

if ($file1_size > 0 && $setup['use_pds'] && $file1) {

    if (!$uf1->isValid()) Error("정상적인 방법으로 업로드 해주세요");
    if ($file1_name == $file2_name) Error("같은 파일은 등록할수 없습니다");
    $file1_size = filesize($file1);

    if ($setup['max_upload_size'] < $file1_size && !$is_admin) error("첫번째 파일 업로드는 최고 " . GetFileSize($setup['max_upload_size']) . " 까지 가능합니다");

    // 업로드 금지
    if ($file1_size > 0) {
        $s_file_name1 = $file1_name;

        //확장자 검사
        if ($setup['pds_ext1']) {
            $upload_check = strtolower(pathinfo($s_file_name1, PATHINFO_EXTENSION));
            $allowed_ext1 = array_map('trim', explode(',', strtolower($setup['pds_ext1'])));

            if (!in_array($upload_check, $allowed_ext1, true) || $upload_check === '') Error("첫번째 업로드는 $setup[pds_ext1] 확장자만 가능합니다");
        }

        $s_file_name1 = str_replace(" ", "_", $s_file_name1);
        $s_file_name1 = str_replace("-", "_", $s_file_name1);

        // 디렉토리를 검사함
        if (!is_dir("data/" . $id)) {
            @mkdir("data/" . $id, 0777);
            @chmod("data/" . $id, 0706);
            createIndexFile("data/" . $id);
        }


        $s_file_name1_ext = strtolower(pathinfo($s_file_name1, PATHINFO_EXTENSION));

        if (in_array($s_file_name1_ext, $image_ext, true)) {
            $file_name1 = "data/$id/" . bin2hex(random_bytes(16)) . '.' . $s_file_name1_ext;
        } else {
            $file_name1 = "data/$id/" . bin2hex(random_bytes(16));
        }

        if (!$uf1->moveTo($file_name1)) Error("파일업로드가 제대로 되지 않았습니다");
        @chmod($file_name1, 0644);
    }
}

if ($file2_size > 0 && $setup['use_pds'] && $file2) {
    if (!$uf2->isValid()) Error("정상적인 방법으로 업로드 해주세요");
    $file2_size = filesize($file2);
    if ($setup['max_upload_size'] < $file2_size && !$is_admin) error("두번째 파일 업로드는 최고 " . GetFileSize($setup['max_upload_size']) . " 까지 가능합니다");
    if ($file2_size > 0) {
        $s_file_name2 = $file2_name;

        //확장자 검사
        if ($setup['pds_ext2']) {
            $upload_check = strtolower(pathinfo($s_file_name2, PATHINFO_EXTENSION));
            $allowed_ext2 = array_map('trim', explode(',', strtolower($setup['pds_ext2'])));

            if (!in_array($upload_check, $allowed_ext2, true) || $upload_check === '') Error("두번째 업로드는 $setup[pds_ext2] 확장자만 가능합니다");
        }

        $s_file_name2 = str_replace(" ", "_", $s_file_name2);
        $s_file_name2 = str_replace("-", "_", $s_file_name2);

        // 디렉토리를 검사함
        if (!is_dir("data/" . $id)) {
            @mkdir("data/" . $id, 0777);
            @chmod("data/" . $id, 0706);
            createIndexFile("data/" . $id);
        }


        $s_file_name2_ext = strtolower(pathinfo($s_file_name2, PATHINFO_EXTENSION));

        if (in_array($s_file_name2_ext, $image_ext, true)) {
            $file_name2 = "data/$id/" . bin2hex(random_bytes(16)) . '.' . $s_file_name2_ext;
        } else {
            $file_name2 = "data/$id/" . bin2hex(random_bytes(16));
        }

        if (!$uf2->moveTo($file_name2)) Error("파일업로드가 제대로 되지 않았습니다");
        @chmod($file_name2, 0644);
    }
}


/***************************************************************************
 * 수정글일때
 **************************************************************************/
if ($mode == "modify" && $no) {

    if ($s_data['ismember']) {
        if (!$is_admin && $member['level'] > $setup['grant_delete'] && $s_data['ismember'] != $member['no']) Error("정상적인 방법으로 수정하세요");
    }

    // 비밀번호 검사;;
    if ($s_data['ismember'] != $member['no'] && !$is_admin) {
        if (!verifyHash($password_plain, $s_data['password'])) Error("비밀번호가 틀렸습니다");
    }

    // 파일삭제
    if ($del_file1 == 1) {@z_unlink("./" . $s_data['file_name1']); $del_que1 = ",file_name1='',s_file_name1=''"; }
    if ($del_file2 == 1) {@z_unlink("./" . $s_data['file_name2']); $del_que2 = ",file_name2='',s_file_name2=''"; }

    $del_params = array();
    if ($file_name1) {$del_que1 = ",file_name1=?,s_file_name1=?"; $del_params[] = $file_name1; $del_params[] = $s_file_name1; }
    if ($file_name2) {$del_que2 = ",file_name2=?,s_file_name2=?"; $del_params[] = $file_name2; $del_params[] = $s_file_name2; }

    // 공지 -> 일반글
    if (!$notice && $s_data['headnum'] <= "-2000000000") {
        $max_division = $connect->value("select max(division) from {$t_division}_{$id}");
        $temp = $connect->value("select max(division) from {$t_division}_{$id} where num>0 and division!=?", [$max_division]);
        if (!$temp) $second_division = 0; else $second_division = $temp;

        // 헤드넘+1 한값을 가짐;;
        $max_headnum = $connect->value("select min(headnum) from {$t_board}_{$id} where (division=? or division=?) and headnum>-2000000000", [$max_division, $second_division]); // 공지가 아닌 최소 headnum 구함
        $headnum = (int)$max_headnum - 1;

        $next_data = $connect->row("select no,headnum,division from {$t_board}_{$id} where (division=? or division=?) and headnum=? and arrangenum='0'", [$max_division, $second_division, $max_headnum]); // 다음글을 구함;;
        $next_no = !empty($next_data['no']) ? $next_data['no'] : "0";

        if (empty($next_data['division'])) $division = 1; else $division = $next_data['division'];

        $prev_data = $connect->value("select no from {$t_board}_{$id} where (division=? or division=?) and headnum<? and no!=? order by headnum desc limit 1", [$max_division, $second_division, $headnum, $no]); // 이전글을 구함;;
        if ($prev_data) $prev_no = $prev_data; else $prev_no = 0;

        $child = "0";
        $depth = "0";
        $arrangenum = "0";
        $father = "0";
        minus_division($s_data['division']);
        $connect->exec(
            "update {$t_board}_{$id} set headnum=?,prev_no=?,next_no=?,child=?,depth=?,arrangenum=?,father=?,name=?,email=?,homepage=?,subject=?,memo=?,sitelink1=?,sitelink2=?,use_html=?,reply_mail=?,is_secret=?,category=? $del_que1 $del_que2 where no=?",
            array_merge([$headnum, $prev_no, $next_no, $child, $depth, $arrangenum, $father, $name, $email, $homepage, $subject, $memo, $sitelink1, $sitelink2, $use_html, $reply_mail, $is_secret, $category], $del_params, [$no])
        );
        plus_division($division);

        // 다음글의 이전글을 수정
        if ($next_no)$connect->exec("update {$t_board}_{$id} set prev_no=? where division=? and headnum=?", [$no, $next_data['division'] ?? '', $next_data['headnum'] ?? '']);

        // 이전글의 다음글을 수정
        if ($prev_no)$connect->exec("update {$t_board}_{$id} set next_no=? where no=?", [$no, $prev_no]);

        $connect->exec("update {$t_board}_{$id} set prev_no=0 where (division=? or division=?) and prev_no=? and headnum!=?", [$max_division, $second_division, $s_data['no'], $next_data['headnum'] ?? '']);
        $connect->exec("update {$t_category}_{$id} set num=num-1 where no=?", [$s_data['category']]);
        $connect->exec("update {$t_category}_{$id} set num=num+1 where no=?", [$category]);
    }

    // 일반글 -> 공지
    elseif ($notice && $s_data['headnum'] > -2000000000) {
        $max_division = $connect->value("select max(division) from {$t_division}_{$id}");
        $temp = $connect->value("select max(division) from {$t_division}_{$id} where num>0 and division!=?", [$max_division]);
        if (!$temp) $second_division = 0; else $second_division = $temp;

        $max_headnum = $connect->value("select min(headnum) from {$t_board}_{$id} where division=? or division=?", [$max_division, $second_division]);  // 최고글을 구함;;
        $headnum = (int)$max_headnum - 1;
        if ($headnum > -2000000000) $headnum = -2000000000; // 최고 headnum이 공지가 아니면 현재 글에 공지를 넣음;

        $next_data = $connect->row("select no,headnum,division from {$t_board}_{$id} where (division=? or division=?) and headnum=? and arrangenum='0'", [$max_division, $second_division, $max_headnum]);
        $next_no = !empty($next_data['no']) ? $next_data['no'] : "0";
        $prev_no = 0;
        $child = "0";
        $depth = "0";
        $arrangenum = "0";
        $father = "0";
        minus_division($s_data['division']);
        $division = add_division();
        $connect->exec(
            "update {$t_board}_{$id} set division=?,headnum=?,prev_no=?,next_no=?,child=?,depth=?,arrangenum=?,father=?,name=?,email=?,homepage=?,subject=?,memo=?,sitelink1=?,sitelink2=?,use_html=?,reply_mail=?,is_secret=?,category=? $del_que1 $del_que2 where no=?",
            array_merge([$division, $headnum, $prev_no, $next_no, $child, $depth, $arrangenum, $father, $name, $email, $homepage, $subject, $memo, $sitelink1, $sitelink2, $use_html, $reply_mail, $is_secret, $category], $del_params, [$no])
        );

        if ($s_data['father']) $connect->exec("update {$t_board}_{$id} set child=? where no=?", [$s_data['child'], $s_data['father']]); // 답글이었으면 원본글의 답글을 현재글의 답글로 대체
        if ($s_data['child']) $connect->exec("update {$t_board}_{$id} set depth=depth-1,father=? where no=?", [$s_data['father'], $s_data['child']]); // 답글이 있으면 현재글의 위치로;;

        // 원래 다음글로 이글을 가지고 있었던 데이타의 prev_no을 바꿈;
        $temp = $connect->value("select max(headnum) from {$t_board}_{$id} where headnum<=?", [$s_data['headnum']]);
        $temp = $connect->row("select no from {$t_board}_{$id} where headnum=? and depth='0'", [$temp]);
        $connect->exec("update {$t_board}_{$id} set prev_no=? where prev_no=?", [$temp['no'] ?? '', $s_data['no']]);

        $connect->exec("update {$t_board}_{$id} set next_no=? where next_no=?", [$s_data['next_no'], $s_data['no']]);

        $connect->exec("update {$t_board}_{$id} set prev_no=? where prev_no='0' and no!=?", [$no, $no]); // 다음글의 이전글을 설정
        $connect->exec("update {$t_category}_{$id} set num=num-1 where no=?", [$s_data['category']]);
        $connect->exec("update {$t_category}_{$id} set num=num+1 where no=?", [$category]);

        // 일반->일반, 공지->공지 일때
    } else {
        $connect->exec(
            "update {$t_board}_{$id} set name=?,subject=?,email=?,homepage=?,memo=?,sitelink1=?,sitelink2=?,use_html=?,reply_mail=?,is_secret=?,category=? $del_que1 $del_que2 where no=?",
            array_merge([$name, $subject, $email, $homepage, $memo, $sitelink1, $sitelink2, $use_html, $reply_mail, $is_secret, $category], $del_params, [$no])
        );
        $connect->exec("update {$t_category}_{$id} set num=num-1 where no=?", [$s_data['category']]);
        $connect->exec("update {$t_category}_{$id} set num=num+1 where no=?", [$category]);
    }



    /***************************************************************************
     * 답변글일때
     **************************************************************************/
} elseif ($mode == "reply" && $no) {

    $prev_no = $s_data['prev_no'];
    $next_no = $s_data['next_no'];
    $father = $s_data['no'];
    $child = 0;
    $headnum = $s_data['headnum'];
    if ($headnum <= -2000000000 && $notice) Error("공지사항에는 답글을 달수가 없습니다");
    $depth = $s_data['depth'] + 1;
    $arrangenum = $s_data['arrangenum'] + 1;
    $move_result = $connect->all("select no from {$t_board}_{$id} where division=? and headnum=? and arrangenum>=?", [$s_data['division'], $headnum, $arrangenum]);
    foreach ($move_result as $move_data) {
        $connect->exec("update {$t_board}_{$id} set arrangenum=arrangenum+1 where no=?", [$move_data['no']]);
    }

    $division = $s_data['division'];
    plus_division($s_data['division']);

    // 답글 데이타 입력;;
    $connect->exec(
        "insert into {$t_board}_{$id} (division,headnum,arrangenum,depth,prev_no,next_no,father,child,ismember,memo,ip,password,name,homepage,email,subject,use_html,reply_mail,category,is_secret,sitelink1,sitelink2,file_name1,file_name2,s_file_name1,s_file_name2,x,y,reg_date,islevel) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [$division, $headnum, $arrangenum, $depth, $prev_no, $next_no, $father, $child, $member['no'], $memo, $ip, $password, $name, $homepage, $email, $subject, $use_html, $reply_mail, $category, $is_secret, $sitelink1, $sitelink2, $file_name1, $file_name2, $s_file_name1, $s_file_name2, $x, $y, $reg_date, $member['is_admin']]
    );

    // 원본글과 원본글의 아래글의 속성 변경;;
    $no = $connect->insertId();
    $connect->exec("update {$t_board}_{$id} set child=? where no=?", [$no, $s_data['no']]);
    $connect->exec("update {$t_category}_{$id} set num=num+1 where no=?", [$category]);

    // 현재글의 조회수를 올릴수 없게 세션 등록
    $hitStr = "," . $setup['no'] . "_" . $no;
    Session::set('zb_hit', Session::get('zb_hit', '') . $hitStr);

    // 현재글의 추천을 할수 없게 세션 등록
    $voteStr = "," . $setup['no'] . "_" . $no;
    Session::set('zb_vote', Session::get('zb_vote', '') . $voteStr);

    // 응답글 보내기일때;;
    if ($s_data['reply_mail'] && $s_data['email']) {

        if ($use_html < 2) $memo = nl2br($memo);

        zb_sendmail($use_html, $s_data['email'], $s_data['name'], $email, $name, $subject, $memo);
    }

    /***************************************************************************
     * 신규 글쓰기일때
     **************************************************************************/
} elseif ($mode == "write") {

    // 공지사항이 아닐때;;
    if (!$notice) {
        $max_division = $connect->value("select max(division) from {$t_division}_{$id}");
        $temp = $connect->value("select max(division) from {$t_division}_{$id} where num>0 and division!=?", [$max_division]);
        if (!$temp) $second_division = 0; else $second_division = $temp;

        $max_headnum = $connect->value("select min(headnum) from {$t_board}_{$id} where (division=? or division=?) and headnum>-2000000000", [$max_division, $second_division]);
        if (!$max_headnum) $max_headnum = 0;

        $headnum = (int)$max_headnum - 1;

        $next_data = $connect->row("select division,headnum,arrangenum from {$t_board}_{$id} where (division=? or division=?) and headnum>-2000000000 order by headnum limit 1", [$max_division, $second_division]);
        if (!empty($next_data['division'])) {
            $next_data = $connect->row("select no,headnum,division from {$t_board}_{$id} where division=? and headnum=? and arrangenum=?", [$next_data['division'], $next_data['headnum'], $next_data['arrangenum']]);
        }

        $prev_data = $connect->value("select no from {$t_board}_{$id} where (division=? or division=?) and headnum<=-2000000000 order by headnum desc limit 1", [$max_division, $second_division]);
        if ($prev_data) $prev_no = $prev_data; else $prev_no = "0";

        // 공지사항일때;;
    } else {
        $temp = $connect->value("select max(division) from {$t_division}_{$id}");
        $max_division = (int)$temp + 1;
        $temp = $connect->value("select max(division) from {$t_division}_{$id} where num>0 and division!=?", [$max_division]);
        if (!$temp) $second_division = 0; else $second_division = $temp;

        $max_headnum = $connect->value("select min(headnum) from {$t_board}_{$id} where division=? or division=?", [$max_division, $second_division]);
        $headnum = (int)$max_headnum - 1;
        if ($headnum > -2000000000) $headnum = -2000000000;

        $next_data = $connect->row("select division,headnum from {$t_board}_{$id} where division=? or division=? order by headnum limit 1", [$max_division, $second_division]);
        if (!empty($next_data['division'])) {
            $next_data = $connect->row("select no,headnum,division from {$t_board}_{$id} where division=? and headnum=? and arrangenum='0'", [$next_data['division'], $next_data['headnum']]);
        }
        $prev_no = 0;
    }

    $next_no = $next_data['no'] ?? '';
    $child = "0";
    $depth = "0";
    $arrangenum = "0";
    $father = "0";
    $division = add_division();

    $connect->exec(
        "insert into {$t_board}_{$id} (division,headnum,arrangenum,depth,prev_no,next_no,father,child,ismember,memo,ip,password,name,homepage,email,subject,use_html,reply_mail,category,is_secret,sitelink1,sitelink2,file_name1,file_name2,s_file_name1,s_file_name2,x,y,reg_date,islevel) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [$division, $headnum, $arrangenum, $depth, $prev_no, $next_no, $father, $child, $member['no'], $memo, $ip, $password, $name, $homepage, $email, $subject, $use_html, $reply_mail, $category, $is_secret, $sitelink1, $sitelink2, $file_name1, $file_name2, $s_file_name1, $s_file_name2, $x, $y, $reg_date, $member['is_admin']]
    );
    $no = $connect->insertId();

    // 현재글의 조회수를 올릴수 없게 세션 등록
    $hitStr = "," . $setup['no'] . "_" . $no;
    Session::set('zb_hit', Session::get('zb_hit', '') . $hitStr);

    // 현재글의 추천을 할수 없게 세션 등록
    $voteStr = "," . $setup['no'] . "_" . $no;
    Session::set('zb_vote', Session::get('zb_vote', '') . $voteStr);

    if ($prev_no) $connect->exec("update {$t_board}_{$id} set next_no=? where no=?", [$no, $prev_no]);
    if ($next_no) $connect->exec("update {$t_board}_{$id} set prev_no=? where headnum=? and division=?", [$no, $next_data['headnum'] ?? '', $next_data['division'] ?? '']);
    $connect->exec("update {$t_category}_{$id} set num=num+1 where no=?", [$category]);
}


// 글의 개수를 다시 갱신
$total = $connect->value("select count(*) from {$t_board}_{$id} ");
$connect->exec("update $admin_table set total_article=? where name=?", [$total, $id]);

// 회원일 경우 해당 해원의 점수 주기
if ($mode == "write" || $mode == "reply") $connect->exec("update $member_table set point1=point1+1 where no=?", [$member['no']]);

// 페이지 이동
$view_file = "zboard.php";
$q_category = urlencode($category);
Response::redirect($view_file . "?id=$id&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$des&sn=$q_sn&ss=$q_ss&sc=$q_sc&keyword=$q_keyword&no=$no&category=$q_category");
?>
