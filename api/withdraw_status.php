<?php

/**
 * POST
 * 
 * pid, trade_no
 */

header("Content-Type: application/json; charset=utf-8");

require '../includes/common.php';

try {

    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception("非法请求");
    }

    $pid = $_POST['pid'] ?? "";
    $out_trade_no = $_POST['out_trade_no'] ?? "";

    if (empty($out_trade_no)) {
        throw new Exception("out_trade_no参数错误");
    }
    if (empty($pid)) {
        throw new Exception("pid参数错误");
    }

    if (!checkOrderFormat($out_trade_no)) {
        throw new Exception("订单格式错误");
    }

    $order = $DB->getRow("SELECT * FROM `pre_withdraw_order` WHERE `out_trade_no` = :out_trade_no AND `uid` = :uid LIMIT 1", [':out_trade_no' => $out_trade_no, ':uid' => $pid]);
    if (empty($order)) {
        throw new Exception("订单不存在");
    }

    if ($order['status'] == 0) {
        $status = 'NOTPAY';
    } elseif ($order['status'] == 1) {
        $status = 'SUCCESS';
    } elseif ($order['status'] == 2) {
        $status = 'REJECT';
    } elseif ($order['status'] == 3) {
        $status = 'ENFORCE_REJECT';
    } else {
        $status = 'ENFORCE_SUCCESS';
    }

    exitWithJson(0, "", [
        'pid' => $order['uid'],
        'trade_no' => $order['trade_no'],
        'out_trade_no' => $order['out_trade_no'],
        'status' => $status,
    ]);
} catch (Exception $e) {
    exitWithJson(6000, $e->getMessage());
}
