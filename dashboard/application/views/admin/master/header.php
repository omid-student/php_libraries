<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <base href="<?=base_url()?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>پنل مدیریت</title>
    <meta content="پنل مدیریت" name="description" />
    <meta content="Omid Aghakhani" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- morris css -->
    <link rel="stylesheet" href="files/panel/admin/plugins/morris/morris.css">

    <link href="files/panel/admin/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="files/panel/admin/css/icons.css" rel="stylesheet" type="text/css">
    <link href="files/panel/admin/css/style.css" rel="stylesheet" type="text/css">

    <script src="files/panel/admin/js/jquery.min.js"></script>

</head>

<body class="fixed-left">

<!-- Loader -->
<div id="preloader">
    <div id="status">
        <div class="spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
    </div>
</div>

<!-- Begin page -->
<div id="wrapper">

    <!-- ========== Left Sidebar Start ========== -->
    <div class="left side-menu">
        <button type="button" class="button-menu-mobile button-menu-mobile-topbar open-left waves-effect">
            <i class="mdi mdi-close"></i>
        </button>

        <div class="left-side-logo d-block d-lg-none">
            <div class="text-center">

                <a href="" class="logo"><h4><?=config_item('project_title')?></h4></a>
            </div>
        </div>

        <div class="sidebar-inner slimscrollleft">

            <div id="sidebar-menu">
                <ul>
                    <li class="menu-title">اصلی</li>

                    <li class="<?=@$menu_dashboard?>">
                        <a href="panel/admin/dashboard" class="waves-effect">
                            <i class="dripicons-home"></i>
                            <span> داشبورد</span>
                        </a>
                    </li>

                    <li class="has_sub <?=@$order_menu?>">
                        <a href="javascript:void(0);" class="waves-effect"><i class="dripicons-store"></i><span> منوی لیستی </span>
                            <span style="margin-top: 0px" class="badge badge-dark badge-pill float-right">2</span>
                        </a>
                        <ul class="list-unstyled" id="order_menu">
                            <li class="<?=@$order_sub_menu?>">
                                <a href="panel/admin/">asdasd
                                    <span style="margin-top: 0px;" class="badge badge-default badge-pill float-right">
                                        2
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </div>
            <div class="clearfix"></div>
        </div> <!-- end sidebarinner -->
    </div>
    <!-- Left Sidebar End -->

    <!-- Start right Content here -->

    <div class="content-page">
        <!-- Start content -->
        <div class="content">

            <!-- Top Bar Start -->
            <div class="topbar">

                <div class="topbar-left	d-none d-lg-block">
                    <div class="text-center">
                        <h4 style="margin-top: 25px;color: white;"><?=config_item('project_title')?></h4>
                    </div>
                </div>

                <nav class="navbar-custom">

                    <ul class="list-inline float-right mb-0">

                        <li class="list-inline-item dropdown notification-list nav-user">
                            <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#" role="button"
                               aria-haspopup="false" aria-expanded="false">
                                <span class="d-none d-md-inline-block ml-1">ادمین <?=$admin_info->full_name?> <i class="mdi mdi-chevron-down"></i> </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated profile-dropdown">
                                <a class="dropdown-item" href="panel/admin/dashboard/profile"><i class="dripicons-user text-muted"></i> پروفایل</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="panel/admin/dashboard/logout"><i class="dripicons-exit text-muted"></i> خروج</a>
                            </div>
                        </li>

                    </ul>

                    <ul class="list-inline menu-left mb-0">
                        <li class="list-inline-item">
                            <button type="button" class="button-menu-mobile open-left waves-effect">
                                <i class="mdi mdi-menu"></i>
                            </button>
                        </li>
                    </ul>

                </nav>

            </div>
            <!-- Top Bar End -->

            <?php if (isset($class_id)) { ?>
            <script>
                setTimeout(function () {
                    $("#<?=$class_id?>").css('display', 'block');
                },500);
            </script>
            <?php } ?>
