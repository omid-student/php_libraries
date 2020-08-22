<footer class="footer">
    © 1399  <span class="d-none d-md-inline-block"> - پیاده سازی با <i class="mdi mdi-heart text-danger"></i> امید آقاخانی.</span>
</footer>

</div>
<!-- End Right content here -->

</div>
<!-- END wrapper -->

<!-- jQuery  -->
<script src="files/panel/admin/js/bootstrap.bundle.min.js"></script>
<script src="files/panel/admin/js/modernizr.min.js"></script>
<script src="files/panel/admin/js/detect.js"></script>
<script src="files/panel/admin/js/fastclick.js"></script>
<script src="files/panel/admin/js/jquery.slimscroll.js"></script>
<script src="files/panel/admin/js/jquery.blockUI.js"></script>
<script src="files/panel/admin/js/waves.js"></script>
<script src="files/panel/admin/js/jquery.nicescroll.js"></script>
<script src="files/panel/admin/js/jquery.scrollTo.min.js"></script>

<!--Morris Chart-->
<script src="files/panel/admin/plugins/morris/morris.min.js"></script>
<script src="files/panel/admin/plugins/raphael/raphael.min.js"></script>

<script src="files/panel/admin//plugins/alertify/js/alertify.js"></script>
<script src="files/panel/admin/pages/alertify-init.js"></script>

<!-- dashboard js -->
<script src="files/panel/admin/pages/dashboard.int.js"></script>

<!-- App js -->
<script src="files/panel/admin/js/app.js"></script>

<?php

$ci = & get_instance();

if ($ci->session->flashdata('message')) { ?>

    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h5><?=$ci->session->flashdata('message')['title']?></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p><?=$ci->session->flashdata('message')['message']?></p>
                </div>
            </div>

        </div>
    </div>

    <script>
        $("#myModal").modal();
    </script>

<?php } ?>

<script>
    $('a[data-question=true]').click(function (e) {
        e.preventDefault();
        var message = 'آیا اطمینان به انجام این عملیات هستید؟';
        if ($(this).data('message')) message = $(this).data('message');
        if (confirm(message))
            location.href = $(this).attr('href');
    });
</script>
</body>

</html>