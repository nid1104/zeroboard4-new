<?php
require_once __DIR__ . '/lib.php';

$connect = dbConn();

// 관리자가 1명이상 있을경우 바로 로그인 페이지로...
$temp = $connect->value("SELECT COUNT(*) FROM {$member_table} WHERE is_admin = '1' ");
if ($temp) {
    Response::redirect("admin.php");
    exit();
}

// 빈문자열인지를 검사
if (isBlank($user_id = Request::post('user_id'))) Error("아이디를 입력하셔야 합니다", "");
if (isBlank($password1 = Request::post('password1'))) Error("비밀번호를 입력하셔야 합니다", "");
if (isBlank($password2 = Request::post('password2'))) Error("비밀번호 확인을 입력하셔야 합니다", "");
if ($password1 !== $password2) Error("비밀번호와 비밀번호 확인이 일치하지 않습니다", "");
if (isBlank($name = Request::post('name'))) Error("이름을 입력하셔야 합니다", "");

// 관리자 정보 입력
$hash = createHash($password1);
$connect->exec("INSERT INTO {$member_table} (group_no, user_id, password, name, is_admin, reg_date, level) VALUES (1, ?, ?, ?, '1', ?, '1')", [$user_id, $hash, $name, time()]);

Response::redirect("admin.php");
?>
