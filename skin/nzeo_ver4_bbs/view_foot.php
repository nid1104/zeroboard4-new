<?php
if (!defined('_ZB_PATH')) exit();

if (stripos($a_list, "Zeroboard") === false) $a_list = str_replace(">", "><font class=list_eng>", $a_list) . "&nbsp;&nbsp;";

if (stripos($a_reply, "Zeroboard") === false) $a_reply = str_replace(">", "><font class=list_eng>", $a_reply) . "&nbsp;&nbsp;";

if (stripos($a_modify, "Zeroboard") === false) $a_modify = str_replace(">", "><font class=list_eng>", $a_modify) . "&nbsp;&nbsp;";

if (stripos($a_delete, "Zeroboard") === false) $a_delete = str_replace(">", "><font class=list_eng>", $a_delete) . "&nbsp;&nbsp;";

if (stripos($a_write, "Zeroboard") === false) $a_write = str_replace(">", "><font class=list_eng>", $a_write) . "&nbsp;&nbsp;";

if (stripos($a_vote, "Zeroboard") === false) $a_vote = str_replace(">", "><font class=list_eng>", $a_vote) . "&nbsp;&nbsp;";

?>



<table border=0 cellspacing=0 cellpadding=0 height=1 width=<?= $width?>>

<tr><td height=1 class=line1 style=height:1px><img src=<?= $dir?>/t.gif border=0 height=1></td></tr>

</table>

<img src=./images/t.gif border=0 height=8><br>



<table width=<?= $width?> cellspacing=0 cellpadding=0>

<tr>

 <td height=30>

    <?= $a_reply?>답글달기</a>

    <?= $a_modify?>수정하기</a>

    <?= $a_delete?>삭제하기</a>

    <?= $a_vote?>추천하기</a>

 </td>

 <td align=right>

    <?= $a_list?>목록보기</a>

    <?= $a_write?>글쓰기</a>

 </td>

</tr>

</table>



<br>

