<?php
include("../includes/common.php");
if (isset($islogin2) && $islogin2 == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$title = '资金明细';
include './head.php';
?>
<div id="content" class="app-content" role="main">
	<div class="app-content-body ">

		<div class="bg-light lter b-b wrapper-md hidden-print">
			<h1 class="m-n font-thin h3">资金明细</h1>
		</div>
		<div class="wrapper-md control">
			<?php if (isset($msg)) { ?>
				<div class="alert alert-info">
					<?php echo $msg ?>
				</div>
			<?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading font-bold">
					<h3 class="panel-title">资金明细<a href="javascript:listTable('start')" class="btn btn-default btn-xs pull-right" title="刷新明细列表"><i class="fa fa-refresh"></i></a></h3>
				</div>
				<div class="row wrapper">
					<form onsubmit="return searchOrder()" method="GET" class="form">
						<div class="col-md-2">
							<div class="form-group">
								<select class="form-control" name="type">
									<option value="1">操作类型</option>
									<option value="2">变更金额</option>
									<option value="3">关联订单号</option>
									<option value="6">交易时间</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group" id="searchword">
								<input type="text" class="form-control" name="kw" placeholder="搜索内容，时间支持区间查询 例如2018-06-07 16:15>2018-07-06 14:00">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<button class="btn btn-default" type="submit">搜索</button>
								<button class="btn btn-default" onclick="javascript:searchExport()">导出</button>
							</div>
						</div>
					</form>
				</div>
				<div id="listTable"></div>

			</div>
		</div>
	</div>
</div>

<?php include 'foot.php'; ?>
<script src="<?php echo $cdnpublic ?>layer/3.1.1/layer.min.js"></script>
<script>
	function listTable(query) {
		var url = window.document.location.href.toString();
		var queryString = url.split("?")[1];
		query = query || queryString;
		if (query == 'start' || query == undefined) {
			query = '';
			history.replaceState({}, null, './record.php');
		} else if (query != undefined) {
			history.replaceState({}, null, './record.php?' + query);
		}
		layer.closeAll();
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'GET',
			url: 'record-table.php?' + query,
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

	function searchOrder() {
		var type = $("select[name='type']").val();
		var kw = $("input[name='kw']").val();
		if (kw == '') {
			listTable();
		} else {
			listTable('type=' + type + '&kw=' + kw);
		}
		return false;
	}
	$(document).ready(function() {
		listTable();
	})

	function searchExport() {
		var query = '';
		var type = $("select[name='type']").val();
		var kw = $("input[name='kw']").val();
		if (kw == '') {

		} else {
			query = 'type=' + type + '&kw=' + kw;
		}

		window.open('record-table.php?action=export&' + query);
	}
</script>