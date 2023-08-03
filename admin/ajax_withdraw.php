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
		if (!in_array($status, [1, 2, 3, 4])) {
			exit('{"code":400,"msg":"参数错误"}');
		}

		$order = $DB->getRow("select * from pre_withdraw_order where trade_no = '{$trade_no}' limit 1");
		if (empty($order)) {
			exit('{"code":400,"msg":"订单不存在"}');
		}

		if ($status == 1) { // 已付款
			if ($order['status'] != 0) {
				exit('{"code":400,"msg":"刷新页面重试"}');
			}

			if (!updateWithdrawOrderStatusAndUserMoney($order)) {
				exit('{"code":400,"msg":"修改订单失败1，也可能是商户余额不足，刷新后重试！"}');
			}
		} elseif ($status == 2) { // 驳回
			if ($order['status'] != 0) {
				exit('{"code":400,"msg":"刷新页面重试"}');
			}

			$re = $DB->exec("update pre_withdraw_order set status = 2, endtime = NOW() where status = 0 and trade_no = '{$trade_no}' limit 1");
			if ($re === false) {
				exit('{"code":400,"msg":"修改订单失败2，刷新后重试！"}');
			}
			if ($re == 0) {
				exit('{"code":400,"msg":"修改订单失败3，刷新后重试！"}');
			}
		} elseif ($status == 3) { // 强制驳回
			if ($order['status'] != 1) {
				exit('{"code":400,"msg":"刷新页面重试"}');
			}

			if (!updateWithdrawOrderStatusAndUserMoneyEnforceReject($order)) {
				exit('{"code":400,"msg":"修改订单失败4，刷新后重试！"}');
			}
		} else { // 强制完成
			if ($order['status'] != 2) {
				exit('{"code":400,"msg":"刷新页面重试"}');
			}

			if (!updateWithdrawOrderStatusAndUserMoneyEnforceSuccess($order)) {
				exit('{"code":400,"msg":"修改订单失败1，也可能是商户余额不足，刷新后重试！"}');
			}
		}

		// 通知商户
		$order['status'] = $status;
		notifyCustomWithdraw($order);

		exit('{"code":200}');

		break;
	case 'order': //订单详情
		$trade_no = trim($_GET['trade_no']);
		$row = $DB->getRow("select * from pre_withdraw_order where trade_no='$trade_no' limit 1");
		if (!$row)
			exit('{"code":-1,"msg":"当前订单不存在！"}');
		$result = array("code" => 0, "msg" => "succ", "data" => $row);
		exit(json_encode($result));
		break;
	case 'notify': //手动发起通知
		$trade_no = trim($_POST['trade_no']);
		$row = $DB->getRow("select * from pre_withdraw_order where trade_no='$trade_no' limit 1");
		if (!$row) {
			exit('{"code":-1,"msg":"当前订单不存在！"}');
		}
		if ($row['status'] == 0) {
			exit('{"code":-1,"msg":"当前订单未完成！"}');
		}

		notifyCustomWithdraw($row);

		exit('{"code":0,"msg":"已发出通知！"}');
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
