<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$expenseObj = new Expenses();


$ordersObj = new Orders();
$categoryObj = new Categories();
$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
$expenseCategories = $categoryObj->getCategories('exp', $ownerId);
usort($expenseCategories, function ($a, $b) {
    return strcasecmp($a['groupName'] . $a['full_name'], $b['groupName'] . $b['full_name']);
});

$dateLabel = "Sales for ";
$start = $end = date('Y-m-d');

$filters = [
    'cat_id' => !empty($_GET['cat_id']) ? $_GET['cat_id'] : '',
    'description' => !empty($_GET['description']) ? $_GET['description'] : ''
];

if (isset($_GET['report'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
    $expenseData = $expenseObj->getShopExpenses($userData['shopId'], $from, $to, $filters);
    $dateLabel .= '<strong>' . $from . '</strong> to <strong>' . $to . '</strong>';
    $start = date('Y-m-d', strtotime($from));
    $end = date('Y-m-d', strtotime($to));
} else {
    $expenseData = $expenseObj->getShopExpenses($userData['shopId'], date('Y-m-d'), null, $filters);
    $dateLabel .= '<strong>' . date('Y-m-d') . '</strong>';
    $start = date('Y-m-d');
    $end = date('Y-m-d');
}

echo mainHeader(['page' => 'expense']);
?>

<div class="container">
    <a href="<?php echo SITE_URL; ?>pages/expenses/bulk.php" class="btn btn-danger btn-sm pull-right" style="margin-left: 10px">Add Expenses</a>
    <!-- <a href="javascript:void(0)" onclick="createCategory()" class="btn btn-success btn-sm pull-right">Add New</a>     -->
    <h3 style="margin-top: 0">Expenses</h3>
    <form method="GET" action="">
        <h4><?php echo $dateLabel; ?></h4>
        <div class="input-group">
            <input class="form-control datepicker" type="text" value="" readonly />
            <div class="input-group-btn" style="width: 25%">
                <select name="cat_id" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($expenseCategories as $cat) { ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filters['cat_id'] == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['full_name'] . (!empty($cat['groupName']) ? ' (' . $cat['groupName'] . ')' : '')); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="input-group-btn" style="width: 25%">
                <input type="text" name="description" class="form-control" placeholder="Description" value="<?php echo htmlspecialchars($filters['description']); ?>" />
            </div>
            <div class="input-group-btn">
                <input type="submit" value="Submit" name="report" class="btn btn-primary" />
            </div>
        </div>

        <input type="hidden" id="start" value="<?php echo $start; ?>">
        <input type="hidden" id="end" value="<?php echo $end; ?>">

        <input type="hidden" id="from" name="from" value="<?php echo $start; ?>">
        <input type="hidden" id="to" name="to" value="<?php echo $end; ?>">

    </form>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="100px">Sr.#</th>
                    <th width="150px">Name</th>
                    <th width="150px">Category</th>
                    <th>Description</th>
                    <th width="150px">Price</th>
                    <th width="150px">Date</th>
                    <th width="150px"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($expenseData as $key => $cust) {
                    $total += $cust['price']; ?>
                    <tr>
                        <td><?php echo $key + 1; ?></td>
                        <td><?php echo $cust['title']; ?></td>
                        <td><?php echo $cust['category_name']; ?></td>
                        <td><?php echo $cust['description']; ?></td>
                        <td><?php echo $cust['price']; ?></td>
                        <td><?php echo date('d M Y', strtotime($cust['exp_date'])); ?></td>
                        <td>
                            <?php if ($userData['role'] === 'owner') { ?><a onclick="deleteExpense(<?php echo $cust['id']; ?>)" class="btn btn-primary btn-xs" href="javascript:void(0)">Delete</a><?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th colspan="7">
                        <table style="text-align: right" width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <th style="text-align: right">Number of Expenses</th>
                                <th style="text-align: right"><?php echo sizeof($expenseData); ?></th>
                            </tr>
                            <tr>
                                <th style="text-align: right">Total Payment</th>
                                <th style="text-align: right"><?php echo $total; ?></th>
                            </tr>
                        </table>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
    function createCategory() {
        window.open("<?php echo SITE_URL; ?>pages/expenses/create.php", "", "width=300,height=400");
    }

    function deleteExpense(id) {
        if (confirm('Are you sure ?')) {
            window.open("<?php echo SITE_URL; ?>pages/expenses/delete.php?id=" + id, "", "width=300,height=400");
            window.location.reload();
        }
    }

    $('.datepicker').daterangepicker({
        maxDate: moment(),
        startDate: new Date($('#start').val()),
        endDate: new Date($('#end').val()),
    }, function(start, end, label) {
        $('#from').val(moment(start).format('YYYY-MM-DD'));
        $('#to').val(moment(end).format('YYYY-MM-DD'));

    });
</script>