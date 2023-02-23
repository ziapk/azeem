<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

   
    $cat = new Categories();
    $stores = new Store();

    $groupNames = $cat->getGroupNames($shop['owner_id']);
    $ownerStores = $stores->getOwnerStores($userData['id']);

    echo mainHeader(['page' => 'reports']);

?>

<div class="container" ng-controller="reportController">
    <form method="POST" action="print.php">
        <h4>Reports</h4>
        <div class="row datepicker-parent">
            <div class="col-sm-3 form-group">
                <label>Select Date/Range</label>
                <input class="form-control datepicker" type="text" />
                <input type="hidden" name="from" id="from">
                <input type="hidden" name="to" id="to">
            </div>
            <?php 

            if($userData['role'] == 'owner') {?>
            <div class="col-sm-3 form-group">
                <label>Select Shop</label>
                <select class="form-control c-select" name="shopId">
                    <?php foreach ($ownerStores as $value) { ?>
                        <option value="<?php echo $value['id'];?>"><?php echo $value['full_name'];?></option>
                    <?php } ?>
                </select>
            </div>
            <?php } ?>
            <div class="col-sm-3 form-group">
                <label>Select Report</label>
                <select class="form-control" name="reportType" ng-change="checkReport(reportType)" ng-model="reportType">
                    <option value="">Select a Report</option>
                    <?php foreach ($reportsArray as $value) { if(in_array($userData['role'], $value['access'])) { ?>
                        <option value="<?php echo $value['id'];?>"><?php echo $value['title'];?></option>
                    <?php } } ?>
                </select>
            </div>
        </div>
        <div class="row" ng-if="reportType == 8 || reportType == 9">
            <?php foreach($groupNames as $group) {?>
                <div class="col-md-3">
                    <label>
                        <input type="checkbox" name="groupName[]" value="<?php echo $group['groupName'];?>">
                        <?php echo $group['groupName'];?>
                    </label>
                </div>
            <?php }?>
        </div>
        <div class="input-group">
            <div class="input-group-btn">
                <input type="submit" value="Submit" name="report" class="btn btn-primary" />
            </div>
        </div>
    </form>
</div>


<script type="text/javascript">
    app.controller('reportController', function($scope, $http, $httpParamSerializerJQLike, $filter) {
        console.log('abc')
    });
</script>
<?php echo mainFooter(); ?>

<script>
    $(document).ready(function () {
        $('.datepicker').daterangepicker({
            minDate: moment().subtract(1, 'year'),
            maxDate: moment(),
            parentEl: '.datepicker-parent',
        },  function(start, end, label) {
            $('#from').val(moment(start).format('YYYY-MM-DD'));
            $('#to').val(moment(end).format('YYYY-MM-DD'));
            
        });
    });
</script>


