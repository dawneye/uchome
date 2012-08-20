<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: class_mysql.php 10484 2008-12-05 05:46:59Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class Cloud_Connect {
	var $connectAppId = '';
    var $connectAppKey = '';
    var $mySiteId = '';
    var $mySiteKey = '';

	function Cloud_Connect($siteId, $siteKey) {
        $this->_requestOpenID = 'https://graph.qq.com/oauth2.0/me';
        $this->_authorizeURL = 'https://graph.qq.com/oauth2.0/authorize';
        $this->_accessTokenURL = 'https://graph.qq.com/oauth2.0/token';
        $this->_FeedURL = 'https://graph.qq.com/share/add_share';
        $this->_getUserInfoURL = 'https://graph.qq.com/user/get_user_info';
        $this->mySiteId = $siteId;
        $this->mySiteKey = $siteKey;
    }

	function Cloud_Login($state, $callback) {
		$extra = array(
			'response_type' => 'code',
			'client_id' => $this->mySiteId,
			'state' => $state,
			'scope' => 'get_user_info,add_share',
			'redirect_uri' => $callback,
		);
		return $this->_authorizeURL.'?'.$this->_httpBuildQuery($extra);
	
	}

	function getAccessToken($code, $callback) {
		$extra = array(
			'grant_type' => 'authorization_code',
			'client_id' => $this->mySiteId,
			'client_secret' => $this->mySiteKey,
			'code' => $code,
			'redirect_uri' => $callback,
		);
		$response = file_get_contents($this->_accessTokenURL.'?'.$this->_httpBuildQuery($extra));
		return $this->RequestTokenMsg($response);
	}

	function getOpenId($access_token) {
		$extra = array(
			'access_token' => $access_token,
		);
		$response = file_get_contents($this->_requestOpenID.'?'.$this->_httpBuildQuery($extra));
		return $this->RequestTokenMsg($response);
	}

		
	function getUserInfo($accessToken, $openid, $format = 'json') {
    	$format = strtolower($format);

    	if (!in_array($format, array('xml', 'json'))) {
    		$format = 'json';
    	}

    	$extra = array(
            'access_token' => $accessToken,
            'oauth_consumer_key' => $this->mySiteId,
            'openid' => $openid,
            'format' => $format,
        );
		
        $response = file_get_contents($this->_getUserInfoURL.'?'.$this->_httpBuildQuery($extra));
		return json_decode($response, 1);
    }

	function PostFeed($token, $openid, $data, $type = 'share',$format = 'json') {
		$extra = array(
			'access_token' => $token,
			'oauth_consumer_key' => $this->mySiteId,
			'openid' => $openid,
			'format' => $format,
		);

		$sharedata = array_merge($extra, array_map('urlencode', $data));

		$response = file_get_contents($this->_FeedURL.'?'.$this->_httpBuildQuery($sharedata));

		return $this->RequestTokenMsg($response);
	}

	function RequestTokenMsg($response) {
		$extra = array();

		if (strpos($response, "callback") !== false) {
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $extra = json_decode($response, 1);
        } else {
			 parse_str($response, $extra);
		}
		return $extra;
	}
	
	function _httpBuildQuery($data, $numeric_prefix='', $arg_separator='', $prefix='', $need_encode = false) {

        settype($data, 'array');
        ksort($data);

        $render = array();
    	if (empty($arg_separator)) {
    		$arg_separator = @ini_get('arg_separator.output');
    		empty($arg_separator) && $arg_separator = '&';
    	}
    	foreach ($data as $key => $val) {
    		if (is_array($val) || is_object($val)) {
    			$_key = empty($prefix) ? "{$key}[%s]" : sprintf($prefix, $key) . "[%s]";
    			$_render = $this->_httpBuildQuery($val, '', $arg_separator, $_key);
    			if (!empty($_render)) {
    				$render[] = $_render;
    			}
    		} else {
    			if (is_numeric($key) && empty($prefix)) {
    				$render[] = "{$numeric_prefix}{$key}" . "=" . ($need_encode ? rawurlencode($val) : $val);
    			} else {
    				if (!empty($prefix)) {
    					$_key = sprintf($prefix, $key);
    					$render[] = $_key . "=" . ($need_encode ? rawurlencode($val) : $val);
    				} else {
    					$render[] = $key . "=" . ($need_encode ? rawurlencode($val) : $val);
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

}

?>