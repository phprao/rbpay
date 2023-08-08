<?php

/**
 * 代理信息
 **/
include("../includes/common.php");
$title = '代理信息';
include './head.php';
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<div class="container" style="padding-top:70px;">
	<div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
		<?php

		$my = isset($_GET['my']) ? $_GET['my'] : null;

		if ($my == 'add') {
			echo '<div class="panel panel-primary">
			<div class="panel-heading"><h3 class="panel-title">添加代理</h3></div>';
			echo '<div class="panel-body">';
			echo '<form action="./agentset.php?my=add_submit" method="POST">
			<h4><font color="blue">基本信息</font></h4>

			<div class="form-group">
			<label>登录账号:</label><br>
			<input type="text" class="form-control" name="name" value="" placeholder="用户名至少6位字符">
			</div>

			<div class="form-group">
			<label>登录密码:</label><br>
			<input type="text" class="form-control" name="pass" value="" placeholder="密码至少6位字符">
			</div>

			<input type="submit" class="btn btn-primary btn-block"
			value="确定添加"></form>';
			echo '<br/><a href="./agentlist.php">>>返回代理列表</a>';
			echo '</div></div>';
		} elseif ($my == 'edit') {
			$id = intval($_GET['id']);
			$row = $DB->getRow("select * from pre_agent where id='$id' limit 1");
			if (!$row) {
				showmsg('该代理不存在', 4);
			}

			echo '<div class="panel panel-primary">
			<div class="panel-heading"><h3 class="panel-title">修改代理信息 ID:' . $id . '</h3></div>';

			echo '<div class="panel-body">';

			echo '<form action="./agentset.php?my=edit_submit&id=' . $id . '" method="POST">
			<h4><font color="blue">基本信息</font></h4>

			<div class="form-group">
			<label>登录账号:</label><br>
			<input type="text" class="form-control" disabled name="name" value="' . $row['name'] . '">
			</div>

			<h4><font color="blue">密码修改</font></h4>
			<div class="form-group">
			<label>重置登录密码:</label><br>
			<input type="text" class="form-control" name="pass" value="" placeholder="不重置密码请留空">
			</div>

			<input type="submit" class="btn btn-primary btn-block" value="确定修改"></form>
			';
			echo '<br/><a href="./agentlist.php">>>返回代理列表</a>';
			echo '</div></div>';
		} elseif ($my == 'add_submit') {
			if (!checkRefererHost()) {
				exit();
			}
			$name = trim($_POST['name']);
			$pass = trim($_POST['pass']);
			if (empty($name) || strlen($name) < 6) {
				showmsg('用户名至少6位字符' . $DB->error(), 4);
			}
			if (empty($pass) || strlen($pass) < 6) {
				showmsg('密码至少6位字符' . $DB->error(), 4);
			}

			$sql = "INSERT INTO `pre_agent` (`name`, `pass`, `addtime`) VALUES (:name, :pass, NOW())";
			$data = [':name' => $name, ':pass' => md5($pass)];
			$sds = $DB->exec($sql, $data);
			if ($sds) {
				$id = $DB->lastInsertId();
				showmsg('添加代理成功！代理ID：' . $id . '<br/><br/><a href="./agentlist.php">>>返回代理列表</a>', 1);
			} else {
				showmsg('添加代理失败！<br/>错误信息：' . $DB->error(), 4);
			}
		} elseif ($my == 'edit_submit') {
			if (!checkRefererHost()) {
				exit();
			}
			$id = $_GET['id'];
			$rows = $DB->getRow("select * from pre_agent where id='$id' limit 1");
			if (!$rows) {
				showmsg('当前代理不存在！', 3);
			}

			$pass = trim($_POST['pass']);
			if (!empty($pass)) {
				if (empty($pass) || strlen($pass) < 6) {
					showmsg('密码至少6位字符' . $DB->error(), 4);
				}
				$pass = md5($pass);
				$sqs = $DB->exec("update `pre_agent` set `pass` ='{$pass}' where `id`='$id'");

				if ($sqs) {
					showmsg('修改代理信息成功！<br/><br/><a href="./agentlist.php">>>返回代理列表</a>', 1);
				} else {
					showmsg('修改代理信息失败！' . $DB->error(), 4);
				}
			}
			showmsg('修改代理信息成功！<br/><br/><a href="./agentlist.php">>>返回代理列表</a>', 1);
		} elseif ($my == 'delete') {
			if (!checkRefererHost()) {
				exit();
			}
			$id = $_GET['id'];
			$sql = "DELETE FROM pre_agent WHERE id='$id'";
			if ($DB->exec($sql)) {
				exit("<script language='javascript'>alert('删除代理成功！');history.go(-1);</script>");
			} else {
				exit("<script language='javascript'>alert(''删除代理失败！" . $DB->error() . "');history.go(-1);</script>");
			}
		}
		?>
	</div>
</div>