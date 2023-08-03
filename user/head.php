<?php
@header('Content-Type: text/html; charset=UTF-8');
if ($userrow['status'] == 0) {
  sysmsg('你的商户已被禁用！');
}
switch ($conf['user_style']) {
  case 1:
    $style = ['bg-black', 'bg-black', 'bg-white'];
    break;
  case 2:
    $style = ['bg-dark', 'bg-white', 'bg-dark'];
    break;
  case 3:
    $style = ['bg-dark', 'bg-dark', 'bg-light'];
    break;
  case 4:
    $style = ['bg-info', 'bg-info', 'bg-black'];
    break;
  case 5:
    $style = ['bg-info', 'bg-info', 'bg-white'];
    break;
  case 6:
    $style = ['bg-primary', 'bg-primary', 'bg-dark'];
    break;
  case 7:
    $style = ['bg-primary', 'bg-primary', 'bg-white'];
    break;
  default:
    $style = ['bg-black', 'bg-white', 'bg-black'];
    break;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8" />
  <title><?php echo $title ?> | <?php echo $conf['sitename'] ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <link rel="stylesheet" href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnpublic ?>animate.css/3.5.2/animate.min.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $cdnpublic ?>simple-line-icons/2.4.1/css/simple-line-icons.min.css" type="text/css" />
  <link rel="stylesheet" href="./assets/css/font.css" type="text/css" />
  <link rel="stylesheet" href="./assets/css/app.css" type="text/css" />

</head>

<body>
  <div class="app app-header-fixed  ">
    <!-- header -->
    <header id="header" class="app-header navbar" role="menu">
      <!-- navbar header -->
      <div class="navbar-header <?php echo $style[0] ?>">
        <button class="pull-right visible-xs dk" ui-toggle="show" target=".navbar-collapse">
          <i class="glyphicon glyphicon-cog"></i>
        </button>
        <button class="pull-right visible-xs" ui-toggle="off-screen" target=".app-aside" ui-scroll="app">
          <i class="glyphicon glyphicon-align-justify"></i>
        </button>
        <!-- brand -->
        <a href="./" class="navbar-brand text-lt">
          <i class="fa fa-btc"></i>
          <span class="hidden-folded m-l-xs"><?php echo $conf['sitename'] ?></span>
        </a>
        <!-- / brand -->
      </div>
      <!-- / navbar header -->

      <!-- navbar collapse -->
      <div class="collapse pos-rlt navbar-collapse box-shadow <?php echo $style[1] ?>">
        <!-- buttons -->
        <div class="nav navbar-nav hidden-xs">
          <a href="#" class="btn no-shadow navbar-btn" ui-toggle="app-aside-folded" target=".app">
            <i class="fa fa-dedent fa-fw text"></i>
            <i class="fa fa-indent fa-fw text-active"></i>
          </a>
        </div>
        <!-- / buttons -->

        <!-- nabar right -->
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="dropdown-toggle clear" data-toggle="dropdown">
              <span class="thumb-sm avatar pull-right m-t-n-sm m-b-n-sm m-l-sm">
                <img src="assets/img/user.png">
                <i class="on md b-white bottom"></i>
              </span>
              <span class="hidden-sm hidden-md" style="text-transform:uppercase;"><?php echo $uid ?></span> <b class="caret"></b>
            </a>
            <!-- dropdown -->
            <ul class="dropdown-menu animated fadeInRight w">
              <li>
                <a href="index.php">
                  <span>用户中心</span>
                </a>
              </li>
              <li>
                <a href="editinfo.php">
                  <span>修改资料</span>
                </a>
              </li>
              <li>
                <a href="userinfo.php?mod=account">
                  <span>修改密码</span>
                </a>
              </li>
              <li class="divider"></li>
              <li>
                <a ui-sref="access.signin" href="login.php?logout">退出登录</a>
              </li>
            </ul>
            <!-- / dropdown -->
          </li>
        </ul>
        <!-- / navbar right -->
      </div>
      <!-- / navbar collapse -->
    </header>
    <!-- / header -->
    <!-- aside -->
    <aside id="aside" class="app-aside hidden-xs <?php echo $style[2] ?>">
      <div class="aside-wrap">
        <div class="navi-wrap">

          <!-- nav -->
          <nav ui-nav class="navi clearfix">
            <ul class="nav">
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>导航</span>
              </li>
              <li class="<?php echo checkIfActive('index,') ?>">
                <a href="./">
                  <i class="glyphicon glyphicon-home icon text-primary-dker"></i>
                  <b class="label bg-info pull-right">N</b>
                  <span class="font-bold">用户中心</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('userinfo,editinfo,certificate') ?>">
                <a href class="auto">
                  <span class="pull-right text-muted">
                    <i class="fa fa-fw fa-angle-right text"></i>
                    <i class="fa fa-fw fa-angle-down text-active"></i>
                  </span>
                  <i class="glyphicon glyphicon-leaf icon text-success-lter"></i>
                  <span>个人资料</span>
                </a>
                <ul class="nav nav-sub dk">
                  <li>
                    <a href="userinfo.php?mod=api">
                      <span>API信息</span>
                    </a>
                  </li>
                  <li>
                    <a href="editinfo.php">
                      <span>修改资料</span>
                    </a>
                  </li>
                  <li>
                    <a href="userinfo.php?mod=account">
                      <span>修改密码</span>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="line dk"></li>
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>查询</span>
              </li>
              <li class="<?php echo checkIfActive('order') ?>">
                <a href="order.php">
                  <i class="glyphicon glyphicon-list-alt"></i>
                  <span>订单记录</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('withdraw') ?>">
                <a href="withdraw.php">
                  <i class="glyphicon glyphicon-list-alt"></i>
                  <span>提现记录</span>
                </a>
              </li>
              <li class="<?php echo checkIfActive('record') ?>">
                <a href="record.php">
                  <i class="glyphicon glyphicon-edit"></i>
                  <span>资金明细</span>
                </a>
              </li>
              <li class="line dk hidden-folded"></li>
              <li class="hidden-folded padder m-t m-b-sm text-muted text-xs">
                <span>其他</span>
              </li>
              <li>
                <a href="/doc.html" target="_blank">
                  <i class="fa fa-book"></i>
                  <span>开发文档</span>
                </a>
              </li>
            </ul>
          </nav>
          <!-- nav -->

          <!-- aside footer -->

          <!-- / aside footer -->
        </div>
      </div>
    </aside>
    <!-- / aside -->
    <!-- content -->