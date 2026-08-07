<?php
/***************************************************************************
 * 공통 파일 include
 **************************************************************************/
require_once __DIR__ . '/_head.php';

$no = (int) Request::req('no');
$filenum = (int) Request::req('filenum');

// 리퍼러 체크
$referer_hdr = Request::header('Referer');
if ($referer_hdr === '' || stripos($referer_hdr, Request::header('Host')) === false) exit();

/***************************************************************************
 * 게시판 설정 체크
 **************************************************************************/

// 사용권한 체크
if ($setup['grant_view'] < $member['level'] && !$is_admin) Error("사용권한이 없습니다", "login.php?id=$id&page=$page&page_num=$page_num&category=$category&sn=$sn&ss=$ss&sc=$sc&keyword=$keyword&no=$no&file=zboard.php");

if ($filenum !== 1 && $filenum !== 2) Error('선택하신 파일이 존재하지 않습니다');

$data = $connect->row("SELECT * FROM `{$t_board}_{$id}` WHERE no = ?", [$no]);

if (empty($data)) Error('선택하신 게시물이 존재하지 않습니다');

if ($data['is_secret'] && !$is_admin && $data['ismember'] != $member['no'] && $member['level'] > $setup['grant_view_secret'] && Session::get('zb_s_check', '') !== $setup['no'] . "_" . $no) {
    error("비밀글을 열람할 권한이 없습니다");
}

// 다운로드;;
$filename = $data["file_name" . $filenum];
$s_filename = basename($data["s_file_name" . $filenum]);

if ($filename === '' || !is_file($filename) || !is_readable($filename)) Error('선택하신 파일이 존재하지 않습니다');

$real = realpath($filename);
$base = realpath(_ZB_PATH . 'data/') . DIRECTORY_SEPARATOR;

if ($real === false || $base === false || strpos($real, $base) !== 0) Error('선택하신 파일이 존재하지 않습니다');

// 현재글의 Download 수를 올림;;
if ($filenum == 1) {
    $connect->exec("UPDATE `{$t_board}_{$id}` SET download1 = download1 + 1 WHERE no = ?", [$no]);
} elseif ($filenum == 2) {
    $connect->exec("UPDATE `{$t_board}_{$id}` SET download2 = download2 + 1 WHERE no = ?", [$no]);
}

$fallback = addcslashes($s_filename, '"\\');
$encoded = rawurlencode($s_filename);

Response::header('Content-Description', 'File Transfer');
Response::header('Content-Type', 'application/octet-stream');
Response::header('Content-Disposition', 'attachment; filename="' . $fallback . '"; filename*=UTF-8\'\'' . $encoded);
Response::header('Expires', '0');
Response::header('Cache-Control', 'must-revalidate');
Response::header('Pragma', 'public');
Response::header('Content-Length', filesize($filename));

flush();
readfile($filename);

exit();

?>
