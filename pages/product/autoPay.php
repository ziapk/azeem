<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$date = date('d-m-Y');

// $attendance = new Banks();
// $stats = $attendance->getBanks();

?>
<div class="main">
    <div class="content-section">
        <h4 class="clearfix">Upload File for Auto Pay Scroll</h4>
        <form novalidate="" id="uploadForm">
            <div class="row">
                <div class="col-sm-6 form-group">
                    <input name="date" class="form-control input-datetimepicker" required placeholder="Date" value="<?php echo $date; ?>">
                </div>
                <div class="col-sm-3 form-group">
                    <input name="AmountColumn" class="form-control" required placeholder="Enter Total Fee Column" value="Amount">
                </div>
                <div class="col-sm-3 form-group">
                    <input name="StudentIdColumn" class="form-control" required placeholder="Enter Student ID Column" value="Student Id">
                </div>
                <div class="col-sm-3 form-group">
                    <input name="ReciptId" class="form-control" required placeholder="Enter Invoice ID Column" value="Recipt No">
                </div>
                <div class="col-sm-3 form-group">
                    <input name="SheetName" class="form-control" required placeholder="Enter Excel Sheet Name" value="Sheet1">
                </div>
                <div class="col-sm-12 form-group">
                    <input name="import" type="file">
                </div>
                <div class="col-sm-12 form-group">
                    <input name="submit" type="submit" class="btn btn-danger pull-right">
                </div>
            </div>
            <div id="showResult"></div>
        </form>
    </div>
</div>

<script type="text/javascript">
    var site_url = '<?php echo SITE_URL . "api/importRacks.php" ?>';
    $('.input-datetimepicker').datetimepicker({
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove'
        },
        viewMode: 'days',
        format: 'DD-MM-YYYY'
    });
    $(document).ready(function() {
        $('#uploadForm').validate({
            debug: true,
            errorClass: 'text-danger',
            errorPlacement: function(error, element) {
                $(element).parents('.form-group').append(error);
            },
            submitHandler: function(form) {
                var formData = new FormData(form);
                var url = site_url;
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#showResult').html("****IMPORT RESULTS****<br><br>Success Invoices: " + (response.data.success.join(', ') || "0") + "<br><br>Failed Invoices: " + (response.data.failed.join(', ') || '0') + "<br><br>Not Matched Invoices: " + (response.data.notfound.join(', ') || '0'))
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }
        })
    })
</script>