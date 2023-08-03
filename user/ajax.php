<?php
include("../includes/common.php");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if (!checkRefererHost()) exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
	case 'login':
		$type = intval($_POST['type']);
		$user = trim(daddslashes($_POST['user']));
		$pass = trim(daddslashes($_POST['pass']));
		if (empty($user) || empty($pass)) exit('{"code":-1,"msg":"请确保各项不能为空"}');
		if (!$_POST['csrf_token'] || $_POST['csrf_token'] != $_SESSION['csrf_token']) exit('{"code":-1,"msg":"CSRF TOKEN ERROR"}');

		if ($conf['captcha_open_login'] == 1) {
			if (!isset($_SESSION['gtserver'])) exit('{"code":-1,"msg":"验证加载失败"}');
			$GtSdk = new \lib\GeetestLib($conf['captcha_id'], $conf['captcha_key']);
			$data = array(
				'user_id' => 'public', # 网站用户id
				'client_type' => "web", # web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
				'ip_address' => $clientip # 请在此处传输用户请求验证时所携带的IP
			);
			if ($_SESSION['gtserver'] == 1) {   //服务器正常
				$result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
				if ($result) {
					//echo '{"status":"success"}';
				} else {
					exit('{"code":-1,"msg":"验证失败，请重新验证"}');
				}
			} else {  //服务器宕机,走failback模式
				if ($GtSdk->fail_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'])) {
					//echo '{"status":"success"}';
				} else {
					exit('{"code":-1,"msg":"验证失败，请重新验证"}');
				}
			}
		}

		if ($type == 1 && is_numeric($user) && strlen($user) <= 6) $type = 0;
		if ($type == 1) {
			$userrow = $DB->getRow("SELECT * FROM pre_user WHERE phone='{$user}' limit 1");
			$pass = getMd5Pwd($pass, $userrow['uid']);
		} else {
			$userrow = $DB->getRow("SELECT * FROM pre_user WHERE uid='{$user}' limit 1");
		}
		if ($userrow && ($type == 0 && $pass == $userrow['key'] || $type == 1 && $pass == $userrow['pwd'])) {
			$uid = $userrow['uid'];
			$DB->exec("insert into `pre_log` (`uid`,`type`,`date`,`ip`) values ('" . $uid . "','普通登录', NOW(),'" . $clientip . "')");
			$session = md5($uid . $userrow['key'] . $password_hash);
			$expiretime = time() + 604800;
			$token = authcode("{$uid}\t{$session}\t{$expiretime}", 'ENCODE', SYS_KEY);
			ob_clean();
			setcookie("user_token", $token, time() + 604800);
			$DB->exec("update `pre_user` set `lasttime` = NOW() where `uid`='$uid'");
			$result = array("code" => 0, "msg" => "登录成功！正在跳转到用户中心", "url" => "./");
			unset($_SESSION['csrf_token']);
		} else {
			$result = array("code" => -1, "msg" => "用户名或密码不正确！");
		}
		exit(json_encode($result));
		break;
	case 'captcha':
		$GtSdk = new \lib\GeetestLib($conf['captcha_id'], $conf['captcha_key']);
		$data = array(
			'user_id' => isset($uid) ? $uid : 'public', # 网站用户id
			'client_type' => "web", # web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
			'ip_address' => $clientip # 请在此处传输用户请求验证时所携带的IP
		);
		$status = $GtSdk->pre_process($data, 1);
		$_SESSION['gtserver'] = $status;
		echo $GtSdk->get_response_str();
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
