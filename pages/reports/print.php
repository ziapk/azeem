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

// print_r($_POST);exit;

switch ($reportType) {
	case '0':
		$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];
		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$search = $productObj->getOwnerProductsPagination($ownerId, ['page' => 1, 'perPage' => 100000, 'publisher_id' => $publisher_id, 'status' => [1], 'sortByField' =>'stock', 'sortByOrder' => 'DESC'], $shopId);
		$orders = $search['records'];
		$stores = new Store();
		$selectShop = $stores->getStore($shopId);
		include_once dirname(__FILE__) . '/shop_products.php';
		exit;
	case '1':
		$product_ids = [];
		if (!empty($_POST['product_id'])) {
			$product_ids[] = $_POST['product_id'];
		}
		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$account_id = !empty($_POST['account_id']) ? $_POST['account_id'] : "";
		if (!empty($product_ids) || !empty($publisher_id) || !empty($account_id)) {
			$orders = $ordersObj->ordersReport($shopId, $from, $to, $product_ids, $publisher_id, $account_id);
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
	case '17':
		$ordersObj = new Supply();
		$product_ids = [];
		if (!empty($_POST['product_id'])) {
			$product_ids[] = $_POST['product_id'];
		}
		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$account_id = !empty($_POST['account_id']) ? $_POST['account_id'] : "";

		$orders = $ordersObj->ordersReport($shopId, $from, $to, $product_ids, $publisher_id, $account_id);
		$reportTitle = "Purchase Orders";
		include_once dirname(__FILE__) . '/purchaseReport.php';

		exit;
		// $headers = ['Order #', 'Date', 'Customer Name', 'Price', 'Discount.', 'Paid', 'Status'];
		// $columns = ['id', 'order_date', 'full_name', 'price', 'discount','paid_amount', 'status'];
		// $sum = ['price', 'discount', 'paid_amount'];

		break;
	case '18':
		$ordersObj = new Supply();
		$product_ids = [];
		if (!empty($_POST['product_id'])) {
			$product_ids[] = $_POST['product_id'];
		}
		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$account_id = !empty($_POST['account_id']) ? $_POST['account_id'] : "";
		if (!empty($product_ids)) {
			$orders = $ordersObj->ordersReportProductWise($shopId, $from, $to, $product_ids, $publisher_id, $account_id);
			$reportTitle = "Purchase Orders (Product Wise)";
			include_once dirname(__FILE__) . '/purchaseProductsReport.php';
		} else {
			$orders = $ordersObj->ordersReportProductWise($shopId, $from, $to, $product_ids, $publisher_id, $account_id);
			$reportTitle = "Purchase Orders (Product Wise)";
			include_once dirname(__FILE__) . '/purchaseProductsReport.php';
		}
		exit;
	case '19':
		$product_ids = [];
		if (!empty($_POST['product_id'])) {
			$product_ids[] = $_POST['product_id'];
		}
		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$account_id = !empty($_POST['account_id']) ? $_POST['account_id'] : "";
		if (!empty($product_ids) || !empty($publisher_id) || !empty($account_id)) {
			$orders = $ordersObj->ordersReport($shopId, $from, $to, $product_ids, $publisher_id, $account_id, 'sample');
			include_once dirname(__FILE__) . '/salesProductsReport.php';
		} else {
			$orders = $ordersObj->ordersReport($shopId, $from, $to, $product_ids, $publisher_id, $account_id, 'sample');
			include_once dirname(__FILE__) . '/salesReport.php';
		}
		exit;

		break;
	case '20':
		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$product_ids = [];
		if (!empty($_POST['product_id'])) {
			$product_ids[] = $_POST['product_id'];
		}
		$orders = $ordersObj->ordersReportProductWise($shopId, $from, $to, $publisher_id, $product_ids, 'sample');
		include_once dirname(__FILE__) . '/salesReportProductWise.php';
		exit;
		break;
	case '2':
		$product_ids = [];
		if (!empty($_POST['product_id'])) {
			$product_ids[] = $_POST['product_id'];
		}
		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$orders = $ordersObj->ordersReportProductWise($shopId, $from, $to, $publisher_id, $product_ids);
		include_once dirname(__FILE__) . '/salesReportProductWise.php';
		exit;
		break;

	case '3':
		$product_ids = [];
		if (!empty($_POST['product_id'])) {
			$product_ids[] = $_POST['product_id'];
		}
		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$orders = $ordersObj->ordersReportDateWise($shopId, $from, $to, $publisher_id, $product_ids);
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
	case '21':

		$publisher_id = !empty($_POST['publisher_id']) ? $_POST['publisher_id'] : "";
		$product_ids = [];
		if (!empty($_POST['product_id'])) {
			$product_ids[] = $_POST['product_id'];
		}

		$reportTitle = "";
		if (!empty($publisher_id)) {
			$ownerId = $userData['role'] == 'owner' ? $userData['id'] : $userData['created_by'];

			$publisherObj = new Publishers();
			$publisher = $publisherObj->getPublisher($publisher_id, $ownerId);

			$reportTitle = "Publisher: " . $publisher['full_name'];
		}
		$orders = $ordersObj->stockAuditProductWise($shopId, $from, $to, $publisher_id, $product_ids);
		include_once dirname(__FILE__) . '/auditReportProductWise.php';
		exit;
		break;
	case '22':
	case '23':
		include_once dirname(__FILE__) . '/payments.php';
		exit;
		break;
	case '24':
		$rawIds = !empty($_POST['product_ids']) ? $_POST['product_ids'] : '';
		$product_ids = [];

		if (!empty($rawIds)) {
			$product_ids = array_values(
				array_filter(
					array_map('intval', explode(',', $rawIds))
				)
			);
		}

		// Also honour the single typeahead product_id if present
		if (!empty($_POST['product_id']) && !in_array((int)$_POST['product_id'], $product_ids)) {
			array_unshift($product_ids, (int)$_POST['product_id']);
		}

		$orders = $productObj->getProductAuditLedger($shopId, $from, $to, $product_ids);
		include_once dirname(__FILE__) . '/productAuditReport.php';
		exit;
	default:
		# code...
		break;
}

$totals = [];
?>