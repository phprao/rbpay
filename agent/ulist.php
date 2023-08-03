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
	<div class="col-md-12 center-block" style="float: none;">
		<div class="modal" id="modal-rmb">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title">余额充值与扣除</h4>
					</div>
					<div class="modal-body">
						<form id="form-rmb" onsubmit="return false;">
							<input type="hidden" name="uid" value="">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon p-0">
										<select name="do" style="-webkit-border-radius: 0;height:20px;border: 0;outline: none !important;border-radius: 5px 0 0 5px;padding: 0 5px 0 5px;">
											<option value="0">充值</option>
											<option value="1">扣除</option>
										</select>
									</span>
									<input type="number" class="form-control" name="rmb" placeholder="输入金额">
									<span class="input-group-addon">元</span>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-info" data-dismiss="modal">取消</button>
						<button type="button" class="btn btn-primary" id="recharge">确定</button>
					</div>
				</div>
			</div>
		</div>
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
					<option value="0">全部用户</option>
					<option value="status_1">用户状态正常</option>
					<option value="status_0">用户状态封禁</option>
					<option value="pay_1">支付状态正常</option>
					<option value="pay_0">支付状态关闭</option>
					<option value="withdraw_1">提现状态正常</option>
					<option value="withdraw_0">提现状态关闭</option>
				</select>
			</div>
			<button type="submit" class="btn btn-primary">搜索</button>&nbsp;<a href="./uset.php?my=add" class="btn btn-success" style="<?php if(!$ismain) echo 'display:none'; ?>">添加商户</a>
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

	function showKey(uid, key) {
		var clipboard;
		var confirmobj = layer.confirm(key + '<input type="hidden" id="copyContent" value="' + key + '"/>', {
			title: '查看密钥',
			shadeClose: true,
			btn: ['复制', '重置', '关闭'],
			success: function() {
				clipboard = new Clipboard('.layui-layer-btn0', {
					text: function() {
						return key;
					}
				});
				clipboard.on('success', function(e) {
					alert('复制成功！');
				});
				clipboard.on('error', function(e) {
					alert('复制失败，请长按链接后手动复制');
				});
			},
			end: function() {
				clipboard.destroy();
			}
		}, function() {}, function() {
			$.ajax({
				type: 'GET',
				url: 'ajax_user.php?act=resetUser&uid=' + uid,
				dataType: 'json',
				success: function(data) {
					if (data.code == 0) {
						alert('重置密钥成功！');
						showKey(uid, data.key);
					} else {
						layer.alert(data.msg, {
							icon: 2
						});
					}
				},
				error: function(data) {
					layer.msg('服务器错误');
					return false;
				}
			});
		}, function() {
			layer.close(confirmobj);
		});
	}

	function setStatus(uid, type, status) {
		$.ajax({
			type: 'GET',
			url: 'ajax_user.php?act=setUser&uid=' + uid + '&type=' + type + '&status=' + status,
			dataType: 'json',
			success: function(data) {
				if (data.code == 0) {
					listTable();
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

	function showRecharge(uid) {
		$("input[name='uid']").val(uid);
		$('#modal-rmb').modal('show');
	}

	$(document).ready(function() {
		$("#recharge").click(function() {
			var uid = $("input[name='uid']").val();
			var actdo = $("select[name='do']").val();
			var rmb = $("input[name='rmb']").val();
			if (rmb == '') {
				layer.alert('请输入金额');
				return false;
			}
			var ii = layer.load(2, {
				shade: [0.1, '#fff']
			});
			$.ajax({
				type: "POST",
				url: "ajax_user.php?act=recharge",
				data: {
					uid: uid,
					actdo: actdo,
					rmb: rmb
				},
				dataType: 'json',
				success: function(data) {
					layer.close(ii);
					if (data.code == 0) {
						layer.msg('修改余额成功');
						$('#modal-rmb').modal('hide');
						listTable();
					} else {
						layer.alert(data.msg);
					}
				},
				error: function(data) {
					layer.msg('服务器错误');
					return false;
				}
			});
		});
		listTable();
		$("#dstatus").change(function() {
			var val = $(this).val();
			dstatus = val;
			listTable();
		});
	})
</script>