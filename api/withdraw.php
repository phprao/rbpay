<?php

/**
 * 客户体现接口
 * 
 * 玩家向客户的服务器发起体现申请，由客户服务器负责校验，校验通过后，客户服务器向我服务器调用此接口。
 * 
 * 需提供客户服务器的IP，加入到我们的IP白名单。
 */

require '../includes/common.php';

use \lib\PayUtils;

try {
    $queryArr = $_POST;

    // $queryArr = [
    //     'uid' => '1001',
    //     'out_trade_no' => 'C2022080909522472338',
    //     'notify_url' => 'http://103.44.251.44/notify_custom.php',
    //     'money' => '200',
    //     'bankname' => '交通银行',
    //     'account' => '111111111',
    //     'username' => 'xxxxxxx',
    //     'sign' => 'acc2959966a59154a3f851a920ff538b',
    //     'sign_type' => 'SHA1',
    // ];

    addLog('[提现订单]' . json_encode($queryArr, 320));

    $prestr = PayUtils::createLinkstring(PayUtils::argSort(PayUtils::paraFilter($queryArr)));
    $uid = intval($queryArr['uid'] ?? '');

    if (empty($uid)) exitWithJson(6000, "uid为空");

    $userrow = $DB->getRow("SELECT * FROM `pre_user` WHERE `uid`='{$uid}' LIMIT 1");
    if (!$userrow) exitWithJson(6000, '商户不存在');

    if (!PayUtils::sha1Verify($prestr, $queryArr['sign'], $userrow['key'])) exitWithJson(6000, '签名校验失败，请返回重试');

    if ($userrow['status'] == 0 || $userrow['withdraw'] == 0) exitWithJson(6000, '商户已封禁，无法提现');

    // IP 白名单
    if (empty($userrow['ip_white_list'])) {
        exitWithJson(6000, '该商户还没有设置IP白名单，无法提现');
    }
    $iparr = explode('|', $userrow['ip_white_list']);
    if (!in_array($clientip, $iparr)) {
        exitWithJson(6000, 'IP地址不在白名单之内');
    }

    $out_trade_no = daddslashes($queryArr['out_trade_no']);
    $notify_url = daddslashes($queryArr['notify_url']);
    $money = daddslashes($queryArr['money']);
    $bankname = daddslashes($queryArr['bankname']);
    $account = daddslashes($queryArr['account']);
    $username = daddslashes($queryArr['username']);
    $channel_type = intval(daddslashes($queryArr['channel_type'] ?? "")); // 1-转数快，2-银行卡
    if (empty($channel_type)) $channel_type = 1;

    if (empty($out_trade_no)) exitWithJson(6000, '订单号(out_trade_no)不能为空');
    if (empty($notify_url) || !isUrl($notify_url)) exitWithJson(6000, '通知地址(notify_url)有误');
    if (empty($bankname)) exitWithJson(6000, '提现(bankname)不能为空');
    if (empty($account)) exitWithJson(6000, '提现(account)不能为空');
    if (empty($username)) exitWithJson(6000, '提现(username)不能为空');
    if (!in_array($channel_type, [1, 2])) exitWithJson(6000, '提现(channel_type)错误');

    if ($money <= 0 || !is_numeric($money) || !preg_match('/^[0-9]+$/', $money) || $money[0] == '0') exitWithJson(6000, '金额为大于0的整数');

    if (!preg_match('/^[a-zA-Z0-9.\_\-|]+$/', $out_trade_no)) exitWithJson(6000, '订单号(out_trade_no)格式不正确');

    $trade_no = 'W' . date("YmdHis") . rand(11111, 99999);

    // 单次提现金额限制
    if (intval($money) < $userrow['withdraw_min'] || intval($money) > $userrow['withdraw_max']) {
        exitWithJson(6000, sprintf("单次提现金额限制：%d - %d", $userrow['withdraw_min'], $userrow['withdraw_max']));
    }

    // 订单号是否重复
    $o = $DB->getRow("SELECT * FROM `pre_withdraw_order` WHERE `uid`='{$uid}' and `out_trade_no`='{$out_trade_no}' LIMIT 1");
    if (!empty($o)) {
        exitWithJson(6000, '商户订单号重复');
    }

    // 提现手续费从商家余额中扣除
    $rate = $channel_type == 1 ? $userrow['withdraw_rate'] : $userrow['withdraw_rate_bank'];
    if ($rate >= 100 || $rate < 0) {
        exitWithJson(6000, '提现费率设置有误');
    }
    $money = intval($money);
    $getmoney = sprintf("%.2f", $money * $rate / 100);
    $realmoney = $money;

    // 校验商家余额
    if ($userrow['money'] < ($realmoney + $getmoney)) {
        exitWithJson(6000, '商户余额不足');
    }

    // 代理收益
    $agent_getmoney = $getmoney * $userrow['agent_withdraw_rate'] / 100;

    // 写入订单
    $re = $DB->exec("INSERT INTO `pre_withdraw_order` (`trade_no`,`out_trade_no`,`uid`,`addtime`,`date`,`money`,`realmoney`,`getmoney`,`notify_url`,`bankname`,`account`,`username`,`ip`,`agent_id`,`agent_withdraw_rate`,`agent_getmoney`) VALUES (:trade_no, :out_trade_no, :uid, NOW(), CURDATE(), :money, :realmoney, :getmoney, :notify_url, :bankname, :account, :username, :clientip, :agent_id, :agent_withdraw_rate, :agent_getmoney)", [':trade_no' => $trade_no, ':out_trade_no' => $out_trade_no, ':uid' => $uid, ':money' => $money, ':realmoney' => $realmoney, ':getmoney' => $getmoney, ':notify_url' => $notify_url, ':bankname' => $bankname, ':account' => $account, ':username' => $username, ':clientip' => $clientip, ':agent_id' => $userrow['agent_id'], ':agent_withdraw_rate' => $userrow['agent_withdraw_rate'], ':agent_getmoney' => $agent_getmoney]);

    if (!$re) {
        throw new Exception("pre_withdraw_order写入失败");
    }

    exitWithJson(0, $trade_no);
} catch (Exception $e) {
    $err = $e->getLine() . ": " . $e->getMessage();

    exitWithJsonLog(6100, "服务器错误", $err, "ERROR");
}
