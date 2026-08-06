<?php
// 라이브러리 함수 파일 인크루드
require_once __DIR__ . '/lib.php';

$mode = Request::post('mode');
$group_no = Request::post('group_no');
$id = Request::post('id');
if ($id !== '' && !isalNum($id)) Error("게시판 이름이 올바르지 않습니다");
$user_id = Request::post('user_id');
$password = Request::post('password');
$password1 = Request::post('password1');
$name = Request::post('name');
$email = Request::post('email');
$homepage = Request::post('homepage');
$icq = Request::post('icq');
$aol = Request::post('aol');
$msn = Request::post('msn');
$jumin1 = Request::post('jumin1');
$jumin2 = Request::post('jumin2');
$jumin = '';
$comment = Request::post('comment');
$job = Request::post('job');
$hobby = Request::post('hobby');
$home_address = Request::post('home_address');
$home_tel = Request::post('home_tel');
$office_address = Request::post('office_address');
$office_tel = Request::post('office_tel');
$handphone = Request::post('handphone');
$mailing = Request::post('mailing');
$birth_1 = Request::post('birth_1');
$birth_2 = Request::post('birth_2');
$birth_3 = Request::post('birth_3');
$openinfo = Request::post('openinfo');
$open_email = Request::post('open_email');
$open_homepage = Request::post('open_homepage');
$open_icq = Request::post('open_icq');
$open_msn = Request::post('open_msn');
$open_aol = Request::post('open_aol');
$open_comment = Request::post('open_comment');
$open_job = Request::post('open_job');
$open_hobby = Request::post('open_hobby');
$open_home_address = Request::post('open_home_address');
$open_home_tel = Request::post('open_home_tel');
$open_office_address = Request::post('open_office_address');
$open_office_tel = Request::post('open_office_tel');
$open_handphone = Request::post('open_handphone');
$open_birth = Request::post('open_birth');
$open_picture = Request::post('open_picture');
$picture = $picture_name = $picture_type = '';
$picture_size = 0;

check_csrf();
$referer_hdr = Request::header('Referer');
if ($referer_hdr === '' || stripos($referer_hdr, Request::header('Host')) === false) Error("정상적으로 작성하여 주시기 바랍니다.");
if (stripos($referer_hdr, "member_join.php") === false) Error("정상적으로 작성하여 주시기 바랍니다.");
if (Request::method() == 'GET' ) Error("정상적으로 작성하여 주시기 바랍니다.");

// DB 연결
if (!isset($connect)) $connect = dbConn();

// 멤버 정보 구해오기;;; 멤버가 있을때
$member = member_info();
if ($mode == "admin" && ($member['is_admin'] == 1 || ($member['is_admin'] == 2 && $member['group_no'] == $group_no))) $mode = "admin";
else $mode = "";

if ($member['no'] && !$mode) Error("이미 가입이 되어 있습니다.", "window.close");


// 현재 게시판 설정 읽어 오기
if ($id) {
    $setup = get_table_attrib($id);

    // 설정되지 않은 게시판일때 에러 표시
    if (!$setup['name']) Error("생성되지 않은 게시판입니다.<br><br>게시판을 생성후 사용하십시오");

    // 현재 게시판의 그룹의 설정 읽어 오기
    $group_data = group_info($setup['group_no']);
    if (!$group_data['use_join'] && !$mode) Error("현재 지정된 그룹은 추가 회원을 모집하지 않습니다");

} else {

    if (!$group_no) Error("회원그룹을 정해주셔야 합니다");
    $group_data = $connect->row("select * from $group_table where no=?", [$group_no]);
    if (empty($group_data['no'])) Error("지정된 그룹이 존재하지 않습니다");
    if (!$group_data['use_join'] && !$mode) Error("현재 지정된 그룹은 추가 회원을 모집하지 않습니다");
}


// 빈문자열인지를 검사
$user_id = str_replace("ㅤ", "", $user_id);
$name = str_replace("ㅤ", "", $name);


$user_id = trim($user_id);
if (isBlank($user_id)) Error("ID를 입력하셔야 합니다");

$canHangulId = strtolower(getDefaultSetup()['enable_hangul_id'] ?? '') == 'true';

if (($canHangulId && !preg_match('/^[a-zA-Z0-9_가-힣]*$/u', $user_id))
	|| (!$canHangulId && !preg_match('/^[a-zA-Z0-9_]*$/', $user_id))) Error("ID를 제대로 입력하여 주세요");
if (mb_strlen($user_id, 'UTF-8') < 4 || mb_strlen($user_id, 'UTF-8') > 40) Error("ID를 제대로 입력하여 주세요");

if (!ismail($email)) Error("E-Mail을 제대로 입력하여 주세요");

$check = $connect->value("select count(*) from $member_table where user_id=?", [$user_id]);
if ($check > 0) Error("이미 등록되어 있는 ID입니다", "");

unset($check);
$check = $connect->value("select count(*) from $member_table where email=?", [$email]);
if ($check > 0) Error("이미 등록되어 있는 E-Mail입니다", "");

if (isBlank($password)) Error("비밀번호를 입력하셔야 합니다", "");

if (isBlank($password1)) Error("비밀번호 확인을 입력하셔야 합니다", "");

if ($password !== $password1) Error("비밀번호와 비밀번호 확인이 일치하지 않습니다", "");

if (isBlank($name)) Error("이름을 입력하셔야 합니다");

if (strpos($name, '<') !== false || strpos($name, '>') !== false) Error("이름을 영문, 한글, 숫자등으로 입력하여 주십시오");

if ($group_data['use_jumin'] && !$mode) {

    // 주민등록 번호 루틴
    if (isBlank($jumin1) || isBlank($jumin2) || strlen($jumin1) != 6 || strlen($jumin2) != 7) Error("주민등록번호를 올바르게 입력하여 주십시요", "");

    if (!check_jumin($jumin1 . $jumin2)) Error("잘못된 주민등록번호입니다");

    $check = $connect->value("select count(*) from $member_table where jumin=?", [hash('sha256', $jumin1 . $jumin2)]);
    if ($check > 0) Error("이미 등록되어 있는 주민등록번호입니다", "");
    $jumin = $jumin1 . $jumin2;
}


if ($_zbDefaultSetup['check_email'] == "true" && !mail_mx_check($email)) Error("입력하신 $email 은 존재하지 않는 메일주소입니다.<br>다시 한번 확인하여 주시기 바랍니다.");
$birth = mktime(0, 0, 0, (int)$birth_2, (int)$birth_3, (int)$birth_1);
if ((stripos($homepage, "http://") !== 0 && stripos($homepage, "https://") !== 0) && $homepage) $homepage = "http://$homepage";
$reg_date = time();

if ($_uf = Request::file('picture')) {
    $picture = $_uf->tmpName();
    $picture_name = $_uf->name();
    $picture_type = $_uf->type();
    $picture_size = $_uf->size();
}

if ($picture_name) {
    if (!$_uf->isValid()) Error("정상적인 방법으로 업로드 해주세요");
    if (!preg_match("/\.(gif|jpe?g)$/i", $picture_name)) Error("사진은 gif 또는 jpg 파일을 올려주세요");
    $size = getimagesize($picture);
    if ($size === false) Error("유효하지 않은 사진입니다.");
    $kind = array("", "gif", "jpg");
    $n = $size[2];
    $path = "icon/member_" . bin2hex(random_bytes(16)) . '.' . $kind[$n];
    if (!@$_uf->moveTo($path)) Error("사진 업로드가 제대로 되지 않았습니다");
    $picture_name = $path;
}


$connect->exec(
    "insert into $member_table (level,group_no,user_id,password,name,email,homepage,icq,aol,msn,jumin,comment,job,hobby,home_address,home_tel,office_address,office_tel,handphone,mailing,birth,reg_date,openinfo,open_email,open_homepage,open_icq,open_msn,open_comment,open_job,open_hobby,open_home_address,open_home_tel,open_office_address,open_office_tel,open_handphone,open_birth,open_picture,picture,open_aol) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
    [$group_data['join_level'], $group_data['no'], $user_id, createHash($password), $name, $email, $homepage, $icq, $aol, $msn,
        $jumin === '' ? '' : hash('sha256', $jumin), $comment, $job, $hobby, $home_address, $home_tel, $office_address, $office_tel, $handphone, $mailing,
        $birth, $reg_date, $openinfo, $open_email, $open_homepage, $open_icq, $open_msn, $open_comment, $open_job, $open_hobby,
        $open_home_address, $open_home_tel, $open_office_address, $open_office_tel, $open_handphone, $open_birth, $open_picture, $picture_name, $open_aol]
);
$new_member_no = $connect->insertId();
$connect->exec("update $group_table set member_num=member_num+1 where no=?", [$group_data['no']]);

if (!$mode) {
    Session::regenerate();

    Session::set('zb_logged_no', $new_member_no);
    Session::set('zb_logged_time', time());
    Session::set('zb_logged_ip', Request::clientIp());
    Session::set('zb_last_connect_check', '0');
}

head();
?>
<script>
	alert("회원가입이 정상적으로 처리 되었습니다\n\n회원이 되신것을 진심으로 축하드립니다.");
	opener.window.history.go(0);
	window.close();
</script>
<?php
    foot();
?>
