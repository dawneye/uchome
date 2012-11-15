<?php
//早上9点到晚上22点，标准时间，早上1点到下午14点，不发邮件
$d = localtime();
$d = $d[2];
if ($d < 1 || $d > 14) exit();

include(S_ROOT.'./source/do_sendmail.lock';);
?>