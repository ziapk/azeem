<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$arr = [
  'from' => date('Y-m-d'),
  'to' => date('Y-m-d'),
];

if (!empty($_POST)) {
  $arr['from'] = $_POST['opening_date'];
  $arr['to'] = $_POST['closing_date'];
  $arr['account_id'] = $_POST['account_id'];
}
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

$doubleEntryObj = new DoubleEntry();
$demands = $doubleEntryObj->getJournals($arr, $ids);
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
<div class="container" ng-controller="accountingController">
  <div class="content-section">
    <h4 class="clearfix">Ledger</h4>
    <form action="" method="POST" class="form-group">
      <div class="row">
        <div class="col-sm-3">
          <input type="hidden" name="account_id" value="{{account_id}}">
          <input type="text" class="type-ahead-input form-control" ng-model="account" placeholder="Account Name" typeahead-on-select="selectAccount(account, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0">
        </div>
        <div class="col-sm-3">
          <input type="text" required class="form-control datepicker" name="opening_date" placeholder="From">
        </div>
        <div class="col-sm-3">
          <input type="text" required class="form-control datepicker" name="closing_date" placeholder="To">
        </div>
        <div class="col-sm-3">
          <input type="submit" value="Submit" class="btn btn-primary">
        </div>
      </div>
    </form>
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
          <th>Credit</th>
          <th>Debit</th>
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
            <td colspan="5">
              <?php echo $rows[0]['reference']; ?> [ <?php echo $rows['0']['v_description']; ?> ]
            </td>
          </tr>
          <?php foreach ($rows as $key => $product) {
            if ($product['account_id'] != $arr['account_id']) {
          ?>
              <tr>
                <td></td>
                <td></td>
                <td><?php echo $accountAssoc[$product['account_id']]['title']; ?></td>
                <td><?php echo $accountAssoc[$product['account_id']]['code']; ?></td>
                <td><?php echo $product['description']; ?></td>
                <td><?php echo ($product['entry_type'] == ($arr['account_id'] ? 'C' : 'D')) ? $product['amount'] : '' ?></td>
                <td><?php if ($product['entry_type'] == ($arr['account_id'] ? 'D' : 'C')) {
                      echo $product['amount'];
                    } ?></td>
                <td></td>
              </tr>
        <?php }
          }
          $count++;
        } ?>
      </tbody>
    </table>
  </div>
</div>

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
            window.location.href = window.location.href;
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

  $('.datepicker').datetimepicker({
    format: 'YYYY-MM-DD',
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
    }
  });
</script>

<script type="text/javascript">
  app.controller('accountingController', function($scope, $http, $window) {
    $scope.account = "";
    $scope.searchGroup = function(term) {
      return $http.get("./getAccountLeafs.php", {
          params: {
            term
          }
        })
        .then(function(response) {
          console.log(response);
          return response.data
        });
    }
    $scope.selectAccount = function(item, data) {
      $scope.account_id = data.account_id;
    }

  });
</script>
<script type="text/ng-template" id="row.html">
  <a href="javascript:void(0)" class="list-item">
      <pre ng-bind-html="match.model.title | uibTypeaheadHighlight:query"></pre><br />
      <pre ng-bind-html="match.model.code | uibTypeaheadHighlight:query"></pre>
      <pre class="catName" ng-bind="(match.model.account_type == '1' ? 'ASSETS' : match.model.account_type == '2' ? 'LIABILITIES' : match.model.account_type == '3' ? 'EQUITY' : match.model.account_type == '4' ? 'INCOME' : 'EXPENSES' )"></pre>
  </a>
</script>

<?php echo mainFooter(['page' => 'coa']); ?>