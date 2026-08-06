<?php
/*******************************************************************************
 * Zeroboard 4.1 pl2 외부 로그인 파일
 *
 * 이 파일은 외부로그인으로 사용할시에 사용하시면 됩니다.
 *
 * 사용방법은 다음과 같습니다.
 *
 * 외부로그인을 원하시는 문서의 제일 상단에 다음과 같이 입력하세요
 *
 * <?php
 *   include _ZB_PATH."outlogin.php";
 * ?>
 * 
 *
 * 그런후 외부로그인 폼이나 로그인 상태를 표시하고 싶은곳에 다음과 같이 입력하세요
 *
 * <?php print_outlogin("스킨이름","그룹번호","허용레벨");?>
 *
 *
 * 위에서 "/home/계정 아이디/public_html/제로보드 경로/" 라는 것은 제로보드의 절대 경로를 나타냅니다.
 * 
 * 위에서 _ZB_URL 과 _ZB_PATH 는 꼭 적어 주셔야 합니다.
 *
 * 절대경로는 관리자 페이지 메인 제일 아래에 있습니다
 *
 * 위와 같이 하면 로그인이 되었을때는 로그인 정보가, 그렇지 않은 경우에는 로그인 폼이 나타납니다.
 *
 * 로그인 정보와 로그인 폼을 수정하실때에는 제로보드경로/outlogin_skin/ 에 있는 파일을 수정하시면 됩니다.
 *
 * 로그인 되어 있는 상태 : logged.html
 * 로그인 폼 : login.html
 *
 * 위의 두 파일을 수정 하시면 됩니다.
 *
 * 그리고 만약 원하는 html 파일에서 레벨에 따른 권한을 제한 하고 싶을때에는 $level 변수를 수정하시면 됩니다.
 *
 * 라고 하시면 9이하의 레벨만 해당 페이지에 접속이 가능합니다.
 *
 * 실제 적용 파일을 보시려면 outlogin_skin 디렉토리내의 index.html 파일을 열어보세요.
 *
 * 외부로그인 모양을 바꾸시려면 outloing_skin 디렉토리 내의 README.TXT 파일을 꼭 읽어 주시기 바랍니다.
 *
 *******************************************************************************/

if (!defined('_ZB_PATH')) exit();

global $member, $_head_php_excuted, $_zb_lib_included, $total_member_connect, $total_guest_connect;
global $a_member_join, $a_member_modify, $a_member_memo, $member_memo_icon, $memo_on_sound, $a_logout, $a_login, $id, $_outlogin_include;



// outlogin.php 파일이 include 되었는지를 체크
if (empty($_outlogin_include)) {
    $_outlogin_include = true;
} else {
    return false;
}

// 처음에 include 되었을때 필요한 파일을 include 하는 부분
if (empty($_head_php_excuted) && !defined('_zb_lib_included')) {

    // 제로보드 디렉토리 인지 체크
    if (!file_exists(_ZB_PATH . 'lib.php')) {
        echo "제로보드 디렉토리가 아닙니다";
        return;
    }

    // _head.php 읽음
    include _ZB_PATH . '_head.php';

}

if (!defined('_ZB_URL')) define('_ZB_URL', '');

// 외부로그인 출력 함수
function print_outlogin($skinname = 'default', $group_no = 1, $level = '10') {
    global $member, $_head_php_excuted, $_SESSION, $total_member_connect, $total_guest_connect;
    global $a_member_join, $a_member_modify, $a_member_memo, $member_memo_icon, $memo_on_sound, $a_logout, $a_login, $id;

    if ($level < $member['level']) {
        ?>
			<script>
				alert("인증된 회원만 접근 가능합니다");
				history.back();
			</script>
<?php
        			exit;
    }

    // 회원 정보가 있는지 없는지를 체크해서 해당 스킨 파일을 읽음
    if (!$member['no']) {

        $_outlogin_script = zReadFile(_ZB_PATH . "script/outlogin_script.php");

        $_outlogin_data = zReadFile(_ZB_PATH . "outlogin_skin/$skinname/login.html");

        $login_img = _ZB_URL . "outlogin_skin/$skinname/images/i_login.gif";
        $join_img = _ZB_URL . "outlogin_skin/$skinname/images/i_join.gif";
        $help_img = _ZB_URL . "outlogin_skin/$skinname/images/i_help.gif";

        $_outlogin_data = str_replace("[action]", _ZB_URL . "login_check.php", $_outlogin_data);
        $s_url = Request::uri();
        if ($id && stripos($s_url, $id) === false) {
            if (strpos($s_url, "?") !== false) $s_url = $s_url . "&id=$id";
            else $s_url = $s_url . "?id=$id";
        }
        $_outlogin_data = str_replace("[s_url]", urlencode($s_url), $_outlogin_data);

        $aUrl = "?group_no=" . $group_no;

        $_outlogin_data = str_replace("[member_join]", "<a href=# onclick=\"window.open('" . _ZB_URL . "member_join.php" . $aUrl . "','zbMemberJoin','width=560,height=590,toolbars=no,resizable=yes,scrollbars=yes')\"><img src=$join_img border=0></a>", $_outlogin_data);
        $_outlogin_data = str_replace("[login]", "<input type=image src=$login_img border=0>", $_outlogin_data);
        $_outlogin_data = str_replace("[lost_id]", "<a href=# onclick='window.open(\"" . _ZB_URL . "lostid.php\",\"lost_id\",\"width=400,height=200,toolbars=no,autoscrollbars=no\")'><img src=$help_img border=0></a>", $_outlogin_data);

        $_outlogin_data = str_replace("[total_member_connect]", number_format((int)$total_member_connect), $_outlogin_data);
        $_outlogin_data = str_replace("[total_guest_connect]", number_format((int)$total_guest_connect), $_outlogin_data);
        $_outlogin_data = str_replace("[total_connect]", number_format((int)$total_member_connect + (int)$total_guest_connect), $_outlogin_data);
        $_outlogin_data = str_replace("[dir]", _ZB_URL . "outlogin_skin/$skinname/images/", $_outlogin_data);

        if ($group_no) {
            $_outlogin_data = str_replace("</form>", "<input type=hidden name=group_no value='$group_no'></form>", $_outlogin_data);
        }

        print $_outlogin_script . "\n";
        print $_outlogin_data . "\n";

    } else {

        $_outlogin_data = zReadFile(_ZB_PATH . "outlogin_skin/$skinname/logged.html");
        $memo_on_sound_out = '';

        $memo_on_img = _ZB_URL . "outlogin_skin/$skinname/images/i_memo_on.gif";
        $memo_off_img = _ZB_URL . "outlogin_skin/$skinname/images/i_memo_off.gif";
        $logout_img = _ZB_URL . "outlogin_skin/$skinname/images/i_logout.gif";
        $info_img = _ZB_URL . "outlogin_skin/$skinname/images/i_info.gif";
        $admin_img = _ZB_URL . "outlogin_skin/$skinname/images/i_admin.gif";
        $memo_swf = _ZB_URL . "outlogin_skin/$skinname/images/i_memo.swf";

        if ($member['new_memo']) {
            $memo_on_image = "<img src=$memo_on_img border=0 align=absmiddle> ";
            $memo_on_sound_out = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0' width='0' height='0'><param name=menu value=false><param name=wmode value=transparent><param name=movie value='$memo_swf'><param name=quality value=low><param name='LOOP' value='false'><embed src='$memo_swf' quality=low pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash' type='application/x-shockwave-flash' width='0' height='0' loop='false' wmode=transparent menu='false'></embed></object>";
        } else {
            $memo_on_image = "<img src=$memo_off_img border=0 align=absmiddle> ";
        }

        $_outlogin_data = str_replace("[memo]", $memo_on_image, $_outlogin_data);
        $_outlogin_data = str_replace("[name]", $a_member_memo . "<b>" . e($member['name']) . "</b></a>", $_outlogin_data);
        $_outlogin_data = str_replace("[logout]", $a_logout . "<img src=$logout_img border=0></a>", $_outlogin_data);

        $_outlogin_data = str_replace("[info]", $a_member_modify . "<img src=$info_img border=0></a>", $_outlogin_data);
        if ($member['is_admin'] == 1 || $member['is_admin'] == 2) $_outlogin_data = str_replace("[admin]", "<a href=" . _ZB_URL . "admin.php target=blank><img src=$admin_img border=0></a>", $_outlogin_data);
        else $_outlogin_data = str_replace("[admin]", "", $_outlogin_data);
        $_outlogin_data = str_replace("[join_date]", date("Y/m/d", $member['reg_date']), $_outlogin_data);
        $_outlogin_data = str_replace("[level]", $member['level'], $_outlogin_data);
        $_outlogin_data = str_replace("[point]", number_format($member['point1'] * 10 + $member['point2']), $_outlogin_data);
        $_outlogin_data = str_replace("[write_num]", number_format($member['point1']), $_outlogin_data);
        $_outlogin_data = str_replace("[write_comment]", number_format($member['point2']), $_outlogin_data);
        $_outlogin_data = str_replace("[total_member_connect]", number_format((int)$total_member_connect), $_outlogin_data);
        $_outlogin_data = str_replace("[total_guest_connect]", number_format((int)$total_guest_connect), $_outlogin_data);
        $_outlogin_data = str_replace("[total_connect]", number_format((int)$total_member_connect + (int)$total_guest_connect), $_outlogin_data);
        $_outlogin_data = str_replace("[dir]", _ZB_URL . "outlogin_skin/$skinname/images/", $_outlogin_data);

        print $_outlogin_data . $memo_on_sound_out . "\n";

    }

    $a_member_join = "<Zeroboard";
    $a_member_modify = "<Zeroboard";
    $a_member_memo = "<Zeroboard";
    $member_memo_icon = "<Zeroboard";
    $memo_on_sound = "";
    $a_logout = "<Zeroboard";
    $a_login = "<Zeroboard";

}

/*******************************************************
 * 최근목록 보여주기를 위한 함수 지정
 ******************************************************/

// 최근 글 목록 (일반 게시판 형)
function print_bbs($skinname, $title, $id, $num = 5, $textlen = 30, $datetype = "Y/m/d") {
    global $connect, $t_board, $admin_table;

    if (!$skinname || !$id || !$title) return;
    if (!isalNum($id)) return;

    $str = zReadFile(_ZB_PATH . "latest_skin/" . $skinname . "/main.html");
    if (!$str) {
        echo "지정하신 " . e($skinname) . " 이라는 최근목록 스킨이 존재하지 않습니다<br>";
        return;
    }

    $setup = $connect->row("select use_alllist from $admin_table where name=?", [$id]);
    if (!empty($setup['use_alllist'])) $target = "zboard.php?id=" . $id;
    else $target = "view.php?id=" . $id;

    $rows = $connect->all("select * from {$t_board}_{$id} where is_secret=0 order by no desc limit ?", [(int)$num]);


    $tmpStr = explode("[loop]", $str);
    $header = $tmpStr[0];
    $tmpStr2 = explode("[/loop]", $tmpStr[1] ?? '');
    $loop = $tmpStr2[0];
    $footer = $tmpStr2[1] ?? '';

    // 공지사항 형식을 만들때 사용
    $offset = 0;
    if (stripos($header, "[notice_") !== false) {
        $data = $rows[0] ?? array('memo' => '', 'use_html' => 0, 'file_name1' => '', 'file_name2' => '', 'ismember' => '', 'subject' => '', 'reg_date' => 0);
        $offset = 1;
        $memo = $data['memo'];
        if ($data['use_html'] < 2) $memo = nl2br($memo);
        else $memo = strip_tags($memo);
        $filename1 = $data['file_name1'];
        $filename2 = $data['file_name2'];
        if (preg_match("/\.gif|\.jpg/i", $filename1))$uploadimage1 = "<img src=" . _ZB_URL . $filename1 . " border=0><br>"; else $uploadimage1 = "";
        if (preg_match("/\.gif|\.jpg/i", $filename2))$uploadimage2 = "<img src=" . _ZB_URL . $filename1 . " border=0><br>"; else $uploadimage2 = "";
        $memo = autolink($uploadimage1 . $uploadimage2 . $memo);
        if ($data['ismember']) {
            $imageBoxPattern = "/\[img\:(.+?)\.(jpg|gif)\,align\=([a-z]){0,}\,width\=([0-9]+)\,height\=([0-9]+)\,vspace\=([0-9]+)\,hspace\=([0-9]+)\,border\=([0-9]+)\]/i";
            $memo = preg_replace($imageBoxPattern, "<img src='" . _ZB_URL . "icon/member_image_box/$data[ismember]/\\1.\\2' align='\\3' width='\\4' height='\\5' vspace='\\6' hspace='\\7' border='\\8'>", $memo);
        }
        $subject = e(cut_str($data['subject'], $textlen)) . "</font></b>";
        $date = date($datetype, (int)$data['reg_date']);
        $header = str_replace("[notice_memo]", $memo, $header);
        $header = str_replace("[notice_subject]", $subject, $header);
        $header = str_replace("[notice_date]", $date, $header);
    }

    $main_data = "";
    foreach (array_slice($rows, $offset) as $data) {
        $name = e($data['name']);
        $subject = e(cut_str($data['subject'], $textlen)) . "</font></b>";
        $date = date($datetype, (int)$data['reg_date']);
        if ($data['total_comment']) $comment = "[" . $data['total_comment'] . "]"; else $comment = "";

        $main = $loop;
        $main = str_replace("[name]", $name, $main);
        $main = str_replace("[date]", $date, $main);
        $main = str_replace("[subject]", "<a href='" . _ZB_URL . $target . "&no=$data[no]'>" . $subject . "</a>", $main);
        $main = str_replace("[comment]", $comment, $main);
        $main_data .= "\n" . $main;
    }
    $list = $header . $main_data . $footer;
    $list = str_replace("[title]", "<a href='" . _ZB_URL . "zboard.php?id=" . e($id) . "'>" . e($title) . "</a>", $list);
    $list = str_replace("[dir]", _ZB_URL . "latest_skin/" . e($skinname) . "/images/", $list);

    echo $list;
}

// 최근 설문조사 (일반 게시판 형)
function print_survey($skinname, $title, $id, $textlen = 30) {
    global $connect, $t_board, $admin_table;

    if (!$skinname || !$id) return;
    if (!isalNum($id)) return;

    $str = zReadFile(_ZB_PATH . "latest_skin/" . $skinname . "/main.html");
    if (!$str) {
        echo "지정하신 " . e($skinname) . " 이라는 최근목록 스킨이 존재하지 않습니다<br>";
        return;
    }

    $setup = $connect->row("select use_alllist from $admin_table where name=?", [$id]);
    if (!empty($setup['use_alllist'])) $target = "zboard.php?id=" . $id;
    else $target = "view.php?id=" . e($id);

    $tmpData = $connect->row("select * from {$t_board}_{$id} order by headnum limit 1");
    $no = $tmpData['no'] ?? '';
    $headnum = $tmpData['headnum'] ?? '';
    $main_subject = "<a href='" . _ZB_URL . $target . "&no=" . e($no) . "'>" . e($tmpData['subject'] ?? '') . "</a>";
    if (!empty($tmpData['vote'])) $main_vote = "[" . e($tmpData['vote']) . "]";
    else $main_vote = "";

    $result = $connect->all("select * from {$t_board}_{$id} where headnum=? and arrangenum > 0 order by arrangenum", [$headnum]);

    $tmpStr = explode("[loop]", $str);
    $header = $tmpStr[0];
    $tmpStr2 = explode("[/loop]", $tmpStr[1] ?? '');
    $loop = $tmpStr2[0];
    $footer = $tmpStr2[1] ?? '';

    $main_data = "";
    foreach ($result as $data) {
        $subject = e(cut_str($data['subject'], $textlen)) . "</font></b>";
        if ($data['vote']) $vote = "[" . $data['vote'] . "]"; else $vote = "";
        $main = $loop;
        $main = str_replace("[subject]", "<a href='" . _ZB_URL . "apply_vote.php?id=$id&no=$no&sub_no=$data[no]'>" . $subject . "</a>", $main);
        $main = str_replace("[vote]", $vote, $main);
        $main_data .= "\n" . $main;
    }
    $list = $header . $main_data . $footer;
    $list = str_replace("[title]", "<a href='" . _ZB_URL . "zboard.php?id=" . e($id) . "'>" . $title . "</a>", $list);
    $list = str_replace("[dir]", _ZB_URL . "latest_skin/" . e($skinname) . "/images/", $list);
    $list = str_replace("[main_subject]", $main_subject, $list);
    $list = str_replace("[main_vote]", $main_vote, $list);

    echo $list;
}

// 갤러리 이미지 뽑아오는 스킨
function print_gallery($skinname, $title, $id, $num = 10, $xsize = 80, $ysize = 80, $xnum = 10) {
    global $connect, $t_board, $admin_table;

    if (!$skinname || !$id) return;
    if (!isalNum($id)) return;

    $str = zReadFile(_ZB_PATH . "latest_skin/" . $skinname . "/main.html");
    if (!$str) {
        echo "지정하신 $skinname 이라는 최근목록 스킨이 존재하지 않습니다<br>";
        return;
    }

    $setup = $connect->row("select use_alllist from $admin_table where name=?", [$id]);
    if (!empty($setup['use_alllist'])) $target = "zboard.php?id=" . $id;
    else $target = "view.php?id=" . $id;

    $result = $connect->all("select * from {$t_board}_{$id} order by no desc limit ?", [(int)$num]);

    $i = 0;
    $imgList = "";
    foreach ($result as $data) {

        if (preg_match("/\.gif|\.jpg/i", $data['file_name1'])) $filename = _ZB_URL . $data['file_name1'];
        elseif (preg_match("/\.gif|\.jpg/i", $data['file_name2'])) $filename = _ZB_URL . $data['file_name2'];
        else $filename = "";

        if ($filename) $imgList .= "<a href='" . _ZB_URL . $target . "&no=$data[no]'><img src='$filename' border=1 style=border-color:black width=$xsize height=$ysize vspacing=10 hspacing=10></a>";
        else $imgList .= "<a href='" . _ZB_URL . $target . "&no=$data[no]'><img src='[dir]t.gif' border=1 style=border-color:black width=$xsize height=$ysize vspacing=10 hspacing=10></a>";
        $i++;
        if ($i >= $xnum) {
            $imgList .= "<br>";
            $i = 0;
        } else {
            $imgList .= "&nbsp;";
        }
    }
    $str = str_replace("[title]","<a href='" . _ZB_URL . "zboard.php?id=" . $id . "'>" . $title . "</a>",$str);
    $str = str_replace("[img]",$imgList,$str);
    $str = str_replace("[dir]",_ZB_URL . "latest_skin/" . $skinname . "/images/",$str);
    echo $str;
}
?>
