<?php
set_time_limit(0);

require_once __DIR__ . '/../lib.php';

$connect = dbconn();
$member = member_info();

if (!$member['no'] || $member['is_admin'] > 1 || $member['level'] > 1) Error("최고 관리자만이 사용할수 있습니다");

check_csrf(true);

head(" bgcolor=white");
?>
<div align=center>
<br>
<table border=0 cellspacing=0 cellpadding=0 width=98%>
<tr>
  <td><img src=../images/arrangefiles.gif border=0></td>
  <td width=100% background=../images/trace_back.gif><img src=../images/trace_back.gif border=0></td>
  <td><img src=../images/trace_right.gif border=0></td>
</tr>
<tr>
  <td colspan=3 style=padding:15px;line-height:160%>
  	이 페이지는 제로보드의 첨부파일을 정리하는 곳입니다.<br>
	모든 게시판을 검토하여 잘못 올려진 첨부파일을 정리하거나, 쓰레기 자료등을 자동으로 정리합니다.<br>
	게시판이 많을수록 많은 시간이 걸리게 되니 정상적으로 종료할때까지 기다려 주시기 바랍니다.<br>
	<br>
	<font color=darkred>* 모든 게시판을 정리하므로, 사용자가 적은 시간에 이용하여 주시기 바랍니다</font>
  </td>
</tr>
</table>
</div>
<?php flush(); ?>
<pre>
<?php
	$result = $connect->all("select * from $admin_table order by name");
$totalfilesnum = 0;
$ntotalfilesnum = 0;
$existsfilesnum = 0;
$nexistsfilesnum = 0;

foreach ($result as $bbs) {

    $id = $bbs['name'];
    if (!isalNum($id)) continue;

    $files1 = $connect->value(("select count(*) from {$t_board}_{$id} where file_name1 != ''"));
    $files2 = $connect->value("select count(*) from {$t_board}_{$id} where file_name2 != ''");

    $filesnum1 = $files1;
    $filesnum2 = $files2;

    $nfiles1 = $connect->all("select no, file_name1 , s_file_name1 from {$t_board}_{$id} where file_name1 !='' and file_name1 not like ?", ["data/{$id}/%"]);
    $nfiles2 = $connect->all("select no, file_name2 , s_file_name2 from {$t_board}_{$id} where file_name2 !='' and file_name2 not like ?", ["data/{$id}/%"]);

    $nfilesnum1 = count($nfiles1);
    $nfilesnum2 = count($nfiles2);

    $totalfilesnum += $filesnum1 + $filesnum2;
    $ntotalfilesnum += $nfilesnum1 + $nfilesnum2;

    // 디렉토리 검사
    if (!is_dir("../data/{$id}")) {
        mkdir("../data/{$id}", 0777);
        createIndexFile("../data/{$id}");
    }

    if (!is_dir("../data/{$id}")) exit("../data/{$id} 디렉토리를 생성할수가 없습니다");

    ?>
	<b><?= e($id) ?></b> 게시판</b>
	 - 총 게시물 수  : <?= e($bbs['total_article']) ?>개
	 - 총 업로드 개수 : <?= number_format($filesnum1 + $filesnum2) ?> 개
	 - 경로가 잘못된 첨부파일 수 : <?= number_format($nfilesnum1 + $nfilesnum2) ?> 개

<?php
    		foreach ($nfiles1 as $data) {

    		    // 소스 파일의 정보를 체크
    		    $filename = basename($data['s_file_name1']);
    		    $source = "../" . $data['file_name1'];
    		    $path = str_replace($filename, "", $source);
    		    $no = $data['no'];

    		    // 소스 파일이 있을 경우에만 체크
    		    if (file_exists($source)) {

    		        $existsfilesnum++;

    		        // 옮길 대상에 같은 파일이 존재하는지 체크
    		        if (file_exists("../data/$id/$filename")) {
    		            $add_dir = time();
    		            $target_path = "../data/$id/$add_dir";
    		            mkdir($target_path, 0777);
                        createIndexFile($target_path);
    		            $target_path = "../data/$id/$add_dir/$filename";
    		            $newval = "data/$id/$add_dir/$filename";
    		        } else {
    		            $target_path = "../data/$id/$filename";
    		            $newval = "data/$id/$filename";
    		        }

    		        if (!copy($source, $target_path)) exit("<center><b>" . e($source) . "</b><br>to<br><b>" . e($target_path) . "</b><br><br> 파일을 복사할수가 없습니다<br>(파일을 체크하신후 다시 실행을 해주시기 바랍니다)</center>");
    		        z_unlink($source);
    		        @rmdir($path);

    		        $connect->exec("update {$t_board}_{$id} set file_name1 = ? where no = ?", [$newval, $no]);

    		    } else {
    		        $nexistsfilesnum++;
    		    }
    		}

    foreach ($nfiles2 as $data) {

        // 소스 파일의 정보를 체크
        $filename = basename($data['s_file_name2']);
        $source = "../" . $data['file_name2'];
        $path = str_replace($filename, "", $source);
        $no = $data['no'];

        // 소스 파일이 있을 경우에만 체크
        if (file_exists($source)) {

            $existsfilesnum++;

            // 옮길 대상에 같은 파일이 존재하는지 체크
            if (file_exists("../data/$id/$filename")) {
                $add_dir = time();
                $target_path = "../data/$id/$add_dir";
                mkdir($target_path, 0777);
                createIndexFile($target_path);
                $target_path = "../data/$id/$add_dir/$filename";
                $newval = "data/$id/$add_dir/$filename";
            } else {
                $target_path = "../data/$id/$filename";
                $newval = "data/$id/$filename";
            }

            if (!copy($source, $target_path)) exit("<center><b>" . e($source) . "</b><br>to<br><b>" . e($target_path) . "</b><br><br> 파일을 복사할수가 없습니다<br><br>(파일을 체크하신후 다시 실행을 해주시기 바랍니다)</center>");
            z_unlink($source);
            @rmdir($path);

            $connect->exec("update {$t_board}_{$id} set file_name2 = ? where no = ?", [$newval, $no]);

        } else {
            $nexistsfilesnum++;

        }
    }

    flush();
}
?>

	<b>전체 첨부파일 수 :</b> <?= number_format($totalfilesnum) ?>

	<b>전체 경로가 잘못된 첨부파일 수 :</b> <?= number_format($ntotalfilesnum) ?>

	<b>파일 존재 개수 :</b> <?= number_format($existsfilesnum) ?>

	<b>파일 미존재 개수 :</b> <?= number_format($nexistsfilesnum) ?> (첨부파일 필드가 다른 용도로 사용되는 경우일수가 있음)

	<font color=red><b>모든 정리가 끝났습니다.

	확실한 처리를 위해서 다시 한번 실행해보시기 바랍니다.</font>

	이 파일 정리기는 DB를 근거로 하여 파일을 정리합니다.

	따라서 미삭제 된 쓰레기 파일이 남아있을수가 있습니다.

	쓰레기 파일 삭제를 원하시면 아래 버튼을 눌러주세요.

	<form action=arrangefile2.php method=post>
	<input type=submit value=" 쓰레기 파일 검사 " class=submit>
	</form>


</pre>
<br><Br><Br>

<?php
 foot();
?>
