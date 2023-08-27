<?php

require '../includes/common.php';

$t = [
    'pid' => '1007',
    'type' => 'charge',
    'out_trade_no' => "T" . date("YmdHis") . rand(11111, 99999),
    'notify_url' => 'http://payapi.hkrainbowpay.com/notify_custom.php',
    'name' => 'VIP年度会员',
    'money' => strval(rand(100, 200)),
    'param' => '',
    'sign' => '',
    'sign_type' => 'SHA1',
    'lang' => 'chinese_hongkong',
];

use \lib\PayUtils;

$prestr = PayUtils::createLinkstring(PayUtils::argSort(PayUtils::paraFilter($t)));
$key = "VbarShhUybZb22j6RjfJrhBbJRJsOByf";
$sign = PayUtils::sha1Sign($prestr, $key);

$t['sign'] = $sign;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>下单</title>
    <style type="text/css">
        .copy-account {
            display: inline-block;
            width: 190px;
            background-color: rgb(39, 162, 81);
            padding-top: 10px;
            padding-bottom: 10px;
            border-radius: 6px;
            color: #eee;
            font-size: 1.2rem;
        }
    </style>
</head>

<body style="background-color: #f1e9e9;">
    <div style="padding: 10px;background-color: #fff9f9;">
        <p>商户ID：<?php echo $t['pid']; ?></p>
        <p>商户订单：<?php echo $t['out_trade_no']; ?></p>
        <p>商品名称：<?php echo $t['name']; ?></p>
        <p>商品金额：HKD<?php echo $t['money']; ?></p>
    </div>
    <p style="text-align: center; margin-top: 30px;"><span data-clipboard-text="1" class="copy-account">下单按钮</span></p>

    <script src="http://cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript">
        var pid = <?php echo $t['pid']; ?>;
        var out_trade_no = <?php echo '"' . $t['out_trade_no'] . '"'; ?>;
        var name = <?php echo '"' . $t['name'] . '"'; ?>;
        var money = <?php echo $t['money']; ?>;
        var sign = <?php echo '"' . $t['sign'] . '"'; ?>;
        var notify_url = <?php echo '"' . $t['notify_url'] . '"'; ?>;
        var lang = <?php echo '"' . $t['lang'] . '"'; ?>;

        var u = "submit.php?pid=" + pid + "&type=charge&out_trade_no=" + out_trade_no + "&notify_url=" + notify_url + "&name=" + name + "&money=" + money + "&param=&sign=" + sign + "&sign_type=SHA1&lang=" + lang;

        $(".copy-account").on("click", function() {
            window.location.href = u;
        })
    </script>
</body>

</html>