<?php

/**
 * 页面跳转支付
 * 
 * 客户完成支付数据之后，跳转到此页面进行支付。
 * 
 * 支持 GET，POST 提交。
 */

header('Content-Type: text/html; charset=UTF-8');

$is_defend = true;
$nosession = true;
require '../includes/common.php';

if (isset($_GET['pid'])) {
	$queryArr = $_GET;
} elseif (isset($_POST['pid'])) {
	$queryArr = $_POST;
} else {
	sysmsg('参数错误！');
}

addLog("[下单]" . json_encode($queryArr, 320));

if (!isset($queryArr['type']) || !in_array($queryArr['type'], ['charge', 'bank_card'])) {
	sysmsg('type参数错误');
}
$payChannelType = $queryArr['type'];

$typeInfo = $DB->getRow("SELECT * FROM pre_type where `name` = 'charge' limit 1");

if (!isset($queryArr['sign'])) {
	sysmsg('缺少参数sign');
}
if (!isset($queryArr['sign_type']) || $queryArr['sign_type'] != 'MD5') {
	sysmsg('缺少参数sign_type');
}

use \lib\PayUtils;

$prestr = PayUtils::createLinkstring(PayUtils::argSort(PayUtils::paraFilter($queryArr)));
$pid = intval($queryArr['pid']);
if (empty($pid)) sysmsg('PID不存在');
$userrow = $DB->getRow("SELECT * FROM `pre_user` WHERE `uid`='{$pid}' LIMIT 1");
if (!$userrow) sysmsg('商户不存在！');
if (!PayUtils::md5Verify($prestr, $queryArr['sign'], $userrow['key'])) sysmsg('签名校验失败，请返回重试！');

if ($userrow['status'] == 0 || $userrow['pay'] == 0) sysmsg('商户已封禁，无法支付！');

$out_trade_no = daddslashes($queryArr['out_trade_no']);
$notify_url = htmlspecialchars(daddslashes($queryArr['notify_url']));
$name = htmlspecialchars(daddslashes($queryArr['name']));
$money = daddslashes($queryArr['money']);
$param = isset($queryArr['param']) ? htmlspecialchars(daddslashes($queryArr['param'])) : null;

if (empty($out_trade_no)) sysmsg('订单号(out_trade_no)不能为空');
if (empty($notify_url) || !isUrl($notify_url)) sysmsg('通知地址(notify_url)有误');
if (empty($name)) sysmsg('商品名称(name)不能为空');
if (empty($money)) sysmsg('金额(money)不能为空');
if ($money <= 0 || !is_numeric($money) || !preg_match('/^[0-9]+$/', $money) || $money[0] == '0') sysmsg('金额为大于0的整数');
if (!checkOrderFormat($out_trade_no)) sysmsg('订单号(out_trade_no)格式不正确');

$domain = getdomain($notify_url);

if (strlen($name) > 127) $name = mb_strcut($name, 0, 127, 'utf-8');

// 单次支付金额限制
if (intval($money) < $userrow['pay_min'] || intval($money) > $userrow['pay_max']) {
	sysmsg(sprintf("单次支付金额限制：%d - %d", $userrow['pay_min'], $userrow['pay_max']));
}

// 校验重复的订单号
$log = $DB->getRow("SELECT trade_no FROM pre_order WHERE `uid` = :uid AND `out_trade_no` = :out_trade_no LIMIT 1", [':uid' => $pid, ':out_trade_no' => $out_trade_no]);
if (!empty($log)) {
	sysmsg('商户订单号重复');
}

$channel = [];

// 支付通道
$channelId = getChannelByUid($userrow['uid'], $payChannelType);

if ($channelId) {

	$random = getOrderLastMoney($channelId);
	if ($random <= 0) {
		sysmsg('下单失败，请稍后重试');
	}

	// 区分转数快费率和银行卡费率
	$pay_rate = $userrow['pay_rate'];
	$channel = $DB->getRow("SELECT * FROM pre_channel WHERE id = {$channelId} LIMIT 1");
	if ($channel['channel_type'] == 2) {
		$pay_rate = $userrow['pay_rate_bank'];
	}

	// 对商品金额减去1，然后加上随机两位小数
	$realmoney = round($money - 1 + $random, 2);
	// 手续费为订单金额*费率
	$getmoney = round($money * $pay_rate / 100, 2);

	// 代理收益
	$agent_getmoney = $getmoney * $userrow['agent_pay_rate'] / 100;

	$trade_no = 'P' . date("YmdHis") . rand(11111, 99999);
	if (!$DB->exec("INSERT INTO `pre_order` (`trade_no`,`out_trade_no`,`uid`,`addtime`,`date`,`name`,`money`,`notify_url`,`param`,`domain`,`ip`,`status`, `type`, `channel`, `realmoney`, `getmoney`, 'agent_id', 'agent_pay_rate', 'agent_getmoney') VALUES (:trade_no, :out_trade_no, :uid, NOW(), CURDATE(), :name, :money, :notify_url, :param, :domain, :clientip, 0, :type, :channel, :realmoney, :getmoney, :agent_id, :agent_pay_rate, :agent_getmoney)", [':trade_no' => $trade_no, ':out_trade_no' => $out_trade_no, ':uid' => $pid, ':name' => $name, ':money' => $money, ':notify_url' => $notify_url, ':domain' => $domain, ':clientip' => $clientip, ':param' => $param, ':type' => $typeInfo['id'], ':channel' => $channelId, ':realmoney' => $realmoney, ':getmoney' => $getmoney, ':agent_id' => $userrow['agent_id'], ':agent_pay_rate' => $userrow['agent_pay_rate'], ':agent_getmoney' => $agent_getmoney])) {
		sysmsg('商户订单号重复');
	}
} else {
	addLog("[channel_error]" . json_encode($queryArr, 320));
	sysmsg('<center>支付通道配置错误！</center>', '跳转提示');
}

$order['trade_no'] = $trade_no;
$order['out_trade_no'] = $out_trade_no;
$order['uid'] = $pid;
$order['addtime'] = $date;
$order['name'] = $name;
$order['realmoney'] = $realmoney;
$order['type'] = $typeInfo['id'];
$order['channel'] = $channelId;
$order['typename'] = $typeInfo['name'];

$lang = $queryArr['lang'] ?? '';

try {
	$result = loadForSubmitNew($trade_no);
	echo $result['data'];
} catch (Exception $e) {
	sysmsg($e->getMessage());
}
