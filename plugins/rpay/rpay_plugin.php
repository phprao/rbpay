<?php

class rpay_plugin
{
	static public $info = [
		'name'        => 'rpay', //支付插件英文名称，需和目录名称一致，不能有重复
		'showname'    => '银行转账插件', //支付插件显示名称
		'author'      => 'rxy', //支付插件作者
		'link'        => '', //支付插件作者链接
		'types'       => ['charge'], //支付插件支持的支付方式
		'inputs' => [ //支付插件要求传入的参数以及参数显示名称，可选的有appid,appkey,appsecret,appurl,appmchid
			'appid' => [
				'name' => '银行卡号',
				'type' => 'input',
				'note' => '输入银行卡号，只能是数字',
			],
			'appkey' => [
				'name' => '账户名',
				'type' => 'input',
				'note' => '输入银行卡账户名',
			],
		],
		'select' => null,
		'note' => '', //支付密钥填写说明
		'bindwxmp' => false, //是否支持绑定微信公众号
		'bindwxa' => false, //是否支持绑定微信小程序
	];

	static public function submit()
	{
		global $siteurl, $channel, $order, $ordername, $sitename, $conf, $lang;

		$parameter = array(
			"trade_no"	=> TRADE_NO,
			"name"	=> $order['name'],
			"money"	=> (float)$order['realmoney'],
			'cardno' => $channel['appid'],
			'cardowner' => $channel['appkey'],
			'channel_type' => $channel['channel_type'],
			'bank_code' => $channel['bank_code'],
			'order_timeout' => $conf['config_order_timeout'] + 86400,
			'order_timeout_text' => '5m 0s',
		);

		$html_text = self::createPage($parameter, $lang);
		return ['type' => 'html', 'data' => $html_text];
	}

	static public function mapi()
	{
		throw new Exception('非法操作');
	}

	static public function createPage($parameter, $lang)
	{
		$chinese_simple = '
		<!DOCTYPE html>
		<html lang="en">

		<head>
			<meta charset="UTF-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>订单</title>
			<style type="text/css">
				.list-left {
					width: 80px;
					display: inline-block;
					color: #777;
				}

				.copy-account {
					display: inline-block;
					width: 50px;
					color: #028EF9;
					font-size: 1rem;
					text-align: center;
					float: right;
				}

				.copy-name {
					display: inline-block;
					width: 50px;
					color: #028EF9;
					font-size: 1rem;
					text-align: center;
					float: right;
				}

				.copy-code {
					display: inline-block;
					width: 50px;
					color: #028EF9;
					font-size: 1rem;
					text-align: center;
					float: right;
				}

				#clocker {
					color: #c51010;
				}

				.order {
					padding: 12px;
				}
		
				.success {
					display: none;
				}
			</style>
		</head>

		<body style="background-color: #FDF0F9;">
			<div class="order">
				<div style="padding-top: 15px;">
					<h2>请使用【%s】支付</h2>
					<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
						<span class="list-left">订单号：</span><span>%s</span>
					</p>
					<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
						<span class="list-left">账&nbsp;&nbsp;&nbsp;户：</span><span>%s</span>
						<span data-clipboard-text="%s" class="copy-account">复制</span>
					</p>
					<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
						<span class="list-left">账户名：</span><span>%s</span>
						<span data-clipboard-text="%s" class="copy-name">复制</span>
					</p>
					<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
						<span class="list-left copy-code-text">银行编码：</span><span>%s</span>
						<span data-clipboard-text="%s" class="copy-code">复制</span>
					</p>
				</div>
				<h1 style="text-align: center; font-size: 3rem; padding-top: 50px;padding-bottom: 50px;">HK$ %.2f</h1>
				<h2>支付前请您仔细阅读</h2>
				<div style="color: #3a3535;">
					<p>1、请按实际金额 <span style="color: #c51010;">HK$ %.2f</span> 支付，不能修改金额，否则可能导致资金不到账或延迟到账。</p>
					<p>2、<span id="clocker">%s</span> 前完成支付，超过时间订单将会失效，强行支付会导致资金无法找回或延迟到账。</p>
					<p>3、完成支付后，请耐心等待到账。</p>
				</div>
			</div>
			<div class="success">
				<div style="text-align: center;padding: 60px 0;">
					<img src="/assets/images/icoSuccess.png" alt="">
				</div>
				<h2 style="text-align: center;margin-top: 0;">支付成功</h2>
				<p style="text-align: center;font-size: 1.2rem;">您的支付已成功，请离开此页面</p>
			</div>
			<script src="https://cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
			<!-- http://www.webkaka.com/blog/archives/clipboard-no-flash.html -->
			<script src="/assets/js/clipboard.min.js"></script>
			<script type="text/javascript">
				$(function(){
					var url=window.location.href;
					if(url.indexOf("?") != -1) {
						url = url.replace(/(\?|#)[^\'"]*/, "");
						window.history.pushState({},0,url);
					}

					hideBankCode();
				});

				var clipboard1 = new Clipboard(".copy-account");
				clipboard1.on("success", function(e) {
					alert("复制成功，请按照实际金额 HK$ %.2f 完成支付。");
				});

				var clipboard2 = new Clipboard(".copy-name");
				clipboard2.on("success", function(e) {
					alert("复制成功，请按照实际金额 HK$ %.2f 完成支付。");
				});

				var clipboard2 = new Clipboard(".copy-code");
				clipboard2.on("success", function(e) {
					alert("复制成功，请按照实际金额 HK$ %.2f 完成支付。");
				});

				var t2 = setInterval(requestOrderResult, 2000);
				function requestOrderResult() {
					$.ajax({
						type: "GET",
						url: "order_result.php?trade_no=%s",
						data: {},
						dataType: "json",
						success: function(data) {
							if (data.code == 0) {
								clearInterval(t2);
								$(".order").hide();
								$(".success").show();
								clearInterval(t1);
							}
						},
					});
				}

				var t = %d;
				var m = 0;
				var s = 0;
				var t1 = setInterval(GetRTime, 1000);
				function GetRTime() {
					t--;
					if (t >= 0) {
						m = Math.floor(t / 60 %s 60);
						s = Math.floor(t %s 60);
						document.getElementById("clocker").innerHTML = m + "m " + s + "s";
					} else {
						clearInterval(t1);
						clearInterval(t2);
						alert("订单已超时失效");
					}
				}

				function hideBankCode() {
					var bank_code = "%s";
					if(bank_code == "") {
					    $(".copy-code").hide();
						$(".copy-code-text").hide();
					}
				}
			</script>
		</body>
		</html>
		';

		$chinese_hongkong = '
		<!DOCTYPE html>
		<html lang="en">

		<head>
			<meta charset="UTF-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>訂單</title>
			<style type="text/css">
				.list-left {
					width: 80px;
					display: inline-block;
					color: #777;
				}

				.copy-account {
					display: inline-block;
					width: 50px;
					color: #028EF9;
					font-size: 1rem;
					text-align: center;
					float: right;
				}

				.copy-name {
					ddisplay: inline-block;
					width: 50px;
					color: #028EF9;
					font-size: 1rem;
					text-align: center;
					float: right;
				}

				.copy-code {
					display: inline-block;
					width: 50px;
					color: #028EF9;
					font-size: 1rem;
					text-align: center;
					float: right;
				}

				#clocker {
					color: #c51010;
				}

				.order {
					padding: 12px;
				}
		
				.success {
					display: none;
				}
			</style>
		</head>

		<body style="background-color: #FDF0F9;">
			<div class="order">
				<div style="background-color: #FCF7FE;">
					<div style="padding-top: 15px;">
						<h2>請使用【%s】支付</h2>
						<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
							<span class="list-left">訂單號：</span><span>%s</span>
						</p>
						<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
							<span class="list-left">帳&nbsp;&nbsp;&nbsp;戶：</span><span>%s</span>
							<span data-clipboard-text="%s" class="copy-account">複製</span>
						</p>
						<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
							<span class="list-left">帳戶名：</span><span>%s</span>
							<span data-clipboard-text="%s" class="copy-name">複製</span>
						</p>
						<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
							<span class="list-left copy-code-text">銀行編碼：</span><span>%s</span>
							<span data-clipboard-text="%s" class="copy-code">複製</span>
						</p>
					</div>
					<h1 style="text-align: center; font-size: 3rem; padding-top: 50px;padding-bottom: 50px;">HK$ %.2f</h1>
				</div>
				<h2>支付前請您仔細閱讀</h2>
				<div style="color: #3a3535;">
					<p>1、請按實際金額 <span style="color: #c51010;">HK$ %.2f</span> 支付，不能修改金額，否則可能導致資金不到賬或延遲到賬。</p>
					<p>2、<span id="clocker">%s</span> 前完成支付，超過時間訂單將會失效，強行支付會導致資金無法找回或延遲到賬。</p>
					<p>3、完成支付后，請耐心等待到賬。</p>
				</div>
			</div>
			<div class="success">
				<div style="text-align: center;padding: 60px 0;">
					<img src="/assets/images/icoSuccess.png" alt="">
				</div>
				<h2 style="text-align: center;margin-top: 0;">支付成功</h2>
				<p style="text-align: center;font-size: 1.2rem;">您的支付已成功，請離開此頁面</p>
			</div>
			<script src="https://cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
			<!-- http://www.webkaka.com/blog/archives/clipboard-no-flash.html -->
			<script src="/assets/js/clipboard.min.js"></script>
			<script type="text/javascript">
				$(function(){
					var url=window.location.href;
					if(url.indexOf("?") != -1) {
						url = url.replace(/(\?|#)[^\'"]*/, "");
						window.history.pushState({},0,url);
					}

					hideBankCode();
				});

				var clipboard1 = new Clipboard(".copy-account");
				clipboard1.on("success", function(e) {
					alert("複製成功，請按照實際金額 HK$ %.2f 完成支付。");
				});

				var clipboard2 = new Clipboard(".copy-name");
				clipboard2.on("success", function(e) {
					alert("複製成功，請按照實際金額 HK$ %.2f 完成支付。");
				});

				var clipboard2 = new Clipboard(".copy-code");
				clipboard2.on("success", function(e) {
					alert("複製成功，請按照實際金額 HK$ %.2f 完成支付。");
				});

				var t2 = setInterval(requestOrderResult, 2000);
				function requestOrderResult() {
					$.ajax({
						type: "GET",
						url: "order_result.php?trade_no=%s",
						data: {},
						dataType: "json",
						success: function(data) {
							if (data.code == 0) {
								clearInterval(t2);
								$(".order").hide();
                        		$(".success").show();
								clearInterval(t1);
							}
						},
					});
				}

				var t = %d;
				var m = 0;
				var s = 0;
				var t1 = setInterval(GetRTime, 1000);
				function GetRTime() {
					t--;
					if (t >= 0) {
						m = Math.floor(t / 60 %s 60);
						s = Math.floor(t %s 60);
						document.getElementById("clocker").innerHTML = m + "m " + s + "s";
					} else {
						clearInterval(t1);
						clearInterval(t2);
						alert("訂單已超時失效");
					}
				}

				function hideBankCode() {
					var bank_code = "%s";
					if(bank_code == "") {
					    $(".copy-code").hide();
						$(".copy-code-text").hide();
					}
				}
			</script>
		</body>
		</html>
		';

		$english = '
		<!DOCTYPE html>
		<html lang="en">

		<head>
			<meta charset="UTF-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Order</title>
			<style type="text/css">
				.list-left {
					width: 80px;
					display: inline-block;
					color: #777;
				}

				.copy-account {
					display: inline-block;
					width: 50px;
					color: #028EF9;
					font-size: 1rem;
					text-align: center;
					float: right;
				}

				.copy-name {
					display: inline-block;
					width: 50px;
					color: #028EF9;
					font-size: 1rem;
					text-align: center;
					float: right;
				}

				.copy-code {
					display: inline-block;
					width: 50px;
					color: #028EF9;
					font-size: 1rem;
					text-align: center;
					float: right;
				}

				#clocker {
					color: #c51010;
				}

				.order {
					padding: 12px;
				}
		
				.success {
					display: none;
				}
			</style>
		</head>

		<body style="background-color: #FDF0F9;">
			<div class="order">
				<div style="padding-top: 15px;">
					<h2>Please use 【%s】to pay</h2>
					<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
						<span class="list-left">Order number：</span><span>%s</span>
					</p>
					<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
						<span class="list-left">Account：</span><span>%s</span>
						<span data-clipboard-text="%s" class="copy-account">Copy</span>
					</p>
					<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
						<span class="list-left">Account name：</span><span>%s</span>
						<span data-clipboard-text="%s" class="copy-name">Copy</span>
					</p>
					<p style="padding-top: 10px;padding-bottom: 10px;border-bottom: 1px solid #777;">
						<span class="list-left copy-code-text">Bank code：</span><span>%s</span>
						<span data-clipboard-text="%s" class="copy-code">Copy</span>
					</p>
				</div>
				<h1 style="text-align: center; font-size: 3rem; padding-top: 50px;padding-bottom: 50px;">HK$ %.2f</h1>
				<h2>Please read carefully before make the payment</h2>
				<div style="color: #3a3535;">
					<p>1. Please pay the actual amount of <span style="color: #c51010;">HK$ %.2f</span>. The amount cannot be modified, otherwise the funds may not be credited or delayed.</p>
					<p>2. make payment before <span id="clocker">%s</span>, and the order will be invalid after the time limit. Forcible payment will cause the funds to be unable to be retrieved or delayed to the account.</p>
					<p>3. After completing the payment, please wait patiently for the account to arrive.</p>
				</div>
			</div>
			<div class="success">
				<div style="text-align: center;padding: 60px 0;">
					<img src="/assets/images/icoSuccess.png" alt="">
				</div>
				<h2 style="text-align: center;margin-top: 0;">success</h2>
				<p style="text-align: center;font-size: 1.2rem;">Please leave this page</p>
			</div>
			<script src="https://cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
			<!-- http://www.webkaka.com/blog/archives/clipboard-no-flash.html -->
			<script src="/assets/js/clipboard.min.js"></script>
			<script type="text/javascript">
				$(function(){
					var url=window.location.href;
					if(url.indexOf("?") != -1) {
						url = url.replace(/(\?|#)[^\'"]*/, "");
						window.history.pushState({},0,url);
					}

					hideBankCode();
				});

				var clipboard1 = new Clipboard(".copy-account");
				clipboard1.on("success", function(e) {
					alert("Copy success, please pay the actual amount of HK$ %.2f");
				});

				var clipboard2 = new Clipboard(".copy-name");
				clipboard2.on("success", function(e) {
					alert("Copy success, please pay the actual amount of HK$ %.2f");
				});

				var clipboard2 = new Clipboard(".copy-code");
				clipboard2.on("success", function(e) {
					alert("Copy success, please pay the actual amount of HK$ %.2f");
				});

				var t2 = setInterval(requestOrderResult, 2000);
				function requestOrderResult() {
					$.ajax({
						type: "GET",
						url: "order_result.php?trade_no=%s",
						data: {},
						dataType: "json",
						success: function(data) {
							if (data.code == 0) {
								clearInterval(t2);
								$(".order").hide();
                        		$(".success").show();
								clearInterval(t1);
							}
						},
					});
				}

				var t = %d;
				var m = 0;
				var s = 0;
				var t1 = setInterval(GetRTime, 1000);
				function GetRTime() {
					t--;
					if (t >= 0) {
						m = Math.floor(t / 60 %s 60);
						s = Math.floor(t %s 60);
						document.getElementById("clocker").innerHTML = m + "m " + s + "s";
					} else {
						clearInterval(t1);
						clearInterval(t2);
						alert("Order timeout");
					}
				}

				function hideBankCode() {
					var bank_code = "%s";
					if(bank_code == "") {
					    $(".copy-code").hide();
						$(".copy-code-text").hide();
					}
				}
			</script>
		</body>
		</html>
		';

		$langList = [
			'chinese_simple' => $chinese_simple,
			'chinese_hongkong' => $chinese_hongkong,
			'english' => $english,
		];
		$channeltype = [
			'chinese_simple' => getChannelTypeName($parameter['channel_type'], 1),
			'chinese_hongkong' => getChannelTypeName($parameter['channel_type'], 2),
			'english' => getChannelTypeName($parameter['channel_type'], 3),
		];

		if (empty($lang)) {
			$lang = 'chinese_hongkong';
		}
		if (isset($langList[$lang])) {
			$sel = $langList[$lang];
		} else {
			$sel = $langList['chinese_hongkong'];
		}

		return sprintf(
			$sel,
			$channeltype[$lang],
			$parameter['trade_no'],
			$parameter['cardno'],
			$parameter['cardno'],
			$parameter['cardowner'],
			$parameter['cardowner'],
			$parameter['bank_code'],
			$parameter['bank_code'],
			$parameter['money'],
			$parameter['money'],
			$parameter['order_timeout_text'],
			$parameter['money'],
			$parameter['money'],
			$parameter['money'],
			$parameter['trade_no'],
			$parameter['order_timeout'],
			"%",
			"%",
			$parameter['bank_code']
		);
	}
}
