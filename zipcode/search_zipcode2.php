<?php
exit();
require_once __DIR__ . '/../lib.php';
$address = Request::post('address');
if (!$address) {
    ?>
		<script>
			alert("우편번호를 입력하셔야 합니다");
			history.back();
		</script>
<?php
    		exit;
}

$url = str_ireplace("search_zipcode.php?", "search_zipcode3.php", Request::header('Referer'));
$url = str_replace("num=1", "", $url);
$url = str_ireplace("num=2", "", $url);
// header("Location: http://zeroboard.com/zipcode/search_zipcode2.html?num=$num&url=$url&address=$address");
?>
