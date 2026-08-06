<?php
// 라이브러리 함수 파일 인크루드
require_once __DIR__ . '/lib.php';

$name = Request::post('name');
$password = Request::post('password');
$password1 = Request::post('password1');
$email = Request::post('email');
$homepage = Request::post('homepage');
$job = Request::post('job');
$hobby = Request::post('hobby');
$icq = Request::post('icq');
$aol = Request::post('aol');
$msn = Request::post('msn');
$home_address = Request::post('home_address');
$home_tel = Request::post('home_tel');
$office_address = Request::post('office_address');
$office_tel = Request::post('office_tel');
$handphone = Request::post('handphone');
$mailing = Request::post('mailing');
$comment = Request::post('comment');
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
$del_picture = Request::post('del_picture');
$picture = $picture_name = $picture_type = '';
$picture_size = 0;

check_csrf();
if (Request::method() == 'GET' ) Error("정상적으로 글을 쓰시기 바랍니다", "");

// DB 연결
if (!isset($connect)) $connect = dbConn();

// 멤버 정보 구해오기;;; 멤버가 있을때
$member = member_info();
if (!$member['no']) Error("회원정보가 존재하지 않습니다");
$group = group_info($member['group_no']);

$name = str_replace("ㅤ", "", $name);

if (isblank($name)) Error("이름을 입력하셔야 합니다");
if (strpos($name, '<') !== false || strpos($name, '>') !== false) Error("이름에는 태그를 사용하실수 없습니다.");

if ($password && $password1 && $password != $password1) Error("비밀번호가 일치하지 않습니다");

$birth = mktime(0, 0, 0, (int)$birth_2, (int)$birth_3, (int)$birth_1);

if (!ismail($email)) Error("E-Mail을 제대로 입력하여 주세요");

$check = $connect->value("select count(*) from $member_table where email=? and no <> ?", [$email, $member['no']]);
if ($check > 0) Error("이미 등록되어 있는 E-Mail입니다");


if ($_zbDefaultSetup['check_email'] == "true" && !mail_mx_check($email)) Error("입력하신 $email 은 존재하지 않는 메일주소입니다.<br>다시 한번 확인하여 주시기 바랍니다.");
if ((stripos($homepage, "http://") !== 0 && stripos($homepage, "https://") !== 0) && $homepage) $homepage = "http://$homepage";

$que = "update $member_table set name=?";
$params = [$name];

if ($password && $password1 && $password == $password) { $que .= " ,password=? "; $params[] = createHash($password); }

if ($birth_1 && $birth_2 && $birth_3 && $group['use_birth']) { $que .= ",birth=?"; $params[] = $birth; }
if ($email) { $que .= ",email=?"; $params[] = $email; }
$que .= ",homepage=?"; $params[] = $homepage;
if (!empty($group['use_job'])) { $que .= ",job=?"; $params[] = $job; }
if (!empty($group['use_hobby'])) { $que .= ",hobby=?"; $params[] = $hobby; }
if (!empty($group['use_icq'])) { $que .= ",icq=?"; $params[] = $icq; }
if (!empty($group['use_aol'])) { $que .= ",aol=?"; $params[] = $aol; }
if (!empty($group['use_msn'])) { $que .= ",msn=?"; $params[] = $msn; }
if (!empty($group['use_home_address'])) { $que .= ",home_address=?"; $params[] = $home_address; }
if (!empty($group['use_home_tel'])) { $que .= ",home_tel=?"; $params[] = $home_tel; }
if (!empty($group['use_office_address'])) { $que .= ",office_address=?"; $params[] = $office_address; }
if (!empty($group['use_office_tel'])) { $que .= ",office_tel=?"; $params[] = $office_tel; }
if (!empty($group['use_handphone'])) { $que .= ",handphone=?"; $params[] = $handphone; }
if (!empty($group['use_mailing'])) { $que .= ",mailing=?"; $params[] = $mailing; }
$que .= ",openinfo=?"; $params[] = $openinfo;
if (!empty($group['use_comment'])) { $que .= ",comment=?"; $params[] = $comment; }
$que .= ",openinfo=?,open_email=?,open_homepage=?,open_icq=?,open_msn=?,open_comment=?,open_job=?,open_hobby=?,open_home_address=?,open_home_tel=?,open_office_address=?,open_office_tel=?,open_handphone=?,open_birth=?,open_picture=?,open_aol=? ";
array_push($params, $openinfo, $open_email, $open_homepage, $open_icq, $open_msn, $open_comment, $open_job, $open_hobby, $open_home_address, $open_home_tel, $open_office_address, $open_office_tel, $open_handphone, $open_birth, $open_picture, $open_aol);
$que .= " where no=?"; $params[] = $member['no'];

$connect->exec($que, $params);

if ($del_picture) {
    $connect->exec("update $member_table set picture='' where no=?", [$member['no']]);
}

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
    if ($size[0] > 200 || $size[1] > 200) Error("사진의 크기는 200*200이하여야 합니다");
    $kind = array("", "gif", "jpg");
    $n = $size[2];
    $path = "icon/member_" . bin2hex(random_bytes(16)) . '.' . $kind[$n];
    if (!$_uf->moveTo($path)) Error("사진 업로드가 제대로 되지 않았습니다");
    $connect->exec("update $member_table set picture=? where no=?", [$path, $member['no']]);
}

head();
?>
<script>
alert("회원님의 정보 수정이 제대로 처리되었습니다.");
opener.window.history.go(0);
window.close();
</script>
<?php
    foot();
?>
