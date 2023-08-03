<?php
include("../includes/common.php");
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if (!checkRefererHost()) exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
	case 'getChannel':
		$id = intval($_GET['id']);
		$row = $DB->getRow("SELECT * FROM pre_channel WHERE id='$id'");
		if (!$row)
			exit('{"code":-1,"msg":"当前支付通道不存在！"}');
		$result = ['code' => 0, 'msg' => 'succ', 'data' => $row];
		exit(json_encode($result));
		break;
	case 'setChannel':
		$id = intval($_GET['id']);
		$status = intval($_GET['status']);
		$row = $DB->getRow("SELECT * FROM pre_channel WHERE id='$id'");
		if (!$row)
			exit('{"code":-1,"msg":"当前支付通道不存在！"}');
		$sql = "UPDATE pre_channel SET status='$status' WHERE id='$id'";
		if ($DB->exec($sql)) exit('{"code":0,"msg":"修改支付通道成功！"}');
		else exit('{"code":-1,"msg":"修改支付通道失败[' . $DB->error() . ']"}');
		break;
	case 'saveChannel':
		if ($_POST['action'] == 'add') {
			$name = trim($_POST['name']);
			$appid = trim($_POST['appid']);
			$appkey = trim($_POST['appkey']);
			$channel_type = trim($_POST['channel_type']);
			$bank_code = trim($_POST['bank_code']);
			if (empty($name) || empty($appid) || empty($appkey) || empty($channel_type)) {
				exit('{"code":-1,"msg":"参数不能为空"}');
			}

			$row = $DB->getRow("SELECT * FROM pre_channel WHERE name='$name' LIMIT 1");
			if ($row) {
				exit('{"code":-1,"msg":"支付通道名称重复"}');
			}

			$row = $DB->getRow("SELECT * FROM pre_channel WHERE appid='$appid' LIMIT 1");
			if ($row) {
				exit('{"code":-1,"msg":"支付通道卡号重复"}');
			}

			$sql = "INSERT INTO pre_channel (name, appid, appkey, channel_type, bank_code) VALUES ('{$name}', '{$appid}', '{$appkey}', '{$channel_type}','{$bank_code}')";
			if ($DB->exec($sql)) exit('{"code":0,"msg":"新增支付通道成功！"}');
			else exit('{"code":-1,"msg":"新增支付通道失败[' . $DB->error() . ']"}');
		} else {
			$id = intval($_POST['id']);
			$row = $DB->getRow("SELECT * FROM pre_channel WHERE id='$id'");
			if (!$row) exit('{"code":-1,"msg":"当前支付通道不存在！"}');

			$name = trim($_POST['name']);
			$appid = trim($_POST['appid']);
			$appkey = trim($_POST['appkey']);
			$channel_type = trim($_POST['channel_type']);
			$bank_code = trim($_POST['bank_code']);

			if (empty($name) || empty($appid) || empty($appkey) || empty($channel_type)) {
				exit('{"code":-1,"msg":"参数不能为空"}');
			}

			$row = $DB->getRow("SELECT * FROM pre_channel WHERE name='$name' AND id<>$id LIMIT 1");
			if ($row)
				exit('{"code":-1,"msg":"支付通道名称重复"}');

			$row = $DB->getRow("SELECT * FROM pre_channel WHERE appid='$appid' AND id<>$id LIMIT 1");
			if ($row)
				exit('{"code":-1,"msg":"支付通道卡号重复"}');

			$sql = "UPDATE pre_channel SET name='{$name}',appid='{$appid}',appkey='{$appkey}',channel_type='{$channel_type}',bank_code='{$bank_code}' WHERE id='$id'";
			if ($DB->exec($sql) !== false) {
				exit('{"code":0,"msg":"修改支付通道成功！"}');
			} else exit('{"code":-1,"msg":"修改支付通道失败[' . $DB->error() . ']"}');
		}
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
