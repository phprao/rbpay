<?php
include("../includes/common.php");
if (isset($islogin_agent) && $islogin_agent == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if (!checkRefererHost()) exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
	case 'order': //订单详情
		$trade_no = trim($_GET['trade_no']);
		$row = $DB->getRow("select * from pre_withdraw_order where trade_no='$trade_no' limit 1");
		if (!$row)
			exit('{"code":-1,"msg":"当前订单不存在！"}');
		$result = array("code" => 0, "msg" => "succ", "data" => $row);
		exit(json_encode($result));
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
