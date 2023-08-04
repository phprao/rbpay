<?php
include("../includes/common.php");
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if (!checkRefererHost()) exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
	case 'setAgent':
		$id = intval($_GET['id']);
		$status = intval($_GET['status']);
		$sql = "UPDATE pre_agent SET agent_status='$status' WHERE id='$id'";
		if ($DB->exec($sql) !== false) exit('{"code":0,"msg":"修改成功！"}');
		else exit('{"code":-1,"msg":"修改失败[' . $DB->error() . ']"}');
		break;
	case 'recharge':
		$id = intval($_POST['id']);
		$do = $_POST['actdo'];
		$rmb = floatval($_POST['rmb']);
		$row = $DB->getRow("select * from pre_agent where id='$id' limit 1");
		if (!$row)
			exit('{"code":-1,"msg":"当前用户不存在！"}');
		if ($do == 1) {
			if ($rmb > $row['agent_money']) exit('{"code":-1,"msg":"余额不足！"}');

			changeAgentMoney($id, $rmb, RECORD_ACTION_DEC, RECORD_TYPE_DLTX);
		}
		exit('{"code":0,"msg":"succ"}');
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
