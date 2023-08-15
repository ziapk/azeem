<?php
include_once dirname(__FILE__) . '/../../include/settings.php';

$orders = [];
$from = $_POST['from'];
$to = $_POST['to'];
$opening_balance = $_POST['opening_balance'];
$shopId = !empty($_POST['shopId']) ? $_POST['shopId'] : $shop['id'];
$reportType = $_POST['reportType'];

$ordersObj = new Orders();
$productObj = new Products();

$columns = [];
$columns = [];
$srNo = true;
// $sum = [];


?>

<style>
	h2 {
		margin-bottom: 10px;
	}

	h4 {
		margin-top: 0;
	}

	body {
		margin: 0
	}

	.table {
		border: 1px solid;
		width: 100%;
		border-collapse: collapse;
	}

	td,
	th {
		padding: 5px;
		border: 1px solid
	}
</style>
<?php
switch ($reportType) {
	case '0':
		$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$search = $productObj->getOwnerProductsPagination($ownerId, ['page' => 1, 'perPage' => 100000, 'publisher_id' => $publisher_id, 'status' => 1], $shopId);
		$orders = $search['records'];
		$stores = new Store();
		$selectShop = $stores->getStore($shopId);
		include_once dirname(__FILE__) . '/shop_products.php';
		exit;
	case '1':
		foreach ($_POST['product_id'] as $pid) {
			if (!empty($pid)) {
				$product_ids[] = $pid;
			}
		}
		if (!empty($product_ids)) {
			$orders = $ordersObj->ordersReport($shopId, $from, $to, $product_ids);
			print_r($orders);
			include_once dirname(__FILE__) . '/salesProductsReport.php';
		} else {
			$orders = $ordersObj->ordersReport($shopId, $from, $to, $product_ids);
			include_once dirname(__FILE__) . '/salesReport.php';
		}
		exit;
		// $headers = ['Order #', 'Date', 'Customer Name', 'Price', 'Discount.', 'Paid', 'Status'];
		// $columns = ['id', 'order_date', 'full_name', 'price', 'discount','paid_amount', 'status'];
		// $sum = ['price', 'discount', 'paid_amount'];

		break;
	case '2':
		$orders = $ordersObj->ordersReportProductWise($shopId, $from, $to);
		include_once dirname(__FILE__) . '/salesReportProductWise.php';
		exit;
		break;

	case '3':
		$orders = $ordersObj->ordersReportDateWise($shopId, $from, $to);
		include_once dirname(__FILE__) . '/salesReportDateWise.php';
		exit;
		break;
	case '4':

		$orders = $ordersObj->returnReportProductWise($shopId, $from, $to);
		include_once dirname(__FILE__) . '/returnReportProductWise.php';
		exit;
		break;
	case '5':
		$inventoryReport = true;
		$orders = $ordersObj->inventoryReturnReport($shopId, $from, $to, 1);
		include_once dirname(__FILE__) . '/inventoryReturnReport.php';
		exit;
		break;
	case '6':
		$faultyReport = true;
		$orders = $ordersObj->inventoryReturnReport($shopId, $from, $to, 2);
		include_once dirname(__FILE__) . '/inventoryReturnReport.php';
		exit;
	case '7':
		$lahoreReport = true;
		$orders = $ordersObj->inventoryReturnReport($shopId, $from, $to, 3);
		include_once dirname(__FILE__) . '/inventoryReturnReport.php';
		exit;
	case '8':
		$expensesObj = new Expenses();
		$expenses = $expensesObj->getExpensesForReport($_POST['groupName'], $from, $to);
		include_once dirname(__FILE__) . '/expenseReport.php';
		exit;
	case '9':
		$expensesObj = new Expenses();
		$expenses = $expensesObj->getExpensesSummeryReport($_POST['groupName'], $from, $to);
		include_once dirname(__FILE__) . '/expenseSummeryReport.php';
		exit;
	case '10':
		include_once dirname(__FILE__) . '/accounting.php';
		exit;
		break;
	case '11':
		include_once dirname(__FILE__) . '/accounting.php';
		exit;
		break;
	case '12':
		include_once dirname(__FILE__) . '/accounting.php';
		exit;
		break;
	case '13':
		include_once dirname(__FILE__) . '/ledger.php';
		exit;
		break;
	case '14':
		include_once dirname(__FILE__) . '/online.php';
		exit;
		break;
	case '15':
		include_once dirname(__FILE__) . '/onlineSummery.php';
		exit;
		break;
	case '16':
		$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
		$search = $productObj->getOwnerProductsByPriority($ownerId, $shopId);
		$orders = $search;
		$stores = new Store();
		$selectShop = $stores->getStore($shopId);
		include_once dirname(__FILE__) . '/shop_products.php';
		exit;
	default:
		# code...
		break;
}

$totals = [];
?>