<?php

error_reporting(E_ALL);
ini_set('display_errors', FALSE);

if (defined('IN_CRONLITE')) return;
define('VERSION', '3038');
define('DB_VERSION', '2020');
define('IN_CRONLITE', true);
define('SYSTEM_ROOT', dirname(__FILE__) . '/');
define('ROOT', dirname(SYSTEM_ROOT) . '/');
define('PAYPAGE_ROOT', SYSTEM_ROOT . 'pages/');
define('TEMPLATE_ROOT', ROOT . 'template/');
define('PLUGIN_ROOT', ROOT . 'plugins/');
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d H:i:s");

if (php_sapi_name() != 'cli') {
	if (!isset($nosession) || !$nosession) session_start();

	if (!function_exists("is_https")) {
		function is_https()
		{
			if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
				return true;
			} elseif (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) {
				return true;
			} elseif (isset($_SERVER['HTTP_X_CLIENT_SCHEME']) && $_SERVER['HTTP_X_CLIENT_SCHEME'] == 'https') {
				return true;
			} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
				return true;
			} elseif (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') {
				return true;
			} elseif (isset($_SERVER['HTTP_EWS_CUSTOME_SCHEME']) && $_SERVER['HTTP_EWS_CUSTOME_SCHEME'] == 'https') {
				return true;
			}
			return false;
		}
	}

	$siteurl = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';
}

include_once(SYSTEM_ROOT . "autoloader.php");
Autoloader::register();

require ROOT . 'config.php';
define('DBQZ', $dbconfig['dbqz']);

$DB = new \lib\PdoHelper($dbconfig);

$CACHE = new \lib\Cache();
$conf = $CACHE->pre_fetch();
define('SYS_KEY', $conf['syskey']);

if (php_sapi_name() != 'cli') {
	if (!$conf['localurl']) $conf['localurl'] = $siteurl;
}

$password_hash = '!@#%!s!0';

include_once(SYSTEM_ROOT . "functions.php");

// 捕获错误
register_shutdown_function("shutdownLog");
set_error_handler("myErrorHandlerLog", E_ALL);

if (php_sapi_name() != 'cli') {
	include_once(SYSTEM_ROOT . "member.php");
}

// 备选：'//lib.baomitu.com/', 'https://cdn.bootcdn.net/ajax/libs/', '//s1.pstatp.com/cdn/expire-1-M/'
$cdnpublic = '//cdn.staticfile.org/';

// 订单超时失效时间：300s
$conf['config_order_timeout'] = 600;
