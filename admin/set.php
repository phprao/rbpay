<?php

/**
 * 系统设置
 **/
include("../includes/common.php");
$title = '系统设置';
include './head.php';
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<div class="container" style="padding-top:70px;">
	<div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
		<?php
		$mod = isset($_GET['mod']) ? $_GET['mod'] : null;
		$mods = ['site' => '网站信息', 'template' => '首页模板', 'upimg' => 'LOGO设置', 'account' => '修改密码'];
		?>
		<ul class="nav nav-pills">
			<?php foreach ($mods as $key => $name) {
				echo '<li class="' . ($key == $mod ? 'active' : null) . '"><a href="set.php?mod=' . $key . '">' . $name . '</a></li>';
			} ?>
		</ul>
		<?php
		$conf = $CACHE->pre_fetch();
		if ($mod == 'site') {
		?>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">网站信息配置</h3>
				</div>
				<div class="panel-body">
					<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal" role="form">
						<div class="form-group">
							<label class="col-sm-2 control-label">网站名称</label>
							<div class="col-sm-10"><input type="text" name="sitename" value="<?php echo $conf['sitename']; ?>" class="form-control" required /></div>
						</div><br />
						<div class="form-group">
							<label class="col-sm-2 control-label">首页标题</label>
							<div class="col-sm-10"><input type="text" name="title" value="<?php echo $conf['title']; ?>" class="form-control" required /></div>
						</div><br />
						<div class="form-group">
							<label class="col-sm-2 control-label">关键字</label>
							<div class="col-sm-10"><input type="text" name="keywords" value="<?php echo $conf['keywords']; ?>" class="form-control" /></div>
						</div><br />
						<div class="form-group">
							<label class="col-sm-2 control-label">网站描述</label>
							<div class="col-sm-10"><input type="text" name="description" value="<?php echo $conf['description']; ?>" class="form-control" /></div>
						</div><br />
						<div class="form-group">
							<label class="col-sm-2 control-label">客服ＱＱ</label>
							<div class="col-sm-10"><input type="text" name="kfqq" value="<?php echo $conf['kfqq']; ?>" class="form-control" /></div>
						</div><br />
						<div class="form-group">
							<label class="col-sm-2 control-label">用户中心风格</label>
							<div class="col-sm-10"><select class="form-control" name="user_style" default="<?php echo $conf['user_style'] ?>">
									<option value="0">黑色（1）</option>
									<option value="1">黑色（2）</option>
									<option value="2">棕色（1）</option>
									<option value="3">棕色（2）</option>
									<option value="4">蓝色（1）</option>
									<option value="5">蓝色（2）</option>
									<option value="6">紫色（1）</option>
									<option value="7">紫色（2）</option>
								</select></div>
						</div><br />
						<div class="form-group">
							<label class="col-sm-2 control-label">首页显示模式</label>
							<div class="col-sm-10"><select class="form-control" name="homepage" default="<?php echo $conf['homepage'] ?>">
									<option value="0">默认显示首页</option>
									<option value="1">直接跳转登录页面</option>
									<option value="2">显示其它指定网址</option>
								</select></div>
						</div><br />
						<div class="form-group" id="setform4" style="<?php echo $conf['homepage'] != 2 ? 'display:none;' : null; ?>">
							<label class="col-sm-2 control-label">显示网址URL</label>
							<div class="col-sm-10"><input type="text" name="homepage_url" value="<?php echo $conf['homepage_url']; ?>" class="form-control" placeholder="将以frame方式显示" /></div>
						</div><br />
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary form-control" /><br />
							</div>
						</div>
					</form>
				</div>
			</div>
			<script>
				$("select[name='homepage']").change(function() {
					if ($(this).val() == 2) {
						$("#setform4").show();
					} else {
						$("#setform4").hide();
					}
				});
			</script>
		<?php
		} elseif ($mod == 'account_n' && $_POST['do'] == 'submit') {
			if (!checkRefererHost()) exit;
			$user = $_POST['user'];
			$oldpwd = $_POST['oldpwd'];
			$newpwd = $_POST['newpwd'];
			$newpwd2 = $_POST['newpwd2'];
			if ($user == null) showmsg('用户名不能为空！', 3);
			saveSetting('admin_user', $user);
			if (!empty($newpwd) && !empty($newpwd2)) {
				if ($oldpwd != $conf['admin_pwd']) showmsg('旧密码不正确！', 3);
				if ($newpwd != $newpwd2) showmsg('两次输入的密码不一致！', 3);
				saveSetting('admin_pwd', $newpwd);
			}
			$ad = $CACHE->clear();
			if ($ad) showmsg('修改成功！请重新登录', 1);
			else showmsg('修改失败！<br/>' . $DB->error(), 4);
		} elseif ($mod == 'account') {
		?>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">管理员账号配置</h3>
				</div>
				<div class="panel-body">
					<form action="./set.php?mod=account_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
						<div class="form-group">
							<label class="col-sm-2 control-label">用户名</label>
							<div class="col-sm-10"><input type="text" name="user" value="<?php echo $conf['admin_user']; ?>" class="form-control" required /></div>
						</div><br />
						<div class="form-group">
							<label class="col-sm-2 control-label">旧密码</label>
							<div class="col-sm-10"><input type="password" name="oldpwd" value="" class="form-control" placeholder="请输入当前的管理员密码" /></div>
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
		<?php
		} elseif ($mod == 'template') {
			$mblist = \lib\Template::getList();
		?>
			<style>
				.mblist {
					margin-bottom: 20px;
				}

				.mblist img {
					height: 110px;
				}
			</style>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title">首页模板设置</h3>
				</div>
				<div class="panel-body">
					<h4>当前使用模板：</h4>
					<div class="row text-center">
						<div class="col-xs-6 col-sm-4">
							<img class="img-responsive img-thumbnail img-rounded" src="/template/<?php echo $conf['template'] ?>/preview.png" onerror="this.src='/assets/img/NoImg.png'">
						</div>
						<div class="col-xs-6 col-sm-4">
							<p>模板名称：<?php echo $conf['template'] ?></p>
						</div>
					</div>
					<hr />
					<h4>更换模板：</h4>
					<div class="row text-center">
						<?php foreach ($mblist as $template) { ?>
							<div class="col-xs-6 col-sm-4 mblist">
								<a href="javascript:changeTemplate('<?php echo $template ?>')"><img class="img-responsive img-thumbnail img-rounded" src="/template/<?php echo $template ?>/preview.png" onerror="this.src='/assets/img/NoImg.png'" title="点击更换到该模板"><br /><?php echo $template ?></a>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php
		} elseif ($mod == 'upimg') {
			echo '<div class="panel panel-primary">
<div class="panel-heading"><h3 class="panel-title">更改首页LOGO</h3></div>
<div class="panel-body">';
			if ($_POST['s'] == 1) {
				if (!checkRefererHost()) exit;
				if (copy($_FILES['file']['tmp_name'], ROOT . 'assets/img/logo.png')) {
					echo "成功上传文件!<br>（可能需要清空浏览器缓存才能看到效果，按Ctrl+F5即可一键刷新缓存）";
				} else {
					echo "上传失败，可能没有文件写入权限";
				}
			}
			echo '<form action="set.php?mod=upimg" method="POST" enctype="multipart/form-data"><label for="file"></label><input type="file" name="file" id="file" /><input type="hidden" name="s" value="1" /><br><input type="submit" class="btn btn-primary btn-block" value="确认上传" /></form><br>现在的图片：<br><img src="../assets/img/logo.png?r=' . rand(10000, 99999) . '" style="max-width:100%">';
			echo '</div></div>';
		}
			?>
			</div>
	</div>
	<script src="<?php echo $cdnpublic ?>layer/3.1.1/layer.min.js"></script>
	<script>
		var items = $("select[default]");
		for (i = 0; i < items.length; i++) {
			$(items[i]).val($(items[i]).attr("default") || 0);
		}

		function saveSetting(obj) {
			var ii = layer.load(2, {
				shade: [0.1, '#fff']
			});
			$.ajax({
				type: 'POST',
				url: 'ajax.php?act=set',
				data: $(obj).serialize(),
				dataType: 'json',
				success: function(data) {
					layer.close(ii);
					if (data.code == 0) {
						layer.alert('设置保存成功！', {
							icon: 1,
							closeBtn: false
						}, function() {
							window.location.reload()
						});
					} else {
						layer.alert(data.msg, {
							icon: 2
						})
					}
				},
				error: function(data) {
					layer.msg('服务器错误');
					return false;
				}
			});
			return false;
		}

		function changeTemplate(template) {
			var ii = layer.load(2, {
				shade: [0.1, '#fff']
			});
			$.ajax({
				type: 'POST',
				url: 'ajax.php?act=set',
				data: {
					template: template
				},
				dataType: 'json',
				success: function(data) {
					layer.close(ii);
					if (data.code == 0) {
						layer.alert('更换模板成功！', {
							icon: 1,
							closeBtn: false
						}, function() {
							window.location.reload()
						});
					} else {
						layer.alert(data.msg, {
							icon: 2
						})
					}
				},
				error: function(data) {
					layer.msg('服务器错误');
					return false;
				}
			});
		}
	</script>