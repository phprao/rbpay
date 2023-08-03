<?php

header("Content-Type: application/json; charset=utf-8");

try {
    $trade_no = addslashes($_GET['trade_no'] ?? "");

    if (empty($trade_no)) {
        throw new Exception("参数错误");
    }

    require '../includes/common.php';

    if (!checkOrderFormat($trade_no)) {
        throw new Exception("");
    }

    $row = $DB->getRow("SELECT * FROM `pre_order` WHERE `trade_no` = :trader_no LIMIT 1", [':trader_no' => $trade_no]);
    if (empty($row)) {
        throw new Exception("");
    }

    if ($row['status'] == 0) {
        throw new Exception("");
    } else {
        exitWithJson(0, "");
    }
} catch (Exception $e) {
    exitWithJson(6000, $e->getMessage());
}
