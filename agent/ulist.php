<?php

/**
 * 商户列表
 **/
include("../includes/common.php");
$title = '商户列表';
include './head.php';
if (isset($islogin_agent) && $islogin_agent == 1) {
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
</style>
<div class="container" style="padding-top:70px;">
	<form onsubmit="return searchUser()" method="GET" class="form-inline">
		<div class="form-group">
			<label>搜索</label>
			<select name="column" class="form-control">
				<option value="uid">商户号</option>
				<option value="phone">手机号码</option>
			</select>
		</div>
		<div class="form-group">
			<input type="text" class="form-control" name="value" placeholder="搜索内容">
		</div>
		<div class="form-group">
			<select name="dstatus" id="dstatus" class="form-control">
				<option value="0">全部商户</option>
				<option value="status_1">商户状态正常</option>
				<option value="status_0">商户状态封禁</option>
				<option value="pay_1">支付状态正常</option>
				<option value="pay_0">支付状态关闭</option>
				<option value="withdraw_1">提现状态正常</option>
				<option value="withdraw_0">提现状态关闭</option>
			</select>
		</div>
		<button type="submit" class="btn btn-primary">搜索</button>
		<a href="javascript:searchClear()" class="btn btn-default" title="刷新用户列表"><i class="fa fa-refresh"></i></a>
	</form>

	<div id="listTable"></div>
</div>
</div>
<script src="<?php echo $cdnpublic ?>layer/3.1.1/layer.min.js"></script>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script>
	var dstatus = 0;

	function listTable(query) {
		var url = window.document.location.href.toString();
		var queryString = url.split("?")[1];
		query = query || queryString;
		if (query == 'start' || query == undefined) {
			query = '';
			history.replaceState({}, null, './ulist.php');
		} else if (query != undefined) {
			history.replaceState({}, null, './ulist.php?' + query);
		}
		layer.closeAll();
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'GET',
			url: 'ulist-table.php?dstatus=' + dstatus + '&' + query,
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

	function searchUser() {
		var column = $("select[name='column']").val();
		var value = $("input[name='value']").val();
		if (value == '') {
			listTable();
		} else {
			listTable('column=' + column + '&value=' + value);
		}
		return false;
	}

	function searchClear() {
		$("input[name='value']").val('');
		listTable('start');
	}

	$(document).ready(function() {
		listTable();
		$("#dstatus").change(function() {
			var val = $(this).val();
			dstatus = val;
			listTable();
		});
	})
</script>