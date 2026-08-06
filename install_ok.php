<?php
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/schema.sql';

if (file_exists(__DIR__ . '/config.php')) error("이미 config.php가 생성되어 있습니다.<br><br>재설치하려면 해당 파일을 지우세요");

// 호스트네임, 아이디, DB네임, 비밀번호의 공백여부 검사
if (isBlank($hostname = Request::post('hostname'))) Error("HostName을 입력하세요", "");
if (isBlank($user_id = Request::post('user_id'))) Error("User ID 를 입력하세요", "");
if (isBlank($password = Request::post('password'))) Error("Password 를 입력하세요", "");
if (isBlank($dbname = Request::post('dbname'))) Error("DB NAME을 입력하세요", "");

// DB에 커넥트 하고 DB NAME으로 select DB
try {
    $connect = new DB($hostname, $user_id, $password, $dbname);

} catch (mysqli_sql_exception $e) {
    Error("MySQL-DB Connect<br>Error!!! : " . e($e->getMessage()), "");

} catch (InvalidArgumentException $e) {
    Error(e($e->getMessage()), "");
}


// 관리자 테이블 생성
if (!isTable($admin_table)) {
    try {
        $connect->exec($admin_table_schema);
    } catch (mysqli_sql_exception $e) {
        Error("관리자 테이블 생성 실패", "");
    }
} else $admin_table_exist = 1;

// 그룹테이블 생성
if (!isTable($group_table)) {
    try {
        $connect->exec($group_table_schema);
    } catch (mysqli_sql_exception $e) {
        Error("그룹 테이블 생성 실패", "");
    }
} else $group_table_exist = 1;

// 회원관리 테이블 생성
if (!istable($member_table)) {
    try {
        $connect->exec($member_table_schema);
    } catch (mysqli_sql_exception $e) {
        Error("회원관리 테이블 생성 실패", "");
    }
} else $member_table_exist = 1;

// 쪽지테이블
if (!istable($get_memo_table)) {
    try {
        $connect->exec($get_memo_table_schema);
    } catch (mysqli_sql_exception $e) {
        Error("받은 쪽지 테이블 생성 실패");
    }
} else $get_memo_table_exists = 1;

if (!istable($send_memo_table)) {
    try {
        $connect->exec($send_memo_table_schema);
    } catch (mysqli_sql_exception $e) {
        Error("보낸 쪽지 테이블 생성 실패");
    }
} else $send_memo_table_exist = 1;

// 파일로 DB 정보 저장
$cwd = getcwd();
chdir(__DIR__);

if (!is_writable('.')) Error("config.php 파일 생성 실패<br><br>디렉토리의 퍼미션을 707로 주십시요", "");

$consts = array();
$tmp_dir = dirname(Request::scriptName());
$tmp_scheme = Request::isHttps() ? 'https' : 'http';

$consts['_ZB_BASE'] = $tmp_dir === '/' ? '/' : $tmp_dir . '/';
$consts['_ZB_URL'] = $tmp_scheme . '://' . Request::header('Host') . $consts['_ZB_BASE'];

$file_content = "<?php\n"
     . "if (!defined('_ZB_PATH')) exit();\n\n"
     . "define('DB_HOST', " . var_export($hostname, true) . ");\n"
	 . "define('DB_USER', " . var_export($user_id, true) . ");\n"
	 . "define('DB_PASS', " . var_export($password, true) . ");\n"
	 . "define('DB_NAME', " . var_export($dbname, true) . ");\n\n"
	 . "define('_ZB_BASE', " . var_export($consts['_ZB_BASE'], true) . ");\n"
	 . "define('_ZB_URL', " . var_export($consts['_ZB_URL'], true) . ");\n";


if (file_put_contents(__DIR__ . '/config.php', $file_content) === false) {
    error("config.php 파일 생성 실패<br><br>디렉토리의 퍼미션을 707로 주십시요", "");
}

$folders = [
    'data',
    'icon',
    'icon/member_image_box',
    'icon/private_icon',
    'icon/private_name'
];

foreach ($folders as $f) {
    if (!file_exists($f)) {
        mkdir($f, 0707);
        chmod($f, 0707);
        createIndexFile($f);
    }
}

chmod("config.php", 0644);
chdir($cwd);

$temp = $connect->value("SELECT COUNT(*) FROM {$member_table} WHERE is_admin = '1' ");

if ($temp) Response::redirect("admin.php");
else Response::redirect("install2.php"); // 관리자 정보가 없을때 관리자 정보 입력
?>
