<?php

/**
 * 商户信息
 **/
include("../includes/common.php");
$title = '商户信息';
include './head.php';
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<div class="container" style="padding-top:70px;">
	<div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
		<?php

		$my = isset($_GET['my']) ? $_GET['my'] : null;
		$channel = $DB->getAll("select id, CONCAT(name,' - ', appid) as name from pre_channel WHERE `status` = 1 order by id asc");
		$channels = '';

		if ($my == 'add') {
			foreach ($channel as $k => $v) {
				$channels .= '<input type="checkbox" name="pay_channel[]" value="' . $v['id'] . '"/> ' . $v['name'] . '&nbsp;';
				if ($k % 2 == 1) {
					$channels .= '<br/>';
				}
			}

			echo '<div class="panel panel-primary">
<div class="panel-heading"><h3 class="panel-title">添加商户</h3></div>';
			echo '<div class="panel-body">';
			echo '<form action="./uset.php?my=add_submit" method="POST">
<h4><font color="blue">基本信息</font></h4>

<div class="form-group">
<label>登录账号:</label><br>
<input type="text" class="form-control" name="phone" value="">
</div>

<div class="form-group">
<label>登录密码:</label><br>
<input type="text" class="form-control" name="pwd" value="" placeholder="留空则只能使用密钥登录">
</div>

<div class="form-group">
<label>服务器IP白名单（提现接口校验，多个请使用竖线|隔开）:</label><br>
<input type="text" class="form-control" name="ip_white_list" value="">
</div>

<div class="form-group">
<label>转数快提现费率（0-100）:</label><br>
<input type="text" class="form-control" name="withdraw_rate" value="" required>
</div>

<div class="form-group">
<label>银行卡提现费率（0-100）:</label><br>
<input type="text" class="form-control" name="withdraw_rate_bank" value="" required>
</div>

<div class="form-group">
<label>单次提现金额范围:</label><br>
<input type="text" style="max-width:200px;display:inline-block" class="form-control" name="withdraw_min" value="0" required>
-
<input type="text" style="max-width:200px;display:inline-block" class="form-control" name="withdraw_max" value="0" required>
</div>

<div class="form-group">
<label>转数快支付费率（0-100）:</label><br>
<input type="text" class="form-control" name="pay_rate" value="" required>
</div>

<div class="form-group">
<label>银行卡支付费率（0-100）:</label><br>
<input type="text" class="form-control" name="pay_rate_bank" value="" required>
</div>

<div class="form-group">
<label>单次支付金额范围:</label><br>
<input type="text" style="max-width:200px;display:inline-block" class="form-control" name="pay_min" value="0" required>
-
<input type="text" style="max-width:200px;display:inline-block" class="form-control" name="pay_max" value="0" required>
</div>

<div class="form-group">
<label>支付通道:</label><br>

' . $channels . '

</div>

<div class="form-group">
<label>备注:</label><br>
<input type="text" class="form-control" name="remark" value="">
</div>

<h4><font color="blue">功能开关</font></h4>

<div class="form-group">
<label>支付权限:</label><br><select class="form-control" name="pay"><option value="1">1_开启</option><option value="0">0_关闭</option></select>
</div>

<div class="form-group">
<label>提现权限:</label><br><select class="form-control" name="withdraw"><option value="1">1_开启</option><option value="0">0_关闭</option></select>
</div>

<div class="form-group">
<label>商户状态:</label><br><select class="form-control" name="status"><option value="1">1_正常</option><option value="0">0_封禁</option></select>
</div>

<input type="submit" class="btn btn-primary btn-block"
value="确定添加"></form>';
			echo '<br/><a href="./ulist.php">>>返回商户列表</a>';
			echo '</div></div>';
		} elseif ($my == 'edit') {
			$uid = intval($_GET['uid']);
			$row = $DB->getRow("select * from pre_user where uid='$uid' limit 1");
			if (!$row) {
				showmsg('该商户不存在', 4);
			}

			$chan = explode(",", $row['pay_channel']);

			foreach ($channel as $k => $v) {
				if (in_array($v['id'], $chan)) {
					$checked = " checked";
				} else {
					$checked = " ";
				}
				$channels .= '<input type="checkbox" name="pay_channel[]" value="' . $v['id'] . '"' . $checked . '/> ' . $v['name'] . '&nbsp;';
				if ($k % 2 == 1) {
					$channels .= '<br/>';
				}
			}

			echo '<div class="panel panel-primary">
<div class="panel-heading"><h3 class="panel-title">修改商户信息 UID:' . $uid . '</h3></div>';

			echo '<div class="panel-body">';

			echo '<form action="./uset.php?my=edit_submit&uid=' . $uid . '" method="POST">
<h4><font color="blue">基本信息</font></h4>

<div class="form-group">
<label>手机号(登录账号):</label><br>
<input type="text" class="form-control" name="phone" value="' . $row['phone'] . '">
</div>

<div class="form-group">
<label>服务器IP白名单（提现接口校验，多个请使用竖线|隔开）:</label><br>
<input type="text" class="form-control" name="ip_white_list" value="' . $row['ip_white_list'] . '">
</div>

<div class="form-group" style="' . ($ismain ? '' : 'display:none') . '">
<label>转数快提现费率（0-100）:</label><br>
<input type="text" class="form-control" name="withdraw_rate" value="' . $row['withdraw_rate'] . '">
</div>
<div class="form-group" style="' . ($ismain ? '' : 'display:none') . '">
<label>银行卡提现费率（0-100）:</label><br>
<input type="text" class="form-control" name="withdraw_rate_bank" value="' . $row['withdraw_rate_bank'] . '">
</div>

<div class="form-group">
<label>单次提现金额范围:</label><br>
<input type="text" style="max-width:200px;display:inline-block" class="form-control" name="withdraw_min" value="' . $row['withdraw_min'] . '" required>
-
<input type="text" style="max-width:200px;display:inline-block" class="form-control" name="withdraw_max" value="' . $row['withdraw_max'] . '" required>
</div>

<div class="form-group" style="' . ($ismain ? '' : 'display:none') . '">
<label>转数快支付费率（0-100）:</label><br>
<input type="text" class="form-control" name="pay_rate" value="' . $row['pay_rate'] . '">
</div>
<div class="form-group" style="' . ($ismain ? '' : 'display:none') . '">
<label>银行卡支付费率（0-100）:</label><br>
<input type="text" class="form-control" name="pay_rate_bank" value="' . $row['pay_rate_bank'] . '">
</div>

<div class="form-group">
<label>单次支付金额范围:</label><br>
<input type="text" style="max-width:200px;display:inline-block" class="form-control" name="pay_min" value="' . $row['pay_min'] . '" required>
-
<input type="text" style="max-width:200px;display:inline-block" class="form-control" name="pay_max" value="' . $row['pay_max'] . '" required>
</div>

<div class="form-group">
<label>支付通道:</label><br>

' . $channels . '

</div>

<div class="form-group">
<label>备注:</label><br>
<input type="text" class="form-control" name="remark" value="' . $row['remark'] . '">
</div>

<h4><font color="blue">功能开关</font></h4>

<div class="form-group">
<label>支付权限:</label><br><select class="form-control" name="pay" default="' . $row['pay'] . '"><option value="1">1_开启</option><option value="0">0_关闭</option></select>
</div>

<div class="form-group">
<label>提现权限:</label><br><select class="form-control" name="withdraw" default="' . $row['withdraw'] . '"><option value="1">1_开启</option><option value="0">0_关闭</option></select>
</div>

<div class="form-group">
<label>商户状态:</label><br><select class="form-control" name="status" default="' . $row['status'] . '"><option value="1">1_正常</option><option value="0">0_封禁</option></select>
</div>

<h4><font color="blue">密码修改</font></h4>
<div class="form-group">
<label>重置登录密码:</label><br>
<input type="text" class="form-control" name="pwd" value="" placeholder="不重置密码请留空">
</div>

<input type="submit" class="btn btn-primary btn-block" value="确定修改"></form>
';
			echo '<br/><a href="./ulist.php">>>返回商户列表</a>';
			echo '</div></div>
<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
	$(items[i]).val($(items[i]).attr("default")||0);
}
</script>';
		} elseif ($my == 'add_submit') {
			if (!checkRefererHost()) {
				exit();
			}
			$phone = trim($_POST['phone']);
			$pay = intval($_POST['pay']);
			$withdraw = intval($_POST['withdraw']);
			$status = intval($_POST['status']);
			$ip_white_list = trim($_POST['ip_white_list']);
			$withdraw_rate = sprintf('%.2f', $_POST['withdraw_rate']);
			$withdraw_rate_bank = sprintf('%.2f', $_POST['withdraw_rate_bank']);
			$pay_rate = sprintf('%.2f', $_POST['pay_rate']);
			$pay_rate_bank = sprintf('%.2f', $_POST['pay_rate_bank']);
			$pay_channel = join(',', $_POST['pay_channel']);
			$remark = trim($_POST['remark']);
			$pay_min = intval($_POST['pay_min']);
			$pay_max = intval($_POST['pay_max']);
			$withdraw_min = intval($_POST['withdraw_min']);
			$withdraw_max = intval($_POST['withdraw_max']);

			if ($phone == NULL) {
				showmsg('手机号不能为空!', 3);
			} else {
				$key = random(32);
				$sql = "INSERT INTO `pre_user` (`key`, `addtime`, `phone`, `pay`, `withdraw`, `pay_rate`, `pay_rate_bank`, `status`, `ip_white_list`, `withdraw_rate`, `withdraw_rate_bank`, `pay_channel`, `remark`, `pay_min`, `pay_max`, `withdraw_min`, `withdraw_max`) VALUES (:key, NOW(), :phone, :pay, :withdraw, :pay_rate, :pay_rate_bank, :status, :ip_white_list, :withdraw_rate, :withdraw_rate_bank, :pay_channel, :remark, :pay_min, :pay_max, :withdraw_min, :withdraw_max)";
				$data = [':key' => $key, ':phone' => $phone, ':pay' => $pay, ':withdraw' => $withdraw, ':pay_rate' => $pay_rate, ':pay_rate_bank' => $pay_rate_bank, ':status' => $status, ':ip_white_list' => $ip_white_list, ':withdraw_rate' => $withdraw_rate, ':withdraw_rate_bank' => $withdraw_rate_bank, ':pay_channel' => $pay_channel, ':remark' => $remark, ':pay_min' => $pay_min, ':pay_max' => $pay_max, ':withdraw_min' => $withdraw_min, ':withdraw_max' => $withdraw_max];
				$sds = $DB->exec($sql, $data);
				if ($sds) {
					$uid = $DB->lastInsertId();
					if (!empty($_POST['pwd'])) {
						$pwd = getMd5Pwd(trim($_POST['pwd']), $uid);
						$DB->exec("update `pre_user` set `pwd` ='{$pwd}' where `uid`='$uid'");
					}
					showmsg('添加商户成功！商户ID：' . $uid . '<br/>密钥：' . $key . '<br/><br/><a href="./ulist.php">>>返回商户列表</a>', 1);
				} else {
					showmsg('添加商户失败！<br/>错误信息：' . $DB->error(), 4);
				}
			}
		} elseif ($my == 'edit_submit') {
			if (!checkRefererHost()) {
				exit();
			}
			$uid = $_GET['uid'];
			$rows = $DB->getRow("select * from pre_user where uid='$uid' limit 1");
			if (!$rows) {
				showmsg('当前商户不存在！', 3);
			}

			$phone = trim($_POST['phone']);
			$pay = intval($_POST['pay']);
			$withdraw = intval($_POST['withdraw']);
			$status = intval($_POST['status']);
			$ip_white_list = trim($_POST['ip_white_list']);
			$withdraw_rate = sprintf('%.2f', $_POST['withdraw_rate']);
			$withdraw_rate_bank = sprintf('%.2f', $_POST['withdraw_rate_bank']);
			$pay_rate = sprintf('%.2f', $_POST['pay_rate']);
			$pay_rate_bank = sprintf('%.2f', $_POST['pay_rate_bank']);
			$pay_channel = "";
			if (!empty($_POST['pay_channel'])) {
				$pay_channel = join(',', $_POST['pay_channel']);
			}
			$remark = trim($_POST['remark']);
			$pay_min = intval($_POST['pay_min']);
			$pay_max = intval($_POST['pay_max']);
			$withdraw_min = intval($_POST['withdraw_min']);
			$withdraw_max = intval($_POST['withdraw_max']);

			if ($phone == NULL) {
				showmsg('手机号不能为空!', 3);
			} else {
				$sql = "update `pre_user` set `phone` = :phone, `pay` = :pay, `withdraw` = :withdraw, `status` = :status, `ip_white_list` = :ip_white_list, `withdraw_rate` = :withdraw_rate, `withdraw_rate_bank` = :withdraw_rate_bank, `pay_rate` = :pay_rate, `pay_rate_bank` = :pay_rate_bank, `pay_channel` = :pay_channel,`remark` = :remark, `pay_min` = :pay_min, `pay_max` = :pay_max, `withdraw_min` = :withdraw_min, `withdraw_max` = :withdraw_max where `uid` = :uid";

				$data = [':phone' => $phone, ':pay' => $pay, ':withdraw' => $withdraw, ':status' => $status, ':ip_white_list' => $ip_white_list, ':withdraw_rate' => $withdraw_rate, ':withdraw_rate_bank' => $withdraw_rate_bank, ':pay_rate' => $pay_rate, ':pay_rate_bank' => $pay_rate_bank, ':pay_channel' => $pay_channel, ':remark' => $remark, ':pay_min' => $pay_min, ':pay_max' => $pay_max, ':withdraw_min' => $withdraw_min, ':withdraw_max' => $withdraw_max, ':uid' => $uid];

				if (!empty($_POST['pwd'])) {
					$pwd = getMd5Pwd(trim($_POST['pwd']), $uid);
					$sqs = $DB->exec("update `pre_user` set `pwd` ='{$pwd}' where `uid`='$uid'");
				} else {
					$sqs = true;
				}
				if ($DB->exec($sql, $data) !== false || $sqs) {
					showmsg('修改商户信息成功！<br/><br/><a href="./ulist.php">>>返回商户列表</a>', 1);
				} else {
					showmsg('修改商户信息失败！' . $DB->error(), 4);
				}
			}
		} elseif ($my == 'delete') {
			if (!checkRefererHost()) {
				exit();
			}
			$uid = $_GET['uid'];
			$sql = "DELETE FROM pre_user WHERE uid='$uid'";
			if ($DB->exec($sql)) {
				exit("<script language='javascript'>alert('删除商户成功！');history.go(-1);</script>");
			} else {
				exit("<script language='javascript'>alert(''删除商户失败！" . $DB->error() . "');history.go(-1);</script>");
			}
		}
		?>
	</div>
</div>