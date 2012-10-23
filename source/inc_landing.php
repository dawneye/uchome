<?php
//pauli，全新 landing page 重定向
if ($_SCONFIG['template'] == 'bootstrap' && !isset($_GET['do']) && !isset($_GET['uid'])){
	if ($_SCONFIG['networkpublic']){
		showmessage('enter_the_space', 'space.php?do=home', 0);
	} else {
		showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
	}
	exit();
}
?>