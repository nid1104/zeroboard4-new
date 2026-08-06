<?php if (!defined('_ZB_PATH')) exit(); ?>
<table border=0 cellspacing=0 cellpadding=0 width=<?= $width?>>
<tr><td width=1>
<form method=post name=list action=list_all.php>
<input type=hidden name=page value=<?= e($page)?>>
<input type=hidden name=id value=<?= e($id)?>>
<input type=hidden name=select_arrange value=<?= e($select_arrange)?>>
<input type=hidden name=desc value=<?= e($desc)?>>
<input type=hidden name=page_num value=<?= e($page_num)?>>
<input type=hidden name=selected>
<input type=hidden name=exec>
<input type=hidden name=keyword value="<?= e($keyword)?>">
<input type=hidden name=sn value="<?= e($sn)?>">
<input type=hidden name=ss value="<?= e($ss)?>">
<input type=hidden name=sc value="<?= e($sc)?>">
</td><td width=100%>
