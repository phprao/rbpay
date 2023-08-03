<?php
include("../includes/common.php");
if (isset($islogin2) && $islogin2 == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$title = '订单记录';
include './head.php';
?>
<?php

$type_select = '<option value="0">所有支付方式</option>';
$rs = $DB->getAll("SELECT * FROM pre_type WHERE status=1 ORDER BY id ASC");
foreach ($rs as $row) {
	$type_select .= '<option value="' . $row['id'] . '">' . $row['showname'] . '</option>';
}
unset($rs);

?>
<div id="content" class="app-content" role="main">
	<div class="app-content-body ">

		<div class="bg-light lter b-b wrapper-md hidden-print">
			<h1 class="m-n font-thin h3">订单记录</h1>
		</div>
		<div class="wrapper-md control">
			<?php if (isset($msg)) { ?>
				<div class="alert alert-info">
					<?php echo $msg ?>
				</div>
			<?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading font-bold">
					<h3 class="panel-title">订单记录<a href="javascript:searchClear()" class="btn btn-default btn-xs pull-right" title="刷新订单列表"><i class="fa fa-refresh"></i></a></h3>
				</div>
				<div class="row wrapper">
					<form onsubmit="return searchOrder()" method="GET" class="form">
						<div class="col-md-2">
							<div class="form-group">
								<select class="form-control" name="type">
									<option value="1">系统订单号</option>
									<option value="2">商户订单号</option>
									<option value="6">交易时间</option>
								</select>
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group" id="searchword">
								<input type="text" class="form-control" name="kw" placeholder="搜索内容，时间支持区间查询 例如2018-06-07 16:15>2018-07-06 14:00">
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<select name="paytype" class="form-control" default="<?php echo $_GET['type'] ?? 0 ?>"><?php echo $type_select ?></select>
							</div>
						</div>
						<div class="col-md-1">
							<div class="form-group">
								<button class="btn btn-default" type="submit">搜索</button>
								<button class="btn btn-default" onclick="javascript:searchExport()">导出</button>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<select id="dstatus" class="form-control">
									<option value="-1">显示全部</option>
									<option value="1">只显示已完成</option>
									<option value="0">只显示未完成</option>
								</select>
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
<a style="display: none;" href="" id="vurl" rel="noreferrer" target="_blank"></a>
<script src="<?php echo $cdnpublic ?>layer/3.1.1/layer.min.js"></script>
<script>
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

	function searchOrder() {
		var type = $("select[name='type']").val();
		var kw = $("input[name='kw']").val();
		var paytype = $("select[name='paytype']").val();
		if (kw == '') {
			listTable('paytype=' + paytype);
		} else {
			listTable('type=' + type + '&kw=' + kw + '&paytype=' + paytype);
		}
		return false;
	}

	function searchClear() {
		$("select[name='type']").val(1);
		$("input[name='kw']").val('');
		$("select[name='paytype']").val(0);
		listTable('start');
	}

	$(document).ready(function() {
		var items = $("select[default]");
		for (i = 0; i < items.length; i++) {
			$(items[i]).val($(items[i]).attr("default") || 0);
		}
		listTable();
		$("#dstatus").change(function() {
			var val = $(this).val();
			dstatus = val;
			listTable();
		});
	})

	function searchExport() {
		var query = '';
		var type = $("select[name='type']").val();
		var kw = $("input[name='kw']").val();
		var paytype = $("select[name='paytype']").val();
		if (kw == '') {
			query = 'paytype=' + paytype;
		} else {
			query = 'type=' + type + '&kw=' + kw;
		}

		window.open('order-table.php?dstatus=' + dstatus + '&action=export&' + query);
	}
</script>