<?php

/**
 * 支付通道
 **/
include("../includes/common.php");
$title = '支付通道';
include './head.php';
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<style>
	.form-inline .form-control {
		display: inline-block;
		width: auto;
		vertical-align: middle;
	}

	.form-inline .form-group {
		display: inline-block;
		margin-bottom: 0;
		vertical-align: middle;
	}
</style>
<div class="container" style="padding-top:70px;">
	<div class="col-md-10 center-block" style="float: none;">
		<?php

		$today = date('Y-m-d');
		$list = $DB->getAll("SELECT * FROM pre_channel ORDER BY `status` desc, id ASC");
		foreach ($list as $k => $v) {
			$t = $DB->getColumn("SELECT SUM(money) as today_money from pre_order where status = 1 and channel = {$v['id']} and date = '{$today}'");
			$list[$k]['today_money'] = $t;
		}

		?>
		<div class="modal" id="modal-store" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
			<div class="modal-dialog">
				<div class="modal-content animated flipInX">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title" id="modal-title">支付通道修改/添加</h4>
					</div>
					<div class="modal-body">
						<form class="form-horizontal" id="form-store">
							<input type="hidden" name="action" id="action" />
							<input type="hidden" name="id" id="id" />

							<div class="form-group">
								<label class="col-sm-2 control-label no-padding-right">通道名称</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" name="name" id="name">
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label no-padding-right">银行卡号</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" name="appid" id="appid">
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label no-padding-right">账户名</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" name="appkey" id="appkey">
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label no-padding-right">通道类型</label>
								<div class="col-sm-10">
									<select class="form-control" name="channel_type" id="channel_type">
										<option value="1">转数快</option>
										<option value="2">银行卡</option>
									</select>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label no-padding-right">银行编码</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" name="bank_code" id="bank_code">
								</div>
							</div>

						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
						<button type="button" class="btn btn-primary" id="store" onclick="save()">保存</button>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-info">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo $titles ?? '系统共有' ?> <b><?php echo count($list); ?></b> 个支付通道&nbsp;
					<span class="pull-right">
						<a href="javascript:addframe()" class="btn btn-default btn-xs"><i class="fa fa-plus"></i> 新增</a>
					</span>
				</h3>
			</div>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>ID</th>
							<th>通道名称</th>
							<th>银行卡号</th>
							<th>账户名</th>
							<th>类型</th>
							<th>银行编码</th>
							<th>今日收款</th>
							<th>状态</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($list as $res) {
							echo '<tr><td><b>' . $res['id'] . '</b></td><td>' . $res['name'] . '</td><td>' . $res['appid'] . '</td><td>' . $res['appkey'] . '</td><td>' . getChannelTypeName($res['channel_type']) . '</td><td>' . $res['bank_code'] . '</td><td>' . $res['today_money'] . '</td><td>' . ($res['status'] == 1 ? '<a class="btn btn-xs btn-success" onclick="setStatus(' . $res['id'] . ',0)">已开启</a>' : '<a class="btn btn-xs btn-warning" onclick="setStatus(' . $res['id'] . ',1)">已关闭</a>') . '</td><td><a class="btn btn-xs btn-info" onclick="editframe(' . $res['id'] . ')">编辑</a>&nbsp;<a href="./order.php?channel=' . $res['id'] . '" target="_blank" class="btn btn-xs btn-default">订单</a></td></tr>';
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo $cdnpublic ?>layer/3.1.1/layer.min.js"></script>
<script>
	function addframe() {
		$("#modal-store").modal('show');
		$("#modal-title").html("新增支付通道");
		$("#action").val("add");
		$("#id").val('');
		$("#name").val('');
		$("#appid").val('');
		$("#appkey").val('');
		$("#channel_type").val('1');
		$("#bank_code").val('');
	}

	function editframe(id) {
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'GET',
			url: 'ajax_pay.php?act=getChannel&id=' + id,
			dataType: 'json',
			success: function(data) {
				layer.close(ii);
				if (data.code == 0) {
					$("#modal-store").modal('show');
					$("#modal-title").html("修改支付通道");
					$("#action").val("edit");
					$("#id").val(data.data.id);
					$("#name").val(data.data.name);
					$("#appid").val(data.data.appid);
					$("#appkey").val(data.data.appkey);
					$("#channel_type").val(data.data.channel_type);
					$("#bank_code").val(data.data.bank_code);
				} else {
					layer.alert(data.msg, {
						icon: 2
					})
				}
			},
			error: function(data) {
				layer.msg('服务器错误');
				return false;
			}
		});
	}

	function save() {
		if ($("#name").val() == '' || $("#appid").val() == '' || $("#appkey").val() == '') {
			layer.alert('请确保各项不能为空！');
			return false;
		}
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'POST',
			url: 'ajax_pay.php?act=saveChannel',
			data: $("#form-store").serialize(),
			dataType: 'json',
			success: function(data) {
				layer.close(ii);
				if (data.code == 0) {
					layer.alert(data.msg, {
						icon: 1,
						closeBtn: false
					}, function() {
						window.location.reload()
					});
				} else {
					layer.alert(data.msg, {
						icon: 2
					})
				}
			},
			error: function(data) {
				layer.msg('服务器错误');
				return false;
			}
		});
	}

	function setStatus(id, status) {
		$.ajax({
			type: 'GET',
			url: 'ajax_pay.php?act=setChannel&id=' + id + '&status=' + status,
			dataType: 'json',
			success: function(data) {
				if (data.code == 0) {
					window.location.reload()
				} else {
					layer.msg(data.msg, {
						icon: 2,
						time: 1500
					});
				}
			},
			error: function(data) {
				layer.msg('服务器错误');
				return false;
			}
		});
	}
	$(function() {
		$('[data-toggle="popover"]').popover()
	})
</script>