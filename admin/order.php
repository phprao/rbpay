<?php

/**
 * 订单列表
 **/
include("../includes/common.php");
$title = '订单列表';
include './head.php';
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");

?>
<style>
	#orderItem .orderTitle {
		word-break: keep-all;
	}

	#orderItem .orderContent {
		word-break: break-all;
	}

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

	.dates {
		max-width: 120px;
	}
</style>
<link href="../assets/css/datepicker.css" rel="stylesheet">
<script src="<?php echo $cdnpublic ?>bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo $cdnpublic ?>bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.zh-CN.min.js"></script>
<div class="container-fluid" style="padding-top:70px;">
	<div class="col-md-12 center-block" style="float: none;">

		<form onsubmit="return searchOrder()" method="GET" class="form-inline">
			<div class="form-group">
				<label>搜索</label>
				<select name="column" class="form-control" default="<?php echo $_GET['column'] ?? 'trade_no' ?>">
					<option value="trade_no">订单号</option>
					<option value="out_trade_no">商户订单号</option>
					<option value="name">商品名称</option>
				</select>
			</div>
			<div class="form-group">
				<input type="text" class="form-control" name="value" placeholder="搜索内容" value="<?php echo $_GET['value'] ?? '' ?>">
			</div>
			<div class="form-group">
				<input type="text" class="form-control" name="uid" style="width: 100px;" placeholder="商户号" value="<?php echo $_GET['uid'] ?? '' ?>">
			</div>
			<div class="form-group">
				<input type="text" class="form-control" name="channel" style="width: 80px;" placeholder="通道ID" value="<?php echo $_GET['channel'] ?? '' ?>">
			</div>
			<div class="input-group">
				<input type="datetime-local" id="starttime" name="starttime" class="form-control" placeholder="开始日期" autocomplete="off" title="留空则不限时间范围" value="<?php if (!empty($_GET['starttime'])) echo $_GET['starttime']; ?>">
				<span class="input-group-addon" onclick="$('#starttime').val('');$('#endtime').val('');" title="清除"><i class="fa fa-chevron-right"></i></span>
				<input type="datetime-local" id="endtime" name="endtime" class="form-control" placeholder="结束日期" autocomplete="off" title="留空则不限时间范围" value="<?php if (!empty($_GET['endtime'])) echo $_GET['endtime']; ?>">
			</div>
			<button type="submit" class="btn btn-primary">&nbsp;搜索&nbsp;</button>
			<a href="javascript:searchClear()" class="btn btn-default" title="刷新订单列表"><i class="fa fa-refresh"></i></a>
			<div class="form-group">
				<select id="dstatus" class="form-control">
					<option value="-1">显示全部</option>
					<option value="1">只显示已完成</option>
					<option value="0">只显示未完成</option>
				</select>
			</div>
			<button onclick="javascript:searchExport()" class="btn btn-primary">&nbsp;导出&nbsp;</button>
		</form>

		<div id="listTable"></div>
	</div>
</div>
<a style="display: none;" href="" id="vurl" rel="noreferrer" target="_blank"></a>
<script src="<?php echo $cdnpublic ?>layer/3.1.1/layer.min.js"></script>
<script>
	var checkflag1 = "false";

	function check1(field) {
		if (checkflag1 == "false") {
			for (i = 0; i < field.length; i++) {
				field[i].checked = true;
			}
			checkflag1 = "true";
			return "false";
		} else {
			for (i = 0; i < field.length; i++) {
				field[i].checked = false;
			}
			checkflag1 = "false";
			return "true";
		}
	}

	function unselectall1() {
		if (document.form1.chkAll1.checked) {
			document.form1.chkAll1.checked = document.form1.chkAll1.checked & 0;
			checkflag1 = "false";
		}
	}

	var dstatus = -1;

	function listTable(query) {
		var url = window.document.location.href.toString();
		var queryString = url.split("?")[1];
		query = query || queryString;
		if (query == 'start' || query == undefined) {
			query = '';
			history.replaceState({}, null, './order.php');
		} else if (query != undefined) {
			history.replaceState({}, null, './order.php?' + query);
		}
		layer.closeAll();
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'GET',
			url: 'order-table.php?dstatus=' + dstatus + '&' + query,
			dataType: 'html',
			cache: false,
			success: function(data) {
				layer.close(ii);
				$("#listTable").html(data)
			},
			error: function(data) {
				layer.msg('服务器错误');
				return false;
			}
		});
	}

	function openlink(full_link) {
		window.open('javascript:window.name;', '<script>location.replace("' + full_link + '")<\/script>');
	}

	function searchOrder() {
		var column = $("select[name='column']").val();
		var value = $("input[name='value']").val();
		var uid = $("input[name='uid']").val();
		var channel = $("input[name='channel']").val();
		var starttime = $("input[name='starttime']").val();
		var endtime = $("input[name='endtime']").val();
		if (value == '') {
			listTable('uid=' + uid + '&channel=' + channel + '&starttime=' + starttime + '&endtime=' + endtime);
		} else {
			listTable('column=' + column + '&value=' + value + '&uid=' + uid + '&channel=' + channel + '&starttime=' + starttime + '&endtime=' + endtime);
		}
		return false;
	}

	function searchExport() {
		var query = '';
		var column = $("select[name='column']").val();
		var value = $("input[name='value']").val();
		var uid = $("input[name='uid']").val();
		var channel = $("input[name='channel']").val();
		var starttime = $("input[name='starttime']").val();
		var endtime = $("input[name='endtime']").val();
		if (value == '') {
			query = 'uid=' + uid + '&channel=' + channel + '&starttime=' + starttime + '&endtime=' + endtime;
		} else {
			query = 'column=' + column + '&value=' + value + '&uid=' + uid + '&channel=' + channel + '&starttime=' + starttime + '&endtime=' + endtime;
		}

		window.open('order-table.php?action=export&dstatus=' + dstatus + '&' + query);
	}

	function searchClear() {
		$("input[name='value']").val('');
		$("input[name='uid']").val('');
		$("input[name='channel']").val('');
		$("input[name='starttime']").val('');
		$("input[name='endtime']").val('');
		listTable('start');
	}

	function operation() {
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'POST',
			url: 'ajax_order.php?act=operation',
			data: $('#form1').serialize(),
			dataType: 'json',
			success: function(data) {
				layer.close(ii);
				if (data.code == 0) {
					listTable();
					layer.alert(data.msg);
				} else {
					layer.alert(data.msg);
				}
			},
			error: function(data) {
				layer.msg('请求超时');
				listTable();
			}
		});
		return false;
	}

	function showOrder(trade_no) {
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		var status = ['<span class="label label-primary">未支付</span>', '<span class="label label-success">已支付</span>'];
		$.ajax({
			type: 'GET',
			url: 'ajax_order.php?act=order&trade_no=' + trade_no,
			dataType: 'json',
			success: function(data) {
				layer.close(ii);
				if (data.code == 0) {
					var data = data.data;
					var item = '<table class="table table-condensed table-hover" id="orderItem">';
					item += '<tr><td colspan="6" style="text-align:center" class="orderTitle"><b>订单信息</b></td></tr>';
					item += '<tr class="orderTitle"><td class="info" class="orderTitle">系统订单号</td><td colspan="5" class="orderContent">' + data.trade_no + '</td></tr>';
					item += '<tr><td class="info" class="orderTitle">商户订单号</td><td colspan="5" class="orderContent">' + data.out_trade_no + '</td></tr>';
					item += '<tr><td class="info">商户ID</td class="orderTitle"><td colspan="5" class="orderContent"><a href="./ulist.php?my=search&column=uid&value=' + data.uid + '" target="_blank">' + data.uid + '</a></td>';
					item += '</tr><tr><td class="info" class="orderTitle">支付通道</td><td colspan="5" class="orderContent">' + data.channelname + '</td></tr>';
					item += '</tr><tr><td class="info" class="orderTitle">商品名称</td><td colspan="5" class="orderContent">' + data.name + '</td></tr>';
					item += '</tr><tr><td class="info" class="orderTitle">订单金额</td><td colspan="5" class="orderContent">' + data.money + '</td></tr>';
					item += '</tr><tr><td class="info" class="orderTitle">实际支付金额</td><td colspan="5" class="orderContent">' + data.realmoney + '</td></tr>';
					item += '</tr><tr><td class="info" class="orderTitle">手续费金额</td><td colspan="5" class="orderContent">' + data.getmoney + '</td></tr>';
					item += '</tr><tr><td class="info" class="orderTitle">创建时间</td><td colspan="5" class="orderContent">' + data.addtime + '</td></tr>';
					item += '</tr><tr><td class="info" class="orderTitle">完成时间</td><td colspan="5" class="orderContent">' + data.endtime + '</td></tr>';
					item += '</tr><tr><td class="info" class="orderTitle" title="只有在官方通道支付完成后才能显示">支付账号</td><td colspan="5" class="orderContent">' + data.buyer + '</td></tr>';
					item += '</tr><tr><td class="info" class="orderTitle">网站域名</td><td colspan="5" class="orderContent"><a href="http://' + data.domain + '" target="_blank" rel="noreferrer">' + data.domain + '</a></td></tr>';
					item += '</tr><tr><td class="info" class="orderTitle">支付IP</td><td colspan="5" class="orderContent"><a href="https://m.ip138.com/iplookup.asp?ip=' + data.ip + '" target="_blank" rel="noreferrer">' + data.ip + '</a></td></tr>';
					item += '<tr><td class="info" class="orderTitle">业务扩展参数</td><td colspan="5" class="orderContent">' + data.param + '</td></tr>';
					item += '<tr><td class="info" class="orderTitle">订单状态</td><td colspan="5" class="orderContent">' + status[data.status] + '</td></tr>';
					if (data.status > 0) {
						item += '<tr><td class="info" class="orderTitle">通知状态</td><td colspan="5" class="orderContent">' + (data.notify_status == 1 ? '<span class="label label-success">通知成功</span>' : '<span class="label label-danger">通知失败</span>（已通知' + data.notify + '次）') + '</td></tr>';
					}
					item += '<tr><td colspan="6" style="text-align:center" class="orderTitle"><b>订单操作</b></td></tr>';
					item += '<tr><td colspan="6"><a href="javascript:callnotify(\'' + data.trade_no + '\')" class="btn btn-xs btn-default">重新通知(异步)</a>&nbsp;</td></tr>';
					item += '</table>';
					var area = [$(window).width() > 480 ? '480px' : '100%'];
					layer.open({
						type: 1,
						area: area,
						title: '订单详细信息',
						skin: 'layui-layer-rim',
						content: item
					});
				} else {
					layer.alert(data.msg);
				}
			},
			error: function(data) {
				layer.msg('服务器错误');
				return false;
			}
		});
	}

	function callnotify(trade_no) {
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'POST',
			url: 'ajax_order.php?act=notify',
			data: {
				trade_no: trade_no
			},
			dataType: 'json',
			success: function(data) {
				layer.close(ii);
				if (data.code == 0) {
					layer.alert(data.msg);
				} else {
					layer.alert(data.msg);
				}
			},
			error: function(data) {
				layer.msg('服务器错误');
			}
		});
		return false;
	}

	function setStatus(trade_no, status) {
        var confirmobj = layer.confirm('确实已收到付款 ？', {
            btn: ['确定', '取消']
        }, function() {
            setStatusDo(trade_no, status);
        }, function() {
            layer.close(confirmobj);
        });
	}

	function setStatusDo(trade_no, status) {
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'get',
			url: 'ajax_order.php',
			data: 'act=setStatus&trade_no=' + trade_no + '&status=' + status,
			dataType: 'json',
			success: function(ret) {
				layer.close(ii);
				if (ret['code'] != 200) {
					alert(ret['msg'] ? ret['msg'] : '操作失败');
				}
				listTable();
			},
			error: function(data) {
				layer.msg('服务器错误');
				return false;
			}
		});
	}

	$(document).ready(function() {
		var items = $("select[default]");
		for (i = 0; i < items.length; i++) {
			$(items[i]).val($(items[i]).attr("default") || 0);
		}
		listTable();

		// $('.input-datepicker, .input-daterange').datepicker({
		// 	format: 'yyyy-mm-dd',
		// 	autoclose: true,
		// 	clearBtn: true,
		// 	language: 'zh-CN'
		// });
		$("#dstatus").change(function() {
			var val = $(this).val();
			dstatus = val;
			listTable();
		});
	})
</script>