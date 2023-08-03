<?php

/**
 * 订单列表
 **/
include("../includes/common.php");
if (isset($islogin_agent) && $islogin_agent == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");

function display_status2($status, $addtime, $config_order_timeout)
{
	if ($status == 1) {
		$msg = '已支付';
	} elseif ((time() - strtotime($addtime)) > $config_order_timeout) {
		$msg = '已过期';
	} else {
		$msg = '未支付';
	}

	return $msg;
}
function display_status($status, $addtime, $config_order_timeout)
{
	if ($status == 1) {
		$msg = '<span style="color:#fff; padding:6px; background-color:#0a9a0a">已支付</span>';
	} elseif ((time() - strtotime($addtime)) > $config_order_timeout) {
		$msg = '<span style="color:#fff; padding:6px; background-color:#4a3dca">已过期</span>';
	} else {
		$msg = '<span style="color:#fff; padding:6px; background-color:#ef3a3a">未支付</span>';
	}
	return $msg;
}
function display_operation($status, $trade_no)
{
	if ($status == 0)
		$msg = '<li><a href="javascript:setStatus(\'' . $trade_no . '\', 1)">改已支付</a></li>';
	else
		$msg = '<li>&nbsp;无</li>';
	return $msg;
}

$sqls = "";
$links = '';
if (isset($_GET['uid']) && !empty($_GET['uid'])) {
	$uid = intval($_GET['uid']);
	$sqls .= " AND A.`uid`='$uid'";
	$links .= '&uid=' . $uid;
}
if (isset($_GET['channel']) && $_GET['channel'] > 0) {
	$channel = intval($_GET['channel']);
	$sqls .= " AND A.`channel`='$channel'";
	$links .= '&channel=' . $channel;
}
if (isset($_GET['dstatus']) && $_GET['dstatus'] >= 0) {
	$dstatus = intval($_GET['dstatus']);
	$sqls .= " AND A.status={$dstatus}";
	$links .= '&dstatus=' . $dstatus;
}

if (!empty($_GET['starttime']) || !empty($_GET['endtime'])) {
	if (!empty($_GET['starttime'])) {
		$starttime = str_replace('T', ' ', $_GET['starttime']) . ':00';
		$sqls .= " AND A.addtime>='{$starttime}'";
		$links .= "&starttime=" . $_GET['starttime'];
	}
	if (!empty($_GET['endtime'])) {
		$endtime = str_replace('T', ' ', $_GET['endtime']) . ':00';
		$sqls .= " AND A.addtime<='{$endtime}'";
		$links .= "&endtime=" . $_GET['endtime'];
	}
}

if (isset($_GET['value']) && !empty($_GET['value'])) {
	if ($_GET['column'] == 'name') {
		$sql = " A.`{$_GET['column']}` like '%{$_GET['value']}%'";
	} elseif ($_GET['column'] == 'addtime') {
		$kws = explode('>', $_GET['value']);
		$sql = " A.`addtime`>='{$kws[0]}' AND A.`addtime`<='{$kws[1]}'";
	} else {
		$sql = " A.`{$_GET['column']}`='{$_GET['value']}'";
	}
	$sql .= $sqls;
	$numrows = $DB->getColumn("SELECT count(*) from pre_order A WHERE{$sql}");

	// 统计
	$row = $DB->getRow("SELECT SUM(money) as money, SUM(getmoney) as getmoney from pre_order A WHERE{$sql}");

	if ($ismain) {
		$con = sprintf('包含 %s 的共有 <span style="color:red;">%d</span> 条订单，商品订单总额 <span style="color:red;">%.2f</span>，手续费总额 <span style="color:red;">%.2f</span>', $_GET['value'], $numrows, $row['money'], $row['getmoney']);
	} else {
		$con = sprintf('包含 %s 的共有 <span style="color:red;">%d</span> 条订单，商品订单总额 <span style="color:red;">%.2f</span>', $_GET['value'], $numrows, $row['money']);
	}

	$link = '&column=' . $_GET['column'] . '&value=' . $_GET['value'] . $links;
} else {
	$sql = " 1";
	$sql .= $sqls;
	$numrows = $DB->getColumn("SELECT count(*) from pre_order A WHERE{$sql}");

	// 统计
	$row = $DB->getRow("SELECT SUM(money) as money, SUM(getmoney) as getmoney from pre_order A WHERE{$sql}");

	if ($ismain) {
		$con = sprintf('共有 <span style="color:red;">%d</span> 条订单，商品订单总额 <span style="color:red;">%.2f</span>，手续费总额 <span style="color:red;">%.2f</span> ', $numrows, $row['money'], $row['getmoney']);
	} else {
		$con = sprintf('共有 <span style="color:red;">%d</span> 条订单，商品订单总额 <span style="color:red;">%.2f</span>', $numrows, $row['money']);
	}

	$link = $links;
}

// 导出表格
if (isset($_GET['action']) && $_GET['action'] == 'export') {
	$filename = '订单导出' . date('YmdHis');

	require_once '../includes/PHPExcel_1.8.0/Classes/PHPExcel.php';

	$objExcel = new \PHPExcel();
	$objExcel->getDefaultStyle()->getFont()->setName('微软雅黑');
	$objExcel->getDefaultStyle()->getFont()->setSize(9);
	$objExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

	$styleArray = array(
		'borders' => array(
			'allborders' => array(
				'style' => \PHPExcel_Style_Border::BORDER_THIN, //细边框
			),
		),
	);
	$objExcel->setActiveSheetIndex();
	$objActSheet = $objExcel->getActiveSheet();
	$objActSheet->setTitle('Sheet1');

	$header = ['系统订单', '商户订单', '商户号', '网站域名', '商品名称', '订单金额', '实付金额', '手续费', '支付通道', '下单时间', '支付状态'];
	for ($i = 0; $i < count($header); $i++) {
		$letter = strtoupper(chr(65 + $i));
		$objActSheet->getColumnDimension($letter)->setWidth(25);

		$objActSheet->setCellValue("{$letter}1", $header[$i]);
		$objActSheet->getStyle("{$letter}1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
	}

	$line = 2;
	$rs = $DB->query("SELECT A.*,B.plugin FROM pre_order A LEFT JOIN pre_channel B ON A.channel=B.id WHERE{$sql} order by addtime desc");
	while ($res = $rs->fetch()) {
		$objActSheet->setCellValueExplicit('A' . $line, $res['trade_no'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('B' . $line, $res['out_trade_no'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('C' . $line, $res['uid'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('D' . $line, $res['domain'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('E' . $line, $res['name'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('F' . $line, sprintf("%.2f", $res['money']), \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('G' . $line, sprintf("%.2f", $res['realmoney']), \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('H' . $line, $ismain ? sprintf("%.2f", $res['getmoney']) : '--', \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('I' . $line, $res['channel'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('J' . $line, $res['addtime'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('K' . $line, display_status2($res['status'], $res['addtime'], $conf['config_order_timeout']), \PHPExcel_Cell_DataType::TYPE_STRING);
		$line++;
	}
	$objActSheet->getStyle('A1:K' . ($line - 1))->applyFromArray($styleArray);
	$filename = iconv('UTF-8', 'GBK', $filename);
	$objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

	header("Content-Disposition:attachment;filename={$filename}.xls");
	$objWriter->save('php://output');
	exit;
}

?>
<form name="form1" id="form1">
	<div class="table-responsive">
		<?php echo $con ?>
		<table class="table table-striped table-bordered table-vcenter">
			<thead>
				<tr>
					<th>系统订单号<br />商户订单号</th>
					<th>商户号<br />网站域名</th>
					<th>商品名称<br />订单金额</th>
					<th>实际支付<br />手续费</th>
					<th>支付通道</th>
					<th>创建时间<br />完成时间</th>
					<th>支付状态</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$pagesize = 30;
				$pages = ceil($numrows / $pagesize);
				$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
				$offset = $pagesize * ($page - 1);

				$rs = $DB->query("SELECT A.*,B.plugin FROM pre_order A LEFT JOIN pre_channel B ON A.channel=B.id WHERE{$sql} order by addtime desc limit $offset,$pagesize");
				while ($res = $rs->fetch()) {
					$channelname = $DB->getColumn("SELECT name FROM pre_channel where id = {$res['channel']}");

					if(!$ismain) {
						$res['getmoney'] = '--';
					}

					echo '<tr><td><input type="checkbox" name="checkbox[]" id="list1" value="' . $res['trade_no'] . '" onClick="unselectall1()"><b><a href="javascript:showOrder(\'' . $res['trade_no'] . '\')" title="点击查看详情">' . $res['trade_no'] . '</a></b><br/>' . $res['out_trade_no'] . '</td><td><a href="./ulist.php?my=search&column=uid&value=' . $res['uid'] . '" target="_blank">' . $res['uid'] . '</a><br/><a onclick="openlink(\'http://' . $res['domain'] . '\')">' . $res['domain'] . '</a></td><td>' . $res['name'] . '<br/>￥<b>' . $res['money'] . '</b></td><td>￥<b>' . $res['realmoney'] . '</b><br/>￥<b>' . $res['getmoney'] . '</b></td><td>' . $channelname . ' (ID:' . $res['channel'] . ')</td><td>' . $res['addtime'] . '<br/>' . $res['endtime'] . '</td><td style="text-align:center">' . display_status($res['status'], $res['addtime'], $conf['config_order_timeout']) . '</td><td><div class="btn-group" role="group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">操作订单 <span class="caret"></span></button><ul class="dropdown-menu">' . display_operation($res['status'], $res['trade_no']) . '</ul></div></td></tr>';
				}
				?>
			</tbody>
		</table>
	</div>
</form>
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
