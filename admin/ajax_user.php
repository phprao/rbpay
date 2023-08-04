<?php
include("../includes/common.php");
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if (!checkRefererHost()) exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
	case 'setUser':
		$uid = intval($_GET['uid']);
		$type = trim($_GET['type']);
		$status = intval($_GET['status']);
		if ($type == 'pay') $sql = "UPDATE pre_user SET pay='$status' WHERE uid='$uid'";
		elseif ($type == 'withdraw') $sql = "UPDATE pre_user SET withdraw='$status' WHERE uid='$uid'";
		else $sql = "UPDATE pre_user SET status='$status' WHERE uid='$uid'";
		if ($DB->exec($sql) !== false) exit('{"code":0,"msg":"修改用户成功！"}');
		else exit('{"code":-1,"msg":"修改用户失败[' . $DB->error() . ']"}');
		break;
	case 'resetUser':
		$uid = intval($_GET['uid']);
		$key = random(32);
		$sql = "UPDATE pre_user SET `key`='$key' WHERE uid='$uid'";
		if ($DB->exec($sql) !== false) exit('{"code":0,"msg":"重置密钥成功","key":"' . $key . '"}');
		else exit('{"code":-1,"msg":"重置密钥失败[' . $DB->error() . ']"}');
		break;
	case 'recharge':
		$uid = intval($_POST['uid']);
		$do = $_POST['actdo'];
		$rmb = floatval($_POST['rmb']);
		$row = $DB->getRow("select uid,money from pre_user where uid='$uid' limit 1");
		if (!$row)
			exit('{"code":-1,"msg":"当前用户不存在！"}');
		if ($do == 1 && $rmb > $row['money']) $rmb = $row['money'];
		if ($do == 0) {
			changeUserMoney($uid, $rmb, true, RECORD_TYPE_HTJK);
		} else {
			changeUserMoney($uid, $rmb, false, RECORD_TYPE_HTKK);
		}
		exit('{"code":0,"msg":"succ"}');
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
