<?php
$clientip = real_ip(2);

if (isset($_COOKIE["admin_token"])) {
	$token = authcode(daddslashes($_COOKIE['admin_token']), 'DECODE', SYS_KEY);
	list($user, $sid, $expiretime) = explode("\t", $token);

	$islogin = 0;
	$ismain = 0;
	if ($expiretime > time()) {
		if (md5($conf['admin_user'] . $conf['admin_pwd'] . $password_hash) == $sid) {
			$islogin = 1;
			$ismain = 1;
		}
		if (md5($conf['son_user'] . $conf['son_pwd'] . $password_hash) == $sid) {
			$islogin = 1;
			$ismain = 0;
		}
	}
}

if (isset($_COOKIE["user_token"])) {
	$token = authcode(daddslashes($_COOKIE['user_token']), 'DECODE', SYS_KEY);
	list($uid, $sid, $expiretime) = explode("\t", $token);
	$uid = intval($uid);
	$userrow = $DB->getRow("SELECT * FROM pre_user WHERE uid='{$uid}' limit 1");
	$session = md5($userrow['uid'] . $userrow['key'] . $password_hash);
	if ($session == $sid && $expiretime > time()) {
		$islogin2 = 1;
	}
}
