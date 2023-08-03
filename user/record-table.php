<?php

/**
 * 资金明细
 **/
include("../includes/common.php");
if (isset($islogin2) && $islogin2 == 1) {
} else exit("<script language='javascript'>window.location.href='./login.php';</script>");

$sql = " uid=$uid";
$link = '';
if (isset($_GET['kw']) && !empty($_GET['kw'])) {
	$kw = daddslashes($_GET['kw']);
	if ($_GET['type'] == 1) {
		$sql .= " AND `type`='{$kw}'";
	} elseif ($_GET['type'] == 2) {
		$sql .= " AND `money`='{$kw}'";
	} elseif ($_GET['type'] == 3) {
		$sql .= " AND `trade_no`='{$kw}'";
	} elseif ($_GET['type'] == 6) {
		$kws = explode('>', $kw);
		$sql .= " AND `date`>='{$kws[0]}' AND `date`<='{$kws[1]}'";
	}
	$numrows = $DB->getColumn("SELECT count(*) from pre_record WHERE{$sql}");
	$con = '包含 ' . $_GET['kw'] . ' 的共有 <b>' . $numrows . '</b> 条记录';
	$link = '&type=' . $_GET['type'] . '&kw=' . $_GET['kw'];
} else {
	$numrows = $DB->getColumn("SELECT count(*) from pre_record WHERE{$sql}");
	$con = '共有 <b>' . $numrows . '</b> 条记录';
}

// 导出表格
if (isset($_GET['action']) && $_GET['action'] == 'export') {
	$filename = '资金明细导出' . date('YmdHis');

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

	$header = ['操作类型', '变更金额', '变更前金额', '变更后金额', '时间', '关联订单号'];
	for ($i = 0; $i < count($header); $i++) {
		$letter = strtoupper(chr(65 + $i));
		$objActSheet->getColumnDimension($letter)->setWidth(25);

		$objActSheet->setCellValue("{$letter}1", $header[$i]);
		$objActSheet->getStyle("{$letter}1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
	}

	$line = 2;
	$rs = $DB->query("SELECT * FROM pre_record WHERE{$sql} order by id desc");
	while ($res = $rs->fetch()) {
		$objActSheet->setCellValueExplicit('A' . $line, $res['type'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('B' . $line, $res['money'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('C' . $line, $res['oldmoney'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('D' . $line, $res['newmoney'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('E' . $line, $res['date'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$objActSheet->setCellValueExplicit('F' . $line, $res['trade_no'], \PHPExcel_Cell_DataType::TYPE_STRING);
		$line++;
	}
	$objActSheet->getStyle('A1:F' . ($line - 1))->applyFromArray($styleArray);
	$filename = iconv('UTF-8', 'GBK', $filename);
	$objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

	header("Content-Disposition:attachment;filename={$filename}.xls");
	$objWriter->save('php://output');
	exit;
}

?>
<div class="table-responsive">
	<table class="table table-striped table-bordered table-vcenter">
		<thead>
			<tr>
				<th>操作类型</th>
				<th>变更金额</th>
				<th>变更前金额</th>
				<th>变更后金额</th>
				<th>时间</th>
				<th>关联订单号</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$pagesize = 30;
			$pages = ceil($numrows / $pagesize);
			$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$offset = $pagesize * ($page - 1);

			$rs = $DB->query("SELECT * FROM pre_record WHERE{$sql} order by id desc limit $offset,$pagesize");
			while ($res = $rs->fetch()) {
				$a = '无';
				if ($res['trade_no']) {
					if ($res['trade_no'][0] == 'P') {
						$a = '<a href="./order.php?type=1&kw=' . $res['trade_no'] . '" target="_blank">' . $res['trade_no'] . '</a>';
					} else {
						$a = '<a href="./withdraw.php?type=1&kw=' . $res['trade_no'] . '" target="_blank">' . $res['trade_no'] . '</a>';
					}
				}
				echo '<tr><td>' . ($res['action'] == 2 ? '<font color="red">' . $res['type'] . '</font>' : '<font color="green">' . $res['type'] . '</font>') . '</td><td>' . ($res['action'] == 2 ? '- ' : '+ ') . $res['money'] . '</td><td>' . $res['oldmoney'] . '</td><td>' . $res['newmoney'] . '</td><td>' . $res['date'] . '</td><td>' . $a . '</td></tr>';
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
