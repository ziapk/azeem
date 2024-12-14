<?php

class Supply extends Connection
{

    private $table = 'supply';
    private $table_pro = 'products';
    private $table_sub = 'supply_items';
    private $table_rs = 'supply_returns';
    private $table_transaction = 'supply_transaction';
    private $table_suppliers = 'suppliers';

    public function searchCustomer($shopId, $search)
    {
        $dbh = $this->connectionPool->getConnection();
        $stmt = "SELECT * FROM `{$this->table}`  WHERE shopId=:shopId AND (full_name LIKE '" . $search . "%' OR code LIKE '" . $search . "%' OR phoneNumber LIKE '" . $search . "%') LIMIT 10";
        $prepare = $dbh->prepare($stmt);
        $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
        //$prepare->bindParam(':search',$search,PDO::PARAM_STR);
        $prepare->execute();
        $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
        $this->connectionPool->releaseConnection($dbh);
        return $result;
    }

    public function addColumn()
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "ALTER TABLE `{$this->table}` ADD COLUMN IF NOT EXISTS `ref_no` varchar(20) NULL DEFAULT NULL AFTER `supply_date`";
            $prepare = $dbh->prepare($stmt);
            $prepare->execute();
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function prepareSupplyAgainstOrder($orderData, $array)
    {
        $customer = $orderData['customer'];
        $order = $orderData['order'];
        $orderItems = $orderData['order_items'];

        $products = new Products();
        $de = new DoubleEntry();
        $storeObj = new Store();
        $shopAccounts = new ShopAccounts();
        $supplierObj = new Suppliers();

        $storeDATA = $storeObj->getStore($customer['linked_shop']);
        $accountsData = $shopAccounts->getSAs($customer['linked_shop']);

        $storeAccounts = [];
        foreach ($accountsData as $a) {
            $storeAccounts[$a['key_value']] = $a['account_id'];
        }

        $supplyId = 0;

        $userInfo = UserInfo();
        $user = $userInfo['user'];

        $totals = ['discount' => 0, 'assetsValue' => 0, 'price' => 0];

        $supplyDetail = $this->getSupplyByRefId($order['id'], $customer['linked_shop']);
        if (!empty($supplyDetail['order'])) {

            $supplyId = $supplyDetail['order']['id'];
            $supplyCurrentStatus = $supplyDetail['order']['status'];

            if (in_array($supplyCurrentStatus, [2, 8, 9])) {

                foreach ($supplyDetail['order_items'] as $prod) {
                    $products->addProductQty($prod['product_id'], ['qty' => -1 * $prod['quantity'], 'pack_qty' => -1 * $prod['pack_qty'], 'owner_id' => $supplyDetail['order']['owner_id']], $supplyDetail['order']['shopId']);
                }
                // delete transactions
                $de->deleteTransactionBySupplyId($supplyDetail['order']['id']);
            }
        }

        $supplier = $supplierObj->getSupplierByLinkShop($order['shopId'], $customer['linked_shop']);

        $data = [
            'id' => $supplyId,
            'user_id' => $user['id'],
            'supplier_id' => $supplier['id'],
            'status' => $order['status'],
            'ref_no' => $order['id'],
            'show_bundle' => $order['show_bundle'],
            'description' => !empty($order['description']) ? $order['description'] : (!empty($order['summery']) ? $order['summery'] : ''),
            'price' => $order['price'],
            'payment_amount' => $order['paid_amount'],
            'payment_with_credit' => 0,
            'discount' => $order['discount'],
            'supplier_type' => 1,
            'shopId' => $customer['linked_shop'],
            'supply_date' => $storeDATA['sale_date']
        ];

        $totals['price'] = $order['price'];

        // {


        //     "id": "4928",
        //     "order_custom_id": "4924",
        //     "user_id": "2",
        //     "customer_id": "84",
        //     "customer_name": "PCC Quaid Campus ( Shahzad Azam )",
        //     "status": "2",
        //     "price": "2030",
        //     "paid_amount": "2030",
        //     "discount": "0",
        //     "gst": "0",
        //     "service_charges": "0",
        //     "shopId": "4",
        //     "order_date": "2023-10-01",
        //     "flag": "1",
        //     "recon": "0",
        //     "reason": null,
        //     "summery": "",
        //     "ref_no": "",
        //     "show_bundle": "0",
        //     "show_discount": "1",
        //     "created_at": "2023-12-27 13:08:55",
        //     "delivery_date": null,
        //     "expected_delivery_date": "2023-12-27",
        //     "complete_date": null,
        //     "status_id": null
        //   }
        $supply_id = $this->createSupply($data);

        $this->deleteSupplyDetails($supply_id);

        foreach ($orderItems as $item) {
            $d = [
                'supply_id' => $supply_id,
                'product_id' => $item['product_id'],
                'product_title' => $item['product_title'],
                'quantity' => $item['quantity'],
                'discount' => !empty($item['discount']) ? round(($item['discount'] / $item['price']) * 100, 2) : 0,
                'price' => $item['price'],
                'pprice' => $item['price'] - $item['discount'],
                'pack_size' => !empty($item['pack_size']) ? $item['pack_size'] : 0,
                'pack_qty' => !empty($item['pack_qty']) ? $item['pack_qty'] : 0,
                'unpack_qty' => !empty($item['unpack_qty']) ? $item['unpack_qty'] : 0,
            ];
            $totals['discount'] += $item['discount'];
            $this->createSupplyDetails($d);
            $products->assignProduct([
                "qty" => $item['quantity'],
                "stock_out" => 0,
                "product_id" => $item['product_id'],
                "shopId" => $customer['linked_shop'],
                "owner_id" => $storeDATA['owner_id'],
                "location" => "",
                "pack_size" => $d['pack_size'],
                "pack_qty" => $d['pack_qty'],
                "min_qty" => $d['unpack_qty'],
            ]);
        }

        $totals['discount'] += $order['discount'];
        $totals['assetsValue'] += $totals['price'];


        $cash = !empty($order['paid_amount']) ? $order['paid_amount'] : 0;
        if ($order['status'] != 1) {
            $account_id = $supplier['account_id'];
            $credit_amount = 0;

            $makeTransaction = [
                'description' => !empty($data['description']) ? $data['description'] : "Supply Invoice: " . $supply_id . " PLACED",
                'transaction_date' => $storeDATA['sale_date'],
                'reference' => $data['ref_no'],
                'transaction_type' => !empty($credit_amount) ? 'EXCHANGE' : 'PURCHASE',
                'shopId' => $storeDATA['id'],
                'created_by' => $_SESSION['user_credentials']['id'],
                'order_ref' => null,
                'supply_ref' => $supply_id,
            ];

            $makeTransactionId = $de->makeTransaction($makeTransaction);

            $assetPrice = $totals['assetsValue'];
            $purchaseDiscount = $totals['discount'];
            $payableAmount = $totals['price'] - $order['discount'];

            $defaultId = 0;

            foreach ($array['payment_with'] as $value) {
                if (!empty($value['is_default'])) {
                    $defaultId = $value['id'];
                }
            }

            // assets debit entry - debit
            // liability payable entry - credit
            // purchase discount entry - credit
            if (!empty($assetPrice)) {
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $storeAccounts['assets'],
                    'entry_type' => 'D',
                    'description' => !empty($data['description']) ? $data['description'] : '',
                    'amount' => $assetPrice, // 2000
                    'payment_mode' => $defaultId,
                    'user_id' => $user['id'],
                ];
                $a[] = $de->makeEntry($entry);
            }

            // payable credit entry
            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $account_id,
                'entry_type' => 'C',
                'description' => !empty($data['description']) ? $data['description'] : '',
                'amount' => $payableAmount,
                'payment_mode' => $defaultId,
                'user_id' => $user['id'],
            ];
            $a[] = $de->makeEntry($entry);

            if (!empty($purchaseDiscount)) {
                // saleDiscount credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $storeAccounts['purchase_discount'],
                    'entry_type' => 'C',
                    'description' => !empty($data['description']) ? $data['description'] : '',
                    'amount' => $purchaseDiscount, // 200 @ 10%
                    'payment_mode' => $defaultId,
                    'user_id' => $user['id'],
                ];
                $a[] = $de->makeEntry($entry);
            }


            if (!empty($cash)) {
                $makeTransactionId = $de->makeTransaction($makeTransaction);
                // payable credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $account_id,
                    'entry_type' => 'D',
                    'description' => '',
                    'amount' => $cash,
                    'payment_mode' => $defaultId,
                    'user_id' => $user['id'],
                ];
                $a[] = $de->makeEntry($entry);
                // cash credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $storeAccounts['cash'],
                    'entry_type' => 'C',
                    'description' => '',
                    'amount' => $cash, // 200 @ 10%
                    'payment_mode' => $defaultId,
                    'user_id' => $user['id'],
                ];
                $a[] = $de->makeEntry($entry);
            }
            // $newsletter = new Newsletter();
            // $dbh = $this->connectionPool->getConnection();
            // try {
            //     $send = $newsletter->send([
            //         'subject' => $makeTransaction['description'],
            //         'body' => $newsletter->drawSupply($supply_id),
            //         'sentTo' => [['email' => !empty($supplier['email']) ? $supplier['email'] : 'zia.pccr@yahoo.com', 'name' => !empty($_POST['supplierName']) ? $_POST['supplierName'] : $supplier['name']]],
            //         'ccEmails' => [['email' => $storeDATA['company_email'], 'name' => $storeDATA['full_name']]],
            //         'client' => $storeDATA['full_name'],
            //         'labels' => [$makeTransaction['transaction_type']]
            //     ]);
            // } catch (Exception $e) {
            //     print_r($e);
            // }
        }
    }

    public function ordersReportSummery($shopId, $date, $to, $publisher_id = null, $product_id = [], $account_id = null)
    {
        $dbh = $this->connectionPool->getConnection();
        try {

            $toCondition = " AND o.supply_date>='" . $date . "' AND o.supply_date<='" . $to . "'";


            if (!empty($account_id)) {
                $toCondition .= " AND (s.account_id=$account_id OR c.account_id=$account_id) ";
            }

            $join = "";
            if (!empty($publisher_id) || !empty($product_id)) {
                $join = " LEFT JOIN `{$this->table_sub}` AS oi ON oi.supply_id=o.id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id ";
            }
            if (!empty($publisher_id)) {
                $toCondition .= " and p.publisher_id = $publisher_id ";
            }
            if (!empty($product_id)) {
                $ids = implode(',', $product_id);
                $toCondition .= " and p.id IN( $ids ) ";
            }

            $stmt = "SELECT count(o.id) AS total, ROUND(SUM(o.price), 2) AS gross, ROUND(SUM(o.discount), 2) AS dist, ROUND(SUM(o.payment_amount), 2) AS paid, ROUND(SUM(o.`price` - o.`discount` - o.`payment_amount`), 2) AS balance FROM `{$this->table}` AS o LEFT JOIN customers AS c ON c.id = o.supplier_id and o.supplier_type = 2 LEFT JOIN `{$this->table_suppliers}` AS s ON s.id = o.supplier_id and o.supplier_type = 1 " . $join . " WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY o.id desc';
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function ordersReportProductWise($shopId, $date, $to, $publisher_id = null, $product_id = [], $account_id = null)
    {
        $dbh = $this->connectionPool->getConnection();
        try {

            $summery = $this->ordersReportSummery($shopId, $date, $to, $publisher_id, $product_id, $account_id);

            $toCondition = " AND o.supply_date>='" . $date . "' AND o.supply_date<='" . $to . "'";

            if (!empty($publisher_id)) {
                $toCondition .= " and p.publisher_id = $publisher_id ";
            }
            if (!empty($product_id)) {
                $ids = implode(',', $product_id);
                $toCondition .= " and p.id IN( $ids ) ";
            }

            if (!empty($account_id)) {
                $toCondition .= " AND (s.account_id=$account_id OR c.account_id=$account_id) ";
            }

            $stmt = "SELECT oi.product_id, sum(oi.quantity) AS quantity, round(sum((oi.pprice * oi.quantity) - (oi.pprice * oi.quantity * oi.discount / 100)), 2) AS pprice, round(sum((oi.price * oi.quantity) - (oi.price * oi.quantity * oi.discount / 100)), 2) AS price, round(sum(oi.pprice * oi.quantity * oi.discount / 100), 2) AS discount, p.full_name, c.full_name as customerName, s.name as supplierName  FROM `{$this->table}` AS o LEFT JOIN customers AS c ON c.id = o.supplier_id and o.supplier_type = 2 LEFT JOIN `{$this->table_suppliers}` AS s ON s.id = o.supplier_id and o.supplier_type = 1 LEFT JOIN `{$this->table_sub}` AS oi ON oi.supply_id=o.id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id  WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 GROUP BY oi.product_id ORDER BY p.full_name asc';
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return ['summery' => $summery, 'rows' => $result];
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function getProductPurchases($params = [])
        
        // $shopId, $date, $to, $publisher_id = null, $product_id = [])
    {
        $shopId = $params['shopId'];
        $product_id = $params['product_id'];
        $dbh = $this->connectionPool->getConnection();
        try {
            $toCondition = "";
            if (!empty($product_id)) {
                $toCondition .= " and p.id IN( $product_id ) ";
            }
            $stmt = "SELECT o.id, oi.product_id, o.supply_date, oi.price AS price, oi.pprice, oi.discount, oi.quantity AS quantity, p.full_name, c.full_name as customerName, s.name as supplierName  FROM `{$this->table}` AS o LEFT JOIN `{$this->table_sub}` AS oi ON oi.supply_id=o.id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id LEFT JOIN customers AS c ON c.id = o.supplier_id and o.supplier_type = 2 LEFT JOIN `{$this->table_suppliers}` AS s ON s.id = o.supplier_id and o.supplier_type = 1  WHERE o.shopId=:shopId " . $toCondition . " and o.flag = 1 ORDER BY oi.quantity desc";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }


    public function ordersReport($shopId, $date, $to, $ids = [], $publisher_id = null, $account_id = null)
    {
        $dbh = $this->connectionPool->getConnection();
        try {

            $toCondition = " AND o.supply_date>='" . $date . "' AND o.supply_date<='" . $to . "' ";

            if (!empty($account_id) || !empty($publisher_id) || !empty($ids)) {

                if (!empty($ids)) {
                    $toCondition .= " AND sub.product_id IN (" . implode(',', $ids) . ") ";
                }
                if (!empty($publisher_id)) {
                    $toCondition .= " AND p.publisher_id=$publisher_id ";
                }
                if (!empty($account_id)) {
                    $toCondition .= " AND (s.account_id=$account_id OR c.account_id=$account_id) ";
                }

                $stmt = "SELECT sub.*, sub.quantity AS quantity, round((sub.pprice * sub.quantity) - (sub.pprice * sub.quantity * sub.discount / 100), 2) AS pprice, round((sub.price * sub.quantity) - (sub.price * sub.quantity * sub.discount / 100)), 2) AS price, round((sub.pprice * sub.quantity * sub.discount / 100), 2) AS discount, sub.supply_id as order_custom_id, o.supply_date as order_date, p.full_name as productName, c.full_name, s.name FROM `{$this->table_sub}` AS sub left join `{$this->table_pro}` as p on p.id = sub.product_id left join `{$this->table}` as o on sub.supply_id = o.id LEFT JOIN customers AS c ON c.id = o.supplier_id and o.supplier_type = 2 LEFT JOIN `{$this->table_suppliers}` AS s ON s.id = o.supplier_id and o.supplier_type = 1 WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY o.supply_date asc, sub.quantity desc';
            } else {
                $stmt = "SELECT sub.*, sub.quantity AS quantity, round((sub.pprice * sub.quantity) - (sub.pprice * sub.quantity * sub.discount / 100), 2) AS pprice, round((sub.price * sub.quantity) - (sub.price * sub.quantity * sub.discount / 100)), 2) AS price, round((sub.pprice * sub.quantity * sub.discount / 100), 2) AS discount, sub.supply_id as order_custom_id, o.supply_date as order_date, p.full_name as productName, c.full_name, s.name FROM `{$this->table_sub}` AS sub left join `{$this->table_pro}` as p on p.id = sub.product_id left join `{$this->table}` as o on sub.supply_id = o.id LEFT JOIN customers AS c ON c.id = o.supplier_id and o.supplier_type = 2 LEFT JOIN `{$this->table_suppliers}` AS s ON s.id = o.supplier_id and o.supplier_type = 1 WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY o.supply_date asc, sub.quantity desc';
            }

            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function createSupply($array)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            if (!empty($array['id'])) {
                // $this->addColumn();
                $stmt = "UPDATE `{$this->table}` SET `supplier_id`=:supplier_id, supplier_type=:supplier_type, `status`=:status, `price`=:price, `payment_amount`=:payment_amount, `payment_with_credit`=:payment_with_credit, `discount`=:discount, `supply_date`=:supply_date, `ref_no`=:ref_no, `show_bundle`=:show_bundle, `description`=:description WHERE id=:id";
                $prepare = $dbh->prepare($stmt);
                $prepare->bindParam(':supplier_id', $array['supplier_id'], PDO::PARAM_STR);
                $prepare->bindParam(':supplier_type', $array['supplier_type'], PDO::PARAM_STR);
                $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
                $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
                $prepare->bindParam(':payment_amount', $array['payment_amount'], PDO::PARAM_STR);
                $prepare->bindParam(':payment_with_credit', $array['payment_with_credit'], PDO::PARAM_STR);
                $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
                $prepare->bindParam(':supply_date', $array['supply_date'], PDO::PARAM_STR);
                $prepare->bindParam(':ref_no', $array['ref_no'], PDO::PARAM_STR);
                $prepare->bindParam(':show_bundle', $array['show_bundle'], PDO::PARAM_STR);
                $prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
                $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
                $prepare->execute();
                return $array['id'];
            } else {
                // $this->addColumn();
                $stmt = "INSERT INTO `{$this->table}` (`user_id`, `supplier_id`, `supplier_type`, `status`, `price`, `payment_amount`, `payment_with_credit`, `discount`, `shopId`, `supply_date`, `ref_no`, `show_bundle`, `description`) VALUES (:user_id, :supplier_id, :supplier_type, :status, :price, :payment_amount, :payment_with_credit, :discount, :shopId, :supply_date, :ref_no, :show_bundle, :description)";
                $prepare = $dbh->prepare($stmt);
                $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
                $prepare->bindParam(':supplier_id', $array['supplier_id'], PDO::PARAM_STR);
                $prepare->bindParam(':supplier_type', $array['supplier_type'], PDO::PARAM_STR);
                $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
                $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
                $prepare->bindParam(':payment_amount', $array['payment_amount'], PDO::PARAM_STR);
                $prepare->bindParam(':payment_with_credit', $array['payment_with_credit'], PDO::PARAM_STR);
                $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
                $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
                $prepare->bindParam(':supply_date', $array['supply_date'], PDO::PARAM_STR);
                $prepare->bindParam(':ref_no', $array['ref_no'], PDO::PARAM_STR);
                $prepare->bindParam(':show_bundle', $array['show_bundle'], PDO::PARAM_STR);
                $prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
                $prepare->execute();
                $result = $dbh->lastInsertId();
                return $result;
            }
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function createSupplyDetails($array)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "INSERT INTO `{$this->table_sub}` (`supply_id`, `product_id`, `product_title`, `quantity`, `price`, `pprice`, `discount`, `pack_size`, `pack_qty`, `unpack_qty`) VALUES (:supply_id, :product_id, :product_title, :quantity, :price, :pprice, :discount, :pack_size, :pack_qty, :unpack_qty)";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':supply_id', $array['supply_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_title', $array['product_title'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':pprice', $array['pprice'], PDO::PARAM_STR);
            $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
            $prepare->bindParam(':pack_size', $array['pack_size'], PDO::PARAM_STR);
            $prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
            $prepare->bindParam(':unpack_qty', $array['unpack_qty'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }
    public function deleteSupplyDetails($supply_id)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "DELETE FROM `{$this->table_sub}` where supply_id = :supply_id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':supply_id', $supply_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function makeTransaction($array)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "INSERT INTO `{$this->table_transaction}` (`user_id`, `supplier_id`, `amount`, `payment_date`, `supply_id`, `transaction_type`, `shopId`) VALUES (:user_id, :supplier_id, :amount, :payment_date, :supply_id, :transaction_type, :shopId)";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
            $prepare->bindParam(':supplier_id', $array['supplier_id'], PDO::PARAM_STR);
            $prepare->bindParam(':amount', $array['amount'], PDO::PARAM_STR);
            $prepare->bindParam(':payment_date', $array['payment_date'], PDO::PARAM_STR);
            $prepare->bindParam(':supply_id', $array['supply_id'], PDO::PARAM_STR);
            $prepare->bindParam(':transaction_type', $array['transaction_type'], PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function getOrder($id)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "SELECT * FROM `{$this->table}` WHERE id=:id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result['order'] = $prepare->fetch(PDO::FETCH_ASSOC);
            if ($result['order']['supplier_type'] == 2) {
                $c = new Customers();
                $result['customer'] = $c->getCustomer($result['order']['supplier_id']);
            } else {
                $c = new Suppliers();
                $result['supplier'] = $c->getSupplier($result['order']['supplier_id']);
            }
            if (!empty($result['order'])) {
                $stmt = "SELECT item.*, p.full_name, p.code, p.barcode, p.id, p.product_type FROM `{$this->table_sub}` as item LEFT JOIN products as p ON item.product_id = p.id WHERE item.supply_id=:id";
                $prepare = $dbh->prepare($stmt);
                $prepare->bindParam(':id', $id, PDO::PARAM_STR);
                $prepare->execute();
                $result['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }
    public function getSupplyByRefId($id, $shopId)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "SELECT * FROM `{$this->table}` WHERE ref_no=:id and shopId=:shopId";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result['order'] = $prepare->fetch(PDO::FETCH_ASSOC);
            if ($result['order']['supplier_type'] == 2) {
                $c = new Customers();
                $result['customer'] = $c->getCustomer($result['order']['supplier_id']);
            } else {
                $c = new Suppliers();
                $result['supplier'] = $c->getSupplier($result['order']['supplier_id']);
            }
            if (!empty($result['order'])) {
                $stmt = "SELECT item.*, p.full_name, p.code, p.barcode, p.id, p.product_type FROM `{$this->table_sub}` as item LEFT JOIN products as p ON item.product_id = p.id WHERE item.supply_id=:id";
                $prepare = $dbh->prepare($stmt);
                $prepare->bindParam(':id', $result['order']['id'], PDO::PARAM_STR);
                $prepare->execute();
                $result['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function getOrders($id)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "SELECT * FROM `{$this->table}` WHERE supplier_id=:id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result['orders'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result['orders'] as $key => $order) {
                $stmt = "SELECT item.*, p.full_name FROM `{$this->table_sub}` as item LEFT JOIN products as p ON item.product_id = p.id WHERE item.supply_id=:id";
                $prepare = $dbh->prepare($stmt);
                $prepare->bindParam(':id', $order['id'], PDO::PARAM_STR);
                $prepare->execute();
                $result['orders'][$key]['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
                $result['supplier'] = $this->getSupplierById($id);
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function getSupplierById($id)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "SELECT * FROM `{$this->table_suppliers}` WHERE id=:id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function userOrders($shopId, $params)
    {
        $dbh = $this->connectionPool->getConnection();
        try {

            $toCondition = "";
            if (!empty($params['to'])) {
                $toCondition .= " AND DATE(o.supply_date) BETWEEN '" . $params['from'] . "' AND '" . $params['to'] . "'";
            } else if (!empty($date)) {
                $toCondition .= " AND DATE(o.supply_date) BETWEEN '" . $params['from'] . "' AND '" . $params['from'] . "'";
            }

            if (!empty($params['orderId'])) {
                $toCondition = " AND o.id='" . $params['orderId'] . "' ";
            }

            if ($params['orderType'] == 'cash') {
                $toCondition .= " AND o.payment_amount > 0 and o.status = 2 ";
            }
            if ($params['orderType'] == 'credit') {
                $toCondition .= " AND ((o.price > 0 and o.price != o.discount and o.payment_amount = 0 AND o.status != 1) OR o.status = 9) ";
            }
            if ($params['orderType'] == 'park') { // all parked
                $toCondition = " ";
                $flagCondition = " AND o.status = 1 ";
            }

            $stmt = "SELECT o.*, c.full_name as c_full_name, s.name as s_full_name FROM `{$this->table}` AS o LEFT JOIN customers AS c ON o.supplier_type = 2 and c.id = o.supplier_id LEFT JOIN suppliers AS s ON o.supplier_type = 1 and s.id = o.supplier_id WHERE o.shopId=:shopId " . $toCondition . ' ' . $flagCondition . ' and o.flag = 1 ORDER BY id desc';
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function manageWallet($array)
    {

        $supplier = $this->getSupplierById($array['id']);
        $amount = $supplier['wallet'] + $array['wallet'];
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "UPDATE `{$this->table_suppliers}` SET `wallet`=:wallet WHERE id=:id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':wallet', $amount, PDO::PARAM_INT);
            $prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }





    public function changeOrderFlag($array)
    {
        $id = $array['id'];
        $reason = $array['reason'];
        $flag = $array['flag'];
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "UPDATE `{$this->table}` SET `reason`=:reason, `flag`=:flag WHERE id=:id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':reason', $reason, PDO::PARAM_STR);
            $prepare->bindParam(':flag', $flag, PDO::PARAM_STR);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function getSupplierTransactions($params)
    {
        $dbh = $this->connectionPool->getConnection();
        try {

            $stmt = "SELECT COUNT(id) as total FROM `{$this->table_transaction}` WHERE supplier_id = :id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':id', $params['id'], PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);

            $no_of_records_per_page = 10;
            $total_rows = $result['total'];
            $total_pages = ceil($total_rows / $no_of_records_per_page);
            $currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
            $offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
            $search = "(id LIKE '%" . $params["search"] . "%' OR amount LIKE '%" . $params["search"] . "%' OR payment_date LIKE '%" . $params["search"] . "%' ) ";
            $stmt = "SELECT * FROM `{$this->table_transaction}` WHERE supplier_id=:id AND $search LIMIT :offset, :perPage";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
            $prepare->bindParam(':id', $params['id'], PDO::PARAM_INT);
            $prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function getSupplyItemsByProductIds($productIds, $supplier_id)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $products = [];
            $price = [];
            foreach ($productIds as $value) {
                $arr = explode('_', $value);
                $products[] = $arr[0];
                $price[] = $arr[1];
            }
            $stmt = "SELECT sp.supplier_id, s.* FROM `{$this->table_sub}` as s left join `{$this->table}` as sp on sp.id = s.supply_id where s.product_id IN (" . implode(', ', $products) . ") and s.price IN (" . implode(', ', $price) . ") and sp.supplier_id=:supplier_id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':supplier_id', $supplier_id, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function orderReturnAll($array, $action, $type = 1)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "INSERT INTO `{$this->table_rs}` (`user_id`, `shopId`, `supply_id`, `product_id`, `quantity`, `price`, `type`) VALUES (:user_id, :shopId, :supply_id, :product_id, :quantity, :price, :type)";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->bindParam(':supply_id', $array['supply_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':type', $array['type'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $dbh->lastInsertId();
            $products = new Products();
            $array['quantity'] = (-1 * $array['quantity']);
            $array['pack_qty'] = (-1 * $array['pack_qty']);
            $products->addProductQty($array['product_id'], ['qty' => $array['quantity'], 'pack_size' => $array['pack_size'], 'pack_qty' => $array['pack_qty']], $array['shopId'], $type);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }
}
