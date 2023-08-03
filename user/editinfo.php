<?php
include("../includes/common.php");
if (isset($islogin2) && $islogin2 == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$title = '个人资料';
include './head.php';
?>
<?php
$mod = isset($_GET['mod']) ? $_GET['mod'] : 'api';

if (strlen($userrow['phone']) == 11) {
	$userrow['phone'] = substr($userrow['phone'], 0, 3) . '****' . substr($userrow['phone'], 7, 10);
}

?>
<input type="hidden" id="situation" value="">
<div id="content" class="app-content" role="main">
	<div class="app-content-body ">
		<div class="bg-light lter b-b wrapper-md hidden-print">
			<h1 class="m-n font-thin h3">个人资料</h1>
		</div>
		<div class="wrapper-md control">
			<?php if (isset($msg)) { ?>
				<div class="alert alert-info">
					<?php echo $msg ?>
				</div>
			<?php } ?>
			<div class="tab-container ng-isolate-scope">
				<ul class="nav nav-tabs">
					<li style="width: 25%;" align="center">
						<a href="userinfo.php?mod=api">API信息</a>
					</li>
					<li style="width: 25%;" align="center" class="active">
						<a href="editinfo.php">修改资料</a>
					</li>
					<li style="width: 25%;" align="center">
						<a href="userinfo.php?mod=account">修改密码</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane ng-scope active">
						<form class="form-horizontal devform">
							<div class="form-group">
								<div class="col-sm-offset-2 col-sm-4">
									<h4>修改用户信息：</h4>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">手机号码</label>
								<div class="col-sm-9">
									<div class="input-group">
										<input class="form-control" type="text" name="phone" value="<?php echo $userrow['phone'] ?>">
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-offset-2 col-sm-4"><input type="button" id="editInfo" value="确定修改" class="btn btn-primary form-control" /><br />
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include 'foot.php'; ?>
<script src="<?php echo $cdnpublic ?>layer/3.1.1/layer.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script src="//static.geetest.com/static/tools/gt.js"></script>
<script>
	$(document).ready(function() {
		$("#editInfo").click(function() {
			var phone = $("input[name='phone']").val();
			if (phone == '') {
				layer.alert('请确保各项不能为空！');
				return false;
			}

			var ii = layer.load(2, {
				shade: [0.1, '#fff']
			});
			$.ajax({
				type: "POST",
				url: "ajax2.php?act=edit_info",
				data: {
					phone: phone,
				},
				dataType: 'json',
				success: function(data) {
					layer.close(ii);
					if (data.code == 1) {
						layer.alert('修改成功！', {
							icon: 1
						});
					} else {
						layer.alert(data.msg);
					}
				}
			});
		});
	});
</script>