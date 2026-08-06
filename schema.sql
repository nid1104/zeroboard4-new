<?php
if (!defined('_ZB_PATH')) exit();

// 이 파일은 제틱스 보드에서 사용하는 테이블의 스키마정보를 가지고 있습니다.
// 이 파일을 수정시에는 조심하여 주시기바랍니다.
// 주의: password/jumin 컬럼은 bcrypt(60자), sha256(64자) 해시를 저장하므로 CHAR(255) 이상을 유지해야 합니다.

$member_table = "zetyx_member_table";  // 회원들의 데이타가 들어 있는 직접적인 테이블
$group_table = "zetyx_group_table";   // 그룹테이블
$admin_table = "zetyx_admin_table";     // 게시판의 관리자 테이블
$table_name = $table_name ?? '';

///////////////////////////////////////////////////////////////////////////
// Division Table
//////////////////////////////////////////////////////////////////////////
$division_table_schema = "
CREATE TABLE zetyx_division_{$table_name} (
   no INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   division INT UNSIGNED NOT NULL DEFAULT 1,
   num INT UNSIGNED NOT NULL DEFAULT 0,
   KEY division (division, num)
) ";

////////////////////////////////////////////////////////////////////////////
// 회원관리 테이블
///////////////////////////////////////////////////////////////////////////

$member_table_schema = "

  CREATE TABLE {$member_table} (
    no INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    group_no INT UNSIGNED NOT NULL,
    user_id CHAR(40) NOT NULL,
    password CHAR(255) NOT NULL,
    board_name CHAR(255) NULL DEFAULT '',
    name CHAR(20) NOT NULL,
    level INT UNSIGNED NOT NULL DEFAULT 10,
    email CHAR(255),
    homepage CHAR(255),
    icq CHAR(20),
    aol CHAR(20),
    msn CHAR(20),
    jumin CHAR(255),
    comment TEXT,
    point1 INT DEFAULT 0,
    point2 INT DEFAULT 0,
    job CHAR(50),
    hobby CHAR(50),
    home_address CHAR(255),
    home_tel CHAR(20),
    office_address CHAR(255),
    office_tel CHAR(20),
    handphone CHAR(20),
    mailing CHAR(1) DEFAULT 0,
    birth INT,
    picture CHAR(255),
    reg_date INT UNSIGNED,
    openinfo CHAR(1) DEFAULT 1,
    is_admin CHAR(1) DEFAULT 3,
    new_memo CHAR(1) DEFAULT 0,

    open_email CHAR(1) DEFAULT 1,
    open_homepage CHAR(1) DEFAULT 1,
    open_icq CHAR(1) DEFAULT 1,
    open_aol CHAR(1) DEFAULT 1,
    open_msn CHAR(1) DEFAULT 1,
    open_comment CHAR(1) DEFAULT 1,
    open_job CHAR(1) DEFAULT 1,
    open_hobby CHAR(1) DEFAULT 1,
    open_home_address CHAR(1) DEFAULT 1,
    open_home_tel CHAR(1) DEFAULT 1,
    open_office_address CHAR(1) DEFAULT 1,
    open_office_tel CHAR(1) DEFAULT 1,
    open_handphone CHAR(1) DEFAULT 1,
    open_birth CHAR(1) DEFAULT 1,
    open_picture CHAR(1) DEFAULT 1,

    KEY group_no (group_no),
    UNIQUE KEY user_id (user_id),
    KEY password (password),
    KEY name (name)
    )


   ";

///////////////////////////////////////////////////////////////////////////
// 그룹들의 내용을 저장하는 테이블
///////////////////////////////////////////////////////////////////////////

$group_table_schema = "

  CREATE TABLE {$group_table} (
    no INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

    name CHAR(20) NOT NULL,

    header_url CHAR(255),
    header TEXT,
    footer_url CHAR(255),
    footer TEXT,

    is_open CHAR(1) NOT NULL DEFAULT 1,
    icon CHAR(255),
    use_join CHAR(1) NOT NULL DEFAULT 1,
    use_icon CHAR(1) NOT NULL DEFAULT 0,
    join_return_url CHAR(255),
    member_num INT UNSIGNED NOT NULL DEFAULT 0,
    board_num INT UNSIGNED NOT NULL DEFAULT 0,

    join_level CHAR(2) DEFAULT 9,
    use_icq CHAR(1) DEFAULT 1,
    use_aol CHAR(1) DEFAULT 0,
    use_msn CHAR(1) DEFAULT 0,
    use_jumin CHAR(1) DEFAULT 0,
    use_comment CHAR(1) DEFAULT 1,
    use_job CHAR(1) DEFAULT 0,
    use_hobby CHAR(1) DEFAULT 0,
    use_home_address CHAR(1) DEFAULT 0,
    use_home_tel CHAR(1) DEFAULT 0,
    use_office_address CHAR(1) DEFAULT 0,
    use_office_tel CHAR(1) DEFAULT 0,
    use_handphone CHAR(1) DEFAULT 0,
    use_mailing CHAR(1) DEFAULT 1,
    use_birth CHAR(1) DEFAULT 0,
    use_picture CHAR(1) DEFAULT 0,

    KEY name (name), 
    KEY member_num (member_num), 
    KEY board_num (board_num), 
    KEY is_open (is_open)
    )

    ";

//////////////////////////////////////////////////////////////////////////
// 게시판 관리자 테이블
//////////////////////////////////////////////////////////////////////////

$admin_table_schema = "

  CREATE TABLE {$admin_table} (

   no INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   group_no INT UNSIGNED NOT NULL,

   name CHAR(40) NOT NULL,

   total_article INT UNSIGNED DEFAULT 0 NOT NULL,

   skinname CHAR(255),

   header TEXT,
   footer TEXT,
   title CHAR(255),
   header_url CHAR(255),
   footer_url CHAR(255),

   bg_image CHAR(255),
   bg_color CHAR(255) DEFAULT '#ffffff',
   table_width INT UNSIGNED DEFAULT 95 NOT NULL,
   memo_num INT UNSIGNED DEFAULT 15 NOT NULL,
   page_num INT UNSIGNED DEFAULT 8 NOT NULL,

   only_board CHAR(1) DEFAULT 1 NOT NULL,

   cut_length INT UNSIGNED DEFAULT 0 NOT NULL,

   use_category CHAR(1) DEFAULT 0 NOT NULL,
   use_html CHAR(1) DEFAULT 1 NOT NULL,
   use_filter CHAR(1) DEFAULT 1 NOT NULL,
   use_status CHAR(1) DEFAULT 1 NOT NULL,

   max_upload_size INT UNSIGNED DEFAULT 2097152,

   use_pds CHAR(1) DEFAULT 0,
   pds_ext1 CHAR(255) DEFAULT '',
   pds_ext2 CHAR(255) DEFAULT '',

   use_homelink CHAR(1) DEFAULT 0 NOT NULL,
   use_filelink  CHAR(1) DEFAULT 0 NOT NULL,
   use_cart CHAR(1) DEFAULT 0 NOT NULL,
   use_autolink CHAR(1) DEFAULT 1 NOT NULL,
   use_showip CHAR(1) DEFAULT 0 NOT NULL,
   use_comment CHAR(1) DEFAULT 1 NOT NULL,
   use_formmail CHAR(1) DEFAULT 1 NOT NULL,
   use_showreply CHAR(1) DEFAULT 1 NOT NULL,
   use_secret CHAR(1) DEFAULT 1 NOT NULL,
   use_alllist CHAR(1) DEFAULT 0  NOT NULL,

   grant_html INT UNSIGNED DEFAULT 2 NOT NULL,
   grant_list INT UNSIGNED DEFAULT 10 NOT NULL,
   grant_view INT UNSIGNED DEFAULT 10 NOT NULL,
   grant_comment INT UNSIGNED DEFAULT 10 NOT NULL,
   grant_write INT UNSIGNED DEFAULT 10 NOT NULL,
   grant_reply INT UNSIGNED DEFAULT 10 NOT NULL,
   grant_delete INT UNSIGNED DEFAULT 1 NOT NULL,
   grant_notice INT UNSIGNED DEFAULT 1 NOT NULL,
   grant_view_secret INT UNSIGNED DEFAULT 1 NOT NULL,

   filter TEXT,
   avoid_tag TEXT,
   avoid_ip TEXT,

   KEY group_no (group_no), 
   KEY total_article (total_article), 
   KEY name (name)
   )

  ";


///////////////////////////////////////////////////////////////////////////
// 게시판 본체 테이블
///////////////////////////////////////////////////////////////////////////

$board_table_main_schema = "

  CREATE TABLE zetyx_board_{$table_name} (

    no INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    division INT UNSIGNED DEFAULT 1 NOT NULL,
    headnum INT DEFAULT 0 NOT NULL,
    arrangenum INT UNSIGNED DEFAULT 0 NOT NULL,
    depth INT UNSIGNED DEFAULT 0 NOT NULL,

    prev_no INT UNSIGNED DEFAULT 0 NOT NULL,
    next_no INT UNSIGNED DEFAULT 0 NOT NULL, 

    father INT UNSIGNED DEFAULT 0 NOT NULL,
    child INT UNSIGNED DEFAULT 0 NOT NULL,

    ismember INT UNSIGNED DEFAULT 0 NOT NULL,
    islevel INT UNSIGNED DEFAULT 10 NOT NULL,

    memo TEXT,

    ip CHAR(15),
    password CHAR(255),
    name CHAR(20) NOT NULL,
    homepage CHAR(255),
    email CHAR(255),
    subject CHAR(250) NOT NULL,
    use_html CHAR(1) DEFAULT 0,
    reply_mail CHAR(1) DEFAULT 0,
    category INT UNSIGNED DEFAULT 1 NOT NULL,
    is_secret CHAR(1) NOT NULL DEFAULT 0,
    sitelink1 CHAR(255),
    sitelink2 CHAR(255),
    file_name1 CHAR(255),
    file_name2 CHAR(255),
    s_file_name1 CHAR(255),
    s_file_name2 CHAR(255),

    download1 INT UNSIGNED DEFAULT 0 NOT NULL,
    download2 INT UNSIGNED DEFAULT 0 NOT NULL,
    reg_date INT UNSIGNED NOT NULL DEFAULT 0,
    hit INT UNSIGNED NOT NULL DEFAULT 0,
    vote INT UNSIGNED NOT NULL DEFAULT 0,

    total_comment INT UNSIGNED NOT NULL DEFAULT 0,

    x CHAR(255),
    y CHAR(255),
    KEY headnum (division, headnum, arrangenum),
    KEY depth (depth),
    KEY father (father),
    KEY prev_no (prev_no),
    KEY next_no (next_no),
    KEY name (name),
    KEY reg_date (reg_date),
    KEY hit (hit),
    KEY vote (vote),
    KEY download1 (download1),
    KEY download2 (download2),
    KEY category (category)
  )

  ";


/////////////////////////////////////////////////////////////////////////////////
// 간단한 답글 테이블
/////////////////////////////////////////////////////////////////////////////////

$board_comment_schema = "

  CREATE TABLE zetyx_board_comment_{$table_name} (
    no INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    parent INT UNSIGNED NOT NULL,
    ismember INT UNSIGNED DEFAULT 0 NOT NULL,
    name CHAR(20),
    password CHAR(255),
    memo TEXT,
    ip CHAR(15),
    reg_date INT UNSIGNED,

    KEY parent (parent)
  )

";

//////////////////////////////////////////////////////////////////////////////
// 카테고리 테이블
//////////////////////////////////////////////////////////////////////////////

$board_category_table = "
  CREATE TABLE zetyx_board_category_{$table_name} (
    no INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    num INT UNSIGNED DEFAULT 0 NOT NULL,
    name CHAR(100) NOT NULL,
    KEY name (name)
  )

";

///////////////////////////////////////////////////////////////////////////
// 쪽지 테이블
///////////////////////////////////////////////////////////////////////////

$get_memo_table_schema = "
  CREATE TABLE zetyx_get_memo (
    no INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    member_no INT UNSIGNED NOT NULL,
    member_from INT UNSIGNED NOT NULL,
    subject CHAR(200) NOT NULL,
    memo text NOT NULL,
    readed CHAR(1) DEFAULT 0 NOT NULL,
    reg_date INT UNSIGNED NOT NULL,
    KEY user_id (member_no),
    KEY member_from (member_from),
    KEY readed (readed),
    KEY reg_date (reg_date)
  )";

$send_memo_table_schema = "
  CREATE TABLE zetyx_send_memo (
    no INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    member_no INT UNSIGNED NOT NULL,
    member_to INT UNSIGNED NOT NULL,
    subject CHAR(200) NOT NULL,
    memo text NOT NULL,
    readed CHAR(1) DEFAULT 0 NOT NULL,
    reg_date INT UNSIGNED NOT NULL,
    KEY user_id (member_no),
    KEY readed (readed),                                                                          
    KEY reg_date (reg_date)
  )";
