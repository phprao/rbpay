<?php

/**
 * 订单列表
 **/
include("../includes/common.php");
if (isset($islogin2) && $islogin2 == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");

function display_status($status, $notify)
{
	if ($status == 0)
		$msg = '<font color=blue>未完成</font>';
	elseif ($status == 1)
		$msg = '<font color=green>已付款</font>';
	elseif ($status == 2)
		$msg = '<font color=red>已驳回</font>';
	elseif ($status == 3)
		$msg = '<font color=red>强制驳回</font>';
	else
		$msg = '<font color=green>强制完成</font>';
	return $msg;
}

$sql = " uid=$uid";
$links = '';
if (isset($_GET['dstatus']) && $_GET['dstatus'] >= 0) {
	$dstatus = intval($_GET['dstatus']);
	$sql .= " AND A.status={$dstatus}";
	$links .= '&dstatus=' . $dstatus;
}
if (isset($_GET['kw']) && !empty($_GET['kw'])) {
	$kw = daddslashes($_GET['kw']);
	if ($_GET['type'] == 1) {
		$sql .= " AND A.`trade_no`='{$kw}'";
	} elseif ($_GET['type'] == 2) {
		$sql .= " AND A.`out_trade_no`='{$kw}'";
	} elseif ($_GET['type'] == 6) {
		$kws = explode('>', $kw);
		$sql .= " AND A.`addtime`>='{$kws[0]}' AND A.`addtime`<='{$kws[1]}'";
	}
	$numrows = $DB->getColumn("SELECT count(*) from pre_withdraw_order A WHERE{$sql}");
	$con = '<p style="padding-left:15px">包含 <span style="color:red">' . $_GET['kw'] . '</span> 的共有 <span style="color:red">' . $numrows . '</span> 条订单</p>';
	$link = '&type=' . $_GET['type'] . '&kw=' . $_GET['kw'] . $links;
} else {
	$numrows = $DB->getColumn("SELECT count(*) from pre_withdraw_order A WHERE{$sql}");
	$con = '<p style="padding-left:15px">共有 <span style="color:red">' . $numrows . '</span> 条订单</p>';
	$link = $links;
}

// 导出表格
if (isset($_GET['action']) && $_GET['action'] == 'export') {
	$filename = '提现记录导出' . date('YmdHis');

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

	$header = ['系统订单号', '商户订单号', '订单金额', '提现银行', '提现卡号', '用户名', '创建时间', '完成时间', '状态'];
	for ($i = 0; $i < count($header); $i++) {
		$letter = strtoupper(chr(65 + $i));
		$objActSheet->getColumnDimension($letter)->setWidth(25);

		$objActSheet->setCellValue("{$letter}1", $header[$i]);
		$objActSheet->getStyle("{$letter}1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
	}

	$line = 2;
	$rs = $DB->query("SELECT * FROM pre_withdraw_order A WHERE{$sql} order by trade_no desc");
	while ($res = $rs->fetch()) {
		$objActSheet->setCellValueExplicit('A' . $line, $res['trade_no'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('B' . $line, $res['out_trade_no'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('C' . $line, $res['money'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('D' . $line, $res['bankname'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('E' . $line, $res['account'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('F' . $line, $res['username'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('G' . $line, $res['addtime'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('H' . $line, $res['endtime'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('I' . $line, [0 => '未完成', 1 => '已完成', 2 => '已驳回', 3 => '强制驳回', 4 => '强制完成'][$res['status']], \PHPExcel_Cell_DataType::TYPE_STRING);
		$line++;
	}
	$objActSheet->getStyle('A1:I' . ($line - 1))->applyFromArray($styleArray);
	$filename = iconv('UTF-8', 'GBK', $filename);
	$objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

	header("Content-Disposition:attachment;filename={$filename}.xls");
	$objWriter->save('php://output');
	exit;
}

?>
<div class="table-responsive">
	<?php echo $con ?>
	<table class="table table-striped table-bordered table-vcenter">
		<thead>
			<tr>
				<th>系统订单号/商户订单号</th>
				<th>订单金额</th>
				<th>提现银行</th>
				<th>提现卡号</th>
				<th>用户名</th>
				<th>创建时间 / 完成时间</th>
				<th>支付状态</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$pagesize = 30;
			$pages = ceil($numrows / $pagesize);
			$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$offset = $pagesize * ($page - 1);

			$rs = $DB->query("SELECT * FROM pre_withdraw_order A WHERE{$sql} order by trade_no desc limit $offset,$pagesize");
			while ($res = $rs->fetch()) {
				echo '<tr><td>' . $res['trade_no'] . '<br/>' . $res['out_trade_no'] . '</td><td>￥ <b>' . $res['money'] . '</b></td><td>' . $res['bankname'] . '</td><td>' . $res['account'] . '</td><td>' . $res['username'] . '</td><td>' . $res['addtime'] . '<br/>' . $res['endtime'] . '</td><td>' . display_status($res['status'], $res['notify']) . '</td></tr>';
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
