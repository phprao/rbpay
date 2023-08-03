<?php

/**
 * POST
 * 
 * body, json
 * 
 * {"pid":"1001","trade_no":"P2022081414340687608","out_trade_no":"C2022081414340238220","type":"charge","name":"VIP年度会员","money":"166.00","param":"","sign":"355188ba76ff2c22b37f407373a04933","sign_type":"MD5","trade_status":"TRADE_SUCCESS"}
 */

require '../includes/common.php';

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
}

$body = file_get_contents('php://input');

addLog('[回调通知]' . $_SERVER['REQUEST_METHOD'] . ' | ' . $body);

$queryArr = json_decode($body, true);
if (empty($queryArr)) {
    exit('error');
}

if (!isset($queryArr['sign']) || empty($queryArr['sign'])) {
    exit('error');
}

if (!isset($queryArr['sign_type']) || empty($queryArr['sign_type'])) {
    exit('error');
}

if (!isset($queryArr['pid']) || empty($queryArr['pid'])) {
    exit('error');
}

$pid = $queryArr['pid'];

use \lib\PayUtils;

$prestr = PayUtils::createLinkstring(PayUtils::argSort(PayUtils::paraFilter($queryArr)));

$userrow = $DB->getRow("SELECT * FROM `pre_user` WHERE `uid`='{$pid}' LIMIT 1");
if (empty($userrow)) {
    exit('error');
}

if (!PayUtils::md5Verify($prestr, $queryArr['sign'], $userrow['key'])) {
    addLog('[回调通知SIGN失败]' . $body);
    exit('error');
}

exit("success");
