<?php
/***************************************************************************
 * 공통 파일 include
 **************************************************************************/
require_once __DIR__ . '/_head.php';

$no = (int) Request::req('no');
$memo = Request::post('memo');
$name = Request::req('name');
$password = Request::post('password');
$c_name = Request::req('c_name');
$mode = Request::req('mode');
$des = Request::req('des');
$subject = $email = $homepage = '';
$s_data = array();

check_csrf();
$referer_hdr = Request::header('Referer');
if ($referer_hdr === '' || stripos($referer_hdr, Request::header('Host')) === false) Error("정상적으로 글을 작성하여 주시기 바랍니다.");

/***************************************************************************
 * 게시판 설정 체크
 **************************************************************************/

// 대상 파일 이름 정리
if (!$setup['use_alllist']) $view_file_link = "view.php"; else $view_file_link = "zboard.php";

// 사용권한 체크
if ($setup['grant_comment'] < $member['level'] && !$is_admin) Error("사용권한이 없습니다", "login.php?id=$id&page=$page&page_num=$page_num&category=$category&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&no=$no&file=$view_file_link");

// 각종 변수 검사;;
$memo = str_replace("ㅤ", "", $memo);
if (isblank($memo)) Error("내용을 입력하셔야 합니다");
if (!$member['no']) {
    if (isblank($name)) Error("이름을 입력하셔야 합니다");
    if (isblank($password)) Error("비밀번호를 입력하셔야 합니다");
}

// 필터링;; 관리자가 아닐때;;
if (!$is_admin && $setup['use_filter']) {
    $filter = explode(",", $setup['filter']);

    $f_memo = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($memo));
    $f_name = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($name));
    $f_subject = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($subject));
    $f_email = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($email));
    $f_homepage = preg_replace('/([\_\-\.\/~@?=%&! ]+)/i', "", strip_tags($homepage));
    for ($i = 0; $i < count($filter); $i++)
        if (!isblank($filter[$i])) {
            if (stripos($f_memo, $filter[$i]) !== false) Error("<b>$filter[$i]</b> 은(는) 등록하기에 적합한 단어가 아닙니다");
            if (stripos($f_name, $filter[$i]) !== false) Error("<b>$filter[$i]</b> 은(는) 등록하기에 적합한 단어가 아닙니다");
        }
}

// 패스워드를 암호화
if ($password) {
    $password = createHash($password);
}

// 관리자이거나 HTML허용레벨이 낮을때 태그의 금지유무를 체크
if (!$is_admin && $setup['grant_html'] < $member['level']) {
    $memo = e($memo); // 내용의 HTML 금지;;
}

// 회원등록이 되어 있을때 이름등을 가져옴;;
if ($member['no']) {
    if ($mode == "modify" && $member['no'] != ($s_data['ismember'] ?? '')) {
        $name = $s_data['name'] ?? '';
    } else {
        $name = $member['name'];
    }
}

// autolink 처리 (이름은 출력측 e()로 방어)
$memo = autolink($memo);

// 코멘트의 최고 Number 값을 구함 (중복 체크를 위해서)
$max_no = $connect->value("select max(no) from {$t_comment}_{$id} where parent=?", [$no]);

// 같은 내용이 있는지 검사;;
if (!$is_admin) {
    $temp = $connect->value("select count(*) from {$t_comment}_{$id} where memo=? and no=?", [$memo, $max_no]);
    if ($temp > 0) Error("같은 내용의 글은 등록할수가 없습니다");
}

// 쿠키 설정;;

if ($c_name) Session::set('writer_name', $name);

// 각종 변수 설정
$reg_date = time(); // 현재의 시간구함;;
$parent = $no;

// 해당글이 있는 지를 검사
$check = $connect->value("select count(*) from {$t_board}_{$id} where no = ?", [$no]);
if (!$check) Error("원본 글이 존재하지 않습니다.");

// 코멘트 입력
$connect->exec(
    "insert into {$t_comment}_{$id} (parent,ismember,name,password,memo,reg_date,ip) values (?,?,?,?,?,?,?)",
    [$parent, $member['no'], $name, $password, $memo, $reg_date, Request::clientIp()]
);


// 코멘트 개수를 구해서 정리
$total = $connect->value("select count(*) from {$t_comment}_{$id} where parent=?", [$no]);
$connect->exec("update {$t_board}_{$id} set total_comment=? where no=?", [$total, $no]);


// 회원일 경우 해당 해원의 점수 주기
$connect->exec("update $member_table set point2=point2+1 where no=?", [$member['no']]);

// 페이지 이동
Response::redirect("$view_file_link?id=$id&page=$page&page_num=$page_num&select_arrange=$select_arrange&desc=$des&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&no=$no&category=$category");
?>
