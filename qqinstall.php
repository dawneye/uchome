<?php
/*
	[UCenter Home QQ connect] (C) 2007-2008 Comsenz Inc.
	$Id: index.php 13234 2009-08-24 08:20:04Z liguode $
*/


include_once('./common.php');
$theurl = 'qqinstall.php';
//变量
$step = empty($_GET['step'])?0:intval($_GET['step']);
$action = empty($_GET['action'])?'':trim($_GET['action']);
$nowarr = array('','','','','','','');

$lockfile = S_ROOT.'./data/qqinstall.lock';
if(file_exists($lockfile)) {
	show_msg('警告!您已经安装过UCenter Home QQ connect<br>
		为了保证数据安全，请立即手动删除 qqinstall.php 文件<br>
		如果您想重新安装UCenter Home QQ connect，请删除 data/qqinstall.lock 文件，再运行安装文件');
}

if (!empty($_POST['installsubmit'])) {

	//安装环境检查
	$step = 1;

	//写入config文件
	$configcontent = sreadfile($configfile);
	$keys = array_keys($_POST['uc']);
	foreach ($keys as $value) {
		$upkey = strtoupper($value);
		$configcontent = preg_replace("/define\('UC_".$upkey."'\s*,\s*'.*?'\)/i", "define('UC_".$upkey."', '".$_POST['uc'][$value]."')", $configcontent);
	}
	if(!$fp = fopen($configfile, 'w')) {
		show_msg("文件 $configfile 读写权限设置错误，请设置为可写后，再执行安装程序");
	}
	fwrite($fp, trim($configcontent));
	fclose($fp);
} elseif(!empty($_POST['appsubmit'])) {
	
	$step = 1;

	$theurl .= '?connectopen=1';
	if(!$_SGLOBAL['db']) {
		show_msg("数据库连接失败，请确定已经正确安装 UCenter Home QQ connect");
	}

	if($_GET['connectopen'] && $_POST['appkey'] && $_POST['appid']) {
		$data = array(
			'cloud_site_id' => trim($_POST['appid']),
			'cloud_site_key' => trim($_POST['appkey']),
			'cloud_open' => 1,
		);
		foreach($data as $key=>$value) {
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." SET var='$key',datavalue='$value';");
		}
		

		show_header();
			print<<<END
			<form id="theform" method="post" action="$theurl">
			<table>
			<tr><td>appkey写入成功，并已经开启 QQ connect 功能。</td></tr>
			</table>

			<table class=button>
			<tr><td>
			<input type="submit" id="installsql" name="installsql" value="进入下一步"></td></tr>
			</table>
			<input type="hidden" name="formhash" value="$formhash">
			</form>
END;
			show_footer();
			exit();
	}

} elseif(!empty($_POST['installsql'])) {
	$step = 2;
	$newsql =<<<END
CREATE TABLE `uchome_connect` (
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `conuin` char(40) NOT NULL default '',
  `conopenid` char(32) NOT NULL default '',
  `conisfeed` tinyint(1) unsigned NOT NULL default '0',
  `conispublishfeed` tinyint(1) unsigned NOT NULL default '0',
  `conispublisht` tinyint(1) unsigned NOT NULL default '0',
  `conisregister` tinyint(1) unsigned NOT NULL default '0',
  `conisqzoneavatar` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `conuin` (`conuin`),
  KEY `conopenid` (`conopenid`)
) ENGINE=MyISAM;	
END;


} elseif(!empty($_POST['deletesubmit'])) {
	//写log
	if(@$fp = fopen($lockfile, 'w')) {
		fwrite($fp, 'QQ connect');
		fclose($fp);
	}
	header("Location:index.php");
}

if(empty($step)) {

	show_header();

	//检查权限设置
	$checkok = true;
	$perms = array();
	if(!function_exists('file_get_contents')) {
		$perms['file_get'] = '失败';
		$checkok = false;
	} else {
		$perms['file_get'] = 'OK';
	}
	 
	if(!function_exists('openssl_open')) {
		$perms['openssl'] = '失败';
		$checkok = false;
	} else {
		$perms['openssl'] = 'OK';
	}


	//安装阅读
	print<<<END
	
	<table class="showtable">
	<tr><td>
	<strong>欢迎您使用UCenter Home QQ connect</strong><br>
	使用 UCenter Home QQ connect 可以使您的站点一键式QQ账号登录。
	</td></tr>
	</table>

	<table>
	</td></tr>
	<tr><td>
	<strong>依赖函数检查</strong><br>
	在您执行安装文件进行安装之前，先要检查您的服务器是否支持以下函数<br>
	<br>
	<table class="datatable">
	<tr style="font-weight:bold;"><td>函数名称</td><td>说明</td><td>检测结果</td></tr>
	<tr><td><strong>file_get_contents</strong></td><td>获取信息</td><td>$perms[file_get]</td></tr>
	<tr><td><strong>openssl</strong></td><td>https支持</td><td>$perms[openssl]</td></tr>
	</table>
	</td></tr>
	</table>
END;

	if(!$checkok) {
		echo "<table><tr><td><b>出现问题</b>:<br>系统检测到以上目录或文件权限没有正确设置<br>强烈建议正常设置权限后再刷新本页面以便继续安装<br>否则系统可能会出现无法预料的问题 [<a href=\"$theurl?step=1\">强制继续</a>]</td></tr></table>";
	} else {
		$appid = empty($_POST['appid']) ? '' : $_POST['appid'];
		$appkey = empty($_POST['appkey']) ? '' : $_POST['appkey'];
		print <<<END
		<form id="theform" method="post" action="$theurl?step=1">
			<table class=button>
				<tr>
					<td><input type="submit" id="startsubmit" name="startsubmit" value="接受授权协议，开始安装UCenter Home QQ connect"></td>
				</tr>
			</table>
			<input type="hidden" name="appid" value="$appid" />
			<input type="hidden" name="appkey" value="$appkey" />
		</form>
END;
	}

	print<<<END
	<table id="tbl_readme" style="display:none;" class="showtable">
	<tr>
	<td><strong>请您务必仔细阅读下面的许可协议:</strong> </td></tr>
	<tr>
	<td>
	<div>中文版授权协议 适用于中文用户
	<p>版权所有 (C) 2001-2009，康盛创想（北京）科技有限公司<br>保留所有权利。
	</p><p>感谢您选择 UCenter Home QQ connect。希望我们的努力能为您提供一个强大的社会化网络(SNS)解决方案。通过 UCenter Home QQ connect，建站者可以轻松构建一个以好友关系为核心的交流网络，让站点用户可以用一句话记录生活中的点点滴滴；方便快捷地发布日志、上传图片；更可以十分方便的与其好友们一起分享信息、讨论感兴趣的话题；轻松快捷的了解好友最新动态。
	</p><p>康盛创想（北京）科技有限公司为 UCenter Home QQ connect 产品的开发商，依法独立拥有 UCenter Home QQ connect 产品著作权（中国国家版权局 著作权登记号 2006SR12091）。康盛创想（北京）科技有限公司网址为
	http://www.comsenz.com，UCenter Home QQ connect 官方网站网址为 http://u.discuz.net。
	</p><p>UCenter Home QQ connect 著作权已在中华人民共和国国家版权局注册，著作权受到法律和国际公约保护。使用者：无论个人或组织、盈利与否、用途如何
	（包括以学习和研究为目的），均需仔细阅读本协议，在理解、同意、并遵守本协议的全部条款后，方可开始使用 UCenter Home QQ connect 软件。
	</p><p>康盛创想（北京）科技有限公司拥有对本授权协议的最终解释权。
	<ul type=i>
	<p>
	<li><b>协议许可的权利</b>
	<ul type=1>
	<li>您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。
	<li>您可以在协议规定的约束和限制范围内修改 UCenter Home QQ connect 源代码(如果被提供的话)或界面风格以适应您的网站要求。
	<li>您拥有使用本软件构建的站点中全部会员资料、文章及相关信息的所有权，并独立承担与文章内容的相关法律义务。
	<li>获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持期限、技术支持方式和技术支持内容，
	自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见
	将被作为首要考虑，但没有一定被采纳的承诺或保证。 </li></ul>
	<p></p>
	<li><b>协议规定的约束和限制</b>
	<ul type=1>
	<li>未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目或实现盈利的网站）。购买商业授权请登陆http://www.discuz.com参考相关说明，也可以致电8610-51657885了解详情。
	<li>不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。
	<li>无论如何，即无论用途如何、是否经过修改或美化、修改程度如何，只要使用 UCenter Home QQ connect 的整体或任何部分，未经书面许可，程序页面页脚处
	的 UCenter Home QQ connect 名称和康盛创想（北京）科技有限公司下属网站（http://www.comsenz.com、http://u.discuz.net） 的 链接都必须保留，而不能清除或修改。
	<li>禁止在 UCenter Home QQ connect 的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。
	<li>如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。 </li></ul>
	<p></p>
	<li><b>有限担保和免责声明</b>
	<ul type=1>
	<li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。
	<li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺提供任何形式的技术支持、使用担保，
	也不承担任何因使用本软件而产生问题的相关责任。
	<li>康盛创想（北京）科技有限公司不对使用本软件构建的站点中的文章或信息承担责任。 </li></ul></li></ul>
	<p>有关 UCenter Home QQ connect 最终用户授权协议、商业授权与技术服务的详细内容，均由 UCenter Home QQ connect 官方网站独家提供。康盛创想（北京）科技有限公司拥有在不 事先通知的情况下，修改授权协议和服务价目表的权力，修改后的协议或价目表对自改变之日起的新授权用户生效。
	<p>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始安装 UCenter Home QQ connect，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。 </p></div>
	</td></tr>
	</table>
END;

	show_footer();

} elseif($step == 1) {

	show_header();
	$appid = '';
	$appkey = '';
	$showdiv = 0;
	if($_POST['appkey'] && $_POST['appid']) {
		$showdiv = 1;
		$appid = trim($_POST['appid']);
		$appkey = trim($_POST['appkey']);
	}

	if($showdiv) {
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<div>
			<table class="showtable">
				<tr><td><strong># QQ connect 参数自动获取</strong></td></tr>
				<tr><td id="msg2"> QQ connect 的相关信息已成功获取，请直接点击下面的按钮提交配置</td></tr>
			</table>
			<br/>
		</div>
		<div>
END;
	} else {
		$plus = '';
		if(!$appkey) {
			$plus = '<tr><td id="msg2">
						若使用 UCenter Home QQ connect 需要通过网页申请 APPID 和 APPKEY 如果没有申请，请 <a href="http://connect.qq.com/manage/" target="_blank">点击此处</a> 申请。
				</td></tr>';
		}
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<div>
			<table class="showtable">
				<tr><td><strong># 请填写 QQ connect 的相关参数</strong></td></tr>
				$plus
			</table>
			<br>
			<p style="font-weight:bold;">请输入已申请的 APPID 和 APPKEY</p>
END;
	}
	print<<<END
		<table class=datatable>
			<tbody>
				<tr>
					<td>APPID</td>
					<td><input type="text" id="appid" name="appid" size="60" value="$appid"><br>例如：123456789</td>
				</tr>
				<tr>
					<td>APPKEY</td>
					<td><input type="appkey" id="appkey" name="appkey" size="20" value="$appkey"></td>
				</tr>
			</tbody>
		</table>
		<br>
	</div>
	<table class=button>
	<tr><td><input type="submit" id="appsubmit" name="appsubmit" value="提交QQ connect配置信息"></td></tr>
	</table>
	<input type="hidden" name="formhash" value="$formhash">
	</form>
END;
	show_footer();

} elseif ($step == 2) {

	if($_SC['tablepre'] != 'uchome_') $newsql = str_replace('uchome_', $_SC['tablepre'], $newsql);//替换表名前缀

	//获取要创建的表
	$tables = $sqls = array();
	if($newsql) {
		preg_match_all("/(CREATE TABLE ([a-z0-9\_\-`]+).+?\s*)(TYPE|ENGINE)+\=/is", $newsql, $mathes);
		$sqls = $mathes[1];
		$tables = $mathes[2];
	}
	if(!empty($tables)) {	
		$heaptype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MEMORY".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=HEAP";
		$myisamtype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MYISAM".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=MYISAM";
		$installok = true;
		foreach ($tables as $key => $tablename) {
			if(strpos($tablename, 'session')) {
				$sqltype = $heaptype;
			} else {
				$sqltype = $myisamtype;
			}
			$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $tablename");
			if(!$query = $_SGLOBAL['db']->query($sqls[$key].$sqltype, 'SILENT')) {
				$installok = false;
				break;
			}
		}
	}

	if(!$installok) {
		show_msg("<font color=\"blue\">数据表 ($tablename) 自动安装失败</font><a href=\"?step=$step\">重试</a>");
	} 
	//更新缓存
	dbconnect();
	include_once(S_ROOT.'./source/function_cache.php');
	config_cache();

	$msg = <<<EOF
	<form method="post" action="$theurl">
	<table>
	<tr><td colspan="2"> UCenter Home QQ connect 程序安装完成，并已经开启 QQ connect ,若要关闭和配置请进入后台管理。<br><br>
	最后，请确认安装完成后删除此文件。
	</td></tr>
	<tr><td><input type="submit" name="deletesubmit" value="安装完成"></td></tr>
	</table>
	</form>
EOF;
	show_msg($msg, 999);
}

//页面头部
function show_header() {
	global $_SGLOBAL, $nowarr, $step, $theurl, $_SC;

	$nowarr[$step] = ' class="current"';
	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$_SC[charset]" />
	<title> UCenter Home QQ connect 程序安装 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	.showtable { width:100%; border: solid; border-color:#86B9D6 #B2C9D3 #B2C9D3; border-width: 3px 1px 1px; margin: 10px auto; background: #F5FCFF; }
	.showtable td { padding: 3px; }
	.showtable strong { color: #5086A5; }
	.datatable { width: 100%; margin: 10px auto 25px; }
	.datatable td { padding: 5px 0; border-bottom: 1px solid #EEE; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	.button { margin: 10px auto 20px; width: 100%; }
	.button td { text-align: center; }
	.button input, .button button { border: solid; border-color:#F90; border-width: 1px 1px 3px; padding: 5px 10px; color: #090; background: #FFFAF0; cursor: pointer; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	<script type="text/javascript">
	function $(id) {
		return document.getElementById(id);
	}
	//添加Select选项
	function addoption(obj) {
		if (obj.value=='addoption') {
			var newOption=prompt('请输入:','');
			if (newOption!=null && newOption!='') {
				var newOptionTag=document.createElement('option');
				newOptionTag.text=newOption;
				newOptionTag.value=newOption;
				try {
					obj.add(newOptionTag, obj.options[0]); // doesn't work in IE
				}
				catch(ex) {
					obj.add(newOptionTag, obj.selecedIndex); // IE only
				}
				obj.value=newOption;
			} else {
				obj.value=obj.options[0].value;
			}
		}
	}
	</script>
	</head>
	<body id="append_parent">
	<div class="bodydiv">
	<h1>UCenter Home QQ connect 程序安装</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[0]}>1.安装开始</td>
	<td{$nowarr[1]}>2.设置APPID 与 APPKEY</td>
	<td{$nowarr[2]}>3.安装完成</td>
	</tr>
	</table>
END;
}

//页面顶部
function show_footer() {
	print<<<END
	</div>
	<iframe id="phpframe" name="phpframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>
	<div id="footer">&copy; Comsenz Inc. 2001-2009 u.discuz.net</div>
	</div>
	<br>
	</body>
	</html>
END;
}


//显示
function show_msg($message, $next=0, $jump=0) {
	global $theurl;

	$nextstr = '';
	$backstr = '';

	obclean();
	if(empty($next)) {
		$backstr .= "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
	} elseif ($next == 999) {
	} else {
		$url_forward = "$theurl?step=$next";
		if($jump) {
			$nextstr .= "<a href=\"$url_forward\">请稍等...</a><script>setTimeout(\"window.location.href ='$url_forward';\", 1000);</script>";
		} else {
			$nextstr .= "<a href=\"$url_forward\">继续下一步</a>";
			$backstr .= "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
		}
	}

	show_header();
	print<<<END
	<table>
	<tr><td>$message</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>$backstr $nextstr</td></tr>
	</table>
END;
	show_footer();
	exit();
}

//检查权限
function checkfdperm($path, $isfile=0) {
	if($isfile) {
		$file = $path;
		$mod = 'a';
	} else {
		$file = $path.'./install_tmptest.data';
		$mod = 'w';
	}
	if(!@$fp = fopen($file, $mod)) {
		return false;
	}
	if(!$isfile) {
		//是否可以删除
		fwrite($fp, ' ');
		fclose($fp);
		if(!@unlink($file)) {
			return false;
		}
		//检测是否可以创建子目录
		if(is_dir($path.'./install_tmpdir')) {
			if(!@rmdir($path.'./install_tmpdir')) {
				return false;
			}
		}
		if(!@mkdir($path.'./install_tmpdir')) {
			return false;
		}
		//是否可以删除
		if(!@rmdir($path.'./install_tmpdir')) {
			return false;
		}
	} else {
		fclose($fp);
	}
	return true;
}

?>
