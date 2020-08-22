<!DOCTYPE html>
<html lang="en">

<head>

    <base href="<?=base_url()?>">
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>پنل مدیریت</title>
    <meta content="پنل مدیریت" name="description" />
    <meta content="Omid Aghakhani" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <link rel="shortcut icon" href="files/panel/admin/images/favicon.ico">

    <link href="files/panel/admin/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="files/panel/admin/css/icons.css" rel="stylesheet" type="text/css">
    <link href="files/panel/admin/css/style.css" rel="stylesheet" type="text/css">

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

<div class="account-pages">

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-4">
            </div>
            <div class="col-lg-4">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="p-12">
                            <div>
                                <h4>تایید کد فعالسازی</h4>
                            </div>
                        </div>

                        <div class="p-2">

                            <?php
                            $ci = & get_instance();
                            if ($ci->session->flashdata('message')) { ?>
                                <div class="alert alert-info">
                                    <strong>توجه </strong> <?=$ci->session->flashdata('message')['message']?>
                                </div>
                            <?php } ?>

                            <form class="form-horizontal m-t-20" action="panel/admin/login/check_confirm" method="post">

                                <div class="form-group row">
                                    <div class="col-12">
                                        <input class="form-control" type="text" required="required" name="code" placeholder="کد فعالسازی">
                                    </div>
                                </div>

                                <div class="form-group text-center row m-t-20">
                                    <div class="col-12">
                                        <button class="btn btn-primary btn-block waves-effect waves-light" type="submit">بررسی کد</button>
                                    </div>
                                </div>

                                <input type="hidden" name="token" value="<?=$token?>">

                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-lg-4">
            </div>
        </div>
        <!-- end row -->
    </div>
</div>



<!-- jQuery  -->
<script src="files/panel/admin/js/jquery.min.js"></script>
<script src="files/panel/admin/js/bootstrap.bundle.min.js"></script>
<script src="files/panel/admin/js/modernizr.min.js"></script>
<script src="files/panel/admin/js/detect.js"></script>
<script src="files/panel/admin/js/fastclick.js"></script>
<script src="files/panel/admin/js/jquery.slimscroll.js"></script>
<script src="files/panel/admin/js/jquery.blockUI.js"></script>
<script src="files/panel/admin/js/waves.js"></script>
<script src="files/panel/admin/js/jquery.nicescroll.js"></script>
<script src="files/panel/admin/js/jquery.scrollTo.min.js"></script>

<!-- App js -->
<script src="files/panel/admin/js/app.js"></script>

</body>

</html>