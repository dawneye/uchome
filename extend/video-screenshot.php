<?php
if(empty($_GET) || empty($_GET['id'])){
	echo 'image/vd.gif';
	exit(0);
}
try {
	$id = $_GET['id'];
	$file = '../attachment/screenshot/'.$id;
	$url = @file_get_contents($file);
	if(!empty($url)){
		echo $url;
		exit(0);
	}

	$content = @file_get_contents('http://v.youku.com/v_show/id_'.$id.'.html');
	if(empty($content)){
		echo 'image/vd.gif';
		exit(0);
	}
	preg_match( '/id="s_msn2".*?screenshot=(.*?)".?target=/', $content, $matchs);
	if(empty($matchs) || sizeof($matchs) < 1) {
		echo 'image/vd.gif';
		exit(0);
	}
	$url = $matchs[1];
	if(empty($url)){
		echo 'image/vd.gif';
		exit(0);
	}
	file_put_contents($file, $url);
	echo $url;
} catch (Exception $e) {
	echo 'image/vd.gif';
}
?>