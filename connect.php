<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: connect.php 12059 2009-05-04 02:43:18Z liguode $
*/
include_once('./common.php');

//获取方法
$ac = empty($_GET['ac'])? '' :$_GET['ac'];

if(!$_SGLOBAL['connect']) {
	showmessage('qqconnect_closed', 'index.php', 3);
}

//允许的方法
$acs =  array('login', 'register');
if(!in_array($ac, $acs)) {
	showmessage('enter_the_space', 'index.php', 3);
}

include_once(S_ROOT.'./source/connect_common.php');

?>