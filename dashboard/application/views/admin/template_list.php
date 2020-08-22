<div class="page-content-wrapper ">

    <div class="container-fluid">

        <div class="row">
            <div class="col-sm-12">
                <div class="page-title-box">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="page-title m-0"><?=$title?></h4>
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

            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th scope="col" style="width: 40px"></th>
                                    <th scope="col" style="width: 40px"></th>
                                    <th scope="col" style="width: 40px"></th>
                                    <th scope="col" style="width: 40px"></th>
                                    <th scope="col" style="width: 40px"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1; ?>
                                <?php foreach ($data as $item) { ?>
                                <tr>
                                    <td><?=($i++)+$offset?></td>
                                    <td></td>
                                    <td><?=long2ip($user['ip'])?></td>
                                    <td><?=gregorian2jalali($user['date_registered'],'d F Y')?></td>
                                    <td>
                                        <a data-question="true" href="panel/admin/users/delete/<?=$item['pid']?>" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <ul class="pagination">
                            <?php echo @$pagination; ?>
                        </ul>
                    </div>
                </div>
            </div> <!-- end col -->

        </div> <!-- end row -->

    </div><!-- container fluid -->

</div>