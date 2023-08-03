<?php

/**
 * 商户列表
 **/
include("../includes/common.php");
if (isset($islogin_agent) && $islogin_agent == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");

$sqls = " agent_id=$agent_id ";

if (isset($_GET['dstatus']) && $_GET['dstatus'] != '0') {
	$dstatus = explode('_', $_GET['dstatus']);
	$sqls .= " `{$dstatus[0]}`='{$dstatus[1]}'";
}

if (isset($_GET['value']) && !empty($_GET['value'])) {
	$sql = " `{$_GET['column']}`='{$_GET['value']}'";
	if (isset($sqls)) $sql .= " AND " . $sqls;
	$numrows = $DB->getColumn("SELECT count(*) from pre_user WHERE {$sql}");
	$con = '包含 ' . $_GET['value'] . ' 的共有 <b>' . $numrows . '</b> 个商户';
	$link = '&column=' . $_GET['column'] . '&value=' . $_GET['value'];
} else {
	$sql = $sqls;

	$numrows = $DB->getColumn("SELECT count(*) from pre_user WHERE {$sql}");
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
				<th>商户号</th>
				<th>商户余额</th>
				<th>代理支付收益</th>
				<th>代理提现收益</th>
				<th>添加时间</th>
				<th>状态</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$pagesize = 30;
			$pages = ceil($numrows / $pagesize);
			$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$offset = $pagesize * ($page - 1);

			$rs = $DB->query("SELECT * FROM pre_user left join pre_agent on pre_user.agent_id = pre_agent.id WHERE {$sql} order by uid desc limit $offset,$pagesize");
			while ($res = $rs->fetch()) {
				$res['pay_rate'] = $res['pay_rate'] . '% / ' . $res['pay_rate_bank'] . '%';
				$res['withdraw_rate'] = $res['withdraw_rate'] . '% / ' . $res['withdraw_rate_bank'] . '%';

				echo '<tr><td><b>' . $res['uid'] . '</b>[<a href="javascript:showKey(' . $res['uid'] . ',\'' . $res['key'] . '\')">查看密钥</a>]<br/>' . $res['phone'] . '</td><td class="money">' . $res['money'] . '</td><td>' . $res['pay_rate'] . '<br/>代理费 ' . $res['agent_pay_rate'] . '</td><td>' . $res['withdraw_rate'] . '<br/>代理费 ' . $res['agent_withdraw_rate'] . '</td><td>' . $res['agent_name'] . ' | ' . $res['agent_id'] . '</td><td>' . $res['addtime'] . '</td></tr>';
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
