<?php
include("../includes/common.php");
$title = '彩虹支付管理中心';
include './head.php';
if (isset($islogin_agent) && $islogin_agent == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<?php
?>
<div class="container" style="padding-top:70px;">
	<div class="col-xs-12 col-lg-9 center-block" style="float: none;">
		<div class="row">
			<div class="col-xs-12 col-lg-8">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h3 class="panel-title" id="title">后台管理首页</h3>
					</div>
					<ul class="list-group">
						<li class="list-group-item"><span class="glyphicon glyphicon-stats"></span> <b>订单总数：</b><span id="count1"></span></li>
						<li class="list-group-item"><span class="glyphicon glyphicon-tint"></span> <b>商户数量：</b><span id="count2"></span></li>
						<li class="list-group-item"><span class="glyphicon glyphicon-tint"></span> <b>代理余额：</b><span id="usermoney"></span>元</li>
						<li class="list-group-item"><span class="glyphicon glyphicon-time"></span> <b>现在时间：</b> <?= $date ?></li>
						</li>
					</ul>
				</div>
			</div>
			<div class="col-xs-12 col-lg-4">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title" id="title">代理信息</h3>
					</div>
					<ul class="list-group text-center">
						<li class="list-group-item">
							<img src="<?php echo ($conf['kfqq']) ? '//q2.qlogo.cn/headimg_dl?bs=qq&dst_uin=' . $conf['kfqq'] . '&src_uin=' . $conf['kfqq'] . '&fid=' . $conf['kfqq'] . '&spec=100&url_enc=0&referer=bu_interface&term_type=PC' : '../assets/img/user.png' ?>" alt="avatar" class="img-circle img-thumbnail"></br>
							<span class="text-muted"><strong>用户名：</strong>
								<font color="blue"><?php echo $agentrow['name'] ?></font>
							</span>
						</li>
						<li class="list-group-item"><a href="./set.php?mod=account" class="btn btn-xs btn-info">修改密码</a>&nbsp;<a href="./login.php?logout" class="btn btn-xs btn-danger">退出登录</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title">收入统计<span class="pull-right"><a href="javascript:getData()" class="btn btn-default btn-xs"><i class="fa fa-refresh"></i></a></span></h3>
			</div>
			<table class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>今天</th>
						<th>昨天</th>
						<th>前天</th>
					</tr>
				</thead>
				<tbody id="paytype_list">
				</tbody>
			</table>
		</div>
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title">收入统计<span class="pull-right"><a href="javascript:getData()" class="btn btn-default btn-xs"><i class="fa fa-refresh"></i></a></span></h3>
			</div>
			<div class="table-responsive">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>时间</th>
							<th>支付收益总额</th>
							<th>提现收益总额</th>
						</tr>
					</thead>
					<tbody id="month_list">
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		getData();
	});

	function getData() {
		$('#title').html('正在加载数据中...');
		$.ajax({
			type: "GET",
			url: "ajax.php?act=getcount",
			dataType: 'json',
			async: true,
			success: function(data) {
				$('#title').html('后台管理首页');
				$('#count1').html(data.count1);
				$('#count2').html(data.count2);
				$('#usermoney').html(data.usermoney);

				$("#paytype_list").empty();
				$("#paytype_list").append('<tr><td>' + data.paytype.today + '</td><td>' + data.paytype.yestoday + '</td><td>' + data.paytype.thirthday + '</td></tr>');

				$("#month_list").empty();
				$.each(data.order_month, function(k, v) {
					$("#month_list").append('<tr><td>' + v.month + '</td><td>' + v.getmoney + '</td><td>' + v.withdrawgetmoney + '</td></tr>');
				});
			}
		});
	}
</script>