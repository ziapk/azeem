<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$doubleEntryObj = new DoubleEntry();
$accountTypes = $doubleEntryObj->getAccountTypes();
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
$accounts = $doubleEntryObj->getAccounts($ids);
echo mainHeader(['page' => 'coa']);
?>
<div ng-controller="coaController">
  <div class="container">
    <div class="content-section">
      <a href="./journal_entries.php" class="btn btn-sm btn-success pull-right">Ledger Entries</a>
      <a href="./ledger.php" class="btn btn-sm btn-success pull-right">Ledger</a>
      <h4 class="clearfix" style="margin-top: 0">Chat of Accounts</h4>
      <?php echo drawList($accounts); ?>
      </ul>
    </div>
  </div>
  <div class="modal fade" id="newAccount">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only">Close</span>
          </button>
          <h4 class="modal-title">New Account</h4>
        </div>
        <form novalidate="" id="newAccountForm">
          <div class="modal-body">
            <fieldset class="form-group">
              <label for="title">Title</label>
              <input type="text" class="form-control" id="title" name="title" placeholder="Title" required="" autocomplete="off">
            </fieldset>
            <input type="hidden" class="form-control" id="code" name="code">
            <input type="hidden" class="form-control" id="account_type" name="account_type">
            <fieldset class="form-group">
              <label for="parent_title">Parent Account</label>
              <input type="hidden" id="parent_id" name="parent_id">
              <input class="form-control" id="parent_title" name="parent_title" required="" readonly>
            </fieldset>
            <fieldset class="form-group">
              <label for="opening_balance">Opening Balance</label>
              <input type="text" class="form-control" id="opening_balance" name="opening_balance" placeholder="Opening Balance" autocomplete="off">
            </fieldset>
            <fieldset class="form-group">
              <label for="status">Status</label>
              <select class="form-control c-select" id="status" name="status" required="">
                <?php foreach ($accStatusArray as $key => $value) { ?>
                  <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php } ?>
              </select>
            </fieldset>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </form>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <div class="modal fade" id="editCategory">
    <div class="modal-dialog" role="document">

      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only">Close</span>
          </button>
          <h4 class="modal-title">Modify Account</h4>
        </div>
        <form novalidate="" id="modifyCategoryForm">
          <div class="modal-body">
            <fieldset class="form-group">
              <label for="mtitle">Title</label>
              <input type="hidden" id="mid" name="mid" autocomplete="off">
              <input type="text" class="form-control" id="mtitle" name="mtitle" placeholder="Title" required="" autocomplete="off">
            </fieldset>
            <fieldset class="form-group">
              <label for="mcode">Code</label>
              <input type="text" class="form-control" id="mcode" name="mcode" placeholder="Category Code" required="" autocomplete="off">
            </fieldset>
            <fieldset class="form-group">
              <label for="mparent_title">Parent Account</label>
              <select id="mparent_id" name="mparent_id" class="form-control">
                <?php foreach ($accounts as $value) { ?>
                  <option value="<?php echo $value['id']; ?>"><?php echo $value['title']; ?></option>
                <?php } ?>
              </select>
            </fieldset>
            <fieldset class="form-group">
              <label for="mopening_balance">Opening Balance</label>
              <input type="text" class="form-control" id="mopening_balance" name="mopening_balance" placeholder="Opening Balance" autocomplete="off">
            </fieldset>
            <fieldset class="form-group">
              <label for="mstatus">Status</label>
              <select class="form-control c-select" id="mstatus" name="mstatus" required="">
                <?php foreach ($accStatusArray as $key => $value) { ?>
                  <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php } ?>
              </select>
            </fieldset>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Submit</button>
          </div>
        </form>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
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
    $('#mparent_id').val(obj.parent_id);
    $('#mopening_balance').val(obj.opening_balance);
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
    $('#mopening_balance').val(obj.opening_balance);
    $('#status').val(obj.status);
    $('#account_type').val(obj.account_type);
  }
</script>
<script type="text/javascript">
  app.controller('coaController', function($scope, $http, $httpParamSerializerJQLike) {
    var site_url = '<?php echo SITE_URL ?>';

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
        var url = './newAccount.php';
        $http.post(url, params, {
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            }
          })
          .then(res => {
            form.reset();
            window.location.href = window.location.href;
          });
      },
      highlight: function(element) {
        $(element).parent().addClass("has-danger");
      },
      unhighlight: function(element) {
        $(element).parent().removeClass("has-danger");
      }
    });

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
        var url = './updateAccount.php';
        $http.post(url, params, {
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            }
          })
          .then(res => {
            form.reset();
            window.location.href = window.location.href;
          });
      },
      highlight: function(element) {
        $(element).parent().addClass("has-danger");
      },
      unhighlight: function(element) {
        $(element).parent().removeClass("has-danger");
      }
    });
    $scope.deleteAccount = (obj) => {
      const url = './deleteAccount.php';
      if (confirm('Are you sure you want to delete Account: ' + obj.title + ' (' + obj.code + ')')) {
        $http.post(url, $httpParamSerializerJQLike({
            id: obj.id
          }), {
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            }
          })
          .then(res => {
            window.location.href = window.location.href;
          });
      }
    }
    $(function() {
      $('#tree').dataTree({
        delimeter: "-"
      });
    });
  });
</script>
<?php echo mainFooter(['page' => 'coa']); ?>