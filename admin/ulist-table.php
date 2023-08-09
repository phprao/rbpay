<?php

/**
 * 商户列表
 **/
include("../includes/common.php");
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");

function display_status($status, $uid)
{
	if ($status == 1) {
		return '<a href="javascript:setStatus(' . $uid . ',\'user\',0)"><font color=green><i class="fa fa-check-circle"></i>正常</font></a>';
	} else {
		return '<a href="javascript:setStatus(' . $uid . ',\'user\',1)"><font color=red><i class="fa fa-times-circle"></i>封禁</font></a>';
	}
}
function display_paystatus($status, $uid)
{
	if ($status == 1) {
		return '<a href="javascript:setStatus(' . $uid . ',\'pay\',0)"><font color=green><i class="fa fa-check-circle"></i>支付</font></a>';
	} else {
		return '<a href="javascript:setStatus(' . $uid . ',\'pay\',1)"><font color=red><i class="fa fa-times-circle"></i>支付</font></a>';
	}
}
function display_withdrawstatus($status, $uid)
{
	if ($status == 1) {
		return '<a href="javascript:setStatus(' . $uid . ',\'withdraw\',0)"><font color=green><i class="fa fa-check-circle"></i>提现</font></a>';
	} else {
		return '<a href="javascript:setStatus(' . $uid . ',\'withdraw\',1)"><font color=red><i class="fa fa-times-circle"></i>提现</font></a>';
	}
}

if (isset($_GET['dstatus']) && $_GET['dstatus'] != '0') {
	$dstatus = explode('_', $_GET['dstatus']);
	$sqls = " `{$dstatus[0]}`='{$dstatus[1]}'";
}

if (isset($_GET['value']) && !empty($_GET['value'])) {
	$sql = " `{$_GET['column']}`='{$_GET['value']}'";
	if (isset($sqls)) $sql .= " AND " . $sqls;
	$numrows = $DB->getColumn("SELECT count(*) from pre_user WHERE{$sql}");
	$con = '包含 ' . $_GET['value'] . ' 的共有 <b>' . $numrows . '</b> 个商户';
	$link = '&column=' . $_GET['column'] . '&value=' . $_GET['value'];
} else {
	$numrows = $DB->getColumn("SELECT count(*) from pre_user WHERE 1");
	$sql = " 1";
	if (isset($sqls)) $sql .= " AND " . $sqls;
	$con = '共有 <b>' . $numrows . '</b> 个商户';
}
if (isset($_GET['dstatus']) && $_GET['dstatus'] != '0') {
	$link .= "&dstatus" . $_GET['dstatus'];
}

?>
<div class="table-responsive">
	<?php echo $con ?>
	<table class="table table-striped table-bordered table-vcenter">
		<thead>
			<tr>
				<th>商户号/登录账号</th>
				<th>余额</th>
				<th>支付费率</th>
				<th>提现费率</th>
				<th>支付通道</th>
				<th>代理信息</th>
				<th>添加时间</th>
				<th>状态</th>
				<th>操作</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$pagesize = 30;
			$pages = ceil($numrows / $pagesize);
			$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$offset = $pagesize * ($page - 1);

			$rs = $DB->query("SELECT * FROM pre_user WHERE{$sql} order by uid desc limit $offset,$pagesize");
			while ($res = $rs->fetch()) {
				$chan = $res['pay_channel'];
				$chanarr = [];
				if (!empty($chan)) {
					$chaninfo = $DB->getAll("SELECT * FROM pre_channel where id in ({$chan})");
					if (!empty($chaninfo)) {
						foreach ($chaninfo as $v) {
							if ($v['status'] == 1) {
								array_push($chanarr, '<span style="color:green">' . $v['name'] . '</span>');
							} else {
								array_push($chanarr, '<span style="color:red">' . $v['name'] . '</span>');
							}
						}
					}
				}

				$res['pay_channel'] = join(", ", $chanarr);

				$res['pay_rate'] = $res['pay_rate'] . '% / ' . $res['pay_rate_bank'] . '%';
				$res['withdraw_rate'] = $res['withdraw_rate'] . '% / ' . $res['withdraw_rate_bank'] . '%';

				if ($res['agent_id'] > 0) {
					$res['name'] = $DB->getColumn("SELECT name FROM pre_agent where id = {$res['agent_id']} limit 1");
				}

				if (!$ismain) {
					$res['pay_rate'] = '--';
					$res['withdraw_rate'] = '--';
					$showRecharge = '<b>' . $res['money'] . '</b>';
					$denglu = '';
				} else {
					$showRecharge = '<b><a href="javascript:showRecharge(' . $res['uid'] . ')">' . $res['money'] . '</a></b>';
					$denglu = '<a href="./sso.php?uid=' . $res['uid'] . '" target="_blank" class="btn btn-xs btn-success">登录</a>&nbsp;<a href="./uset.php?my=delete&uid=' . $res['uid'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此商户吗？\');">删除</a>';
				}

				echo '<tr><td><b>' . $res['uid'] . '</b>[<a href="javascript:showKey(' . $res['uid'] . ',\'' . $res['key'] . '\')">查看密钥</a>]<br/>' . $res['phone'] . '</td><td class="money">' . $showRecharge . '</td><td>' . $res['pay_rate'] . '<br/>代理佣金 ' . $res['agent_pay_rate'] . '%</td><td>' . $res['withdraw_rate'] . '<br/>代理佣金 ' . $res['agent_withdraw_rate'] . '%</td><td>' . $res['pay_channel'] . '</td><td>' . $res['name'] . ' | ' . $res['agent_id'] . '</td><td>' . $res['addtime'] . '</td><td>' . display_status($res['status'], $res['uid']) . '&nbsp;' . '<br/>' . display_paystatus($res['pay'], $res['uid']) . '&nbsp;' . display_withdrawstatus($res['withdraw'], $res['uid']) . '</td><td><a href="./uset.php?my=edit&uid=' . $res['uid'] . '" class="btn btn-xs btn-info">编辑</a>&nbsp;' . $denglu . '<br/><a href="./order.php?uid=' . $res['uid'] . '" target="_blank" class="btn btn-xs btn-default">订单</a>&nbsp;<a href="./withdraw.php?column=uid&value=' . $res['uid'] . '" target="_blank" class="btn btn-xs btn-default">提现</a>&nbsp;<a href="./record.php?column=uid&value=' . $res['uid'] . '" target="_blank" class="btn btn-xs btn-default">明细</a></td></tr>';
			}
			?>
		</tbody>
	</table>
</div>
<?php
echo '<div class="text-center"><ul class="pagination">';
$first = 1;
$prev = $page - 1;
$next = $page + 1;
$last = $pages;
if ($page > 1) {
	echo '<li><a href="javascript:void(0)" onclick="listTable(\'page=' . $first . $link . '\')">首页</a></li>';
	echo '<li><a href="javascript:void(0)" onclick="listTable(\'page=' . $prev . $link . '\')">&laquo;</a></li>';
} else {
	echo '<li class="disabled"><a>首页</a></li>';
	echo '<li class="disabled"><a>&laquo;</a></li>';
}
$start = $page - 10 > 1 ? $page - 10 : 1;
$end = $page + 10 < $pages ? $page + 10 : $pages;
for ($i = $start; $i < $page; $i++)
	echo '<li><a href="javascript:void(0)" onclick="listTable(\'page=' . $i . $link . '\')">' . $i . '</a></li>';
echo '<li class="disabled"><a>' . $page . '</a></li>';
for ($i = $page + 1; $i <= $end; $i++)
	echo '<li><a href="javascript:void(0)" onclick="listTable(\'page=' . $i . $link . '\')">' . $i . '</a></li>';
if ($page < $pages) {
	echo '<li><a href="javascript:void(0)" onclick="listTable(\'page=' . $next . $link . '\')">&raquo;</a></li>';
	echo '<li><a href="javascript:void(0)" onclick="listTable(\'page=' . $last . $link . '\')">尾页</a></li>';
} else {
	echo '<li class="disabled"><a>&raquo;</a></li>';
	echo '<li class="disabled"><a>尾页</a></li>';
}
echo '</ul></div>';
