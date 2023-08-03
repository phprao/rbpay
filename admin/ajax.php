<?php
include("../includes/common.php");
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

if (!checkRefererHost()) exit('{"code":403}');

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
	case 'getcount':
		$count1 = $DB->getColumn("SELECT count(*) from pre_order where status = 1");
		$count2 = $DB->getColumn("SELECT count(*) from pre_user");
		$usermoney = $DB->getColumn("SELECT SUM(money) FROM pre_user");

		$paytype = [];
		$rs = $DB->getAll("SELECT * FROM pre_type WHERE status=1");
		foreach ($rs as $row) {
			$paytype[$row['id']] = $row['showname'];
		}
		unset($rs);

		$channel = [];
		$rs = $DB->getAll("SELECT * FROM pre_channel WHERE status=1");
		foreach ($rs as $row) {
			$channel[$row['id']] = $row['name'];
		}
		unset($rs);

		$today = date("Y-m-d"); // 今天
		$yestoday = date("Y-m-d", strtotime("-1 day")); // 昨天
		$thirthday = date("Y-m-d", strtotime("-2 days")); // 前天

		foreach ($paytype as $k => $v) {
			$order_paytype[$k][0] = 0; // 今天
			$order_paytype[$k][1] = 0; // 昨天
			$order_paytype[$k][2] = 0; // 前天
		}
		foreach ($channel as $k => $v) {
			$order_channel[$k][0] = 0;
			$order_channel[$k][1] = 0;
			$order_channel[$k][2] = 0;
		}

		$paytypesum = [0, 0, 0];
		$channelsum = [0, 0, 0];
		$rs = $DB->query("SELECT * from pre_order where status = 1 and date >= '{$thirthday}'");
		while ($row = $rs->fetch()) {
			$d = $row['date'];
			if (isset($order_paytype[$row['type']])) {
				if ($d == $today) {
					$order_paytype[$row['type']][0] += $row['realmoney'];
					$paytypesum[0] += $row['realmoney'];
				} elseif ($d == $yestoday) {
					$order_paytype[$row['type']][1] += $row['realmoney'];
					$paytypesum[1] += $row['realmoney'];
				} else {
					$order_paytype[$row['type']][2] += $row['realmoney'];
					$paytypesum[2] += $row['realmoney'];
				}
			}
			if (isset($order_channel[$row['channel']])) {
				if ($d == $today) {
					$order_channel[$row['channel']][0] += $row['realmoney'];
					$channelsum[0] += $row['realmoney'];
				} elseif ($d == $yestoday) {
					$order_channel[$row['channel']][1] += $row['realmoney'];
					$channelsum[1] += $row['realmoney'];
				} else {
					$order_channel[$row['channel']][2] += $row['realmoney'];
					$channelsum[2] += $row['realmoney'];
				}
			}
		}

		$order_paytype_list = [];
		foreach ($order_paytype as $k => $v) {
			array_push($order_paytype_list, [
				'name' => $paytype[$k] . "({$k})",
				'today' => sprintf("%.2f", $v[0]),
				'yestoday' => sprintf("%.2f", $v[1]),
				'thirthday' => sprintf("%.2f", $v[2]),
			]);
		}
		$order_channel_list = [];
		foreach ($order_channel as $k => $v) {
			array_push($order_channel_list, [
				'name' => $channel[$k] . "({$k})",
				'today' => sprintf("%.2f", $v[0]),
				'yestoday' => sprintf("%.2f", $v[1]),
				'thirthday' => sprintf("%.2f", $v[2]),
			]);
		}
		array_unshift($order_paytype_list, [
			'name' => '<span style="color:green">合计</span>',
			'today' => '<span style="color:green">' . $paytypesum[0] . '</span>',
			'yestoday' => '<span style="color:green">' . $paytypesum[1] . '</span>',
			'thirthday' => '<span style="color:green">' . $paytypesum[2] . '</span>',
		]);
		array_unshift($order_channel_list, [
			'name' => '<span style="color:green">合计</span>',
			'today' => '<span style="color:green">' . $channelsum[0] . '</span>',
			'yestoday' => '<span style="color:green">' . $channelsum[1] . '</span>',
			'thirthday' => '<span style="color:green">' . $channelsum[2] . '</span>',
		]);

		$result = [
			"code" => 0,
			"type" => "online",
			"count1" => $count1,
			"count2" => $count2,
			"usermoney" => round($usermoney, 2),
			"paytype" => $order_paytype_list,
			"channel" => $order_channel_list,
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
		$re1 = $DB->getRow("SELECT SUM(money) as money, SUM(realmoney) as realmoney, SUM(getmoney) as getmoney from pre_order where status = 1 and addtime >= '{$thismonthstart}' and addtime <= '{$thismonthend}'");
		if (!empty($re1)) {
			$result['order_month'][0]['money'] = sprintf("%.2f", $re1['money']);
			$result['order_month'][0]['realmoney'] = sprintf("%.2f", $re1['realmoney']);
			$result['order_month'][0]['getmoney'] = sprintf("%.2f", $re1['getmoney']);
		}
		$re2 = $DB->getRow("SELECT SUM(money) as withdrawmoney, SUM(getmoney) as withdrawgetmoney from pre_withdraw_order where status in (1, 4) and addtime >= '{$thismonthstart}' and addtime <= '{$thismonthend}'");
		if (!empty($re2)) {
			$result['order_month'][0]['withdrawmoney'] = sprintf("%.2f", $re2['withdrawmoney']);
			$result['order_month'][0]['withdrawgetmoney'] = sprintf("%.2f", $re2['withdrawgetmoney']);
		}

		// 上月
		$result['order_month'][1]['month'] = date('Y-m', strtotime('-1 month'));
		$lastmonthstart = date('Y-m-01 00:00:00', strtotime('-1 month'));
		$re3 = $DB->getRow("SELECT SUM(money) as money, SUM(realmoney) as realmoney, SUM(getmoney) as getmoney from pre_order where status = 1 and addtime >= '{$lastmonthstart}' and addtime < '{$thismonthstart}'");
		if (!empty($re3)) {
			$result['order_month'][1]['money'] = sprintf("%.2f", $re3['money']);
			$result['order_month'][1]['realmoney'] = sprintf("%.2f", $re3['realmoney']);
			$result['order_month'][1]['getmoney'] = sprintf("%.2f", $re3['getmoney']);
		}
		$re4 = $DB->getRow("SELECT SUM(money) as withdrawmoney, SUM(getmoney) as withdrawgetmoney from pre_withdraw_order where status in (1, 4) and addtime >= '{$lastmonthstart}' and addtime < '{$thismonthstart}'");
		if (!empty($re4)) {
			$result['order_month'][1]['withdrawmoney'] = sprintf("%.2f", $re4['withdrawmoney']);
			$result['order_month'][1]['withdrawgetmoney'] = sprintf("%.2f", $re4['withdrawgetmoney']);
		}

		exit(json_encode($result));
		break;
	case 'set':
		foreach ($_POST as $k => $v) {
			saveSetting($k, $v);
		}
		$ad = $CACHE->clear();
		if ($ad) exit('{"code":0,"msg":"succ"}');
		else exit('{"code":-1,"msg":"修改设置失败[' . $DB->error() . ']"}');
		break;
	case 'setGonggao':
		$id = intval($_GET['id']);
		$status = intval($_GET['status']);
		$sql = "UPDATE pre_anounce SET status='$status' WHERE id='$id'";
		if ($DB->exec($sql)) exit('{"code":0,"msg":"修改状态成功！"}');
		else exit('{"code":-1,"msg":"修改状态失败[' . $DB->error() . ']"}');
		break;
	case 'delGonggao':
		$id = intval($_GET['id']);
		$sql = "DELETE FROM pre_anounce WHERE id='$id'";
		if ($DB->exec($sql)) exit('{"code":0,"msg":"删除公告成功！"}');
		else exit('{"code":-1,"msg":"删除公告失败[' . $DB->error() . ']"}');
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
