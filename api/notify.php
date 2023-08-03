<?php

/**
 * 监控客户端捕获的安卓手机的到账通知后，调用此接口，完成转账订单流程，消息体有sign校验，确保安全
 * 
 * 
 * 匹配逻辑：
 * 
 * 号 金额 时间5分钟内  匹配对应订单
 * 如果匹配到多个，或者没有匹配到：那就不处理 日志提示未匹配 人工处理
 * 
 * 下单需要实际支付的金额 是我们给的 随机一个可以处理一下 尽量不重复，比如实际支付90  则随机金额为90.00到90.99，客户得严格按照这个金额付款，不然不到账得人工处理
 * 
 * 
 * {"package_name":"com.alipay.antbank.hk.portal","text":"您收到一筆HKD199.99的款項，已存入您的存款寶賬戶。","title":"轉賬成功","bigText":"您收到一筆HKD199.99的款項，已存入您的存款寶賬戶。","timestamp":1655418765687,"cardNo":"111111111", "sign":"1a1f89525991250d4a5be13ffea58f0e"}
 * 
 * 
 * {"bigText":"💰❤️ HO H* Y**已向你付款HKD499.97。","cardNo":"55322805","package_name":"com.mox.app","showWhen":true,"sign":"e51076cd581d3220807c72f5e9552152","text":"收到款項","timestamp":1660889030226,"title":"💰❤️ HO H* Y**已向你付款HKD499.97。"}
 * 
 * {"bigText":"💸 你已成功向LAU W*** C**付款HKD10,000.00。","cardNo":"55322805","package_name":"com.mox.app","showWhen":true,"sign":"fa9ad7857990a568b465097b38162504","text":"轉數成功","timestamp":1660889165771,"title":"💸 你已成功向LAU W*** C**付款HKD10,000.00。"}

 */

header("Content-Type: application/json; charset=utf-8");

require '../includes/common.php';

// set key
$key = "1kwKYYyIp5ki124Z5KP1IYwUzc6yzu";

try {
    $body = file_get_contents('php://input');

    addLog('[支付通知]' . $body);

    if (empty($body)) {
        exitWithJsonNoData(6000, "body is empty");
    }
    $notifyInfo = json_decode($body, true);
    if (empty($notifyInfo)) {
        exitWithJsonNoData(6001, "body is not json");
    }

    // cardNo
    if (empty($notifyInfo['cardNo'])) {
        exitWithJsonNoData(6002, "cardNo is empty");
    }

    // timestamp
    if (!isset($notifyInfo['timestamp']) || empty($notifyInfo['timestamp']) || !is_numeric($notifyInfo['timestamp'])) {
        exitWithJsonNoData(6003, "timestamp is empty");
    }

    // sign
    $sign = md5($notifyInfo['cardNo'] . $key . strval($notifyInfo['timestamp']));
    if ($sign !== $notifyInfo['sign']) {
        exitWithJsonNoDataLog(6001, "sign check failed", "sign invalid: getval={$notifyInfo['sign']}, correct={$sign}", "ERROR");
    }

    // packege_name
    if (empty($notifyInfo['package_name'])) {
        exitWithJsonNoData(6001, "skip");
    } else {
        $package_name = $notifyInfo['package_name'];
    }

    // 入账的消息才处理，出账的消息不处理
    if ($package_name == 'com.mox.app') {
        if (isset($notifyInfo['text']) && in_array($notifyInfo['text'], ['收到款項', '收到款项'])) {
            // money
            if (empty($notifyInfo['bigText'])) {
                exitWithJsonNoData(6004, "bigText are empty");
            }
            // 超过 1000 的金额会变成 1,000.00
            if (!preg_match("/HKD([0-9.,]+)/", $notifyInfo['bigText'], $res)) {
                exitWithJsonNoData(6005, "can not find HKD in bigText");
            }
        } else {
            exitWithJsonNoData(6001, "skip");
        }
    } elseif ($package_name == 'com.alipay.antbank.hk.portal') {
        // 蚂蚁银行，暂时不用
        // 
        // if (isset($notifyInfo['title']) && in_array($notifyInfo['title'], ['轉賬成功', '转账成功'])) {
        //     // money
        //     if (empty($notifyInfo['bigText'])) {
        //         exitWithJsonNoData(6004, "bigText are empty");
        //     }
        //     // 超过 1000 的金额会变成 1,000.00
        //     if (!preg_match("/HKD([0-9.,]+)/", $notifyInfo['bigText'], $res)) {
        //         exitWithJsonNoData(6005, "can not find HKD in bigText");
        //     }
        // } else {
        //     exitWithJsonNoData(6001, "skip");
        // }

        exitWithJsonNoData(6001, "skip");
    } elseif ($package_name == 'com.scb.breezebanking.hk') {
        // 渣打银行，同时会有2条通知，一条中文，一条英文的，此处只处理中文的。
        // 渣打香港︰您透過SC Pay從 WU M** Y** 收到HKD802.00.日期:01/10/2022.
        // SCBHK: You have received HKD802.00 via SC Pay from WU M** Y** on 01/10/2022.

        // 2023-7-11 加上
        // 渣打香港︰您從 HO, Y L 收到HKD1000.35.日期:08/07/2023.

        // money
        if (empty($notifyInfo['bigText'])) {
            exitWithJsonNoData(6004, "bigText are empty");
        }
        if (preg_match("/You have received/", $notifyInfo['bigText'])) {
            exitWithJsonNoData(6001, "skip");
        }
        // 超过 1000 的金额会变成 1,000.00
        if (!preg_match("/從.+收到HKD([0-9.,]+)/", $notifyInfo['bigText'], $res)) {
            exitWithJsonNoData(6005, "can not find HKD in bigText");
        }
        // 处理最后的点号：1000.35. --> 1000.35
        $mon = $res[1];
        if ($mon[strlen($mon) - 1] == '.') {
            $res[1] = substr($mon, 0, -1);
        }
    } elseif ($package_name == 'com.zhongan.ibank') {
        // ZA Bank
        // 刚收到来自 WU M** Y**  HKD 1.00 的款项。\n交易类型：转入\n(2022-10-18 14:34:00)\n你可到我们APP首页里的交易历史查看详情。
        // 剛收到來自 WONG C*** K** M******  HKD 202.97 的款項。\n交易類型：存入\n(2022-10-25 19:02:34)\n你可到我們APP首頁裏的交易記錄查看詳情。

        // money
        if (empty($notifyInfo['bigText'])) {
            exitWithJsonNoData(6004, "bigText are empty");
        }
        // 超过 1000 的金额会变成 1,000.00
        if (!preg_match("/[刚收到来自|剛收到來自].+HKD ([0-9.,]+)/", $notifyInfo['bigText'], $res)) {
            exitWithJsonNoData(6005, "can not find HKD in bigText");
        }
    } elseif ($package_name == 'com.livibank.hk') {
        // livibank
        // 你已成功於 29/10/2022 17:16 收到 CHEUNG C** W**  向你轉賬的 9.00HKD
        if (empty($notifyInfo['bigText'])) {
            exitWithJsonNoData(6004, "bigText are empty");
        }
        // 超过 1000 的金额会变成 1,000.00
        if (!preg_match("/[向你轉賬的|向你转账的] ([0-9.,]+)HKD/", $notifyInfo['bigText'], $res)) {
            exitWithJsonNoData(6005, "can not find HKD in bigText");
        }
    } elseif ($package_name == 'welab.bank') {
        // {"bigText":"CHU W** M** 啱啱過咗 HKD 160.99 畀你喇 交易時間： 5 Mar 2023 21:28 HKT。 參考編號： FT23064STC2Q","cardNo":"168888jun@gmail.com","package_name":"welab.bank","sign":"18ce05f6304a1da6914166066172080f","text":"收到錢喇🥳","timestamp":1678022913252,"title":"收到錢喇🥳"}

        if (isset($notifyInfo['text']) && strpos($notifyInfo['text'], '收到錢喇') !== false) {
            // money
            if (empty($notifyInfo['bigText'])) {
                exitWithJsonNoData(6004, "bigText are empty");
            }
            // 超过 1000 的金额会变成 1,000.00
            if (!preg_match("/HKD ([0-9.,]+)/", $notifyInfo['bigText'], $res)) {
                exitWithJsonNoData(6005, "can not find HKD in bigText");
            }
        } else {
            exitWithJsonNoData(6001, "skip");
        }
    } elseif ($package_name == 'com.airstarbank.mobilebanking') {
        // {"bigText":"天星銀行Airstar: 來自WONG H* A*** HKD10.00的轉賬已於07/05/2023 21:27存入您的賬戶（尾數[99]）。查詢：37181818","cardNo":"168baby168baby@gmail.com","package_name":"com.airstarbank.mobilebanking","sign":"a36be14007cb3901498f0fd34c15e39d","timestamp":1683466024350}

        if (empty($notifyInfo['bigText'])) {
            exitWithJsonNoData(6004, "bigText are empty");
        }
        // 超过 1000 的金额会变成 1,000.00
        if (!preg_match("/HKD([0-9.,]+)的轉賬已於/", $notifyInfo['bigText'], $res)) {
            exitWithJsonNoData(6005, "can not find HKD in bigText");
        }
    } elseif ($package_name == 'com.fusionbank.vb') {
        // {"bigText":"您尾數為9106之Fusion Bank戶口成功收取WONG H* A***於眾安銀行有限公司轉入HKD 5.00，查詢詳情請前往信息中心或交易記錄。","cardNo":"168baby168baby@gmail.com","package_name":"com.fusionbank.vb","sign":"727deb4eab273f6064e91b787f316284","text":"富融銀行交易提示","timestamp":1683466284435,"title":"富融銀行交易提示"}

        if (empty($notifyInfo['bigText'])) {
            exitWithJsonNoData(6004, "bigText are empty");
        }
        // 超过 1000 的金额会变成 1,000.00
        if (!preg_match("/轉入HKD ([0-9.,]+)/", $notifyInfo['bigText'], $res)) {
            exitWithJsonNoData(6005, "can not find HKD in bigText");
        }
    } elseif ($package_name == 'com.bochk.app.aos') {
        // {"bigText":"WONG H* A***已於2023/05/07 21:37經轉數快轉入港元3,000.00給您的賬戶012...857。 \n\n交易參考編號為「FRN20230507PAYC0101043707005」。\n\n查詢：在線客服/39882388","cardNo":"105588800","package_name":"com.bochk.app.aos","sign":"d5029f3bf42c57552499e4673a5a3502","text":"BOCHK 中銀香港","timestamp":1683466625339,"title":"BOCHK 中銀香港"}

        if (empty($notifyInfo['bigText'])) {
            exitWithJsonNoData(6004, "bigText are empty");
        }
        // 超过 1000 的金额会变成 1,000.00
        if (!preg_match("/轉入港元([0-9.,]+)給您的賬戶/", $notifyInfo['bigText'], $res)) {
            exitWithJsonNoData(6005, "can not find HKD in bigText");
        }
    } else {
        exitWithJsonNoData(6001, "skip");
    }

    // 1,000.00 --> 1000.00
    $realmoney = str_replace(',', '', $res[1]);

    $cardNo = $notifyInfo['cardNo'];
    $channel = $DB->getRow("SELECT * FROM `pre_channel` WHERE `status`= 1 and `appid` = :appid LIMIT 1", [':appid' => $cardNo]);
    if (empty($channel)) {
        exitWithJsonNoDataLog(6002, "invalid cardNo", "invalid cardNo" . $cardNo, "ERROR");
    }

    $timestamp = intval($notifyInfo['timestamp'] / 1000);
    $timestampStart = $timestamp - $conf['config_order_timeout'];
    $timestamp = date("Y-m-d H:i:s", $timestamp);
    $timestampStart = date("Y-m-d H:i:s", $timestampStart);

    // 匹配订单
    $row = $DB->getAll("SELECT * FROM `pre_order` WHERE `channel` = :channel and `realmoney`= :realmoney and addtime >= :timestampStart and addtime < :timestampEnd", [':channel' => $channel['id'], ':realmoney' => $realmoney, ':timestampStart' => $timestampStart, ':timestampEnd' => $timestamp]);

    if (empty($row)) {
        exitWithJsonNoDataLog(6006, "没有找到对应的订单，请及时处理", "没有找到对应的订单，请及时处理，" . $realmoney, "ERROR");
    } elseif (count($row) > 1) {
        exitWithJsonNoDataLog(6007, "找到多条订单记录，请及时处理", "找到多条订单记录，请及时处理", "ERROR");
    } elseif ($row[0]['status'] == 1) {
        exitWithJsonNoDataLog(6008, "订单已被处理，请勿重复通知", "订单已被处理，请勿重复通知", "ERROR");
    }

    $order = $row[0];

    if (updateOrderStatusAndUserMoney($order)) {
        // 通知商户
        $order['status'] = 1;
        notifyCustom($order);

        exitWithJsonNoData(0, $order['trade_no']);
    } else {
        exitWithJsonNoData(6009, '处理失败！');
    }
} catch (Exception $e) {
    $err = sprintf("%s in %s:%d", $e->getMessage(), $e->getFile(), $e->getLine());

    exitWithJsonNoDataLog(6100, "服务器错误", $err, "ERROR");
}
