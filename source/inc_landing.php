<?php
//pauli��ȫ�� landing page �ض���
if ($_SCONFIG['template'] == 'bootstrap' && !isset($_GET['do']) && !isset($_GET['uid']) && !isset($_GET['username'])){
	if ($_SCONFIG['networkpublic']){
		showmessage('enter_the_space', 'space.php?do=landing', 0);
	} else {
		showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
	}
	exit();
}
?>