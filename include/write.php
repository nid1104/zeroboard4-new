<?php
if (!defined('_ZB_PATH')) exit();

if (strpos($dir, "://") !== false || strpos($dir, "..") !== false) $dir = "./";

// 쿠키값을 이용;;
$zetyx = Request::cookieArr('zetyx');
$name = $zetyx['name'] ?? '';
$email = $zetyx['email'] ?? '';
$homepage = $zetyx['homepage'] ?? '';

$hide_start = $hide_end = '';
$hide_secret_start = $hide_secret_end = '';
$hide_notice_start = $hide_notice_end = '';

// 회원일때는 기본 입력사항 안보이게;;
if ($member['no']) { $hide_start = "<!--"; $hide_end = "-->"; }

// 비밀글 사용;;
if (!$setup['use_secret']) { $hide_secret_start = "<!--"; $hide_secret_end = "-->"; }

// 공지기능 사용하는지 안하는지 표시;;
if (!$is_admin || $mode == "reply") { $hide_notice_start = "<!--"; $hide_notice_end = "-->"; }

include $dir . "/write.php";
?>
