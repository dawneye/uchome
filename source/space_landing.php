<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_album.php 12078 2009-05-04 08:28:37Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//是否公开
if(empty($_SCONFIG['networkpublic'])) {
	checklogin();//需要登录
}

include_once(S_ROOT.'./data/data_network.php');

//获得个性模板
$templates = $default_template = array();
$tpl_dir = sreaddir(S_ROOT.'./template');
foreach ($tpl_dir as $dir) {
	if(file_exists(S_ROOT.'./template/'.$dir.'/style.css')) {
		$tplicon = file_exists(S_ROOT.'./template/'.$dir.'/image/template.gif')?'template/'.$dir.'/image/template.gif':'image/tlpicon.gif';
		$tplvalue = array('name'=> $dir, 'icon'=>$tplicon);
		if($dir == $_SCONFIG['template']) {
			$default_template = $tplvalue;
		} else {
			$templates[$dir] = $tplvalue;
		}
	}
}
$_TPL['templates'] = $templates;
$_TPL['default_template'] = $default_template;

$_TPL['css'] = 'network';

//处理相册图片
$album_list = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid != $_SC[landingalbum] ORDER BY dateline DESC LIMIT 3");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value['pic'] = pic_get($value['filepath'], $value['thumb'], $value['remote']);
	$album_list[] = $value;
}

//处理滚动图片
$pic_list = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid=$_SC[landingalbum] ORDER BY dateline DESC LIMIT $_SC[landingpicnum]");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value['pic'] = pic_get($value['filepath'], 0, $value['remote']);
	
	$value['comment'] = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT message FROM ".tname('comment')." WHERE id='$value[picid]' AND idtype='picid' ORDER BY dateline LIMIT 1"),0);
	$pic_list[] = $value;
}
if(!empty($pic_list)) {
	$pic_list[0]['active'] = ' active';
}

//最新日志
$latest_blog = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT bf.message, bf.target_ids, bf.magiccolor, b.* FROM ".tname('blog')." b LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid ORDER BY b.dateline DESC LIMIT 1"));
if(!empty($latest_blog)) {
	$latest_blog['message'] = getstr($latest_blog['message'], 160, 0, 0, 0, 0, -1);
}

//最新视频
$latest_video = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE type='video' ORDER BY dateline DESC	LIMIT 1"));
if(!empty($latest_video)) {
	$latest_video = mkshare($latest_video);
	$latest_video['title'] = getstr($latest_video['body_general'], 26, 0, 0, 0, 0, -1);
}

//最新群组
$latest_mtag = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT * FROM ".tname('mtag')." ORDER by tagid LIMIT 1"));
if(!empty($latest_mtag)) {
	@include_once(S_ROOT.'./data/data_profield.php');
	$latest_mtag['title'] = $_SGLOBAL['profield'][$latest_mtag['fieldid']]['title'];
	if(empty($latest_mtag['pic'])) $latest_mtag['pic'] = 'image/nologo.jpg';
}

//欢迎新成员
if($_SCONFIG['newspacenum']>0) {
	$newspacelist = unserialize(data_get('newspacelist'));
	if(!is_array($newspacelist)) $newspacelist = array();
	foreach ($newspacelist as $value) {
		$oluids[] = $value['uid'];
		realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
	}
}

//记录
$dolist = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." ORDER BY dateline DESC LIMIT 0,5");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	realname_set($value['uid'], $value['username']);
	$value['title'] = getstr($value['message'], 0, 0, 0, 0, 0, -1);
	$dolist[] = $value;
}

//标签
$taglist = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tag')." ORDER BY blognum DESC LIMIT 20");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$taglist[] = $value;
}

include_once template("space_landing");

?>