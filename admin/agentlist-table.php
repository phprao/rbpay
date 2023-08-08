<?php

/**
 * 商户列表
 **/
include("../includes/common.php");
if (isset($islogin) && $islogin == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");

function display_status($status, $id)
{
	if ($status == 1) {
		return '<a href="javascript:setStatus(' . $id . ', 0)"><font color=green><i class="fa fa-check-circle"></i>正常</font></a>';
	} else {
		return '<a href="javascript:setStatus(' . $id . ', 1)"><font color=red><i class="fa fa-times-circle"></i>封禁</font></a>';
	}
}

if (isset($_GET['dstatus']) && $_GET['dstatus'] != '0') {
	$dstatus = explode('_', $_GET['dstatus']);
	$sqls = " `{$dstatus[0]}`='{$dstatus[1]}'";
}

if (isset($_GET['value']) && !empty($_GET['value'])) {
	$sql = " `{$_GET['column']}`='{$_GET['value']}'";
	if (isset($sqls)) $sql .= " AND " . $sqls;
	$numrows = $DB->getColumn("SELECT count(*) from pre_agent WHERE {$sql}");
	$con = '包含 ' . $_GET['value'] . ' 的共有 <b>' . $numrows . '</b> 个代理';
	$link = '&column=' . $_GET['column'] . '&value=' . $_GET['value'];
} else {
	$numrows = $DB->getColumn("SELECT count(*) from pre_agent WHERE 1");
	$sql = " 1";
	if (isset($sqls)) $sql .= " AND " . $sqls;
	$con = '共有 <b>' . $numrows . '</b> 个代理';
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
				<th>代理ID/登录账号</th>
				<th>代理余额</th>
				<th>商户列表</th>
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
			$rs = $DB->query("SELECT * FROM pre_agent WHERE {$sql} order by id desc limit $offset,$pagesize");
			if ($rs) {
				while ($res = $rs->fetch()) {

					if (!$ismain) {
						$showRecharge = '<b>' . $res['money'] . '</b>';
					} else {
						$showRecharge = '<b><a href="javascript:showRecharge(' . $res['id'] . ')">' . $res['money'] . '</a></b>';
					}

					$rows = $DB->getAll("SELECT uid from pre_user WHERE agent_id={$res['id']}");
					$userlist = '';
					if (!empty($rows)) {
						$c = [];
						foreach ($rows as $v) {
							array_push($c, $v['uid']);
						}
						$userlist = join(',', $c);
					}

					echo '<tr><td><b>' . $res['id'] . '</b><br/>' . $res['name'] . '</td><td class="money">' . $showRecharge . '</td><td>' . $userlist . '</td><td>' . $res['addtime'] . '</td><td>' . display_status($res['status'], $res['id']) . '</td><td><a href="./agentset.php?my=edit&id=' . $res['id'] . '" class="btn btn-xs btn-info">编辑</a>&nbsp;<a href="./agent-record.php?column=agent_id&value=' . $res['id'] . '" target="_blank" class="btn btn-xs btn-default">明细</a></td></tr>';
				}
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
