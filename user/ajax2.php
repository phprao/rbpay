<?php
include("../includes/common.php");
if (isset($islogin2) && $islogin2 == 1) {
} else exit('{"code":-3,"msg":"No Login"}');
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if (!checkRefererHost()) exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
	case 'getchart':
		$end = date('Y-m-d');
		$start = date('Y-m-d', strtotime('-10 days'));

		$days = [];
		for ($i = 9; $i >= 0; $i--) {
			array_push($days, date('Y-m-d', strtotime("-{$i} days")));
		}
		$daystr = "'" . join("','", $days) . "'";

		$list1 = $DB->getAll("SELECT date, SUM(money) as money FROM pre_order WHERE uid = {$uid} AND status = 1 AND date in ({$daystr}) GROUP BY date");
		$dataList1 = [];
		foreach ($days as $day) {
			$dataList1[$day] = 0;
			foreach ($list1 as $d) {
				if ($day == $d['date']) {
					$dataList1[$day] = intval($d['money']);
				}
			}
		}

		$list2 = $DB->getAll("SELECT date, SUM(money) as money FROM pre_withdraw_order WHERE uid = {$uid} AND status in (1, 4) AND date in ({$daystr}) GROUP BY date");
		$dataList2 = [];
		foreach ($days as $day) {
			$dataList2[$day] = 0;
			foreach ($list2 as $d) {
				if ($day == $d['date']) {
					$dataList2[$day] = intval($d['money']);
				}
			}
		}

		$list = [
			['name' => '订单金额', 'lineWidth' => 1, 'data' => array_values($dataList1)],
			['name' => '提现金额', 'lineWidth' => 1, 'data' => array_values($dataList2)],
		];

		exit(json_encode([
			'rqList' => $days,
			'countList' => $list,
		]));

		break;
	case 'getcount':
		$lastday = date("Y-m-d", strtotime("-1 day"));
		$today = date("Y-m-d");

		$orders = $DB->getColumn("SELECT count(*) FROM pre_order WHERE uid={$uid} AND status=1");
		$orders_today = $DB->getColumn("SELECT count(*) from pre_order WHERE uid={$uid} AND status=1 AND date='$today'");

		$withdraw_money = $DB->getColumn("SELECT sum(realmoney) FROM pre_withdraw_order WHERE uid={$uid} and status in (1, 4)");
		$withdraw_money = round($withdraw_money, 2);

		$order_today['all'] = $DB->getColumn("SELECT sum(realmoney) FROM pre_order WHERE uid={$uid} AND status=1 AND date='$today'");
		$order_today['all'] = round($order_today['all'], 2);

		$order_lastday['all'] = $DB->getColumn("SELECT sum(realmoney) FROM pre_order WHERE uid={$uid} AND status=1 AND date='$lastday'");
		$order_lastday['all'] = round($order_lastday['all'], 2);

		$result = ['code' => 0, 'orders' => $orders, 'orders_today' => $orders_today, 'withdraw_money' => $withdraw_money, 'order_today' => $order_today, 'order_lastday' => $order_lastday];
		exit(json_encode($result));
		break;
	case 'edit_info':
		$phone = daddslashes(htmlspecialchars(strip_tags(trim($_POST['phone']))));

		if ($phone == null) {
			exit('{"code":-1,"msg":"请确保每项都不为空"}');
		}

		$sqs = $DB->exec("update `pre_user` set `phone` ='{$phone}' where `uid`='$uid'");

		if ($sqs !== false) {
			exit('{"code":1,"msg":"succ"}');
		} else {
			exit('{"code":-1,"msg":"保存失败！' . $DB->error() . '"}');
		}
		break;
	case 'edit_pwd':
		$oldpwd = trim($_POST['oldpwd']);
		$newpwd = trim($_POST['newpwd']);
		$newpwd2 = trim($_POST['newpwd2']);

		if (!empty($userrow['pwd']) && $oldpwd == null || $newpwd == null || $newpwd2 == null) {
			exit('{"code":-1,"msg":"请确保每项都不为空"}');
		}
		if (!empty($userrow['pwd']) && getMd5Pwd($oldpwd, $uid) != $userrow['pwd']) {
			exit('{"code":-1,"msg":"旧密码不正确"}');
		}
		if ($newpwd != $newpwd2) {
			exit('{"code":-1,"msg":"两次输入密码不一致！"}');
		}
		if ($oldpwd == $newpwd) {
			exit('{"code":-1,"msg":"旧密码和新密码相同！"}');
		}
		if (strlen($newpwd) < 6) {
			exit('{"code":-1,"msg":"新密码不能低于6位"}');
		}
		$pwd = getMd5Pwd($newpwd, $uid);
		$sqs = $DB->exec("update `pre_user` set `pwd` ='{$pwd}' where `uid`='$uid'");
		if ($sqs !== false) {
			exit('{"code":1,"msg":"修改密码成功！请牢记新密码"}');
		} else {
			exit('{"code":-1,"msg":"修改密码失败！' . $DB->error() . '"}');
		}
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
