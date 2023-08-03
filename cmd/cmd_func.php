<?php

require dirname(__FILE__) . '/../includes/common.php';

class CronFunc
{
    public function __construct()
    {
    }

    public function compareData()
    {
        compareMoney();
    }

    public function fixData()
    {
        fixData();
    }

    public function testNotify()
    {
        global $DB;

        $trade_no = "P2022081414340687608";
        $order = $DB->getRow("SELECT * FROM `pre_order` WHERE `trade_no` = :trader_no AND status = 1 LIMIT 1", [':trader_no' => $trade_no]);
        notifyCustom($order);
    }

    public function testWithdrawSign()
    {
        $t = [
            'pid' => '1001',
            'out_trade_no' => 'C2022080909522472338',
            'notify_url' => 'http://103.44.251.44/notify_custom.php',
            'money' => '200',
            'bankname' => '交通银行',
            'account' => '111111111',
            'username' => 'xxxxxxx',
            'sign' => '',
            'sign_type' => 'MD5',
        ];

        $prestr = \lib\PayUtils::createLinkstring(\lib\PayUtils::argSort(\lib\PayUtils::paraFilter($t)));
        $key = "6RZRC040Ryr626c58554g26pgvm22R4G";
        $sign = md5($prestr . $key);
        echo $sign;
    }

    public function testSubmitSign()
    {
        // money=500
        // &name=%E5%95%86%E5%93%81%E4%B8%80%E6%89%B9
        // &notify_url=https://dg66.net/payment/payresult/hkpay2
        // &out_trade_no=AH16608004284800
        // &pid=1002
        // &type=charge
        // &sign=7170088342d06f51bfc873c1c0d53ecb
        // &sign_type=MD5

        // {"money":"500","name":"商品一批","notify_url":"https://dg66.net/payment/payresult/hkpay2","out_trade_no":"AH16607993184790","param":"","pid":"1002","type":"charge","sign":"b5334e04f594cc704f0321b434e66dc6","sign_type":"MD5"}

        // http://payapi.dx888.me/submit.php?pid=1010&type=charge&out_trade_no=52076&notify_url=http://localhost:64339/services/dxpayCallback.ashx&name=AE365CLUB&money=31&sign=00b075d2330d5add11989d70a51df9f6&sign_type=MD5

        $t = [
            'pid' => '1010',
            'type' => 'charge',
            'out_trade_no' => '52076',
            'notify_url' => 'http://localhost:64339/services/dxpayCallback.ashx',
            'name' => 'AE365CLUB',
            'money' => '31',
            'param' => '',
            'sign' => '',
            'sign_type' => 'MD5',
        ];

        $prestr = \lib\PayUtils::createLinkstring(\lib\PayUtils::argSort(\lib\PayUtils::paraFilter($t)));
        $key = "08K3KvXFoKZZfA8o3akOVKKvKPcokT1O";
        $sign = md5($prestr . $key);
        echo $sign; // f9b6ef0f409d2e37d8d5f7437c80f986
    }

    public function testExec()
    {
        global $DB;

        $re = $DB->execNew("UPDATE pre_anounce set `sort` = :sort", [':sort' => 2]);
        var_dump($re);

        $re = $DB->exec("UPDATE pre_anounce set `sort` = :sort", [':sort' => 3]);
        var_dump($re);

        $re = $DB->execNew("UPDATE pre_anounce set `sort` = 2");
        var_dump($re);

        $re = $DB->exec("UPDATE pre_anounce set `sort` = 3");
        var_dump($re);
    }
}

$o = new CronFunc();

if (!isset($argv[1])) {
    exit("nothing to do");
} else {
    $func = $argv[1];
    if (!method_exists("CronFunc", $func)) {
        exit("can not find method {$func}");
    }

    $o->$func();
}
