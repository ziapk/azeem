<?php

class Orders extends Connection
{

    private $table = 'orders';
    private $table_store = 'store';
    private $table_sub = 'order_items';
    private $table_services = 'services';
    private $table_oservice = 'order_services';
    private $table_pro = 'products';
    private $table_rp = 'product_returns';
    private $table_transaction = 'transaction';
    private $table_customers = 'customers';
    private $table_ro = 'return_orders';

    public function searchCustomer($shopId, $search)
    {
        $stmt = "SELECT * FROM `{$this->table}`  WHERE shopId=:shopId AND (full_name LIKE '" . $search . "%' OR code LIKE '" . $search . "%' OR phoneNumber LIKE '" . $search . "%') LIMIT 10";
        $prepare = $this->dbh->prepare($stmt);
        $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
        //$prepare->bindParam(':search',$search,PDO::PARAM_STR);
        $prepare->execute();
        $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    public function getNextId($shopId)
    {
        $stmt = "SELECT last_bill_no from `{$this->table_store}` where id=:shopId";
        $prepare = $this->dbh->prepare($stmt);
        $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
        $prepare->execute();
        $result = $prepare->fetch(PDO::FETCH_ASSOC);
        return $result['last_bill_no'] + 1;
    }
    public function setNextId($shopId, $id)
    {
        $stmt = "UPDATE `{$this->table_store}` SET last_bill_no = :last_bill_no where id=:shopId";
        $prepare = $this->dbh->prepare($stmt);
        $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
        $prepare->bindParam(':last_bill_no', $id, PDO::PARAM_STR);
        $prepare->execute();
    }

    public function updateOrderAdjustment($array)
    {
        try {
            $stmt = "UPDATE `{$this->table}` SET `paid_amount`=paid_amount+:amount, status=:status WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':amount', $array['amount'], PDO::PARAM_STR);
            $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
            $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }



    public function prepareOrder($array)
    {
        $customersObj = new Customers();
        $customer = $customersObj->getCustomer($array['customerId']);

        if (!empty($customer)) {

            $storeObj = new Store();
            $shopAccounts = new ShopAccounts();
            $doubleEntry = new DoubleEntry();
            $userObj = UserInfo();
            $user = $userObj['user'];
            $shop = $userObj['shop'];

            $status = 9;
            $totalDiscount = 0;
            $additionalDiscount = 0;
            $parked = false;

            $storeDATA = $storeObj->getStore($shop['id']);

            if ($array['status'] == 1) {
                $status = 1; // parked
                $parked = true;
            } else if (!empty($array['payment_amount'])) {
                $gst = round($array['subTotal'] * ($array['gst'] / 100));
                $service_charges = round($array['subTotal'] * ($array['service_charges'] / 100));
                $status = 8;
                if ((($array['subTotal'] + $gst + $service_charges) - $array['discount'] - $array['payment_amount']) == 0) {
                    $status = 2;
                }
            }

            $data = [
                'user_id' => $user['id'],
                'customer_name' => $array['customer_name'],
                'customer_id' => !empty($customer['id']) ? $customer['id'] : 0,
                'status' => $status,
                'price' => $array['subTotal'],
                'paid_amount' => $array['payment_amount'],
                'show_discount' => !empty($array['show_discount']) ? 1 : 0,
                'discount' => $array['discount'],
                'show_bundle' => !empty($array['show_bundle']) ? 1 : 0,
                'gst' => $array['gst'],
                'service_charges' => !empty($array['service_charges']) ? $array['service_charges'] : 0,
                'shopId' => !empty($array['shopId']) ? $array['shopId'] : $shop['id'],
                'order_date' => $storeDATA['sale_date'],
                'summery' => $array['summery'],
                'ref_no' => $array['ref_no'],
                'id' => $array['id'],
                'linked_shop' => $customer['linked_shop'],
                'status_id' => !empty($array['status_id']) ? $array['status_id'] : null,
                'expected_delivery_date' => !empty($array['expected_delivery_date']) ? $array['expected_delivery_date'] : null,
            ];

            $additionalDiscount += $array['discount'];
            $accountsData = $shopAccounts->getSAs($shop['id']);
            $storeAccounts = [];
            foreach ($accountsData as $a) {
                $storeAccounts[$a['key_value']] = $a['account_id'];
            }

            if (!empty($array['id'])) {

                $orderDetail = $this->getOrder($array['id']);
                $currentStatus = $orderDetail['order']['status'];

                if (in_array($currentStatus, [2, 8, 9])) {

                    // rollback products first
                    $products = new Products();
                    foreach ($orderDetail['order_items'] as $prod) {
                        $products->subProductQty(['product_id' => $prod['product_id'], 'quantity' => -1 * $prod['quantity'], 'pack_qty' => $prod['pack_qty'], 'owner_id' => $user['role'] == 'owner' ? $user['id'] : $user['created_by'], 'shopId' => $orderDetail['order']['shopId']]);
                        if (!empty($customer['linked_shop'])) {
                            $products->addProductQty($prod['product_id'], ['qty' => -1 * $prod['quantity'], 'pack_qty' => $prod['pack_qty']], $customer['linked_shop']);
                        }
                    }

                    // delete transactions
                    $doubleEntry->deleteTransactionByOrderId($orderDetail['order']['id']);
                }
            }
            $order_id = $this->createOrder($data);

            if ($status == 1 && !empty($array['id'])) { // when edit a parked entry
                $order_id = $array['id'];
            }

            if ($order_id) {
                $items = [];
                if (sizeof($array['items'])) {
                    $this->deleteOrderItems($order_id);
                    $this->deleteOrderServices($order_id);

                    $c = [];
                    foreach ($array['items'] as $item) {
                        $discount = !empty($item['discount']) ? $item['discount'] : 0;

                        $d = [
                            'shopId' => $shop['id'],
                            'owner_id' => $user['role'] == 'owner' ? $user['id'] : $user['created_by'],
                            'product_id' => $item['id'],
                            'order_id' => $order_id,
                            'description' => $item['description'],
                            'quantity' => $item['qty'] + (!empty($item['unpack_qty']) ? $item['unpack_qty'] : 0),
                            'pack_size' => !empty($item['pack_size']) ? $item['pack_size'] : 0,
                            'pack_qty' => !empty($item['pack_qty']) ? $item['pack_qty'] : 0,
                            'unpack_qty' => !empty($item['unpack_qty']) ? $item['unpack_qty'] : 0,
                            'discount' => $discount,
                            'discount_type' => !empty($item['discount_type']) ? $item['discount_type'] : 1,
                            'price' => $item['price'],
                            'services' => $item['services'],
                            'raw_items' => $item['raw_items'],
                            'status' => $array['status'],
                            'item_status' => !empty($item['item_status']) ? $item['item_status'] : 1,
                            'employee_id' => !empty($item['employee_id']) ? $item['employee_id'] : null,
                            'start_date' => !empty($item['start_date']) ? $item['start_date'] : null,
                            'end_date' => !empty($item['end_date']) ? $item['end_date'] : null,
                            'priority' => !empty($item['priority']) ? $item['priority'] : 1,
                        ];

                        $totalDiscount += $item['discount'];
                        $c[] = $this->createOrderDetails($d, $customer);
                    }
                }

                if ($status != 1) { // if not in park state


                    $orderDetail = $this->getOrder($order_id);
                    $assetPrice = $data['price'] + $totalDiscount;
                    $cash = $data['paid_amount'];
                    $saleDiscount = $totalDiscount + $additionalDiscount;
                    $receivable = ($assetPrice - $saleDiscount) + $gst + $service_charges;
                    $defaultId = 0;

                    foreach ($array['payment_with'] as $value) {
                        if (!empty($value['is_default'])) {
                            $defaultId = $value['id'];
                        }
                    }

                    $makeTransaction = [
                        'description' => !empty($array['summery']) ? $array['summery'] : "ORDER ID: " . $orderDetail['order']['order_custom_id'] . " PLACED",
                        'transaction_date' => $storeDATA['sale_date'],
                        'reference' => !empty($array['ref_no']) ? $array['ref_no'] : '',
                        'transaction_type' => 'SALE',
                        'shopId' => $shop['id'],
                        'created_by' => $_SESSION['user_credentials']['id'],
                        'order_ref' => $order_id,
                        'supply_ref' => null,
                    ];

                    $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

                    // assets debit entry - credit
                    // assets receivable entry - debit
                    // expense discount entry - debit

                    // receivable credit entry
                    $entry = [
                        'transaction_id' => $makeTransactionId,
                        'account_id' => $customer['account_id'],
                        'entry_type' => 'D',
                        'description' => '',
                        'amount' => $receivable,
                        'payment_mode' => $defaultId,
                        'user_id' => $_SESSION['user_credentials']['id'],
                    ];
                    $a[] = $doubleEntry->makeEntry($entry);

                    if (!empty($saleDiscount)) {
                        // saleDiscount credit entry
                        $entry = [
                            'transaction_id' => $makeTransactionId,
                            'account_id' => $storeAccounts['sale_discount'],
                            'entry_type' => 'D',
                            'description' => '',
                            'amount' => $saleDiscount, // 200 @ 10%
                            'payment_mode' => $defaultId,
                            'user_id' => $_SESSION['user_credentials']['id'],
                        ];
                        $a[] = $doubleEntry->makeEntry($entry);
                    }

                    if (!empty($gst) && !empty($storeAccounts['gst'])) {
                        // saleDiscount credit entry
                        $entry = [
                            'transaction_id' => $makeTransactionId,
                            'account_id' => $storeAccounts['gst'],
                            'entry_type' => 'C',
                            'description' => '',
                            'amount' => $gst, // 200 @ 10%
                            'payment_mode' => $defaultId,
                            'user_id' => $_SESSION['user_credentials']['id'],
                        ];
                        $a[] = $doubleEntry->makeEntry($entry);
                    } else {
                        $assetPrice += $gst;
                    }

                    if (!empty($service_charges) && $storeAccounts['service_charges']) {
                        // saleDiscount credit entry
                        $entry = [
                            'transaction_id' => $makeTransactionId,
                            'account_id' => $storeAccounts['service_charges'],
                            'entry_type' => 'C',
                            'description' => '',
                            'amount' => $service_charges, // 200 @ 10%
                            'payment_mode' => $defaultId,
                            'user_id' => $_SESSION['user_credentials']['id'],
                        ];
                        $a[] = $doubleEntry->makeEntry($entry);
                    } else {
                        $assetPrice += $service_charges;
                    }

                    $entry = [
                        'transaction_id' => $makeTransactionId,
                        'account_id' => $storeAccounts['assets'],
                        'entry_type' => 'C',
                        'description' => '',
                        'amount' => $assetPrice, // 2000
                        'payment_mode' => $defaultId,
                        'user_id' => $_SESSION['user_credentials']['id'],
                    ];

                    $a[] = $doubleEntry->makeEntry($entry);

                    if (!empty($cash)) {
                        $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);
                        foreach ($array['payment_with'] as $value) {
                            if (!empty($value['amount'])) {
                                // cash credit entry
                                $entry = [
                                    'transaction_id' => $makeTransactionId,
                                    'account_id' => $storeAccounts['cash'],
                                    'entry_type' => 'D',
                                    'description' => '',
                                    'amount' => $value['amount'], // 200 @ 10%
                                    'payment_mode' => $value['id'],
                                    'user_id' => $_SESSION['user_credentials']['id'],
                                ];
                                $a[] = $doubleEntry->makeEntry($entry);

                                // receivable credit entry
                                $entry = [
                                    'transaction_id' => $makeTransactionId,
                                    'account_id' => $customer['account_id'],
                                    'entry_type' => 'C',
                                    'description' => '',
                                    'amount' => $value['amount'],
                                    'payment_mode' => $value['id'],
                                    'user_id' => $_SESSION['user_credentials']['id'],
                                ];
                                $a[] = $doubleEntry->makeEntry($entry);
                            }
                        }
                    }
                    $newsletter = new Newsletter();
                    $send = $newsletter->send([
                        'subject' => "Order.#" . $orderDetail['order']['order_custom_id'] . " has been generated",
                        'body' => $newsletter->drawInvoice($order_id),
                        'sentTo' => [['email' => !empty($customer['email']) ? $customer['email'] : 'zia.pccr@yahoo.com', 'name' => $array['customer_name']]],
                        'ccEmails' => [['email' => $shop['company_email'], 'name' => $shop['full_name']]],
                        'client' => $shop['full_name'],
                        'labels' => [$makeTransaction['transaction_type']]
                    ]);
                }

                return ['status' => 200, 'send' => $send, 'message' => 'successfully done', 'order' => ['id' => $order_id]];
            }
        } else {
            return ['status' => 400, 'message' => 'Please select a customer'];
        }
    }
    public function createOrder($array)
    {
        try {
            if (!empty($array['id'])) {
                $stmt = "UPDATE `{$this->table}` SET `user_id`=:user_id, `customer_id`=:customer_id, `customer_name`=:customer_name, `status`=:status, `price`=:price, `paid_amount`=:paid_amount, `discount`=:discount, `shopId`=:shopId, `linked_shop`=:linked_shop, `order_date`=:order_date, `gst`=:gst, `service_charges`=:service_charges, `summery`=:summery, `ref_no`=:ref_no, `show_discount`=:show_discount, `show_bundle`=:show_bundle, status_id=:status_id, expected_delivery_date=:expected_delivery_date WHERE id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_name', $array['customer_name'], PDO::PARAM_STR);
                $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
                $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
                $prepare->bindParam(':paid_amount', $array['paid_amount'], PDO::PARAM_STR);
                $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
                $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
                $prepare->bindParam(':linked_shop', $array['linked_shop'], PDO::PARAM_STR);
                $prepare->bindParam(':order_date', $array['order_date'], PDO::PARAM_STR);
                $prepare->bindParam(':gst', $array['gst'], PDO::PARAM_STR);
                $prepare->bindParam(':service_charges', $array['service_charges'], PDO::PARAM_STR);
                $prepare->bindParam(':summery', $array['summery'], PDO::PARAM_STR);
                $prepare->bindParam(':ref_no', $array['ref_no'], PDO::PARAM_STR);
                $prepare->bindParam(':show_discount', $array['show_discount'], PDO::PARAM_STR);
                $prepare->bindParam(':show_bundle', $array['show_bundle'], PDO::PARAM_STR);
                $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
                $prepare->bindParam(':status_id', $array['status_id'], PDO::PARAM_STR);
                $prepare->bindParam(':expected_delivery_date', $array['expected_delivery_date'], PDO::PARAM_STR);
                $prepare->execute();
                $result = $prepare->rowCount();
                return $array['id'];
            } else {
                $id = $this->getNextId($array['shopId']);
                $stmt = "INSERT INTO `{$this->table}` (`order_custom_id`,`user_id`, `customer_id`, `customer_name`, `status`, `price`, `paid_amount`, `discount`, `shopId`, `linked_shop`, `order_date`, `gst`, `service_charges`, `summery`, `ref_no`, `show_discount`, `show_bundle`, `status_id`, `expected_delivery_date`) VALUES (:order_custom_id, :user_id, :customer_id, :customer_name, :status, :price, :paid_amount, :discount, :shopId, :linked_shop, :order_date, :gst, :service_charges, :summery, :ref_no, :show_discount, :show_bundle, :status_id, :expected_delivery_date)";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':order_custom_id', $id, PDO::PARAM_STR);
                $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_name', $array['customer_name'], PDO::PARAM_STR);
                $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
                $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
                $prepare->bindParam(':paid_amount', $array['paid_amount'], PDO::PARAM_STR);
                $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
                $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
                $prepare->bindParam(':linked_shop', $array['linked_shop'], PDO::PARAM_STR);
                $prepare->bindParam(':order_date', $array['order_date'], PDO::PARAM_STR);
                $prepare->bindParam(':gst', $array['gst'], PDO::PARAM_STR);
                $prepare->bindParam(':service_charges', $array['service_charges'], PDO::PARAM_STR);
                $prepare->bindParam(':summery', $array['summery'], PDO::PARAM_STR);
                $prepare->bindParam(':ref_no', $array['ref_no'], PDO::PARAM_STR);
                $prepare->bindParam(':show_discount', $array['show_discount'], PDO::PARAM_STR);
                $prepare->bindParam(':show_bundle', $array['show_bundle'], PDO::PARAM_STR);
                $prepare->bindParam(':status_id', $array['status_id'], PDO::PARAM_STR);
                $prepare->bindParam(':expected_delivery_date', $array['expected_delivery_date'], PDO::PARAM_STR);
                $prepare->execute();
                $result = $this->dbh->lastInsertId();
                if (!empty($result)) {
                    $this->setNextId($array['shopId'], $id);
                }
                return $result;
            }
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function orderReturnAll($array, $reverse = false, $ownerShopId, $flag = 1)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_rp}` (`user_id`, `shopId`, `order_id`, `product_id`, `quantity`, `price`, `discount`, `discount_type`, `discount_value`, `type`, `pack_size`, `pack_qty`, `unpack_qty` ) VALUES (:user_id, :shopId, :order_id, :product_id, :quantity, :price, :discount, :discount_type, :discount_value, :type, :pack_size, :pack_qty, :unpack_qty)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->bindParam(':order_id', $array['order_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
            $prepare->bindParam(':discount_type', $array['discount_type'], PDO::PARAM_STR);
            $prepare->bindParam(':discount_value', $array['discount_value'], PDO::PARAM_STR);
            $prepare->bindParam(':type', $array['type'], PDO::PARAM_STR);
            $prepare->bindParam(':pack_size', $array['pack_size'], PDO::PARAM_STR);
            $prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
            $prepare->bindParam(':unpack_qty', $array['unpack_qty'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            if ($flag == 2) {
                $products = new Products();
                $qty = -1 * $array['quantity'];
                $pack_qty = -1 * $array['pack_qty'];
                if ($reverse) {
                    $products->addProductQty($array['product_id'], ['qty' => $qty, 'pack_qty' => $pack_qty, 'pack_size' => $array['pack_size']], $array['shopId']);
                } else {
                    if (!empty($ownerShopId)) {
                        $products->addProductQty($array['product_id'], ['qty' => $array['quantity'], 'pack_qty' => $array['pack_qty']], $ownerShopId);
                        $products->subProductQty(['product_id' => $array['product_id'], 'quantity' => $array['quantity'], 'pack_qty' => $array['pack_qty'], 'owner_id' => $array['owner_id'], 'shopId' => $array['shopId']]);
                    } else {
                        $products->subProductQty(['product_id' => $array['product_id'], 'quantity' => $qty, 'pack_qty' => $pack_qty, 'owner_id' => $array['owner_id'], 'shopId' => $array['shopId']]);
                    }
                }
            }

            // $this->orderReturn($array['order_id'], ($action + 4));
            // if(!empty($delete)) {
            //     $this->deleteOrderItem($array['order_id'], $array['product_id']);
            // }
            // else {
            //     $this->updateOrderItem($array['order_id'], $array['product_id'], $array['quantity']);
            // }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function addColumn($columnName, $after, $table)
    {
        try {
            $stmt = "ALTER TABLE `{$table}` ADD COLUMN IF NOT EXISTS `{$columnName}` int(11) NULL DEFAULT NULL AFTER `{$after}`";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->execute();
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function deleteOrderItem($order_id, $product_id)
    {
        try {
            $stmt = "DELETE FROM `{$this->table_sub}` where `order_id` = :order_id and `product_id` = :product_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':order_id', $order_id, PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function deleteReturnOrderItem($id)
    {
        try {
            $stmt = "DELETE FROM `{$this->table_rp}` where `order_id` = :order_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':order_id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function updateOrderItem($order_id, $product_id, $qty)
    {
        try {
            $stmt = "UPDATE `{$this->table_sub}` SET quantity=quantity-:qty where `order_id` = :order_id and `product_id` = :product_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $prepare->bindParam(':qty', $qty, PDO::PARAM_STR);
            $prepare->bindParam(':order_id', $order_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function deleteOrderItems($order_id)
    {
        try {
            $stmt = "DELETE FROM `{$this->table_sub}` where `order_id` = :order_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':order_id', $order_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function deleteOrderServices($order_id)
    {
        try {
            $stmt = "DELETE FROM `{$this->table_oservice}` where `order_id` = :order_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':order_id', $order_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getOrderItemsByProductIds($productIds, $customer_id)
    {
        try {
            $stmt = "SELECT i.* FROM `{$this->table_sub}` as i left join `{$this->table}` as o on o.id=i.order_id where o.status IN (2, 5, 6, 7, 8, 9) and o.customer_id = :customer_id and i.product_id IN (" . implode(', ', $productIds) . ")";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function createOrderService($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_oservice}` (`order_id`, `order_item_id`, `service_id`, `status_id`, `employee_id`, `cost`, `price`, `flag`) VALUES (:order_id,:order_item_id,:service_id,:status_id,:employee_id,:cost,:price,:flag)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':order_id', $array['order_id'], PDO::PARAM_STR);
            $prepare->bindParam(':order_item_id', $array['order_item_id'], PDO::PARAM_STR);
            $prepare->bindParam(':service_id', $array['service_id'], PDO::PARAM_STR);
            $prepare->bindParam(':status_id', $array['status_id'], PDO::PARAM_STR);
            $prepare->bindParam(':employee_id', $array['employee_id'], PDO::PARAM_STR);
            $prepare->bindParam(':cost', $array['cost'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':flag', $array['flag'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            if ($array['flag'] == 2) {
                $array['product_id'] = $array['service_id'];
                $prod = new Products();
                $prod->subProductQty($array);
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function createOrderDetails($array, $customer = null)
    {
        try {

            $stmt = "INSERT INTO `{$this->table_sub}` (`order_id`, `product_id`, `quantity`, `price`, `discount`, `discount_type`, `description`, `item_status`, `employee_id`, `start_date`, `end_date`, `priority`, `pack_size`, `pack_qty`, `unpack_qty`) VALUES (:order_id, :product_id, :quantity, :price, :discount, :discount_type, :description, :item_status,:employee_id,:start_date,:end_date, :priority, :pack_size, :pack_qty, :unpack_qty)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':order_id', $array['order_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
            $prepare->bindParam(':discount_type', $array['discount_type'], PDO::PARAM_STR);
            $prepare->bindParam(':item_status', $array['item_status'], PDO::PARAM_STR);
            $prepare->bindParam(':employee_id', $array['employee_id'], PDO::PARAM_STR);
            $prepare->bindParam(':start_date', $array['start_date'], PDO::PARAM_STR);
            $prepare->bindParam(':end_date', $array['end_date'], PDO::PARAM_STR);
            $prepare->bindParam(':priority', $array['priority'], PDO::PARAM_STR);
            $prepare->bindParam(':pack_size', $array['pack_size'], PDO::PARAM_STR);
            $prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
            $prepare->bindParam(':unpack_qty', $array['unpack_qty'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            foreach ($array['services'] as $value) {
                $dd = [
                    'order_id' => $array['order_id'],
                    'order_item_id' => $result,
                    'service_id' => !empty($value['service']) ? $value['service']['id'] : null,
                    'status_id' => !empty($value['status']) ? $value['status'] : null,
                    'employee_id' => !empty($value['employeeSelect']) ? $value['employeeSelect']['id'] : null,
                    'assign_date' => !empty($value['employeeSelect']) ? date('Y-m-d') : null,
                    'cost' => !empty($value['cost']) ? $value['cost'] : 0,
                    'price' => !empty($value['price']) ? $value['price'] : 0,
                    'quantity' => 1,
                    'flag' => 1, // service
                ];
                $this->createOrderService($dd);
            }
            foreach ($array['raw_items'] as $value) {
                $dd = [
                    'order_id' => $array['order_id'],
                    'order_item_id' => $result,
                    'service_id' => !empty($value['product']) ? $value['product']['id'] : null,
                    'status_id' => !empty($value['status']) ? $value['status'] : null,
                    'employee_id' => null,
                    'assign_date' => null,
                    'cost' => !empty($value['cost']) ? $value['cost'] : 0,
                    'price' => !empty($value['price']) ? $value['price'] : 0,
                    'quantity' => !empty($value['qty']) ? $value['qty'] : 1,
                    'flag' => 2, // raw_item
                    'shopId' => $array['shopId'],
                    'owner_id' => $array['shopId'],
                    'product_id' => !empty($value['product']) ? $value['product']['id'] : null,
                ];
                $this->createOrderService($dd);
            }
            if ($array['status'] != 1) {
                $prod = new Products();
                $prod->subProductQty($array);


                if (!empty($customer['linked_shop'])) {

                    $dd = [
                        'pprice' => $array['price'] - $array['discount'],
                        'price' => $array['price'],
                        'discount' => !empty($array['discount']) ? $array['discount'] : 0,
                        'qty' => $array['quantity'],
                        'pack_size' => !empty($array['pack_size']) ? $array['pack_size'] : 0,
                        'pack_qty' => !empty($array['pack_qty']) ? $array['pack_qty'] : 0,
                        'unpack_qty' => !empty($array['unpack_qty']) ? $array['unpack_qty'] : 0,
                        'stock_out' => 0,
                        'location' => null,
                        'pin' => 0,
                        'minQty' => 2,
                        'product_id' => $array['product_id'],
                        'shopId' => $customer['linked_shop'],
                        'owner_id' => $array['owner_id'],
                    ];

                    $prod->assignProduct($dd);
                }
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }


    public function makeTransaction($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_transaction}` (`user_id`, `customer_id`, `amount`, `payment_date`, `order_id`, `shopId`) VALUES (:user_id, :customer_id, :amount, :payment_date, :order_id, :shopId)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
            $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
            $prepare->bindParam(':amount', $array['amount'], PDO::PARAM_STR);
            $prepare->bindParam(':payment_date', $array['payment_date'], PDO::PARAM_STR);
            $prepare->bindParam(':order_id', $array['order_id'], PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getTransactionsByOIds($array)
    {
        $arr = implode(',', $array);
        try {
            $stmt = "SELECT * FROM `{$this->table_transaction}` WHERE order_id IN ($arr)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }


    public function runQuery()
    {
        try {
            $stmt = "ALTER TABLE `orders` CHANGE `paid_amount` `paid_amount` FLOAT(11) NULL DEFAULT NULL, CHANGE `discount` `discount` FLOAT(11) NOT NULL, CHANGE `gst` `gst` FLOAT(11) NULL DEFAULT NULL, CHANGE `service_charges` `service_charges` FLOAT(11) NULL DEFAULT NULL;
            ALTER TABLE `order_items` CHANGE `discount` `discount` FLOAT NOT NULL DEFAULT '0';";
            $prepare = $this->dbh->prepare($stmt);
            // $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            // $result['order'] = $prepare->fetch(PDO::FETCH_ASSOC);
            // if (!empty($result['order'])) {
            //     $stmt = "SELECT item.*, p.full_name AS product_title FROM `{$this->table_sub}` AS item LEFT JOIN products AS p ON item.product_id = p.id WHERE item.order_id=:id";
            //     $prepare = $this->dbh->prepare($stmt);
            //     $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            //     $prepare->execute();
            //     $result['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
            //     $c = new Customers();
            //     $result['customer'] = $c->getCustomer($result['order']['customer_id']);
            // }
            return true;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function getReturnOrder($id, $disableConcat = false)
    {
        try {
            // $this->runQuery();
            $stmt = "SELECT * FROM `{$this->table_ro}` WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result['order'] = $prepare->fetch(PDO::FETCH_ASSOC);
            if (!empty($result['order'])) {
                $full_name = '';
                if ($disableConcat) {
                    $full_name .= ", p.full_name AS product_title";
                } else {
                    $full_name .= ", concat(p.id, ' | ', p.full_name) AS product_title";
                }
                $stmt = "SELECT item.* $full_name FROM `{$this->table_rp}` AS item LEFT JOIN products AS p ON item.product_id = p.id WHERE item.order_id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':id', $id, PDO::PARAM_STR);
                $prepare->execute();
                $result['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
                $de = new DoubleEntry();
                if ($result['order']['is_supplier'] == 2) {
                    $c = new Suppliers();
                    $result['customer'] = $c->getSupplier($result['order']['customer_id']);
                } else {
                    $c = new Customers();
                    $result['customer'] = $c->getCustomer($result['order']['customer_id']);
                }
                $result['transactions'] = $de->getReturnTransactionsByAccountId($result['order']['id'], $result['customer']['account_id']);
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function getOrderServices($params = [])
    {
        try {
            $stmt = "SELECT b.*, s.full_name as serviceName, p.full_name as productName FROM `{$this->table_oservice}` as b left join `{$this->table_services}` as s on s.id=b.service_id and b.flag = 1 left join `{$this->table_pro}` as p on p.id=b.service_id and b.flag = 2 where b.`order_id` = :order_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':order_id', $params['order_id'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            $response = [];
            $empIds = [];

            foreach ($result as $k => $v) {
                $result[$k]['qty'] = $v['quantity'];
                if ($v['flag'] == 1) {
                    $result[$k]['service'] = ['full_name' => $v['serviceName'], 'id' => $v['service_id']];
                } elseif ($v['flag'] == 2) {
                    $result[$k]['product'] = ['full_name' => $v['productName'], 'id' => $v['service_id']];
                }
                if (!empty($v['employee_id'])) {
                    $empIds[] = $v['employee_id'];
                }
            }


            if (!empty($empIds)) {
                $employeesObj = new Employees();
                $employees = $employeesObj->getEmployees($params['shopId']);
                $emp = [];
                foreach ($employees as $em) {
                    $emp[$em['id']] = $em;
                }
                foreach ($result as $k => $v) {
                    if (!empty($v['employee_id']) && !empty($emp[$v['employee_id']])) {
                        $result[$k]['employeeSelect'] = $emp[$v['employee_id']];
                    }
                }
            }

            foreach ($result as $fal) {
                $response[$fal['order_item_id']][$fal['flag']][] = $fal;
            }

            return $response;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getOrder($id, $disableConcat = false)
    {
        try {
            // $this->runQuery();
            $stmt = "SELECT * FROM `{$this->table}` WHERE id=:id ";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result['order'] = $prepare->fetch(PDO::FETCH_ASSOC);
            if (!empty($result['order'])) {
                $full_name = '';
                if ($disableConcat) {
                    $full_name .= ", p.full_name AS product_title";
                } else {
                    $full_name .= ", concat(p.id, ' | ', p.full_name) AS product_title";
                }
                $stmt = "SELECT item.*, p.product_type, p.id as product_id $full_name FROM `{$this->table_sub}` AS item LEFT JOIN products AS p ON item.product_id = p.id WHERE item.order_id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':id', $id, PDO::PARAM_STR);
                $prepare->execute();
                $result['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
                $services = $this->getOrderServices(['shopId' => $result['order']['shopId'], 'order_id' => $id]);
                foreach ($result['order_items'] as $key => $val) {
                    $result['order_items'][$key]['expected_dates']['startDate'] = $val['start_date'];
                    $result['order_items'][$key]['expected_dates']['endDate'] = $val['end_date'];
                    if ($services[$val['id']][1]) { // service
                        $result['order_items'][$key]['services'] = $services[$val['id']][1];
                    }
                    if ($services[$val['id']][2]) { // raw
                        $result['order_items'][$key]['raw_items'] = $services[$val['id']][2];
                    }
                }
                $c = new Customers();
                $de = new DoubleEntry();
                $result['customer'] = $c->getCustomer($result['order']['customer_id']);
                $result['transactions'] = $de->getPaymentTransactionsByAccountId($result['order']['id'], $result['customer']['account_id']);
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getCustomerById($id, $shopId)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table_customers}` WHERE id=:id AND shopId = :shopId";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function userOrders($shopId, $params, $flag = null, $ignore = true)
    {
        try {
            $toCondition = "";
            if ($ignore) {
                if (!empty($params['to'])) {
                    $toCondition .= " AND DATE(o.order_date) BETWEEN '" . $params['from'] . "' AND '" . $params['to'] . "'";
                } else if (!empty($date)) {
                    $toCondition .= " AND DATE(o.order_date) BETWEEN '" . $params['from'] . "' AND '" . $params['from'] . "'";
                }
            }
            $flagCondition = "";
            if (!empty($flag)) {
                $flagCondition .= " AND o.status=$flag ";
            }

            if (!empty($params['orderId'])) {
                $toCondition = " AND o.order_custom_id='" . $params['orderId'] . "' ";
            }
            if ($params['orderType'] == 'cash') {
                $toCondition .= " AND o.paid_amount > 0 and o.status = 2 ";
            }
            if ($params['orderType'] == 'credit') {
                $toCondition .= " AND o.price > 0 and o.price != o.discount and o.paid_amount = 0 AND o.status != 1";
            }
            if ($params['orderType'] == 'park') { // all parked
                $toCondition = " ";
                $flagCondition = " AND o.status = 1 ";
            }
            if ($params['orderType'] == 'sample') {
                $toCondition .= " AND o.price = o.discount ";
            }

            if ($params['orderType'] == 'linked') {
                $toCondition .= " AND o.linked_shop = :shopId ";
            } else {
                $toCondition .= " AND o.shopId=:shopId ";
            }

            $stmt = "SELECT o.*, full_name, account_id, is_default FROM `{$this->table}` AS o LEFT JOIN customers AS c ON c.id = o.customer_id WHERE ((o.flag = 1) or (o.flag = 2 and o.status IN (5,6,7))) " . $toCondition . ' ' . $flagCondition . ' ORDER BY id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            $orderIds = [];
            $accountIds = [];
            foreach ($result as $order) {
                if ($order['status'] != 1) {
                    if (!empty($order['id'])) {
                        $orderIds[] = $order['id'];
                    }
                    if (!empty($order['account_id'])) {
                        $accountIds[] = $order['account_id'];
                    }
                }
            }
            if (!empty($orderIds) && !empty($accountIds) &&  !empty($shopId)) {
                $de = new DoubleEntry();
                $list = $de->getDebitEntriesByOrderIds(array_unique($orderIds), array_unique($accountIds), $shopId);
                // $result[0]['list'] = $list;
                $finalList = [];
                foreach ($list as $li) {
                    $finalList[$li['order_ref']][$li['payment_mode']] = $li['amount'];
                }
                foreach ($result as $key => $order) {
                    $result[$key]['prices'] = $finalList[$order['id']];
                }
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function userReturnOrders($shopId, $params)
    {
        try {

            $toCondition = "";
            if (!empty($params['to'])) {
                $toCondition .= " AND DATE(o.return_date) BETWEEN '" . $params['from'] . "' AND '" . $params['to'] . "'";
            } else if (!empty($date)) {
                $toCondition .= " AND DATE(o.return_date) BETWEEN '" . $params['from'] . "' AND '" . $params['from'] . "'";
            }

            if (!empty($params['orderId'])) {
                $toCondition = " AND o.id='" . $params['orderId'] . "' ";
            }

            if (!empty($params['orderType'])) {
                $toCondition .= " AND o.main_shop_rid=:shopId ";
            } else {
                $toCondition .= " AND o.shopId=:shopId ";
            }


            $stmt = "SELECT o.*, o.amount as price, full_name, account_id FROM `{$this->table_ro}` AS o LEFT JOIN customers AS c ON c.id = o.customer_id WHERE (o.flag IN (1, 2)) " . $toCondition . '  ' . '  ORDER BY id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            $orderIds = [];
            $accountIds = [];
            foreach ($result as $order) {
                if (!empty($order['id'])) {
                    $orderIds[] = $order['id'];
                }
                if (!empty($order['account_id'])) {
                    $accountIds[] = $order['account_id'];
                }
            }
            if (!empty($orderIds) && !empty($accountIds) &&  !empty($shopId)) {
                $de = new DoubleEntry();
                $list = $de->getDebitEntriesByReturnIds($orderIds, $accountIds, $shopId);
                // $result[0]['list'] = $list;
                $finalList = [];
                foreach ($list as $li) {
                    $finalList[$li['return_ref']][$li['payment_mode']] = $li['amount'];
                }
                foreach ($result as $key => $order) {
                    $result[$key]['prices'] = $finalList[$order['id']];
                }
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function ordersReport($shopId, $date, $to, $ids = [], $publisher_id = null, $account_id = null, $report = '')
    {
        try {

            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "' ";

            if (!empty($report) && $report == 'sample') {
                $toCondition .= " AND o.price = o.discount ";
            }
            if (!empty($account_id) || !empty($publisher_id) || !empty($ids)) {
                if (!empty($ids)) {
                    $toCondition .= " AND sub.product_id IN (" . implode(',', $ids) . ") ";
                }
                if (!empty($publisher_id)) {
                    $toCondition .= " AND p.publisher_id= $publisher_id ";
                }
                if (!empty($account_id)) {
                    $toCondition .= " AND o.customer_id=c.id and c.account_id=$account_id ";
                }
                $stmt = "SELECT sub.*, o.order_custom_id, o.order_date, p.full_name as productName, c.full_name, o.customer_name FROM `{$this->table_sub}` AS sub left join `{$this->table_pro}` as p on p.id = sub.product_id left join `{$this->table}` as o on sub.order_id = o.id LEFT JOIN customers AS c ON c.id = o.customer_id WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY o.order_date asc, sub.quantity desc';
            } else {
                $stmt = "SELECT o.*, full_name FROM `{$this->table}` AS o LEFT JOIN customers AS c ON c.id = o.customer_id WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY id asc';
            }
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function inventoryReturnReport($shopId, $date, $to, $type)
    {
        try {
            $toCondition = " AND DATE(rp.datetime)>='" . $date . "' AND DATE(rp.datetime)<='" . $to . "'";
            $stmt = "SELECT sum(rp.quantity) AS quantity, sum(rp.quantity * rp.price) AS total, p.full_name AS product_name, rp.price FROM `{$this->table_rp}` AS rp LEFT JOIN `{$this->table_pro}` AS p on rp.product_id = p.id WHERE rp.shopId=:shopId " . $toCondition . ' and rp.type =:type group by rp.product_id ORDER BY rp.id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->bindParam(':type', $type, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function returnToHeadoffice($id)
    {
        try {
            $stmt = "UPDATE `{$this->table_rp}` SET type=3 WHERE id=:id AND type=2";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function reconcileBill($id, $recon)
    {
        try {
            $stmt = "UPDATE `{$this->table}` SET recon=:recon WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->bindParam(':recon', $recon, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getReturnRecord($id)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table_ro}` WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            $items = $this->getFaultyReturnProducts($result['id']);
            return ['data' => $result, 'items' => $items];
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getFaultyReturnProducts($id)
    {
        try {
            $stmt = "SELECT rp.product_id, sum(quantity) as quantity, p.full_name as product_name FROM `{$this->table_rp}` AS rp LEFT JOIN `{$this->table_pro}` AS p ON rp.product_id=p.id WHERE rp.order_id = :id GROUP BY product_id ORDER BY rp.id desc";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getReturnRecords($array = [])
    {
        try {
            $stmt = "SELECT * FROM `{$this->table_ro}` where flag=1 and owner_id=:owner_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getFaultyProductsById($array)
    {
        try {
            $ids = implode(',', $array);
            $stmt = "SELECT id FROM `{$this->table_rp}` WHERE product_id IN (" . $ids . ")";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getFaultyProducts()
    {
        try {
            $stmt = "SELECT rp.product_id, rp.id, sum(quantity) as quantity, p.full_name as product_name FROM `{$this->table_rp}` AS rp LEFT JOIN `{$this->table_pro}` AS p ON rp.product_id=p.id GROUP BY product_id ORDER BY rp.id desc";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function makeReturn($array)
    {
        // $idJson = json_encode($ids);

        try {

            $ref_no = !empty($array['ref_no']) ? $array['ref_no'] : null;
            $flag = $array['flag'];
            if (!empty($array['id'])) {
                $stmt = "UPDATE `{$this->table_ro}` SET `amount`=:amount, `paid`=:paid, `discount`=:discount, `ref_no`=:ref_no, `shopId`=:shopId, `main_shop_rid`=:main_shop_rid, `owner_id`=:owner_id, `order_id`=:order_id, `customer_id`=:customer_id, `customer_name`=:customer_name, `return_date`=:return_date, `return_type`=:return_type, `is_supplier`=:is_supplier, `flag`=:flag, `show_bundle`=:show_bundle where id=:id";
            } else {
                $stmt = "INSERT INTO `{$this->table_ro}` (`amount`, `paid`, `discount`, `ref_no`, `shopId`, `main_shop_rid`, `owner_id`, `order_id`, `customer_id`, `customer_name`, `return_date`, `return_type`, `is_supplier`, `flag`, `show_bundle`) VALUES (:amount, :paid, :discount, :ref_no, :shopId, :main_shop_rid, :owner_id, :order_id, :customer_id, :customer_name, :return_date, :return_type, :is_supplier, :flag, :show_bundle)";
            }
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':amount', $array['amount'], PDO::PARAM_STR);
            $prepare->bindParam(':paid', $array['paid'], PDO::PARAM_STR);
            $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
            $prepare->bindParam(':ref_no', $ref_no, PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->bindParam(':main_shop_rid', $array['main_shop_rid'], PDO::PARAM_STR);
            $prepare->bindParam(':return_date', $array['return_date'], PDO::PARAM_STR);
            $prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
            $prepare->bindParam(':order_id', $array['order_id'], PDO::PARAM_STR);
            $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
            $prepare->bindParam(':customer_name', $array['customer_name'], PDO::PARAM_STR);
            $prepare->bindParam(':return_type', $array['return_type'], PDO::PARAM_STR);
            $prepare->bindParam(':is_supplier', $array['is_supplier'], PDO::PARAM_STR);
            $prepare->bindParam(':flag', $flag, PDO::PARAM_STR);
            $prepare->bindParam(':show_bundle', $array['show_bundle'], PDO::PARAM_STR);
            if (!empty($array['id'])) {
                $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
            }
            $prepare->execute();

            if (!empty($array['id'])) {
                $result = $array['id'];
            } else {
                $result = $this->dbh->lastInsertId();
            }

            return $result;
        } catch (PDOException $e) {
            die("Error!: here" . $e->getMessage() . "<br/>");
        }
    }


    public function ordersReportSummery($shopId, $date, $to, $publisher_id = null, $product_id = [], $report = '')
    {
        try {

            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "'";

            if (!empty($report) && $report == 'sample') {
                $toCondition .= " AND o.price = o.discount ";
            }

            $join = "";
            if (!empty($publisher_id) || !empty($product_id)) {
                $join = " LEFT JOIN `{$this->table_sub}` AS oi ON oi.order_id=o.id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id ";
            }
            if (!empty($publisher_id)) {
                $toCondition .= " and p.publisher_id = $publisher_id ";
            }
            if (!empty($product_id)) {
                $ids = implode(',', $product_id);
                $toCondition .= " and p.id IN( $ids ) ";
            }

            $stmt = "SELECT count(o.id) AS total, ROUND(SUM(o.price), 2) AS gross, ROUND(SUM(o.discount), 2) AS dist, ROUND(SUM(o.paid_amount), 2) AS paid, ROUND(SUM(o.`price` - o.`discount` - o.`paid_amount`), 2) AS balance FROM `{$this->table}` AS o " . $join . " WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY o.id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function ordersReportProductWise($shopId, $date, $to, $publisher_id = null, $product_id = [], $report = '')
    {
        try {

            $summery = $this->ordersReportSummery($shopId, $date, $to, $publisher_id, $product_id, $report);

            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "'";

            if (!empty($report) && $report == 'sample') {
                $toCondition .= " AND o.price = o.discount ";
            }
            if (!empty($publisher_id)) {
                $toCondition .= " and p.publisher_id = $publisher_id ";
            }
            if (!empty($product_id)) {
                $ids = implode(',', $product_id);
                $toCondition .= " and p.id IN( $ids ) ";
            }


            $stmt = "SELECT oi.product_id, oi.price AS price, sum(oi.quantity) AS quantity, p.full_name  FROM `{$this->table}` AS o LEFT JOIN `{$this->table_sub}` AS oi ON oi.order_id=o.id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id  WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 GROUP BY oi.product_id ORDER BY oi.quantity desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return ['summery' => $summery, 'rows' => $result];
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function stockAuditProductWise($shopId, $date, $to, $publisher_id = null, $product_id = [])
    {
        try {

            $supply = new Supply();

            $supplyReport = $supply->ordersReportProductWise($shopId, $date, $to, $publisher_id, $product_id);

            $summery = $this->ordersReportSummery($shopId, $date, $to, $publisher_id, $product_id);


            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "'";

            if (!empty($report) && $report == 'sample') {
                $toCondition .= " AND o.price = o.discount ";
            }
            if (!empty($publisher_id)) {
                $toCondition .= " and p.publisher_id = $publisher_id ";
            }
            if (!empty($product_id)) {
                $ids = implode(',', $product_id);
                $toCondition .= " and p.id IN( $ids ) ";
            }


            $stmt = "SELECT oi.product_id, oi.price AS price, sum(oi.quantity) AS quantity, p.full_name  FROM `{$this->table}` AS o LEFT JOIN `{$this->table_sub}` AS oi ON oi.order_id=o.id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id  WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 GROUP BY oi.product_id ORDER BY sum(oi.quantity) desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);

            $item = [];
            foreach ($supplyReport['rows'] as $key => $value) {
                $item[$value['product_id']]['full_name'] = $value['full_name'];
                $item[$value['product_id']]['product_id'] = $value['product_id'];
                $item[$value['product_id']]['purchase_qty'] = $value['quantity'];
            }
            foreach ($result as $key => $value) {
                $item[$value['product_id']]['full_name'] = $value['full_name'];
                $item[$value['product_id']]['product_id'] = $value['product_id'];
                $item[$value['product_id']]['sale_qty'] = $value['quantity'];
            }

            return ['summery' => $summery, 'rows' => $item];
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function returnReportProductWise($shopId, $date, $to)
    {
        try {

            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "'";
            $stmt = "SELECT rp.*, p.full_name  FROM `{$this->table}` AS o LEFT JOIN `{$this->table_rp}` AS rp ON rp.order_id = o.id LEFT JOIN `{$this->table_sub}` AS oi ON o.id=oi.order_id and oi.id=rp.product_id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id  WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 2 and o.status IN (5,6,7)';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function ordersReportDateWise($shopId, $date, $to, $publisher_id = null, $product_id = [])
    {
        try {

            $summery = $this->ordersReportSummery($shopId, $date, $to, $publisher_id, $product_id);

            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "'";
            $join = "";

            if (!empty($publisher_id) || !empty($product_id)) {
                $join = " LEFT JOIN `{$this->table_sub}` AS oi ON oi.order_id=o.id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id ";
            }
            if (!empty($publisher_id)) {
                $toCondition .= " and p.publisher_id = $publisher_id ";
            }
            if (!empty($product_id)) {
                $ids = implode(',', $product_id);
                $toCondition .= " and p.id IN( $ids ) ";
            }

            $stmt = "SELECT o.order_date, ROUND(sum(o.price), 2) AS price, ROUND(sum(o.discount), 2) AS discount, ROUND(sum(o.paid_amount), 2) AS paid_amount, ROUND(sum(o.price - o.discount - o.paid_amount), 2) AS balance FROM `{$this->table}` AS o " . $join . " WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 GROUP BY o.order_date ORDER BY o.id asc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return ['summery' => $summery, 'rows' => $result];
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getCustomerOrders($shopId, $customer_id)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table}` WHERE shopId=:shopId AND customer_id=:customer_id AND flag = 1 ORDER BY id desc";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function manageWallet($array)
    {
        $customer = $this->getCustomerById($array['id'], $array['shopId']);

        if (!empty($customer)) {

            $customer['wallet'] += $array['wallet'];

            try {
                $stmt = "UPDATE `{$this->table_customers}` SET `wallet`=:wallet WHERE id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':wallet', $customer['wallet'], PDO::PARAM_STR);
                $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
                $prepare->execute();
                $result = $prepare->rowCount();
                return $result;
            } catch (PDOException $e) {
                die("Error!: " . $e->getMessage() . "<br/>");
            }
        } else {
            return false;
        }
    }



    public function changeOrderFlag($array)
    {
        $id = $array['id'];
        $reason = $array['reason'];
        $flag = $array['flag'];
        try {
            $stmt = "UPDATE `{$this->table}` SET `reason`=:reason, `flag`=:flag WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':reason', $reason, PDO::PARAM_STR);
            $prepare->bindParam(':flag', $flag, PDO::PARAM_STR);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function changeReturnFlag($array)
    {
        $id = $array['id'];
        $reason = $array['reason'];
        $flag = $array['flag'];
        try {
            $stmt = "UPDATE `{$this->table_ro}` SET `reason`=:reason, `flag`=:flag WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':reason', $reason, PDO::PARAM_STR);
            $prepare->bindParam(':flag', $flag, PDO::PARAM_STR);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function orderReturn($id, $action)
    {
        try {
            $stmt = "UPDATE `{$this->table}` SET `status`=:status, flag = 2 WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':status', $action, PDO::PARAM_STR);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function prepareReturn($array)
    {

        $customersObj = new Customers();
        $storeObj = new Store();

        $array['flag'] = !empty($array['flag']) ? $array['flag'] : 1;

        $userData = UserInfo();
        $user = $userData['user'];
        $LinkedCustomer = null;
        if (!empty($array['LinkForMainShop'])) {
            $LinkedCustomer = $customersObj->getCustomerByLinkedShop($user['shopId']); // main shop customer's account
        }
        if (!empty($array['returnOrder'])) {
            $order = $this->getReturnOrder($array['returnOrder']);
            if (!empty($order['order']['main_shop_rid'])) {
                $LinkedCustomer = $customersObj->getCustomerByLinkedShop($order['order']['shopId']); // main shop customer's account
            }
        }

        $shopId = (!empty($array['shopId']) ? $array['shopId'] : $user['shopId']);
        $ownerShopId = !empty($LinkedCustomer) ? $LinkedCustomer['shopId'] : (!empty($array['shopId']) ? $array['shopId'] : $user['shopId']);

        $array['supplierId'] = !empty($LinkedCustomer) ? $LinkedCustomer['id'] : $array['supplierId'];
        $array['supplierName'] = !empty($LinkedCustomer) ? $LinkedCustomer['full_name'] : $array['supplierName'];

        $selectedStoreDATA = $storeObj->getStore($shopId);
        $storeDATA = $storeObj->getStore($ownerShopId);


        $shopAccounts = new ShopAccounts();
        $products = new Products();
        $doubleEntry = new DoubleEntry();
        $supplierId = $array['supplierId'];

        $purchaseValue = $array['subTotal'];
        $discount = !empty($array['discount']) ? $array['discount'] : 0;
        $givenDiscount = !empty($array['givenDiscount']) ? $array['givenDiscount'] : 0;
        $discount += $givenDiscount;
        $productsValue = $purchaseValue;
        $payment_amount = !empty($array['payment_amount']) ? $array['payment_amount'] : 0;

        $returnType = !empty($LinkedCustomer) ? 1 : (!empty($array['return_type']) ? $array['return_type'] : 1); // 1 = sale return, 2 = purchase return
        $isSupplier = !empty($LinkedCustomer) ? 1 : (!empty($array['is_supplier']) ? $array['is_supplier'] : 1); // 1 = customer, 2 = supplier
        $supplierObj = $isSupplier == 1 ? new Customers() : new Suppliers();
        $accountsData = $shopAccounts->getSAs($storeDATA['id']);
        $storeAccounts = [];
        foreach ($accountsData as $a) {
            $storeAccounts[$a['key_value']] = $a['account_id'];
        }

        if (!empty($array['returnOrder'])) {

            $order = $this->getReturnOrder($array['returnOrder']);

            if (!empty($order['order']['id'])) {
                $currentStatus = $order['order']['flag'];

                $this->deleteReturnOrderItem($order['order']['id']);
                // delete transactions
                $doubleEntry->deleteTransactionByReturnId($array['returnOrder']);

                if (in_array($currentStatus, [2])) { // approve 

                    // rollback products first
                    foreach ($order['order_items'] as $prod) {
                        if ($returnType == 1) { // sales return
                            if (!empty($LinkedCustomer)) {
                                $products->addProductQty($prod['product_id'], ['qty' => $prod['quantity'], 'pack_qty' => $prod['pack_qty']], $shopId);
                                $products->subProductQty(['product_id' => $prod['product_id'], 'quantity' => $prod['quantity'], 'pack_qty' => $prod['pack_qty'], 'owner_id' => $storeDATA['owner_id'], 'shopId' => $ownerShopId]);
                            } else {
                                $products->subProductQty(['product_id' => $prod['product_id'], 'quantity' => $prod['quantity'], 'pack_qty' => $prod['pack_qty'], 'owner_id' => $storeDATA['owner_id'], 'shopId' => $shopId]);
                            }
                        } else { // supply return
                            $products->addProductQty($prod['product_id'], ['qty' => $prod['quantity'], 'pack_qty' => $prod['pack_qty']], $shopId);
                        }
                    }
                }
            }
        }

        $returnId = $this->makeReturn([
            "id" => $array['returnOrder'],
            "amount" => $purchaseValue,
            "paid" => $payment_amount,
            "discount" => $discount,
            "flag" => $array['flag'],
            "shopId" => $shopId,
            "main_shop_rid" => !empty($LinkedCustomer['shopId']) ? $LinkedCustomer['shopId'] : null,
            "return_date" => $selectedStoreDATA['sale_date'],
            "owner_id" => $storeDATA['owner_id'],
            'show_bundle' => !empty($array['show_bundle']) ? 1 : 0,
            "order_id" => !empty($array['order_id']) ? $array['order_id'] : null,
            "customer_id" => $supplierId,
            "customer_name" => $array['supplierName'],
            "return_type" => $returnType,
            "is_supplier" => $isSupplier
        ]);

        $products = [];
        foreach ($array['items'] as $value) {
            $products[] = [
                'user_id' => $user['id'],
                'shopId' => $shopId,
                'order_id' => $returnId,
                'product_id' => $value['product_id'],
                'quantity' => $value['qty'] + (!empty($value['unpack_qty']) ? $value['unpack_qty'] : 0),
                "owner_id" => $storeDATA['owner_id'],
                'pack_size' => !empty($value['pack_size']) ? $value['pack_size'] : 0,
                'pack_qty' => !empty($value['pack_qty']) ? $value['pack_qty'] : 0,
                'unpack_qty' => !empty($value['unpack_qty']) ? $value['unpack_qty'] : 0,
                'price' => $value['price'],
                'discount_type' => !empty($value['discount_type']) ? $value['discount_type'] : 1,
                'discount_value' => !empty($value['discount_value']) ? $value['discount_value'] : 0,
                'discount' => !empty($value['discount']) ? $value['discount'] : 0,
                'type' => 1,
            ];
        }

        $returnOrder = $this->getReturnOrder($returnId);

        $res = [];
        foreach ($products as $row) {
            $res[$row['product_id']][] = $this->orderReturnAll($row, $returnType == 1 ? false : true, !empty($LinkedCustomer['shopId']) ? $LinkedCustomer['shopId'] : null, $array['flag']);
        }

        if ($returnOrder['order']['flag'] == 2) {
            if ($isSupplier == 1) {
                $supplier = $supplierObj->getCustomer($supplierId);
            } else {
                $supplierObj = new Suppliers();
                $supplier = $supplierObj->getSupplier($supplierId);
            }

            $config = [
                'description' => !empty($array['summery']) ? $array['summery'] : ($returnType == 1 ? "Sale Return PLACED" : "Purchase Return PLACED"),
                'transaction_type' => ($returnType == 1 ? 'SALE_RETURN' : 'PURCHASE_RETURN'),
                'shop_entry' => 'D',
                'customer_entry' => 'C',
                'customer_cash_entry' => 'D',
                'shop_cash_entry' => 'C',
            ];

            if ($returnType == 2) {
                $config['shop_entry'] = 'C';
                $config['customer_entry'] = 'D';
                $config['customer_cash_entry'] = 'C';
                $config['shop_cash_entry'] = 'D';
            }

            $makeTransaction = [
                'description' => $config['description'],
                'transaction_date' => $storeDATA['sale_date'],
                'reference' => $array['ref_no'],
                'transaction_type' => $config['transaction_type'],
                'shopId' => $ownerShopId,
                'created_by' => $_SESSION['user_credentials']['id'],
                'return_ref' => $returnId
            ];

            $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);


            $assetPrice = $productsValue; // D 1000
            $saleDiscount = $discount; // C 200
            $returnAmount = $purchaseValue - $discount; // C 800

            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $storeAccounts['assets'],
                'entry_type' => $config['shop_entry'],
                'description' => '',
                'amount' => $assetPrice, // 2000
                'payment_mode' => $array['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];

            $a[] = $doubleEntry->makeEntry($entry);

            $entry = [
                'transaction_id' => $makeTransactionId,
                'account_id' => $supplier['account_id'],
                'entry_type' => $config['customer_entry'],
                'description' => '',
                'amount' => $returnAmount,
                'payment_mode' => $array['payment_mode'],
                'user_id' => $_SESSION['user_credentials']['id'],
            ];

            $a[] = $doubleEntry->makeEntry($entry);

            if (!empty($saleDiscount)) {
                // saleDiscount credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $returnType == 1 ? $storeAccounts['sale_discount'] : $storeAccounts['purchase_discount'],
                    'entry_type' => $config['customer_entry'], // as per customer
                    'description' => '',
                    'amount' => $saleDiscount, // 200 @ 10%
                    'payment_mode' => $array['payment_mode'],
                    'user_id' => $_SESSION['user_credentials']['id'],
                ];
                $a[] = $doubleEntry->makeEntry($entry);
            }


            // assets debit entry - debit
            // liability payable entry - credit
            // purchase discount entry - credit


            if (!empty($payment_amount)) {
                // $makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);
                // payable credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $supplier['account_id'],
                    'entry_type' => $config['customer_cash_entry'],
                    'description' => '',
                    'amount' => $payment_amount,
                    'payment_mode' => $array['payment_mode'],
                    'user_id' => $_SESSION['user_credentials']['id'],
                ];
                $a[] = $doubleEntry->makeEntry($entry);
                // cash credit entry
                $entry = [
                    'transaction_id' => $makeTransactionId,
                    'account_id' => $returnType == 1 ? $storeAccounts['sale_returns'] : $storeAccounts['purchase_returns'],
                    'entry_type' => $config['shop_cash_entry'],
                    'description' => '',
                    'amount' => $payment_amount, // 200 @ 10%
                    'payment_mode' => $array['payment_mode'],
                    'user_id' => $_SESSION['user_credentials']['id'],
                ];
                $a[] = $doubleEntry->makeEntry($entry);
            }

            $newsletter = new Newsletter();
            $newsletter->send([
                'subject' => $config['description'],
                'body' => $newsletter->drawReturn($returnId),
                'sentTo' => [['email' => !empty($supplier['email']) ? $supplier['email'] : 'zia.pccr@yahoo.com', 'name' => $array['supplierName']]],
                'ccEmails' => [['email' => $storeDATA['company_email'], 'name' => $storeDATA['full_name']]],
                'client' => $storeDATA['full_name'],
                'labels' => [$makeTransaction['transaction_type']]
            ]);
        }

        return ['status' => 200, 'message' => 'successfully done', 'order' => ['id' => $returnId, 'linked_shop' => $supplier['linked_shop'], 'shopId' => $LinkedCustomer]];
    }
}
