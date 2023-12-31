<?php

/**
 * 资金明细
 **/
include("../includes/common.php");
$title = '资金明细';
include './head.php';
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<div class="container" style="padding-top:70px;">
	<div class="col-md-12 center-block" style="float: none;">
		<form onsubmit="return searchRecord()" method="GET" class="form-inline">
			<div class="form-group">
				<label>搜索</label>
				<select name="column" class="form-control">
					<option value="type">操作类型</option>
					<option value="money">变更金额</option>
					<option value="trade_no">关联订单号</option>
				</select>
			</div>
			<div class="form-group">
				<input type="text" class="form-control" name="value" placeholder="搜索内容">
			</div>
			<button type="submit" class="btn btn-primary">搜索</button>
			<a href="javascript:listTable('start')" class="btn btn-default" title="刷新明细列表"><i class="fa fa-refresh"></i></a>
		</form>

		<div id="listTable"></div>
	</div>
</div>
<script src="<?php echo $cdnpublic ?>layer/3.1.1/layer.min.js"></script>
<script>
	function listTable(query) {
		var url = window.document.location.href.toString();
		var queryString = url.split("?")[1];
		query = query || queryString;
		if (query == 'start' || query == undefined) {
			query = '';
			history.replaceState({}, null, './agent-record.php');
		} else if (query != undefined) {
			history.replaceState({}, null, './agent-record.php?' + query);
		}
		layer.closeAll();
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'GET',
			url: 'agent-record-table.php?' + query,
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

	function searchRecord() {
		var column = $("select[name='column']").val();
		var value = $("input[name='value']").val();
		if (value == '') {
			listTable();
		} else {
			listTable('column=' + column + '&value=' + value);
		}
		return false;
	}
	$(document).ready(function() {
		listTable();
	})
</script>