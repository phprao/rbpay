<?php
@header('Content-Type: text/html; charset=UTF-8');

$cdnpublic = '//cdn.staticfile.org/';

?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
  <meta charset="utf-8" />
  <meta name="renderer" content="webkit">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $title ?></title>
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet" />
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
  <script src="<?php echo $cdnpublic ?>modernizr/2.8.3/modernizr.min.js"></script>
  <script src="<?php echo $cdnpublic ?>jquery/2.1.4/jquery.min.js"></script>
  <script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>

<body>
  <?php if (isset($islogin) && $islogin == 1) { ?>
    <nav class="navbar navbar-fixed-top navbar-default">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">导航按钮</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="./">大象支付管理中心</a>
        </div><!-- /.navbar-header -->
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav navbar-right">
            <li class="<?php echo checkIfActive('index,') ?>">
              <a href="./"><i class="fa fa-home"></i> 平台首页</a>
            </li>
            <li class="<?php echo checkIfActive('order') ?>">
              <a href="./order.php"><i class="fa fa-list"></i> 订单管理</a>
            </li>
            <li class="<?php echo checkIfActive('withdraw') ?>">
              <a href="./withdraw.php"><i class="fa fa-list"></i> 提现管理</a>
            </li>
            <li class="<?php echo checkIfActive('ulist') ?>">
              <a href="./ulist.php"><i class="fa fa-user"></i> 用户列表</a>
            </li>
            <li class="<?php echo checkIfActive('pay_channel') ?>">
              <a href="./pay_channel.php"><i class="fa fa-credit-card"></i> 支付通道</a>
            </li>

            <li class="<?php echo checkIfActive('set,gonggao') ?>">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> 系统设置<b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="./set.php?mod=site">网站信息配置</a></li>
                <li><a href="./gonggao.php">网站公告配置</a></li>
                <li><a href="./set.php?mod=template">首页模板配置</a></li>
                <li><a href="./set.php?mod=upimg">网站Logo上传</a></li>
              </ul>
            </li>
            <li class="<?php echo checkIfActive('log') ?>">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cube"></i> 其他功能<b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="./log.php">登录日志</a>
                <li>
              </ul>
            </li>
            <li><a href="./login.php?logout"><i class="fa fa-power-off"></i> 退出登录</a></li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container -->
    </nav><!-- /.navbar -->
  <?php } ?>