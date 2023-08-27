<?php

/**
 * POST
 * 
 * uid, trade_no
 */

header("Content-Type: application/json; charset=utf-8");

require '../includes/common.php';

try {

    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception("非法请求");
    }

    $uid = $_POST['uid'] ?? "";
    $out_trade_no = $_POST['out_trade_no'] ?? "";

    if (empty($out_trade_no)) {
        throw new Exception("out_trade_no参数错误");
    }
    if (empty($uid)) {
        throw new Exception("uid参数错误");
    }

    if (!checkOrderFormat($out_trade_no)) {
        throw new Exception("out_trade_no订单格式错误");
    }

    $order = $DB->getRow("SELECT * FROM `pre_order` WHERE `out_trade_no` = :out_trade_no AND `uid` = :uid LIMIT 1", [':out_trade_no' => $out_trade_no, ':uid' => $uid]);

    if (empty($order)) {
        throw new Exception("订单不存在");
    }

    $status = 'SUCCESS';
    if ($order['status'] == 0) {
        $status = 'NOTPAY';
    }

    exitWithJson(0, "", [
        'uid' => $order['uid'],
        'trade_no' => $order['trade_no'],
        'out_trade_no' => $order['out_trade_no'],
        'status' => $status,
    ]);
} catch (Exception $e) {
    exitWithJson(6000, $e->getMessage());
}
