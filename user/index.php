<?php
include("../includes/common.php");
if (isset($islogin2) && $islogin2 == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");

if ($userrow['status'] == 0) {
	$status = '<font color="red">已封禁</font>';
} elseif ($userrow['pay'] == 0 && $userrow['withdraw'] == 0) {
	$status = '<font color="red">关闭支付、提现</font>';
} elseif ($userrow['pay'] == 0) {
	$status = '<font color="red">关闭支付</font>';
} elseif ($userrow['withdraw'] == 0) {
	$status = '<font color="red">关闭提现</font>';
} else {
	$status = '<font color="green">正常</font>';
}
$title = '用户中心';
include './head.php';
?>
<style>
	.round {
		line-height: 53px;
		color: #7266ba;
		width: 58px;
		height: 58px;
		font-size: 26px;
		margin-left: 15px;
		display: inline-block;
		font-weight: 400;
		border: 3px solid #f8f8fe;
		text-align: center;
		border-radius: 50%;
		background: #e3dff9;
	}
</style>
<?php

$list = $DB->getAll("SELECT * FROM pre_anounce ORDER BY sort ASC");

?>
<div id="content" class="app-content" role="main">
	<div class="app-content-body ">
		<div class="bg-light lter b-b wrapper-md hidden-print">
			<h1 class="m-n font-thin h3">用户中心</h1>
			<small class="text-muted">欢迎使用<?php echo $conf['sitename'] ?></small>
		</div>
		<div class="wrapper-md control">
			<!-- stats -->
			<?php
			if (empty($userrow['phone'])) {
				echo '<div class="alert alert-warning"><span class="btn-sm btn-warning">提示</span>&nbsp;您还没有绑定手机，请&nbsp;<a href="editinfo.php" class="btn btn-default btn-xs">尽快绑定</a></div>';
			}
			if (empty($userrow['pwd'])) {
				echo '<div class="alert alert-warning"><span class="btn-sm btn-warning">提示</span>&nbsp;您还没有设置登录密码，请&nbsp;<a href="userinfo.php?mod=account" class="btn btn-default btn-xs">点此设置</a>，设置登录密码之后你就可以使用手机号/邮箱+密码登录</div>';
			}
			?>

			<div class="row row-sm text-center">
				<div class="col-xs-6 col-sm-3">
					<div class="panel padder-v item">
						<div class="top text-right w-full"><i class="fa fa-caret-down text-warning m-r-sm"></i></div>
						<div class="row">
							<div class="col-xs-3">
								<div class="round"><i class="fa fa-money fa-fw"></i></div>
							</div>
							<div class="col-xs-9">
								<div class="h1 text-primary-dk font-thin h1"><span class="text-muted text-md">￥</span><?php echo $userrow['money'] ?></div><span class="text-muted">商户当前余额</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-6 col-sm-3">
					<div class="panel padder-v item">
						<div class="top text-right w-full"><i class="fa fa-caret-down text-warning m-r-sm"></i></div>
						<div class="row">
							<div class="col-xs-3">
								<div class="round"><i class="fa fa-check-square-o fa-fw"></i></div>
							</div>
							<div class="col-xs-9">
								<div class="h1 text-dark-dk font-thin h1"><span class="text-muted text-md">￥</span><span id="withdraw_money"></span></div><span class="text-muted">已提现余额</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-6 col-sm-3">
					<div class="panel padder-v item">
						<div class="top text-right w-full"><i class="fa fa-caret-down text-warning m-r-sm"></i></div>
						<div class="row">
							<div class="col-xs-3">
								<div class="round"><i class="fa fa-area-chart fa-fw"></i></div>
							</div>
							<div class="col-xs-9">
								<div class="h1 text-success-dk font-thin h1"><span id="orders"></span><span class="text-muted text-md">个</span></div><span class="text-muted">订单总数</span>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-6 col-sm-3">
					<div class="panel padder-v item">
						<div class="top text-right w-full"><i class="fa fa-caret-down text-warning m-r-sm"></i></div>
						<div class="row">
							<div class="col-xs-3">
								<div class="round"><i class="fa fa-cart-plus fa-fw"></i></div>
							</div>
							<div class="col-xs-9">
								<div class="h1 text-info-dk font-thin h1"><span id="orders_today"></span><span class="text-muted text-md">个</span></div><span class="text-muted">今日订单</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">

					<div class="panel b-a">
						<div class="panel-heading bg-info dk no-border wrapper-lg">
							<a class="btn btn-sm btn-rounded btn-info pull-right m-r" href="./editinfo.php"><i class="fa fa-cog fa-fw"></i>&nbsp;修改资料</a>
							<a class="btn btn-sm btn-rounded btn-info m-l" href="./userinfo.php?mod=api"><i class="fa fa-lock fa-fw"></i>&nbsp;API信息</a>
						</div>
						<div class="text-center m-b clearfix">
							<div class="thumb-lg avatar m-t-n-xxl">
								<img src="assets/img/user.png" alt="..." class="b b-3x b-white">
							</div>
							<div class="h2 font-thin m-t-sm">欢迎您，<?php echo $userrow['phone'] ?></div>
							<div class="h4 font-thin m-t-sm">商户状态：<?php echo $status; ?></div>
						</div>
						<div class="hbox text-center b-t b-light bg-light">
							<a class="col padder-v text-muted b-r b-light">
								<div class="h3"><span id="order_today_all"></span></div>
								<i class="fa fa-plus fa-fw"></i><span>今日收入</span>
							</a>
							<a class="col padder-v text-muted">
								<div class="h3"><span id="order_lastday_all"></span></div>
								<i class="fa fa-plus-circle fa-fw"></i><span>昨日收入</span>
							</a>
						</div>

					</div>

					<div class="panel panel-default text-center">
						<div class="panel-heading font-bold">
							通道费率
						</div>
						<div class="table-responsive">
							<table class="table table-striped">
								<thead>
									<tr>
										<th style="text-align: center;">支付费率</th>
										<th style="text-align: center;">提现费率</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><?php echo $userrow['pay_rate'] . '%'; ?></td>
										<td><?php echo $userrow['withdraw_rate'] . '%'; ?></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

				</div>
				<div class="col-md-6">

					<div class="panel panel-default">
						<div class="panel-heading font-bold text-center">
							公告通知
						</div>
						<div class="list-group">
							<?php foreach ($list as $row) { ?>
								<a class="list-group-item"><em class="fa fa-fw fa-volume-up"></em>
									<font color="<?php echo $row['color'] ? $row['color'] : null ?>"><?php echo $row['content'] ?></font><span class="text-xs text-muted">&nbsp;-<?php echo $row['addtime'] ?></span>
								</a>
							<?php } ?>
						</div>
					</div>

					<div class="panel wrapper">
						<div id="container1"></div>
					</div>
				</div>
			</div>
			<!-- / stats -->
		</div>
	</div>
</div>

<?php include 'foot.php'; ?>
<script>
	$(document).ready(function() {
		$.ajax({
			type: "GET",
			url: "ajax2.php?act=getcount",
			dataType: 'json',
			async: true,
			success: function(data) {
				$('#orders').html(data.orders);
				$('#orders_today').html(data.orders_today);
				$('#withdraw_money').html(data.withdraw_money);
				$('#order_today_all').html(data.order_today.all);
				$('#order_lastday_all').html(data.order_lastday.all);
			}
		});
		
		$.ajax({
			type: "GET",
			url: "ajax2.php?act=getchart",
			dataType: 'json',
			async: true,
			success: function(data) {
				var rqList = data.rqList;
				var countList = data.countList;
				var chart = Highcharts.chart('container1', {
					chart: {
						type: 'line',
					},
					credits: {
						enabled: false,
					},
					title: {
						text: '收入统计',
					},
					xAxis: {
						categories: rqList,
						tickInterval: 5,
					},
					yAxis: {
						title: {
							text: null,
						},
					},
					series: countList,
					legend: {
						enabled: true,
						layout: 'vertical',
						align: 'right',
						verticalAlign: 'middle',
					},
				});
			}
		});
	});

</script>