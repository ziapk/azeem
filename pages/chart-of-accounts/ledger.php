<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$doubleEntryObj = new DoubleEntry();
$accountTypes = $doubleEntryObj->getAccountTypes();
$accounts = $doubleEntryObj->getAccountLeafs($shop['id']);

echo mainHeader(['page' => 'coa']);


?>
<div class="container" ng-controller="accountingController">
  <div class="content-section">
    <h4 class="clearfix" style="margin-top: 0">General Journal</h4>
    <form id="form" ng-submit="generateJournal()">
      <div class="row">
        <div class="col-sm-4 form-group">
          <label for="reference" class="control-label">Voucher</label>
          <input type="text" id="reference" ng-model="form.reference" class="form-control">
        </div>
        <div class="col-sm-4 form-group">
          <label for="date" class="control-label">Voucher Date</label>
          <input type="text" id="date" ng-model="form.date" class="form-control datepicker">
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12 form-group">
          <label for="description" class="control-label">Narration / Description</label>
          <input type="text" id="description" ng-model="form.description" class="form-control">
        </div>
      </div>
      <table class="table table-hover table-striped">
        <thead>
          <tr>
            <th></th>
            <th>Select an Account</th>
            <th>Account Code</th>
            <th>Description</th>
            <th>D/C</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr class="row" ng-repeat="account in form.accounts track by $index">
            <td style="vertical-align: middle; text-align: center">
              <a ng-if="$index > 0" href="javascript:void(0)" ng-click="removeAccount($index)"><span class="fa fa-minus"></span></a>
              <a ng-if="$index == 0" href="javascript:void(0)" ng-click="newAccount()"><span class="fa fa-plus"></span></a>
            </td>
            <td>
              <input type="text" class="type-ahead-input form-control" ng-model="account.account" placeholder="Account Name" typeahead-on-select="selectAccount(account, $item)" uib-typeahead="address as address.title for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0">
            </td>
            <td>
              <input type="text" class="type-ahead-input form-control" ng-model="account.code" placeholder="Account Code" typeahead-on-select="selectAccount(account, $item)" uib-typeahead="address as address.code for address in searchGroup($viewValue)" typeahead-template-url="row.html" typeahead-show-hint="true" typeahead-min-length="0">
            </td>
            <td>
              <input type="text" ng-model="account.description" placeholder="Description" class="form-control">
            </td>
            <td>
              <select type="text" ng-model="account.type" ng-change="calculateSum()" class="form-control c-select">
                <option value="C">Credit</option>
                <option value="D">Debit</option>
              </select>
            </td>
            <td>
              <input type="text" ng-model="account.amount" ng-change="calculateSum()" placeholder="{{ account.type == 'C' ? 'Credit': 'Debit' }}" class="form-control">
            </td>
          </tr>
        </tbody>
      </table>
      <div ng-if="form.accounts[0].amount">
        <h5>Result</h5>
        <table class="table table-hover table-striped">
          <tbody>
            <tr>
              <td colspan="3">Total Credit</td>
              <td>{{total.credit}}</td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3">Total Debit</td>
              <td>{{total.debit}}</td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3">Balance (Debit - Credit)</td>
              <td>{{total.total}}</td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="clearfix">
        <input type="submit" value="Generate Now" class="pull-right btn btn-primary" />
      </div>
    </form>
  </div>
</div>


<script src="<?php echo $commonArray['root_dir']; ?>assets/vendor/angular.min.js"></script>
<script src="<?php echo $commonArray['root_dir']; ?>assets/vendor/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script type="text/javascript">
  app.controller('accountingController', function($scope, $http, $window) {
    $scope.form = {
      date: moment().format('YYYY-MM-DD'),
      reference: '',
      description: "",
      accounts: []
    };
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
    $scope.generateJournal = () => {
      if ($scope.form.date && $scope.form.description && $scope.form.accounts[0].amount && $scope.form.accounts[0].type && $scope.form.accounts[0].account) {
        $http.post('./makeJournal.php', JSON.stringify($scope.form)).then(response => {
          alert('Created successfully!');
          $scope.form = {
            date: moment().format('YYYY-MM-DD'),
            reference: '',
            description: "",
            accounts: []
          };
          // $window.location.assign("<?php echo $commonArray['site_url']; ?>journal_entries");

        }).catch(err => {
          console.log(err)
        })
      } else {
        alert('Your form seems incomplete, please fill all necessary fields and submit again');
      }
    }
    $scope.newAccount = () => {
      $scope.form.accounts.push({
        account: "",
        amount: "",
        type: 'C'
      });
    }

    $scope.selectAccount = function(item, data) {
      item.account = data.title;
      item.code = data.code;
      item.account_id = data.account_id;

      if (data.account_type === '5' || data.account_type === '1') {
        item.type = 'D';
      } else {
        item.type = 'C';
      }

      $scope.calculateSum();
    }

    $scope.total = {
      credit: 0,
      debit: 0,
      total: 0
    };

    $scope.calculateSum = () => {
      $scope.total.credit = 0;
      $scope.total.debit = 0;
      $scope.form.accounts.map(acc => {
        if (acc.type == 'C') {
          $scope.total.credit += parseFloat(acc.amount) || 0;
        } else {
          $scope.total.debit += parseFloat(acc.amount) || 0;
        }
      });
      $scope.total.total = $scope.total.debit - $scope.total.credit;
      console.log($scope.form);
      console.log($scope.total);
    }

    $scope.removeAccount = (index) => {
      $scope.form.accounts = $scope.form.accounts.filter((e, i) => i !== index);
    }
    $scope.newAccount();
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
      },
    }).on('dp.change', function(ev, old) {
      $scope.form.date = ev.date.format('YYYY-MM-DD');
      $scope.$apply();
    });
  });


  function fillModifyForm(element) {
    var id = $(element).parent().children('.category_id').val();
    var title = $(element).parent().children('.category_title').val();
    var code = $(element).parent().children('.category_code').val();
    var account_type = $(element).parent().children('.category_account_type').val();
    var group_id = $(element).parent().children('.category_group_id').val();
    var status = $(element).parent().children('.category_status').val();
    $('#mid').val(id);
    $('#mtitle').val(title);
    $('#mcode').val(code);
    $('#maccount_type').val(account_type);
    $('#mgroup_id').val(group_id);
    $('#mstatus').val(status);
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
  })
</script>
<script type="text/ng-template" id="row.html">
  <a href="javascript:void(0)" class="list-item">
      <pre ng-bind-html="match.model.title | uibTypeaheadHighlight:query"></pre><br />
      <pre ng-bind-html="match.model.code | uibTypeaheadHighlight:query"></pre>
      <pre class="catName" ng-bind="(match.model.account_type == '1' ? 'ASSETS' : match.model.account_type == '2' ? 'LIABILITIES' : match.model.account_type == '3' ? 'EQUITY' : match.model.account_type == '4' ? 'INCOME' : 'EXPENSES' )"></pre>
  </a>
</script>
<?php echo mainFooter(['page' => 'coa']); ?>