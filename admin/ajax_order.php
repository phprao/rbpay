<?php
include("../includes/common.php");
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if (!checkRefererHost()) exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
	case 'setStatus': //改变订单状态
		$trade_no = trim($_GET['trade_no']);
		$status = intval($_GET['status']);
		if ($status != 1) {
			exit('{"code":400,"msg":"参数错误"}');
		}

		$order = $DB->getRow("select * from pre_order where trade_no = '{$trade_no}' limit 1");
		if (empty($order)) {
			exit('{"code":400,"msg":"订单不存在"}');
		}
		if ($order['status'] != 0) {
			exit('{"code":400,"msg":"无需重复操作"}');
		}

		if (updateOrderStatusAndUserMoney($order)) {
			// 通知商户
			$order['status'] = 1;
			notifyCustom($order);

			exit('{"code":200}');
		} else {
			exit('{"code":400,"msg":"修改订单失败，刷新后重试！"}');
		}

		break;
	case 'order': //订单详情
		$trade_no = trim($_GET['trade_no']);
		$row = $DB->getRow("select A.*,B.showname typename,C.name channelname from pre_order A,pre_type B,pre_channel C where trade_no='$trade_no' and A.type=B.id and A.channel=C.id limit 1");
		if (!$row)
			exit('{"code":-1,"msg":"当前订单不存在或未成功选择支付通道！"}');
		$result = array("code" => 0, "msg" => "succ", "data" => $row);
		exit(json_encode($result));
		break;
	case 'notify': //获取回调地址
		$trade_no = trim($_POST['trade_no']);
		$row = $DB->getRow("select * from pre_order where trade_no='$trade_no' limit 1");
		if (!$row) {
			exit('{"code":-1,"msg":"当前订单不存在！"}');
		}
		if ($row['status'] == 0) {
			exit('{"code":-1,"msg":"当前订单未完成！"}');
		}

		notifyCustom($row);

		exit('{"code":0,"msg":"已发出通知！"}');

		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
