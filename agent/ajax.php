<?php
include("../includes/common.php");
if (isset($islogin_agent) && $islogin_agent == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if (!checkRefererHost()) exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
	case 'getcount':
		$agent = $DB->getRow("select * from pre_agent where id = {$agent_id} limit 1");
		$count1 = $DB->getColumn("SELECT count(*) from pre_order where status = 1 and agent_id = {$agent_id}");
		$count2 = $DB->getColumn("SELECT count(*) from pre_user where status = 1 and agent_id = {$agent_id}");
		$usermoney = $agent['agent_money'];

		$today = date("Y-m-d"); // 今天
		$yestoday = date("Y-m-d", strtotime("-1 day")); // 昨天
		$thirthday = date("Y-m-d", strtotime("-2 days")); // 前天

		$paytypesum = [0, 0, 0];
		$rs = $DB->query("SELECT * from pre_order where status = 1 and agent_id = {$agent_id} and date >= '{$thirthday}'");
		while ($row = $rs->fetch()) {
			$d = $row['date'];
			if ($d == $today) {
				$paytypesum[0] += $row['agent_getmoney'];
			} elseif ($d == $yestoday) {
				$paytypesum[1] += $row['agent_getmoney'];
			} else {
				$paytypesum[2] += $row['agent_getmoney'];
			}
		}

		$order_paytype_list = [
			'today' => '<span style="color:green">' . $paytypesum[0] . '</span>',
			'yestoday' => '<span style="color:green">' . $paytypesum[1] . '</span>',
			'thirthday' => '<span style="color:green">' . $paytypesum[2] . '</span>',
		];

		$result = [
			"code" => 0,
			"type" => "online",
			"count1" => $count1,
			"count2" => $count2,
			"usermoney" => round($usermoney, 2),
			"paytype" => $order_paytype_list,
			"order_month" => [
				[
					'month' => '',
					'money' => '0.00', 'realmoney' => '0.00', 'getmoney' => '0.00',
					'withdrawmoney' => '0.00', 'withdrawgetmoney' => '0.00'
				],
				[
					'month' => '',
					'money' => '0.00', 'realmoney' => '0.00', 'getmoney' => '0.00',
					'withdrawmoney' => '0.00', 'withdrawgetmoney' => '0.00'
				],
			],
		];

		// 本月
		$result['order_month'][0]['month'] = date('Y-m');
		$thismonthstart = date('Y-m-01 00:00:00');
		$thismonthend = date('Y-m-d 23:59:59');
		$re1 = $DB->getRow("SELECT SUM(agent_getmoney) as money from pre_order where status = 1 and agent_id = {$agent_id} and addtime >= '{$thismonthstart}' and addtime <= '{$thismonthend}'");
		if (!empty($re1)) {
			$result['order_month'][0]['money'] = sprintf("%.2f", $re1['money']);
		}
		$re2 = $DB->getRow("SELECT SUM(agent_getmoney) as withdrawmoney from pre_withdraw_order where status in (1, 4) and agent_id = {$agent_id} and addtime >= '{$thismonthstart}' and addtime <= '{$thismonthend}'");
		if (!empty($re2)) {
			$result['order_month'][0]['withdrawmoney'] = sprintf("%.2f", $re2['withdrawmoney']);
		}

		// 上月
		$result['order_month'][1]['month'] = date('Y-m', strtotime('-1 month'));
		$lastmonthstart = date('Y-m-01 00:00:00', strtotime('-1 month'));
		$re3 = $DB->getRow("SELECT SUM(agent_getmoney) as money from pre_order where status = 1 and agent_id = {$agent_id} and addtime >= '{$lastmonthstart}' and addtime < '{$thismonthstart}'");
		if (!empty($re3)) {
			$result['order_month'][1]['money'] = sprintf("%.2f", $re3['money']);
		}
		$re4 = $DB->getRow("SELECT SUM(agent_getmoney) as withdrawmoney from pre_withdraw_order where status in (1, 4) and agent_id = {$agent_id} and addtime >= '{$lastmonthstart}' and addtime < '{$thismonthstart}'");
		if (!empty($re4)) {
			$result['order_month'][1]['withdrawmoney'] = sprintf("%.2f", $re4['withdrawmoney']);
		}

		exit(json_encode($result));
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
