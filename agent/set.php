<?php

/**
 * 系统设置
 **/
include("../includes/common.php");
$title = '修改密码';
include './head.php';
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");

$agentrow = $DB->getRow("SELECT * FROM pre_agent WHERE id='{$agent_id}' limit 1");

$mod = isset($_GET['mod']) ? $_GET['mod'] : null;
$mods = ['account' => '修改密码'];

if ($mod == 'account_n' && $_POST['do'] == 'submit') {
	if (!checkRefererHost()) exit;
	$oldpwd = trim($_POST['oldpwd']);
	$newpwd = trim($_POST['newpwd']);
	$newpwd2 = trim($_POST['newpwd2']);
	$md5pass = md5($oldpwd);
	if ($md5pass != $agentrow['agent_pass']) {
		showmsg('旧密码不正确！', 3);
	}
	if ($newpwd != $newpwd2 || empty($newpwd)) {
		showmsg('新密码错误！', 3);
	}

	$DB->exec("UPDATE pre_agent set agent_pass = '{$md5pass}' where id = {$agent_id} limit 1");

	setcookie("agent_token", "", time() - 604800);

	if ($ad) showmsg('修改成功！请重新登录', 1);
}

?>
<div class="container" style="padding-top:70px;">
	<div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">修改密码</h3>
			</div>
			<div class="panel-body">
				<form action="./set.php?mod=account_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-sm-2 control-label">用户名</label>
						<div class="col-sm-10"><input type="text" disabled name="user" value="<?php echo $agentrow['agent_name']; ?>" class="form-control" required /></div>
					</div><br />
					<div class="form-group">
						<label class="col-sm-2 control-label">旧密码</label>
						<div class="col-sm-10"><input type="password" name="oldpwd" value="" class="form-control" placeholder="请输入当前的代理密码" /></div>
					</div><br />
					<div class="form-group">
						<label class="col-sm-2 control-label">新密码</label>
						<div class="col-sm-10"><input type="password" name="newpwd" value="" class="form-control" placeholder="不修改请留空" /></div>
					</div><br />
					<div class="form-group">
						<label class="col-sm-2 control-label">重输密码</label>
						<div class="col-sm-10"><input type="password" name="newpwd2" value="" class="form-control" placeholder="不修改请留空" /></div>
					</div><br />
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary form-control" /><br />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>