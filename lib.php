<?php
/*******************************************************************************
 * include 되었는지를 검사
******************************************************************************/
if (defined('_zb_lib_included')) return;
define('_zb_lib_included', true);

error_reporting(E_ALL);
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');

// 현재 버젼
define('ZB_VERSION', '4.1 pl8');
define('_ZB_PATH', __DIR__ . '/');

set_exception_handler(function (Throwable $e) {
    error_log('[zb] ' . $e->getMessage());
    error("처리중 오류가 발생했습니다");
});

$_startTime = getmicrotime();

$_sessionStart = $_sessionEnd = $_nowConnectStart = $_nowConnectEnd = 0.0;
$_dbTime = $_skinTime = $_listCheckTime = $_queryTime = 0.0;
$_zbResizeCheck = false;
$zbLayer = '';
$total_connect = $total_member_connect = $total_guest_connect = '';

if (is_file(__DIR__ . '/config.php')) {
    // config.php 파일은 존재하지만 읽기 권한이 없는 비정상적인 경우에 대한 검사
    if (!is_readable(__DIR__ . '/config.php')) error('config.php 파일에 대한 읽기 권한이 없습니다.');

    require_once __DIR__ . '/config.php';
}

require_once __DIR__ . '/class.php';
require_once __DIR__ . '/safehtml.php';


// W3C P3P 규약설정
Response::header('P3P', 'CP="ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC"');

/*******************************************************************************
 * 기본 변수 초기화. (php의 오류같지 않은 오류 때문에;; ㅡㅡ+)
******************************************************************************/
$member = array();
$group = array();
$setup = array();

/*******************************************************************************
 * 기본 설정 파일을 읽음
******************************************************************************/
$_zbDefaultSetup = getDefaultSetup();

date_default_timezone_set($_zbDefaultSetup['site_timezone']);

/*******************************************************************************
 * install 페이지가 아닌 경우
******************************************************************************/
if (file_exists(_ZB_PATH . 'config.php') && !preg_match('/install/i', Request::scriptName())) {

    //세션 처리 (세션은 3일동안 유효하게 설정)
    define('SESSION_PATH', _ZB_PATH . $_zbDefaultSetup['session_path']);
    if (!file_exists(SESSION_PATH) && is_writable(dirname(SESSION_PATH))) {
        mkdir(SESSION_PATH, 0777);
        chmod(SESSION_PATH, 0777);
        createIndexFile(SESSION_PATH);
    }

    // Data, Icon, 세션디렉토리의 쓰기 권한이 없다면 에러 처리
    if (!is_writable(_ZB_PATH . 'data')) error("Data 디렉토리의 쓰기 권한이 없습니다<br>제로보드를 사용하기 위해서는 Data 디렉토리의 쓰기 권한이 있어야 합니다");
    if (!is_writable(_ZB_PATH . 'icon')) error("icon 디렉토리의 쓰기 권한이 없습니다<br>제로보드를 사용하기 위해서는 icon 디렉토리의 쓰기 권한이 있어야 합니다");
    if (!is_writable(SESSION_PATH)) error("세션 디렉토리(" . SESSION_PATH . ")의 쓰기 권한이 없습니다<br>제로보드를 사용하기 위해서는 세션디렉토리의 쓰기 권한이 있어야 합니다");

    $_sessionStart = getmicrotime();

    // 세션 변수의 등록
    Session::start();

    // 조회수 가 512byte를, 투표 세션변수가 256byte를 넘을시 리셋 (개인서버를 이용시에는 조금 더 늘려도 됨)
    if (strlen(Session::get('zb_hit', '')) > $_zbDefaultSetup['session_view_size']) {
        Session::set('zb_hit', '');
    }
    if (strlen(Session::get('zb_vote', '')) > $_zbDefaultSetup['session_vote_size']) {
        Session::set('zb_vote', '');
    }

    // 자동 로그인일때 제대로 된 자동 로그인인지 체크하는 부분
    $autoLoginData = getZBSessionID();

    if (!empty($autoLoginData['no'])) {
        Session::regenerate();
        Session::set('zb_logged_no', (int) $autoLoginData['no']);
        Session::set('zb_logged_ip', Request::clientIp());
        Session::set('zb_logged_time', time());

        // 세션 값을 체크하여 로그인을 처리
    } elseif (Session::get('zb_logged_no')) {

        // 로그인 시간이 지정된 시간을 넘었거나 로그인 아이피가 현재 사용자의 아이피와 다를 경우 로그아웃 시킴
        if (time() - (int) Session::get('zb_logged_time', 0) > $_zbDefaultSetup['login_time']
            || Session::get('zb_logged_ip') !== Request::clientIp()) {
            Session::destroy();
            Session::start();

            // 유효할 경우 로그인 시간을 다시 설정
        } else {
            Session::set('zb_logged_time', time());
        }

    }
    $_sessionEnd = getmicrotime();

    // 현재 접속자의 데이타를 체크하여 파일로 저장 (회원, 비회원으로 구분해서 저장)
    $_nowConnectStart = getmicrotime();

    if ($_zbDefaultSetup['nowconnect_enable'] === 'true') {
        $_zb_now_check_intervalTime = time() - (int) Session::get('zb_last_connect_check', 0);

        if (!Session::get('zb_last_connect_check')
        	|| $_zb_now_check_intervalTime > $_zbDefaultSetup['nowconnect_refresh_time']) {

            Session::set('zb_last_connect_check', time());

            if (Session::get('zb_logged_no')) {
                $total_member_connect = $total_connect = getNowConnector(_ZB_PATH . "data/now_member_connect.php", Session::get('zb_logged_no'));
                $total_guest_connect = getNowConnector_num(_ZB_PATH . "data/now_connect.php", true);
            } else {
                $total_member_connect = $total_connect = getNowConnector_num(_ZB_PATH . "data/now_member_connect.php", true);
                $total_guest_connect = getNowConnector(_ZB_PATH . "data/now_connect.php", Request::clientIp());
            }
        } else {
            $total_member_connect = $total_connect = getNowConnector_num(_ZB_PATH . "data/now_member_connect.php", false);
            $total_guest_connect = getNowConnector_num(_ZB_PATH . "data/now_connect.php", false);
        }

    }

}

$_nowConnectEnd = getmicrotime();

// 전 페이지 출력을 버퍼링하여 폼에 토큰 자동 주입 + 동적 폼/AJAX용 JS 삽입
if (Session::isActive()) {
    $__csrf_token = Session::csrfToken();
    $__csrf_host = Request::header('Host');
    ob_start(function ($buffer) use ($__csrf_token, $__csrf_host) {
        return zb_csrf_process_output($buffer, $__csrf_token, $__csrf_host);
    });
}


// 익스와 넷스케이프일때 처리
if (preg_match('/msie/i', Request::header('User-Agent'))) $browser = '1';
else $browser = '0';


// DB가 설정이 되었는지를 검사
if (!file_exists(_ZB_PATH . 'config.php') && !preg_match('/install/i', Request::scriptName())) {
    Response::redirect('install.php');
}


// 관리자 테이블과 회원관리 테이블의 이름을 미리 변수로 정의
$member_table = "zetyx_member_table";  // 회원들의 데이타가 들어 있는 직접적인 테이블
$group_table = "zetyx_group_table";   // 그룹테이블
$admin_table = "zetyx_admin_table";     // 게시판의 관리자 테이블

$send_memo_table = "zetyx_send_memo";
$get_memo_table = "zetyx_get_memo";

$t_division = "zetyx_division"; // Division 테이블
$t_board = "zetyx_board"; // 메인 테이블
$t_comment = "zetyx_board_comment"; // 코멘트테이블
$t_category = "zetyx_board_category"; // 카테고리 테이블


// 마이크로 타임 구함
function getmicrotime() {
    $microtimestmp = explode(' ', microtime());
    return (float) $microtimestmp[0] + (float) $microtimestmp[1];
}


/******************************************************************************
 * Division 관련 함수
*****************************************************************************/
// 전체 division 구함
function total_division() {
    global $connect, $t_division, $id;

    if (!isalNum($id)) error("게시판 이름이 올바르지 않습니다");

    $temp = $connect->value("SELECT MAX(division) FROM {$t_division}_{$id} ");
    return $temp;
}

// 답글일때 해당 division의 num 값 증가
function plus_division($division) {
    global $connect, $t_division, $id;

    if (!isalNum($id)) error("게시판 이름이 올바르지 않습니다");

    $connect->exec("UPDATE {$t_division}_{$id} SET num = num + 1 WHERE division = ?", [$division]);
}

// 삭제하거나 공지글을 일반글로 옮기는 등의 division num값 변화시 해당 division의 num값 감소시킴
function minus_division($division) {
    global $connect, $t_division, $id;

    if (!isalNum($id)) error("게시판 이름이 올바르지 않습니다");

    $connect->exec("UPDATE {$t_division}_{$id} SET num = num - 1 WHERE division = ?", [$division]);
}


// 신규글쓰기일때 최근 division의 num 값 증가
function add_division($board_name = '') {
    global $connect, $t_division, $id, $t_board;
    if ($board_name) $board_id = $board_name;
    else $board_id = $id;

    if (!isalNum($board_id)) error("게시판 이름이 올바르지 않습니다");

    $temp = $connect->value("SELECT num FROM {$t_division}_{$board_id} ORDER BY division DESC LIMIT 1");

    // 현재 division의 num값이 기준값일때는 division +1 해줌;
    if ($temp >= 5000) {
        $temp = $connect->value("SELECT MAX(division) FROM {$t_division}_{$board_id}");
        $max_division = (int) $temp + 1;

        $temp = $connect->value("SELECT MAX(division) FROM {$t_division}_{$board_id} WHERE num > 0 AND division != ?", [$max_division]);
        if (!$temp) $second_division = 0;
        else $second_division = $temp;

        $temp = $connect->value("SELECT COUNT(*) FROM {$t_board}_{$board_id} WHERE (division = ? OR division = ?) AND headnum <= -2000000000", [$max_division, $second_division]);
        if ($temp > 0) {
            $connect->exec("UPDATE {$t_board}_{$board_id} SET division = ? WHERE (division = ? OR division = ?) AND headnum <= -2000000000", [$max_division, $max_division, $second_division]);
            $connect->exec("UPDATE {$t_division}_{$board_id} SET num = num - ? WHERE division = ?", [$temp, $max_division - 1]);
        }
        $num = (int) $temp + 1;
        $connect->exec("INSERT INTO {$t_division}_{$board_id} (division, num) VALUES (?, ?)", [$max_division, $num]);

        return $max_division;
    } else {
        // 현재 division이 기준값개보다 작을때~
        $temp = $connect->value("SELECT MAX(division) FROM {$t_division}_{$board_id}");
        $division = $temp;

        $connect->exec("UPDATE {$t_division}_{$board_id} SET num = num + 1 WHERE division = ?", [$division]);

        return $division;
    }
}


/******************************************************************************
 * 로그인이 되어 있는지를 검사하여 로그인되어있으면 해당 회원의 정보를 저장
*****************************************************************************/
function member_info() {

    global $member_table, $member, $connect;

    if (defined('_member_info_included') && !empty($member['no'])) return $member;
    define('_member_info_included', true);

    if (!empty($member['no'])) return $member;

    if (Session::get('zb_logged_no')) {
        $member = $connect->row("SELECT * FROM {$member_table} WHERE no = ?", [Session::get('zb_logged_no')]);
        if (empty($member['no'])) {
            $member = array();
            $member['level'] = 10;
        }
    } else $member['level'] = 10;

    $member += array('no' => '', 'user_id' => '', 'name' => '', 'email' => '', 'homepage' => '',
        'is_admin' => '', 'group_no' => '', 'board_name' => '', 'new_memo' => '', 'picture' => '', 'icq' => '', 'msn' => '',
        'aol' => '', 'comment' => '', 'job' => '', 'hobby' => '', 'home_address' => '', 'home_tel' => '',
        'office_address' => '', 'office_tel' => '', 'handphone' => '', 'birth' => '', 'mailing' => '', 'openinfo' => '');

    return $member;
}


function group_info($no) {
    global $group_table, $connect;
    $temp = $connect->row("SELECT * FROM {$group_table} WHERE no = ?", [$no]);

    return $temp;
}



/******************************************************************************
 * 제로보드 전용 함수
*****************************************************************************/
// MySQL 데이타 베이스에 접근
function dbconn() {

    global $connect, $autologin, $_dbconn_is_included;

    if ($_dbconn_is_included) return $connect;
    $_dbconn_is_included = true;

    loadConfig();

    if (!isset($connect))
        try {
            $connect = new DB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $connect->raw()->query("SET sql_mode = ''");

        } catch (mysqli_sql_exception $e) {
            Error("DB 접속시 에러가 발생했습니다");

        } catch (InvalidArgumentException $e) {
            Error("DB 설정에 오류가 있습니다");
        }

    return $connect;
}


// 글의 아이콘을 뽑아줌;;
function get_icon($data) {
    global $dir;

    // 글쓴 시간 구함
    $check_time = (time() - $data['reg_date']) / 60 / 60;

    // 앞에 붙는 아이콘 정의
    if ($data['depth']) {
        if ($check_time <= 12) $icon = "<img src=\"" . e($dir) . "/reply_new_head.gif\" border=0 align=absmiddle>&nbsp;"; // 최근 글일경우
        else $icon = "<img src=\"" . e($dir) . "/reply_head.gif\" border=0 align=absmiddle>&nbsp;"; // 답글일때
    } else {
        if ($check_time <= 12) $icon = "<img src=\"" . e($dir) . "/new_head.gif\" border=0 align=absmiddle>&nbsp;"; // 최근 글일경우
        else $icon = "<img src=\"" . e($dir) . "/old_head.gif\" border=0 align=absmiddle>&nbsp;";          // 답글이 아닐때
    }
    if ($data['headnum'] <= -2000000000) $icon = "<img src=\"" . e($dir) . "/notice_head.gif\" border=0 align=absmiddle>&nbsp;"; // 공지사항일때
    elseif ($data['is_secret'] == 1) $icon = "<img src=\"" . e($dir) . "/secret_head.gif\" border=0 align=absmiddle alt='비밀글입니다'>&nbsp;";
    return $icon;
}


// 회원 개인에게 주어지는 아이콘을 찾는 함수
// $type : 1 -> 이름앞에 나타나는 아이콘
// $type : 2 -> 이름을 대신하는 아이콘
function get_private_icon($no, $type) {
    if ($type == 1) $dir = "icon/private_icon/";
    elseif ($type == 2) $dir = "icon/private_name/";
    else return '';

    if (is_file($dir . $no . ".gif")) return $dir . $no . ".gif";
}


// 이름 앞에 붙는 얼굴 아이콘
function get_face($data, $check = 0) {
    global $group;

    $data += array('ismember' => '', 'islevel' => '');
    $face_image = '';

    // 이름앞에 붙는 아이콘 정의;;
    if ($group['use_icon'] == 0) {
        if ($data['ismember']) {
            if ($data['islevel'] == 2) $face_image = "<img src=images/admin2_face.gif border=0 align=absmiddle>";
            elseif ($data['islevel'] == 1) $face_image = "<img src=images/admin1_face.gif border=0 align=absmiddle>";
            else {
                if ($group['icon']) $face_image = "<img src=\"icon/" . e($group['icon']) . "\" border=0 align=absmiddle>";
                else $face_image = "<img src=images/member_face.gif border=0 align=absmiddle>";
            }
        }
        else $face_image = "<img src=images/blank_face.gif border=0 align=absmiddle> ";
    }

    $temp_name = get_private_icon($data['ismember'], "1");
    if ($temp_name) $face_image = "<img src='" . e($temp_name) . "' border=0 align=absmiddle>";

    if ($group['use_icon'] < 2 && $data['ismember']) $face_image .= "<b>";

    return $face_image;
}


// 게시판 관리자인지 체크하는 부분
function check_board_master($member, $board_num) {
    $temp = explode(',', $member['board_name'] ?? '');
    for ($i = 0; $i < count($temp); $i++) {
        $t = trim($temp[$i]);
        if ($t && $t == $board_num) return 1;
    }
    return 0;
}

/******************************************************************************
 * CSRF 방어
 *  - 서버측: 출력버퍼 콜백이 same-origin 폼에 hidden 토큰 자동주입 (외부 스킨 포함)
 *  - 클라측: 삽입된 JS가 submit 시점에 동적 생성 폼까지 토큰 주입 + AJAX 헤더
 *  - 검증: 상태변경 엔드포인트에서 check_csrf() 호출
 *****************************************************************************/

// 상태변경 요청에 유효 토큰 필수. 토큰은 POST 필드 / (allow_get 시)GET / X-CSRF-Token 헤더에서 수용
// $allow_get: vote 등 링크(GET) 기반 네비게이션 엔드포인트 전용. GET 토큰은 referer/history 노출이 있어
//             저심각도 엔드포인트에만 사용. 일반 상태변경은 POST-only 유지
function check_csrf(bool $allow_get = false) {
    if (!$allow_get && Request::method() !== 'POST') Error("정상적인 접근이 아닙니다.");
    $token = Request::req('zb_csrf_token'); // POST 우선, GET 폴백
    if ($token === '') $token = Request::header('X-CSRF-Token');
    if (!Session::checkCsrf($token)) Error("정상적인 접근이 아닙니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.");
}

// 출력 HTML의 폼에 토큰 hidden 필드 주입 + 동적 폼/AJAX용 JS 삽입
function zb_csrf_process_output(string $html, string $token, string $host): string {
    foreach (headers_list() as $h) {
        $hl = strtolower($h);
        if (strpos($hl, 'content-disposition:') === 0 && strpos($hl, 'attachment') !== false) return $html;
        if (strpos($hl, 'content-type:') === 0 && strpos($hl, 'text/html') === false) return $html;
    }
    if (!preg_match('/<(html|body|form)\b/i', $html)) return $html;

    $field = '<input type="hidden" name="zb_csrf_token" value="' . $token . '" />';

    // method=post 폼에만 주입. action이 외부 호스트인 폼은 토큰 유출 방지로 제외
    $html = preg_replace_callback('/<form\b[^>]*>/i', function ($m) use ($field, $host) {
        $tag = $m[0];
        if (!preg_match('/\bmethod\s*=\s*("|\'|)\s*post\b/i', $tag)) return $tag;
        if (preg_match('#\baction\s*=\s*("|\'|)\s*(?:[a-z][a-z0-9+.\-]*:)?//([^/"\'\s>]+)#i', $tag, $mm)) {
            if (strcasecmp($mm[2], $host) !== 0) return $tag;
        }
        return $tag . $field;
    }, $html);

    // 동적 생성 폼/AJAX 커버용 스크립트를 </body> 앞(없으면 끝)에 삽입
    $js = '<script>' . zb_csrf_js($token) . '</script>';
    if (stripos($html, '</body>') !== false) return preg_replace('#</body>#i', $js . '</body>', $html, 1);

    return $html . $js;
}

// 클라이언트 스크립트
function zb_csrf_js(string $token): string {
    $t = json_encode($token);
    return <<<JS
(function(){var T={$t};
function same(u){try{return new URL(u,location.href).host===location.host;}catch(e){return true;}}
function ensure(f){if(!f||!f.tagName||f.tagName.toLowerCase()!=='form')return;
if((f.method||'get').toLowerCase()!=='post')return;
var a=f.getAttribute('action');if(a&&!same(a))return;
var x=f.querySelector('input[name=zb_csrf_token]');
if(!x){x=document.createElement('input');x.type='hidden';x.name='zb_csrf_token';f.appendChild(x);}x.value=T;}
document.addEventListener('submit',function(e){ensure(e.target);},true);
if(window.HTMLFormElement){var ns=HTMLFormElement.prototype.submit;HTMLFormElement.prototype.submit=function(){ensure(this);return ns.apply(this,arguments);};}
if(window.XMLHttpRequest){var o=XMLHttpRequest.prototype.open,s=XMLHttpRequest.prototype.send;
XMLHttpRequest.prototype.open=function(m,u){this._zc=(m||'').toUpperCase()!=='GET'&&same(u);return o.apply(this,arguments);};
XMLHttpRequest.prototype.send=function(){if(this._zc){try{this.setRequestHeader('X-CSRF-Token',T);}catch(e){}}return s.apply(this,arguments);};}
if(window.fetch){var of=window.fetch;window.fetch=function(i,n){n=n||{};
var m=(n.method||(typeof i==='object'&&i&&i.method)||'GET').toUpperCase();
var u=(typeof i==='string')?i:(i&&i.url)||'';
if(m!=='GET'&&same(u)){var h=new Headers(n.headers||(typeof i==='object'&&i&&i.headers)||{});h.set('X-CSRF-Token',T);n.headers=h;}
return of.call(this,i,n);};}})();
JS;
}

//  초기 헤더를 뿌려주는 부분;;;;
function head($body = '', $scriptfile = '') {

    global $group, $setup, $dir, $member, $id, $_head_executived, $width;

    if ($_head_executived) return;
    $_head_executived = true;

    if (!is_array($setup)) $setup = array();
    if (!is_array($group)) $group = array();
    $setup += array('skinname' => '', 'use_formmail' => '', 'title' => '', 'bg_color' => '', 'bg_image' => '', 'header_url' => '', 'header' => '');
    $group += array('header_url' => '', 'header' => '');
    if (!isset($width)) $width = '';

    $license_file = __DIR__ . '/license.txt';

    $license = zReadFile($license_file);

    echo "<!--\n" . $license . "\n-->\n";

    if (!preg_match('/member_/i', Request::scriptName())) $stylefile = "skin/{$setup['skinname']}/style.css";
    else $stylefile = 'style.css';

    if ($setup['use_formmail']) $zbLayerScript = zReadFile(__DIR__ . '/script/script_zbLayer.php');

    // html 시작부분 출력
    if ($setup['skinname']) {
        ?>
<html lang="ko"> 
<head>
	<title><?= e($setup['title']) ?></title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel=StyleSheet HREF=<?= $stylefile ?> type=text/css title=style>
	<?php if ($setup['use_formmail']) echo $zbLayerScript; ?>
	<?php if ($scriptfile) include "script/" . $scriptfile; ?>
</head>
<body topmargin='0'  leftmargin='0' marginwidth='0' marginheight='0' <?= $body ?><?php

        if ($setup['bg_color']) echo " bgcolor=\"" . e($setup['bg_color']) . "\" ";
        if ($setup['bg_image']) echo " background=\"" . e($setup['bg_image']) . "\" ";

        ?>>
			<?php
        if ($group['header_url'] && isValidIncludePath($group['header_url'])) { include $group['header_url']; }
        if ($setup['header_url'] && isValidIncludePath($setup['header_url'])) { include $setup['header_url']; }
        if ($group['header']) echo $group['header'];
        if ($setup['header']) echo $setup['header'];
        ?>
			<table border=0 cellspacing=0 cellpadding=0 width=<?= $width ?> height=1 style="table-layout:fixed;"><col width=100%></col><tr><td><img src=images/t.gif border=0 width=98% height=1 name=zb_get_table_width><br><img src=images/t.gif border=0 name=zb_target_resize width=1 height=1></td></tr></table>
			<?php
    } else {
        ?>
<html lang="ko">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel=StyleSheet HREF=style.css type=text/css title=style>
</head>
<body topmargin='0'  leftmargin='0' marginwidth='0' marginheight='0' <?= $body ?>>
			<?php
        	if ($group['header_url'] && isValidIncludePath($group['header_url'])) { include $group['header_url']; }
        if ($group['header']) echo $group['header'];
    }

}



// 푸터 부분 출력
function foot() {

    global $width, $group, $setup, $_startTime , $_queryTime , $_foot_executived, $_skinTime, $_sessionStart, $_sessionEnd, $_nowConnectStart, $_nowConnectEnd, $_dbTime, $_listCheckTime, $_zbResizeCheck;

    if ($_foot_executived) return;
    $_foot_executived = true;

    if (!is_array($setup)) $setup = array();
    if (!is_array($group)) $group = array();
    $setup += array('skinname' => '', 'footer' => '', 'footer_url' => '');
    $group += array('footer' => '', 'footer_url' => '');
    if (!isset($width)) $width = '';

    $maker_file_name = "skin/{$setup['skinname']}/maker.txt";

    if (is_file($maker_file_name) && is_readable($maker_file_name)) $maker_file = file($maker_file_name);
    else $maker_file = array();

    if (isset($maker_file[0])) $maker = "/ skin by {$maker_file[0]}";
    else $maker = '';

    if ($setup['skinname']) {
        ?>

			<table border=0 cellpadding=0 cellspacing=0 height=20 width=<?= $width ?>>
			<tr>
				<td align=right style=font-family:tahoma,굴림;font-size:8pt;line-height:150%;letter-spacing:0px>
					<font style=font-size:7pt>Copyright 1999-<?= date('Y') ?></font> <a href=http://www.zeroboard.com target=_blank onfocus=blur()><font style=font-family:tahoma,굴림;font-size:8pt;>Zeroboard</a> <?= $maker ?>
				</td>   
			</tr>
			</table>

			<?php
        if ($_zbResizeCheck) {
            ?>
			<!-- 이미지 리사이즈를 위해서 처리하는 부분 -->
			<script>
				function zb_img_check(){
					var zb_main_table_width = document.zb_get_table_width.width;
					var zb_target_resize_num = document.zb_target_resize.length;
					for(i=0;i<zb_target_resize_num;i++){ 
						if(document.zb_target_resize[i].width > zb_main_table_width) {
							document.zb_target_resize[i].width = zb_main_table_width;
						}
					}
				}
				window.onload = zb_img_check;
			</script>

			<?php
        }

        if ($setup['footer']) echo $setup['footer'];
        if ($group['footer']) echo $group['footer'];
        if ($setup['footer_url'] && isValidIncludePath($setup['footer_url'])) { include $setup['footer_url']; }
        if ($group['footer_url'] && isValidIncludePath($group['footer_url'])) { include $group['footer_url']; }
        ?>

</body>
</html>
			<?php

    } else {

        if ($group['footer']) echo $group['footer'];
        if ($group['footer_url'] && isValidIncludePath($group['footer_url'])) { include $group['footer_url']; }

        ?>
			</body>
			</html>
			<?php
    }

    $_phpExcutedTime = (getmicrotime() - $_startTime) - ($_sessionEnd - $_sessionStart) - ($_nowConnectEnd - $_nowConnectStart) - $_dbTime - $_skinTime;
    // 실행시간 출력
    echo "\n\n<!--";
    if ($_sessionStart && $_sessionEnd)  		echo "\n Session Executed  : " . sprintf("%0.4f", $_sessionEnd - $_sessionStart);
    if ($_nowConnectStart && $_nowConnectEnd) 	echo "\n Connect Checked  : " . sprintf("%0.4f", $_nowConnectEnd - $_nowConnectStart);
    if ($_dbTime)  								echo "\n Query Executed  : " . sprintf("%0.3f", $_dbTime);
    if ($_phpExcutedTime)  						echo "\n PHP Executed  : " . sprintf("%0.3f", $_phpExcutedTime);
    if ($_listCheckTime) 						echo "\n Check Lists : " . sprintf("%0.3f", $_listCheckTime);
    if ($_skinTime) 								echo "\n Skins Executed  : " . sprintf("%0.3f", $_skinTime);
    if ($_startTime) 							echo "\n Total Executed Time : " . sprintf("%0.3f", getmicrotime() - $_startTime);
    echo "\n-->\n";
}


// zbLayer 출력
function check_zbLayer($data) {
    global $zbLayer, $setup, $member, $is_admin, $id, $_zbCheckNum;
    $_zbCount = 0;
    $traceID = $traceType = '';
    $isAdmin = $isMember = 0;
    if ($setup['use_formmail']) {
        if (!$_zbCheckNum) $_zbCheckNum = 0;
        // 코멘트 행 등 homepage/email 컬럼이 없는 데이타 대비
        $data += array('name' => '', 'homepage' => '', 'email' => '', 'ismember' => '');
        $data['name'] = urlencode($data['name']);

        if ($data['homepage']){
            preg_match('#^https?://#', $data['homepage'], $m);
            $scheme = $m[0] ?? '';
            $data['homepage'] = str_replace($scheme, '', $data['homepage']);
            $data['homepage'] = urlencode($data['homepage']);
            $data['homepage'] = $scheme . $data['homepage'];
        }

        $data['email'] = base64_encode($data['email']);

        $_zbCheckNum++;
        $_zbCount = 1;

        if (($member['is_admin'] == 1 || $member['is_admin'] == 2) && $data['ismember']) {
            $traceID = $data['ismember'];
            $traceType = 't';
            $isAdmin = 1;
        } elseif (($member['is_admin'] == 1 || $member['is_admin'] == 2) && !$data['ismember']) {
            $traceID = $data['name'];
            $traceType = 'tn';
            $isAdmin = 1;
        }

        if ($member['no']) $isMember = 1;

        if ($data['ismember'] < 1) $data['ismember'] = '';

        $zbLayer = $zbLayer . "\nprint_ZBlayer('zbLayer{$_zbCheckNum}', '" . e_js($data['homepage']) . "', '" . e_js($data['email']) . "', '" . e_js($data['ismember']) . "', '" . e_js($id) . "', '" . e_js($data['name']) . "', '" . e_js($traceID) . "', '" . e_js($traceType) . "', '" . $isAdmin . "', '" . $isMember . "');";
    }
    return $_zbCount;
}


// 에러 메세지 출력
function error($message, $url = '') {
    global $setup, $dir;

    if (!is_array($setup)) $setup = array();
    $setup += array('skinname' => '');

    $dir = "skin/" . $setup['skinname'];

    if ($url === "window.close") {
        $message = str_replace('<br>', "\n", $message);
        ?>
			<script>
				alert("<?= e_js($message) ?>");
				window.close();
			</script>
			<?php
    } else {

        $url = e_js($url);
        $message = str_replace(['&lt;br&gt;', '&lt;b&gt;', '&lt;/b&gt;'], ['<br>', '<b>', '</b>'], e($message));
        
        head();

        if ($setup['skinname']) {
            include "skin/{$setup['skinname']}/error.php";
        } else {
            include _ZB_PATH . "error.php";
        }

        foot();

    }

    exit();
}


// 게시판 설정을 읽어옴
function get_table_attrib($id) {

    global $connect, $admin_table;

    $data = $connect->row("SELECT * FROM {$admin_table} WHERE name = ?", [$id]);

    $data['table_width'] = $data['table_width'] ?? 95;
    if ($data['table_width'] <= 100) $data['table_width'] = $data['table_width'] . '%';

    // 원래는 IP를 보여주는 기능인데, DB 변경을 피하기 위해서 이미지 박스 사용 권한으로 변경하여 사용
    if (empty($data['use_showip'])) $data['use_showip'] = 1;
    $data['grant_imagebox'] = $data['use_showip'];

    $data += array('no' => '', 'group_no' => '', 'name' => '', 'total_article' => '', 'skinname' => '', 'header' => '', 'footer' => '', 'title' => '', 'header_url' => '', 'footer_url' => '', 'bg_image' => '', 'bg_color' => '',
        'table_width' => '', 'memo_num' => '', 'page_num' => '', 'only_board' => '', 'cut_length' => '', 'use_category' => '', 'use_html' => '', 'use_filter' => '', 'use_status' => '', 'max_upload_size' => '', 'use_pds' => '', 'pds_ext1' => '',
        'pds_ext2' => '', 'use_homelink' => '', 'use_filelink' => '', 'use_cart' => '', 'use_autolink' => '', 'use_showip' => '', 'use_comment' => '', 'use_formmail' => '', 'use_showreply' => '', 'use_secret' => '', 'use_alllist' => '', 'grant_html' => '',
        'grant_list' => '', 'grant_view' => '', 'grant_comment' => '', 'grant_write' => '', 'grant_reply' => '', 'grant_delete' => '', 'grant_notice' => '', 'grant_view_secret' => '', 'filter' => '', 'avoid_tag' => '', 'avoid_ip' => '');

    return $data;
}


// 게시판의 생성유무 검사
function istable($str) {
    global $connect;

    return $connect->tableExists($str);
}


// 현재 아이피와 주어진 아이피 리스트를 비교하여 아이피 블럭 대상자인지 검사
function check_blockip() {
    global $setup;
    $avoid_ip = explode(",", $setup['avoid_ip'] ?? '');
    $count = count($avoid_ip);
    for ($i = 0; $i < $count; $i++) {
        if (!isblank($avoid_ip[$i]) && preg_match('/^' . preg_quote(trim($avoid_ip[$i]), '/') . '/', Request::clientIp())) Error("차단당한 IP 주소입니다.");
    }
}


// 접속자수 체크
function getNowConnector($filename, $div) {
    global $_zbDefaultSetup;
    $_str = trim(zReadFile($filename));
    $num = 0;
    if ($_str) {
        if (strpos($_str, "<?php exit('Access Denied');/*") === 0) {
            $_str = str_replace("<?php exit('Access Denied');/*", "", $_str);
            $_str = str_replace("*/?>", "", $_str);
            $_str = base64_decode($_str);
        } else {
            $_str = str_replace("<? die('Access Denied');/*", "", $_str);
            $_str = str_replace("*/?>", "", $_str);
        }

        $_connector = explode(":", $_str);
        $_sizeConnector = count($_connector);
        $_nowtime = date("YmdHi");
        $_realNowConnector = '';
        if ($_sizeConnector) {
            for ($i = 0; $i < $_sizeConnector; $i++) {
                $_time = substr($_connector[$i], 0, 12);
                $_div = substr($_connector[$i], 12);
                if ((int) $_time + $_zbDefaultSetup['nowconnect_time'] >= $_nowtime && $_div != $div) {
                    $_realNowConnector .= $_time . $_div . ":";
                    $num++;
                }
            }
        }
    }
    $_realNowConnector .= $_nowtime . $div;
    //check_fileislocked($filename);
    zWriteFile($filename, "<?php exit('Access Denied');/*" . base64_encode($_realNowConnector) . "*/?>");
    return $num;
}

// 접속자수 구하기
function getNowConnector_num($filename, $FLAG = false) {
    global $_zbDefaultSetup;
    $_str = trim(zReadFile($filename));
    $num = 0;
    if ($_str) {
        if (strpos($_str, "<?php exit('Access Denied');/*") === 0) {
            $_str = str_replace("<?php exit('Access Denied');/*", "", $_str);
            $_str = str_replace("*/?>", "", $_str);
            $_str = base64_decode($_str);
        } else {
            $_str = str_replace("<? die('Access Denied');/*", "", $_str);
            $_str = str_replace("*/?>", "", $_str);
        }

        $_connector = explode(":", $_str);
        $_sizeConnector = count($_connector);
        $_nowtime = date("YmdHi");
        $_realNowConnector = '';
        if ($_sizeConnector) {
            for ($i = 0; $i < $_sizeConnector; $i++) {
                $_time = substr($_connector[$i], 0, 12);
                $_div = substr($_connector[$i], 12);
                if ((int) $_time + $_zbDefaultSetup['nowconnect_time'] >= $_nowtime) {
                    $_realNowConnector .= $_time . $_div . ":";
                    $num++;
                }
            }
        }
    }
    if ($FLAG) {
        //check_fileislocked($filename);
        zWriteFile($filename, "<?php exit('Access Denied');/*" . base64_encode($_realNowConnector) . "*/?>");
    }
    return $num;
}


// 제로보드 자동 로그인 세션값이 있는지 판단해서 있으면 해당 값을 리턴
function getZBSessionID() {
    global $_zbDefaultSetup;

    $zbSessionID = Request::cookie('ZBSESSIONID');

    if (!$zbSessionID) return '';
    if (!preg_match('/^[a-f0-9]{64}$/', $zbSessionID)) return '';


    $str = zReadFile(_ZB_PATH . $_zbDefaultSetup['session_path'] . "/zbSessionID_" . $zbSessionID . ".php");
    $str = explode("\n", $str);

    if (count($str) < 4) {
        Response::removeCookie('ZBSESSIONID');
        return '';
    }

    $data = array();
    $data['no'] = base64_decode(trim($str[1]));
    $data['time'] = base64_decode(trim($str[2]));
    $data['key'] = base64_decode(trim($str[3]));

    $newZBSessionID = hash('sha256', $data['no'] . "-^A-" . $data['time'] . $data['key']);

    if ($newZBSessionID !== $zbSessionID) {
        Response::removeCookie('ZBSESSIONID');
        return '';
    }

    z_unlink(_ZB_PATH . $_zbDefaultSetup['session_path'] . "/zbSessionID_" . $zbSessionID . ".php");
    makeZBSessionID($data['no']);

    return $data;
}


// 제로보드 자동 로그인 세션값을 만드는 함수
function makeZBSessionID($no) {
    global $_zbDefaultSetup;
    $no = (int) $no;

    $key = bin2hex(random_bytes(16));
    $zbSessionID = hash('sha256', $no . "-^A-" . time() . $key);

    $newStr = "<?php /*\n" . base64_encode($no) . "\n" . base64_encode(time()) . "\n" . base64_encode($key) . "\n*/?>";

    zWriteFile(_ZB_PATH . $_zbDefaultSetup['session_path'] . "/zbSessionID_{$zbSessionID}.php", $newStr);

    Response::cookie('ZBSESSIONID', $zbSessionID, time() + 60 * 60 * 24 * 365);
}


// 제로보드 자동 로그인 세션값 파기시키는 함수
function destroyZBSessionID($no) {
    global $_zbDefaultSetup;
    $zbSessionID = Request::cookie('ZBSESSIONID');
    if (!preg_match('/^[a-f0-9]{64}$/', $zbSessionID)) return;

    z_unlink(_ZB_PATH . $_zbDefaultSetup['session_path'] . "/zbSessionID_{$zbSessionID}.php");

    Response::removeCookie('ZBSESSIONID');
}

// 제로보드의 기본 설정 파일을 읽어오는 함수
function getDefaultSetup() {
    $data = zReadFile(_ZB_PATH . 'setup.php');

    if (strpos($data, "<?php /*") === 0) {
        $data = str_replace("<?php /*", '', $data);
        $data = str_replace("*/?>", '', $data);
    } else {
        $data = str_replace("<?/*", "", $data);
        $data = str_replace("*/?>", "", $data);
    }

    $data = explode("\n", $data);
    $_c = count($data);
    $defaultSetup = array();
    for ($i = 0; $i < $_c; $i++) {
        if (!preg_match('/;/', $data[$i]) && strlen(trim($data[$i]))) {
            $tmpStr = explode('=', $data[$i], 2);
            $name = trim($tmpStr[0]);
            $value = trim($tmpStr[1] ?? '');
            $defaultSetup[$name] = $value;
        }
    }

    $defaultSetup += array('url' => '', 'sitename' => '', 'session_path' => '',
        'session_view_size' => '', 'session_vote_size' => '', 'login_time' => '',
        'nowconnect_enable' => '', 'nowconnect_refresh_time' => '', 'nowconnect_time' => '',
        'enable_hangul_id' => '', 'check_email' => '', 'memo_limit_time' => '',
        'site_timezone' => '');

    if ($defaultSetup['url'] === '') $defaultSetup['url'] = Request::header('Host');
    if ($defaultSetup['sitename'] === '') $defaultSetup['sitename'] = Request::header('Host');
    if ($defaultSetup['session_path'] === '') $defaultSetup['session_path'] = "data/__zbSessionTMP";
    if ($defaultSetup['session_view_size'] === '') $defaultSetup['session_view_size'] = 512;
    if ($defaultSetup['session_vote_size'] === '') $defaultSetup['session_vote_size'] = 256;
    if ($defaultSetup['login_time'] === '') $defaultSetup['login_time'] = 60 * 30;
    if ($defaultSetup['nowconnect_enable'] === '') $defaultSetup['nowconnect_enable'] = 'true';
    if ($defaultSetup['nowconnect_refresh_time'] === '') $defaultSetup['nowconnect_refresh_time'] = 60 * 3;
    if ($defaultSetup['nowconnect_time'] === '') $defaultSetup['nowconnect_time'] = 60 * 5;
    if ($defaultSetup['enable_hangul_id'] === '') $defaultSetup['enable_hangul_id'] = 'false';
    if ($defaultSetup['check_email'] === '') $defaultSetup['check_email'] = 'true';
    if ($defaultSetup['memo_limit_time'] === '') $defaultSetup['memo_limit_time'] = 7;
    if ($defaultSetup['site_timezone'] === '' || !in_array($defaultSetup['site_timezone'], timezone_identifiers_list(), true)) $defaultSetup['site_timezone'] = 'Asia/Seoul';
    $defaultSetup['memo_limit_time'] = 60 * 60 * 24 * (int) $defaultSetup['memo_limit_time'];

    return $defaultSetup;
}


/******************************************************************************
 * 일반 함수
*****************************************************************************/
// 빈문자열 경우 1을 리턴
function isblank($str) {
    $temp = str_replace('　', '', $str);
    $temp = str_replace("\n", '', $temp);
    $temp = strip_tags($temp);
    $temp = str_replace('&nbsp;', '', $temp);
    $temp = str_replace(' ', '', $temp);

    if (preg_match('/[^[:space:]]/', $temp)) return 0;

    return 1;
}


// 숫자일 경우 1을 리턴
function isnum($str) {
    if (preg_match("/[^0-9]/", $str)) return 0;

    return 1;
}


// 숫자, 영문자 일경우 1을 리턴
function isalNum($str) {
    if (preg_match("/[^0-9a-zA-Z_]/i", $str)) return 0;

    return 1;
}


// HTML Tag를 제거하는 함수
function del_html($str) {
    $str = str_replace( ">", "&gt;", $str );
    $str = str_replace( "<", "&lt;", $str );

    return $str;
}




// 주민등록번호 검사
function check_jumin($jumin) {
    $weight = '234567892345'; // 자리수 weight 지정
    $len = strlen($jumin);
    $sum = 0;

    if ($len <> 13) return false;

    for ($i = 0; $i < 12; $i++) {
        $sum = $sum + ((int) substr($jumin, $i, 1) * (int) substr($weight, $i, 1));
    }

    $rst = $sum % 11;
    $result = 11 - $rst;

    if ($result === 10) $result = 0;
    elseif ($result === 11) $result = 1;

    $ju13 = (int) substr($jumin, 12, 1);

    if ($result <> $ju13) return false;

    return true;
}


// E-mail 주소가 올바른지 검사
function ismail($str) {
    if (filter_var($str, FILTER_VALIDATE_EMAIL) !== false) return $str;
    else return '';
}

// E-mail 의 MX를 검색하여 실제 존재하는 메일인지 검사
function mail_mx_check($email) {
    if (!ismail($email)) return false;
    
    $pos = strrpos($email, '@');
    if ($pos === false) return false;

    $user = substr($email, 0, $pos);
    $host = substr($email, $pos + 1);

    if (checkdnsrr($host, 'MX') || checkdnsrr($host, 'A')) return true;
    else return false;
}


// 홈페이지 주소가 올바른지 검사
function isHomepage($str) {
    if (preg_match("#^https?://([a-z0-9\_\-\./~@?=&amp;-\#{5,}]+)#", $str)) return $str;
    else return '';
}


// URL, Mail을 자동으로 체크하여 링크만듬
function autolink($str) {
    // URL 치환
    $homepage_pattern = "/([^\"\'\=\>])(mms|https?|ftp|telnet)\:\/\/([^ \n\<\>\"\']+)/";
    $str = preg_replace($homepage_pattern, "\\1<a href=\"\\2://\\3\" target=_blank>\\2://\\3</a>", " " . $str);

    // 메일 치환
    $email_pattern = "/([ \n]+)([a-z0-9\_\-\.]+)@([a-z0-9\_\-\.]+)/";
    $str = preg_replace($email_pattern, "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", " " . $str);

    return $str;
}


// 파일 사이즈를 kb, mb에 맞추어서 변환해서 리턴
function getfilesize(int $size): string {
    if (!$size) return "0 Byte";
    if ($size < 1024) {
        return ($size . " Byte");
    } elseif ($size >= 1024 && $size < 1024 * 1024)  {
        return sprintf("%0.1f KB", $size / 1024);
    }
    else return sprintf("%0.2f MB", $size / (1024 * 1024));
}


// 문자열 끊기 (이상의 길이일때는 ... 로 표시)
function cut_str(string $msg, int $cut_size): string {
    if ($cut_size <= 0) return $msg;
    if (strpos($msg, '[re]') !== false) $cut_size += 4;

    if (preg_match_all('/./us', $msg, $m) === false) return $msg;

    $width = 0;
    $out = '';
    foreach ($m[0] as $ch) {
        $b0 = ord($ch[0]);
        if ($b0 < 0x80) {
            $cp = $b0;
        } elseif ($b0 < 0xE0) {
            $cp = (($b0 & 0x1F) << 6) | (ord($ch[1]) & 0x3F);
        } elseif ($b0 < 0xF0) {
            $cp = (($b0 & 0x0F) << 12) | ((ord($ch[1]) & 0x3F) << 6) | (ord($ch[2]) & 0x3F);
        } else {
            $cp = (($b0 & 0x07) << 18) | ((ord($ch[1]) & 0x3F) << 12) | ((ord($ch[2]) & 0x3F) << 6) | (ord($ch[3]) & 0x3F);
        }

        $wide =
        	($cp >= 0x1100 && $cp <= 0x115F) ||
        	($cp >= 0x2E80 && $cp <= 0x303E) ||
        	($cp >= 0x3041 && $cp <= 0x33FF) ||
        	($cp >= 0x3400 && $cp <= 0x4DBF) ||
        	($cp >= 0x4E00 && $cp <= 0x9FFF) ||
        	($cp >= 0xA000 && $cp <= 0xA4CF) ||
        	($cp >= 0xAC00 && $cp <= 0xD7A3) ||
        	($cp >= 0xF900 && $cp <= 0xFAFF) ||
        	($cp >= 0xFE30 && $cp <= 0xFE4F) ||
        	($cp >= 0xFF00 && $cp <= 0xFF60) ||
        	($cp >= 0xFFE0 && $cp <= 0xFFE6) ||
        	($cp >= 0x20000 && $cp <= 0x3FFFD);

        $w = $wide ? 2 : 1;
        if ($width + $w > $cut_size) return $out . '...';
        $out .= $ch;
        $width += $w;
    }
    return $out;
}

// 페이지 이동 스크립트
function movepage($url) {
    global $connect;

    $url = e($url);
    echo "<meta http-equiv=\"refresh\" content=\"0; url=$url\">";
    exit();
}

// input 또는 textarea의 사이즈를 넷쓰와 익스일때 구분하여 리턴
function size($size) {
    global $browser;

    if (!$browser) return " size=" . ($size * 0.6) . " ";
    else return " size={$size} ";
}

function size2($size) {
    global $browser;

    if (!$browser) return " cols=" . ($size * 0.6) . " ";
    else return " cols={$size} ";
}


// 메일 보내는 함수
function zb_sendmail($type, $to, $to_name, $from, $from_name, $subject, $comment, $cc = '', $bcc = '') {
    if (!ismail($to)) return false;
    if (!ismail($from)) return false;
    if ($cc && !ismail($cc)) return false;
    if ($bcc && !ismail($bcc)) return false;

    $recipient = str_replace(["\r", "\n", "\0"], '', "{$to_name} <{$to}>");
    $subject = str_replace(["\r", "\n", "\0"], '', (string) $subject);
    $from = str_replace(["\r", "\n", "\0"], '', $from);
    $from_name = str_replace(["\r", "\n", "\0"], '', $from_name);
    $cc = str_replace(["\r", "\n", "\0"], '', $cc);
    $bcc = str_replace(["\r", "\n", "\0"], '', $bcc);


    if ($type == 1) $comment = nl2br($comment);

    $headers = "From: {$from_name} <{$from}>\r\n" .
        "X-Sender: <{$from}>\r\n" .
        "X-Mailer: PHP\r\n" .
        "X-Priority: 1\r\n" .
        "Return-Path: <{$from}>\r\n";

    if (!$type) $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    else $headers .= "Content-Type: text/html; charset=utf-8\r\n";


    if ($cc) $headers .= "Cc: {$cc}\r\n";
    if ($bcc) $headers .= "Bcc: {$bcc}\r\n";

    return mail($recipient, $subject, $comment, $headers);

}

// 지정된 디렉토리의 파일 정보를 구함
function get_dirinfo($path) {
    $handle = opendir($path);
    $dir = array();

    while (($info = readdir($handle)) !== false) {
        if ($info !== '.' && $info !== '..') {
            $dir[] = $info;
        }
    }

    closedir($handle);
    return $dir;
}

// 파일을 삭제하는 함수
function z_unlink(string $filename): bool {

    $filename = realpath($filename);
    if ($filename === false) return false;

    if (!is_file($filename) || !is_writable(dirname($filename))) return false;

    return unlink($filename);
}

// 지정된 파일의 내용을 읽어옴
function zReadFile(string $filename): string {
    if (!is_file($filename) || !is_readable($filename)) return '';

    $str = file_get_contents($filename);

    return $str !== false ? $str : '';
}

// 지정된 파일에 주어진 데이타를 씀
function zWriteFile(string $filename, string $str) {
    $dir = realpath(dirname($filename));
    if ($dir === false) return;

    $filename = $dir . DIRECTORY_SEPARATOR . basename($filename);

    if (file_exists($filename)) {
        if (!is_file($filename) || !is_writable($filename)) return;
    } elseif (!is_writable($dir)) {
        return;
    }

    $f = fopen($filename, 'w');
    $lock = flock($f, 2);
    if ($lock) {
        fwrite($f, $str);
    }
    flock($f, 3);
    fclose($f);
}

// 지정된 파일이 Locking중인지 검사
function check_fileislocked($filename) {
    $f = @fopen($filename, 'w');
    $count = 0;
    $break = true;
    while (!@flock($f, 2)) {
        $count++;
        if ($count > 10) {
            $break = false;
            break;
        }
    }
    if ($break != false) @flock($f, 3);
    @fclose($f);
}

// 순환적으로 디렉토리를 삭제
function zRmDir($path) {
    if (!is_dir($path) || !is_readable($path) || !is_writable($path)) return;

    $directory = dir($path);
    while (($entry = $directory->read()) !== false) {
        if ($entry !== '.' && $entry !== '..') {
            if (is_dir($path . '/' . $entry)) {
                zRmDir($path . '/' . $entry);
            } else {
                z_unlink($path . '/' . $entry);
            }
        }
    }
    $directory->close();
    rmdir($path);
}



function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
}

function e_js(string $value): string {
    $json = json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS
        | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

    if ($json === false) return '';

    return substr($json, 1, -1);
}

function isSafeRedirect(string $url): bool {
    if ($url === '') return false;
    if (strpbrk($url, "\r\n\t") !== false) return false;
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url)) return false;
    if (preg_match('#^[/\\\\]{2}#', $url)) return false;
    return true;
}

function loadConfig(): void {

    $config_file = _ZB_PATH . 'config.php';

    if (!is_file($config_file)) error('config.php파일이 없습니다.<br>DB설정을 먼저 하십시오', 'install.php');
    if (!is_readable($config_file)) error('config.php 파일에 대한 읽기 권한이 없습니다.');

    require_once $config_file;

    if (!defined('DB_HOST')
    	|| !defined('DB_USER')
        || !defined('DB_PASS')
    	|| !defined('DB_NAME')
    ) error('config.php 파일이 잘못 구성되어 있습니다.');
}

function isValidIncludePath(string $path): bool {
    if (strlen($path) > 255) return false;

    if (strpos($path, '://') !== false
    	|| strpos($path, "\0") !== false
    	|| preg_match('#^data:#i', $path)
    	|| (DIRECTORY_SEPARATOR == '\\' && preg_match('#^(//|\\\\\\\\)#', $path))) return false;

    if (!is_file($path) || !is_readable($path)) return false;

    if (!preg_match('/\.(php|html?)$/i', $path)) return false;

    $real = realpath($path);
    $base = realpath(_ZB_PATH);

    if ($real === false || $base === false) return false;
    if ($real !== false && strpos($real, $base . DIRECTORY_SEPARATOR) !== 0) return false;

    foreach (['data', 'icon'] as $up) {
        $u = realpath(_ZB_PATH . $up);
        if ($u !== false && strpos($real, $u . DIRECTORY_SEPARATOR) === 0) return false;
    }

    return true;
}

function createHash(string $plain): string {
    if (strlen($plain) > 72) throw new InvalidArgumentException('Password too long');

    return password_hash($plain, PASSWORD_DEFAULT);
}

function verifyHash(string $plain, string $hash): bool {
    return password_verify($plain, $hash);
}

function needsRehash(string $hash): bool {
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}

function createIndexFile(string $path, string $fileName = 'index.php'): bool {
    if ($fileName === '' || $fileName === '.' || $fileName === '..'
        || $fileName !== basename($fileName)) {
        throw new InvalidArgumentException('Filename must not contain path components');
    }
    $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

    if (!is_dir($path) || !is_writable($path) || file_exists($filePath)) return false;

    return touch($filePath);
}

if (!function_exists('mb_strlen')) {
    function mb_strlen(string $string, ?string $encoding = null): int {
        return count(preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY));
    }
}
?>