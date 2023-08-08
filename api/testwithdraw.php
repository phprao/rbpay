<?php

require '../includes/common.php';

$t = [
    'pid' => '1001',
    'out_trade_no' => "T" . date("YmdHis") . rand(11111, 99999),
    'notify_url' => 'http://payapi.hkrainbowpay.com/notify_custom.php',
    'money' => strval(rand(100, 200)),
    'bankname' => '交通银行',
    'account' => '111111111',
    'username' => 'xxxxxxx',
    'channel_type' => 2,
    'sign' => '',
    'sign_type' => 'MD5',
];

use \lib\PayUtils;

$prestr = PayUtils::createLinkstring(PayUtils::argSort(PayUtils::paraFilter($t)));
$key = "pZZdEdWfw3Mw3x3SHB13XD3732MxShxF";
$sign = PayUtils::md5Sign($prestr, $key);

$t['sign'] = $sign;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>提现</title>
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
        <p>商品金额：HKD<?php echo $t['money']; ?></p>
        <p>银行名称：<?php echo $t['bankname']; ?></p>
        <p>账号：HKD<?php echo $t['account']; ?></p>
        <p>账户名：HKD<?php echo $t['username']; ?></p>
        <p>提现方式：<?php echo $t['channel_type']; ?></p>
    </div>
    <p style="text-align: center; margin-top: 30px;"><span data-clipboard-text="1" class="copy-account">提现按钮</span></p>

    <script src="http://cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript">
        var pid = <?php echo $t['pid']; ?>;
        var out_trade_no = <?php echo '"' . $t['out_trade_no'] . '"'; ?>;
        var money = <?php echo $t['money']; ?>;
        var sign = <?php echo '"' . $t['sign'] . '"'; ?>;
        var notify_url = <?php echo '"' . $t['notify_url'] . '"'; ?>;
        var bankname = <?php echo '"' . $t['bankname'] . '"'; ?>;
        var account = <?php echo '"' . $t['account'] . '"'; ?>;
        var username = <?php echo '"' . $t['username'] . '"'; ?>;
        var channel_type = <?php echo '"' . $t['channel_type'] . '"'; ?>;

        $(".copy-account").on("click", function() {
            $.ajax({
                type: 'post',
                url: 'withdraw.php',
                data: {
                    pid: pid,
                    out_trade_no: out_trade_no,
                    money: money,
                    sign: sign,
                    notify_url: notify_url,
                    bankname: bankname,
                    account: account,
                    username: username,
                    channel_type: channel_type,
                    sign_type: "MD5",
                },
                dataType: 'json',
                success: function(ret) {
                    if (ret.code == 0) {
                        alert("下单成功-" + ret.msg);
                        window.location.reload();
                    } else {
                        alert(ret.msg);
                    }
                },
            });
        })
    </script>
</body>

</html>