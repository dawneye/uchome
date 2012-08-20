<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_common.php 13519 2010-01-05 02:42:46Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

function connect_login($connect_member) {
	global $_SGLOBAL;

	$member = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT * FROM ".tname('member')." WHERE uid='$connect_member[uid]' LIMIT 1"));
	if(!$member) {
		return false;
	}
	$setarr = array(
		'uid' => $member['uid'],
		'username' => addslashes($member['username']),
		'password' => md5("$member[uid]|$_SGLOBAL[timestamp]")//本地密码随机生成
	);
	
	include_once(S_ROOT.'./source/function_space.php');
	//开通空间
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$setarr[uid]'");
	if(!$space = $_SGLOBAL['db']->fetch_array($query)) {
		$space = space_open($setarr['uid'], $setarr['username'], 0, $passport['email']);
	}
	
	$_SGLOBAL['member'] = $space;
	
	//实名
	realname_set($space['uid'], $space['username'], $space['name'], $space['namestatus']);
	
	//检索当前用户
	$query = $_SGLOBAL['db']->query("SELECT password FROM ".tname('member')." WHERE uid='$setarr[uid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		$setarr['password'] = addslashes($value['password']);
	} else {
		//更新本地用户库
		inserttable('member', $setarr, 0, true);
	}

	//清理在线session
	insertsession($setarr);
	
	//设置cookie
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), $cookietime);
	ssetcookie('loginuser', $member['username'], 31536000);
	ssetcookie('_refer', '');
	
	//同步登录
	if($_SCONFIG['uc_status']) {
		include_once S_ROOT.'./uc_client/client.php';
		$ucsynlogin = uc_user_synlogin($setarr['uid']);
	} else {
		$ucsynlogin = '';
	}
	
	//好友邀请
	if($invitearr) {
		//成为好友
		invite_update($invitearr['id'], $setarr['uid'], $setarr['username'], $invitearr['uid'], $invitearr['username'], $app);
	}
	$_SGLOBAL['supe_uid'] = $space['uid'];
	//判断用户是否设置了头像
	$reward = $setarr = array();
	$experience = $credit = 0;
	$avatar_exists = ckavatar($space['uid']);
	if($avatar_exists) {
		if(!$space['avatar']) {
			//奖励积分
			$reward = getreward('setavatar', 0);
			$credit = $reward['credit'];
			$experience = $reward['experience'];
			if($credit) {
				$setarr['credit'] = "credit=credit+$credit";
			}
			if($experience) {
				$setarr['experience'] = "experience=experience+$experience";
			}
			$setarr['avatar'] = 'avatar=1';
			$setarr['updatetime'] = "updatetime=$_SGLOBAL[timestamp]";
		}
	} else {
		if($space['avatar']) {
			$setarr['avatar'] = 'avatar=0';
		}
	}
	
	if($setarr) {
		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarr)." WHERE uid='$space[uid]'");
	}

	if(empty($_POST['refer'])) {
		$_POST['refer'] = 'space.php?do=home';
	}
	
	realname_get();
	showmessage('login_success', $app?"userapp.php?id=$app":$_POST['refer'], 1, array($ucsynlogin));
	return true;
}

function cloud_http_build_query($data, $numeric_prefix='', $arg_separator='', $prefix='') {
	$render = array();
	if (empty($arg_separator)) {
		$arg_separator = @ini_get('arg_separator.output');
		empty($arg_separator) && $arg_separator = '&';
	}
	foreach ((array) $data as $key => $val) {
		if (is_array($val) || is_object($val)) {
			$_key = empty($prefix) ? "{$key}[%s]" : sprintf($prefix, $key) . "[%s]";
			$_render = cloud_http_build_query($val, '', $arg_separator, $_key);
			if (!empty($_render)) {
				$render[] = $_render;
			}
		} else {
			if (is_numeric($key) && empty($prefix)) {
				$render[] = urlencode("{$numeric_prefix}{$key}") . "=" . urlencode($val);
			} else {
				if (!empty($prefix)) {
					$_key = sprintf($prefix, $key);
					$render[] = urlencode($_key) . "=" . urlencode($val);
				} else {
					$render[] = urlencode($key) . "=" . urlencode($val);
				}
			}
		}
	}
	$render = implode($arg_separator, $render);
	if (empty($render)) {
		$render = '';
	}
	return $render;
}

function cloud_token() {
	global $_SCOOKIE, $_SGLOBAL;
	
	if(!$_SGLOBAL['connect']) {
		return false;
	}

	$data = array();
	if(isset($_SCOOKIE['cloud_conuin']) && isset($_SCOOKIE['cloud_conopenid'])) {
		$data['cloud_conuin'] = $_SCOOKIE['cloud_conuin'];
		$data['cloud_conopenid']= $_SCOOKIE['cloud_conopenid'];
	} else {
		$connect_member =  $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT uid, conuin, conopenid FROM ".tname('connect')." WHERE uid='$_SGLOBAL[supe_uid]' LIMIT 1"));
		if($connect_member) {
			$data['cloud_conuin'] = $connect_member['conuin'];
			$data['cloud_conopenid'] = $connect_member['conopenid'];
		}
		ssetcookie('cloud_conuin', $data['cloud_conuin']);
		ssetcookie('cloud_conopenid', $data['cloud_conopenid']);
	}
	return $data;
}


?>
