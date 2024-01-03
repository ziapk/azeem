<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$doubleEntryObj = new DoubleEntry();
$ids = [];
if ($userData['role'] === 'owner') {
  $storeObj = new Store();
  $ownerStores = $storeObj->getOwnerStores($shop['owner_id']);
  foreach ($ownerStores as $v) {
    $ids[] = $v['id'];
  }
} else {
  $ids[] = $shop['id'];
}

$startDate = $shop['sale_date'];
$endDate = $shop['sale_date'];

if (!empty($_GET['startDate'])) {
  $startDate = date('Y-m-d', strtotime($_GET['startDate']));
}
if (!empty($_GET['endDate'])) {
  $endDate = date('Y-m-d', strtotime($_GET['endDate']));
}
$demands = $doubleEntryObj->getJournals(['from' => $startDate, 'to' => $endDate], $ids);
$acd_ids = [];
$grouping = [];
foreach ($demands as $value) {
  $acd_ids[] = $value['account_id'];
  $grouping[$value['transaction_id']][] = $value;
}
$accountAssoc = [];
if (!empty($acd_ids)) {
  $accounts = $doubleEntryObj->getAccountsByIds(array_unique($acd_ids));
  foreach ($accounts as $value) {

    $accountAssoc[$value['id']] = $value;
  }
}


echo mainHeader(['page' => 'coa']);

?>
<div class="container" ng-controller="coaController">
  <div class="content-section">
    <h4 class="clearfix" style="margin-top: 0">General Journal <span class="text-danger"><?php echo $startDate; ?> - <?php echo $endDate; ?></span></h4>
    <form method="GET" action="">
      <div class="input-group form-group">
        <input date-range-picker placeholder="Date Range" class="form-control date-picker" type="text" ng-model="datePicker.date" options="{ locale: {format: 'DD/MM/YYYY'}}" />
        <div class="input-group-btn">
          <input type="submit" value="Submit" name="report" class="btn btn-primary" />
        </div>
      </div>
      <input type="hidden" name="startDate" value="{{datePicker.date.startDate | date:'yyyy-MM-dd'}}" />
      <input type="hidden" name="endDate" value="{{datePicker.date.endDate | date:'yyyy-MM-dd'}}" />
    </form>
    <div class="table-responsive">
      <table class="table table-sm table-func table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Account</th>
            <th>Account Code</th>
            <th>Description</th>
            <th>Debit</th>
            <th>Credit</th>
            <th></th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Account</th>
            <th>Account Code</th>
            <th>Description</th>
            <th>Debit</th>
            <th>Credit</th>
            <th></th>
          </tr>
        </tfoot>
        <tbody>
          <?php $count = 1;
          foreach ($grouping as $id => $rows) {  ?>
            <tr>
              <td>
                <?php echo $count; ?>
              </td>
              <td>
                <?php echo $rows[0]['transaction_date']; ?>
              </td>
              <td colspan="6">
                <?php echo $rows[0]['reference']; ?> [ <?php echo $rows['0']['v_description']; ?> ]
                <?php if ($userData['role'] === 'owner') { ?><a href="javascript:void(0)" class="text-danger" ng-click="deleteTransaction(<?php echo $id; ?>)">Delete Transaction</a><?php } ?>
              </td>
            </tr>
            <?php foreach ($rows as $key => $product) { ?>
              <tr>
                <td></td>
                <td></td>
                <td><?php echo $accountAssoc[$product['account_id']]['title']; ?></td>
                <td><?php echo $accountAssoc[$product['account_id']]['code']; ?></td>
                <td><?php echo $product['description']; ?></td>
                <td><?php echo ($product['entry_type'] == 'D') ? $product['amount'] : '' ?></td>
                <td><?php if ($product['entry_type'] == 'C') {
                      echo $product['amount'];
                    } ?></td>
                <td></td>
              </tr>
          <?php }
            $count++;
          } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script type="text/javascript">
  app.controller('coaController', function($scope, $http, $httpParamSerializerJQLike) {
    var site_url = '<?php echo SITE_URL ?>';
    $scope.deleteTransaction = (id) => {
      var site_url = '<?php echo $commonArray["site_url"] ?>';
      const params = 'id=' + id;
      console.log('id', id, params, site_url);
      if (confirm('Are you sure you want to delete this transaction')) {
        var url = './deleteTransaction.php';
        $http.post(url, $httpParamSerializerJQLike({
            id
          }), {
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            }
          })
          .then(res => {
            alert('Transaction Deleted!');
            window.location.reload();
          });
      }
    }
  });
</script>
<?php echo mainFooter(['page' => 'coa']); ?>
<script type="text/javascript">
  function fillModifyForm(obj) {
    // var id = $(element).parent().children('.category_id').val();
    // var title = $(element).parent().children('.category_title').val();
    // var code = $(element).parent().children('.category_code').val();
    // var account_type = $(element).parent().children('.category_account_type').val();
    // var group_id = $(element).parent().children('.category_group_id').val();
    // var status = $(element).parent().children('.category_status').val();
    console.log(obj);
    $('#mid').val(obj.id);
    $('#mtitle').val(obj.title);
    $('#mcode').val(obj.code);
    $('#maccount_type').val(obj.account_type);
    $('#mgroup_id').val(obj.group_id);
    $('#mstatus').val(obj.status);
  }

  function fillModifyForm2(obj) {
    // var id = $(element).parent().children('.category_id').val();
    // var title = $(element).parent().children('.category_title').val();
    // var code = $(element).parent().children('.category_code').val();
    // var account_type = $(element).parent().children('.category_account_type').val();
    // var group_id = $(element).parent().children('.category_group_id').val();
    // var status = $(element).parent().children('.category_status').val();
    $('#parent_id').val(obj.id);
    $('#parent_title').val(obj.code + " " + obj.title);
    // $('#title').val(obj.title);
    $('#code').val(obj.code + '-');
    $('#account_type').val(obj.account_type);
    $('#group_id').val(obj.group_id);
    $('#status').val(obj.status);
  }

  var site_url = '<?php echo $commonArray["site_url"] ?>';

  $(document).ready(function() {
    $('#newAccountForm').validate({
      debug: false,
      errorClass: "text-help",
      errorElement: "span",
      // default error placement
      errorPlacement: function(error, element) {
        $(element).parent().append(error);
      },
      submitHandler: function(form) {
        var params = $(form).serialize();
        var url = site_url + 'actions/double-entry/newAccount.php';
        $.ajax({
          type: "POST",
          url: url,
          data: params,
          processData: false,
          async: false,
          success: function(result) {
            form.reset();
            window.location.reload();
          }
        })
      },
      highlight: function(element) {
        $(element).parent().addClass("has-danger");
      },
      unhighlight: function(element) {
        $(element).parent().removeClass("has-danger");
      }
    });
  })


  $(document).ready(function() {
    $('#modifyCategoryForm').validate({
      debug: false,
      errorClass: "text-help",
      errorElement: "span",
      // default error placement
      errorPlacement: function(error, element) {
        $(element).parent().append(error);
      },
      submitHandler: function(form) {
        var params = $(form).serialize();
        var url = site_url + 'actions/double-entry/updateAccount.php';
        $.ajax({
          type: "POST",
          url: url,
          data: params,
          processData: false,
          async: false,
          success: function(result) {
            form.reset();
            window.location = window.location;
          }
        })
      },
      highlight: function(element) {
        $(element).parent().addClass("has-danger");
      },
      unhighlight: function(element) {
        $(element).parent().removeClass("has-danger");
      }
    });
    $(function() {
      $('#tree').dataTree({
        delimeter: "-"
      });
    });
  })
</script>