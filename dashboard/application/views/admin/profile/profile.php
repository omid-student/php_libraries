<div class="page-content-wrapper ">

    <div class="container-fluid">

        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="page-title m-0">تنظیمات مدیر</h4>
                        </div>
                        <div class="col-md-4">
                        </div>
                        <!-- end col -->
                    </div>
                    <!-- end row -->
                </div>
                <!-- end page-title-box -->
            </div>
        </div>
        <!-- end page title -->

        <div class="row">

            <div class="col-lg-4">
                <div class="card m-b-30">
                    <div class="card-body">

                        <h4 class="mt-0 header-title">تغییر رمز عبور</h4>
                        <hr>

                        <form class="" action="panel/admin/dashboard/change_password" novalidate="" method="post" enctype="multipart/form-data">

                            <div class="form-group">
                                <label>رمز عبور قبلی</label>
                                <input type="password" autocomplete="off" class="form-control" required="required" name="old_password">
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>رمز عبور جدید</label>
                                        <input type="password" autocomplete="off" class="form-control" required="required" name="new_password">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>تایید رمز عبور جدید</label>
                                        <input type="password" autocomplete="off" class="form-control" required="required" name="confirm_password">
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                تغییر رمز عبور
                            </button>

                        </form>

                    </div>
                </div>
            </div> <!-- end col -->

            <div class="col-lg-4">
                <div class="card m-b-30">
                    <div class="card-body">

                        <h4 class="mt-0 header-title">اطلاعات کاربری</h4>
                        <hr>

                        <form class="" action="panel/admin/dashboard/change_profile" novalidate="" method="post" enctype="multipart/form-data">

                            <div class="form-group">
                                <label>نام و نام خانوادگی</label>
                                <input type="text" value="<?=$full_name?>" class="form-control" required="required" name="full_name">
                            </div>

                            <hr>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                ذخیره تغییرات
                            </button>

                        </form>

                    </div>
                </div>
            </div> <!-- end col -->

        </div> <!-- end row -->

    </div><!-- container fluid -->

</div>