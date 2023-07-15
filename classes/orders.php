<?php

class Orders extends Connection
{

    private $table = 'orders';
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

    public function createOrder($array)
    {
        try {
            if (!empty($array['id'])) {
                $stmt = "UPDATE `{$this->table}` SET `user_id`=:user_id, `customer_id`=:customer_id, `customer_name`=:customer_name, `status`=:status, `price`=:price, `paid_amount`=:paid_amount, `discount`=:discount, `shopId`=:shopId, `order_date`=:order_date, `gst`=:gst, `service_charges`=:service_charges, `summery`=:summery, `ref_no`=:ref_no, `show_discount`=:show_discount, status_id=:status_id, expected_delivery_date=:expected_delivery_date WHERE id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_name', $array['customer_name'], PDO::PARAM_STR);
                $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
                $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
                $prepare->bindParam(':paid_amount', $array['paid_amount'], PDO::PARAM_STR);
                $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
                $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
                $prepare->bindParam(':order_date', $array['order_date'], PDO::PARAM_STR);
                $prepare->bindParam(':gst', $array['gst'], PDO::PARAM_STR);
                $prepare->bindParam(':service_charges', $array['service_charges'], PDO::PARAM_STR);
                $prepare->bindParam(':summery', $array['summery'], PDO::PARAM_STR);
                $prepare->bindParam(':ref_no', $array['ref_no'], PDO::PARAM_STR);
                $prepare->bindParam(':show_discount', $array['show_discount'], PDO::PARAM_STR);
                $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
                $prepare->bindParam(':status_id', $array['status_id'], PDO::PARAM_STR);
                $prepare->bindParam(':expected_delivery_date', $array['expected_delivery_date'], PDO::PARAM_STR);
                $prepare->execute();
                $result = $prepare->rowCount();
                return $array['id'];
            } else {
                $stmt = "INSERT INTO `{$this->table}` (`user_id`, `customer_id`, `customer_name`, `status`, `price`, `paid_amount`, `discount`, `shopId`, `order_date`, `gst`, `service_charges`, `summery`, `ref_no`, `show_discount`, `status_id`, `expected_delivery_date`) VALUES (:user_id, :customer_id, :customer_name, :status, :price, :paid_amount, :discount, :shopId, :order_date, :gst, :service_charges, :summery, :ref_no, :show_discount, :status_id, :expected_delivery_date)";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_name', $array['customer_name'], PDO::PARAM_STR);
                $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
                $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
                $prepare->bindParam(':paid_amount', $array['paid_amount'], PDO::PARAM_STR);
                $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
                $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
                $prepare->bindParam(':order_date', $array['order_date'], PDO::PARAM_STR);
                $prepare->bindParam(':gst', $array['gst'], PDO::PARAM_STR);
                $prepare->bindParam(':service_charges', $array['service_charges'], PDO::PARAM_STR);
                $prepare->bindParam(':summery', $array['summery'], PDO::PARAM_STR);
                $prepare->bindParam(':ref_no', $array['ref_no'], PDO::PARAM_STR);
                $prepare->bindParam(':show_discount', $array['show_discount'], PDO::PARAM_STR);
                $prepare->bindParam(':status_id', $array['status_id'], PDO::PARAM_STR);
                $prepare->bindParam(':expected_delivery_date', $array['expected_delivery_date'], PDO::PARAM_STR);
                $prepare->execute();
                $result = $this->dbh->lastInsertId();
                return $result;
            }
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function orderReturnAll($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_rp}` (`user_id`, `shopId`, `order_id`, `product_id`, `quantity`, `price`, `discount`, `discount_type`, `discount_value`, `type`) VALUES (:user_id, :shopId, :order_id, :product_id, :quantity, :price, :discount, :discount_type, :discount_value, :type)";
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
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            $products = new Products();
            $products->addProductQty($array['product_id'], $array['quantity'], $array['shopId']);
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

    public function createOrderDetails($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_sub}` (`order_id`, `product_id`, `quantity`, `price`, `discount`, `discount_type`, `description`, `item_status`, `employee_id`, `start_date`, `end_date`, `priority`) VALUES (:order_id, :product_id, :quantity, :price, :discount, :discount_type, :description, :item_status,:employee_id,:start_date,:end_date, :priority)";
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
                $stmt = "SELECT item.* $full_name FROM `{$this->table_sub}` AS item LEFT JOIN products AS p ON item.product_id = p.id WHERE item.order_id=:id";
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

    public function userOrders($shopId, $date, $to = null, $flag = null, $ignore = true)
    {
        try {
            $toCondition = "";
            if ($ignore) {
                if (!empty($to)) {
                    $toCondition .= " AND DATE(o.order_date) BETWEEN '" . $date . "' AND '" . $to . "'";
                } else if (!empty($date)) {
                    $toCondition .= " AND DATE(o.order_date) BETWEEN '" . $date . "' AND '" . $date . "'";
                }
            }
            $flagCondition = "";
            if (!empty($flag)) {
                $flagCondition .= " AND o.status=$flag ";
            }

            $stmt = "SELECT o.*, full_name, account_id FROM `{$this->table}` AS o LEFT JOIN customers AS c ON c.id = o.customer_id WHERE o.shopId=:shopId " . $toCondition . ' ' . $flagCondition . ' and ((o.flag = 1) or (o.flag = 2 and o.status IN (5,6,7))) ORDER BY id desc';
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
    public function userReturnOrders($shopId, $date, $to = null)
    {
        try {
            $toCondition = "";
            if (!empty($to)) {
                $toCondition .= " AND DATE(o.return_date) BETWEEN '" . $date . "' AND '" . $to . "'";
            } else if (!empty($date)) {
                $toCondition .= " AND DATE(o.return_date) BETWEEN '" . $date . "' AND '" . $date . "'";
            }
            $stmt = "SELECT o.*, o.amount as price, full_name, account_id FROM `{$this->table_ro}` AS o LEFT JOIN customers AS c ON c.id = o.customer_id WHERE o.shopId=:shopId " . $toCondition . '  ' . ' and (o.flag = 1) ORDER BY id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            $orderIds = [];
            $accountIds = [];
            foreach ($result as $order) {
                $orderIds[] = $order['id'];
                $accountIds[] = $order['account_id'];
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

    public function ordersReport($shopId, $date, $to, $ids = [])
    {
        try {

            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "'";
            if (!empty($ids)) {
                $toCondition .= " AND sub.product_id IN (" . implode(',', $ids) . ")";
                $stmt = "SELECT sub.*, p.full_name as productName, c.full_name FROM `{$this->table_sub}` AS sub left join `{$this->table_pro}` as p on p.id = sub.product_id left join `{$this->table}` as o on sub.order_id = o.id LEFT JOIN customers AS c ON c.id = o.customer_id WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY id asc';
            } else {
                $stmt = "SELECT o.*, full_name FROM `{$this->table}` AS o LEFT JOIN customers AS c ON c.id = o.customer_id left join `{$this->table_sub}` as sub on sub.order_id = o.id WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY id asc';
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

    public function getReturnRecord($id)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table_ro}` WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            $items = $this->getFaultyReturnProducts($result['ids']);
            return ['data' => $result, 'items' => $items];
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getFaultyReturnProducts($ids)
    {
        try {
            $json = json_decode($ids, true);
            $final = implode(',', $json);
            $stmt = "SELECT rp.product_id, sum(quantity) as quantity, p.full_name as product_name FROM `{$this->table_rp}` AS rp LEFT JOIN `{$this->table_pro}` AS p ON rp.product_id=p.id WHERE rp.id IN ($final) GROUP BY product_id ORDER BY rp.id desc";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getReturnRecords()
    {
        try {
            $stmt = "SELECT * FROM `{$this->table_ro}`";
            $prepare = $this->dbh->prepare($stmt);
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
            $stmt = "SELECT id FROM `{$this->table_rp}` WHERE type=2 AND product_id IN (" . $ids . ")";
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
            $stmt = "SELECT rp.product_id, rp.id, sum(quantity) as quantity, p.full_name as product_name FROM `{$this->table_rp}` AS rp LEFT JOIN `{$this->table_pro}` AS p ON rp.product_id=p.id WHERE rp.type=2 GROUP BY product_id ORDER BY rp.id desc";
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


        $ref_no = !empty($array['ref_no']) ? $array['ref_no'] : null;
        $flag = 1;
        try {
            if (!empty($array['id'])) {
                $stmt = "UPDATE `{$this->table_ro}` SET `amount`=:amount, `paid`=:paid, `discount`=:discount, `ref_no`=:ref_no, `shopId`=:shopId, `owner_id`=:owner_id, `order_id`=:order_id, `customer_id`=:customer_id, `customer_name`=:customer_name, `return_date`=:return_date, `flag`=:flag where id=:id";
            } else {
                $stmt = "INSERT INTO `{$this->table_ro}` (`amount`, `paid`, `discount`, `ref_no`, `shopId`, `owner_id`, `order_id`, `customer_id`, `customer_name`, `return_date`, `flag`) VALUES (:amount, :paid, :discount, :ref_no, :shopId, :owner_id, :order_id, :customer_id, :customer_name, :return_date, :flag)";
            }
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':amount', $array['amount'], PDO::PARAM_STR);
            $prepare->bindParam(':paid', $array['paid'], PDO::PARAM_STR);
            $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
            $prepare->bindParam(':ref_no', $ref_no, PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->bindParam(':return_date', $array['return_date'], PDO::PARAM_STR);
            $prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
            $prepare->bindParam(':order_id', $array['order_id'], PDO::PARAM_STR);
            $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
            $prepare->bindParam(':customer_name', $array['customer_name'], PDO::PARAM_STR);
            $prepare->bindParam(':flag', $flag, PDO::PARAM_STR);
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


    public function ordersReportSummery($shopId, $date, $to)
    {
        try {

            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "'";
            $stmt = "SELECT count(id) AS total, SUM(price) AS gross, SUM(discount) AS dist, SUM(paid_amount) AS paid, SUM(`price` - `discount` - `paid_amount`) AS balance FROM `{$this->table}` AS o WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function ordersReportProductWise($shopId, $date, $to)
    {
        try {

            $summery = $this->ordersReportSummery($shopId, $date, $to);

            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "'";
            $stmt = "SELECT oi.product_id, oi.price AS price, sum(oi.quantity) AS quantity, p.full_name  FROM `{$this->table}` AS o LEFT JOIN `{$this->table_sub}` AS oi ON oi.order_id=o.id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id  WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 GROUP BY oi.product_id ORDER BY o.id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return ['summery' => $summery, 'rows' => $result];
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

    public function ordersReportDateWise($shopId, $date, $to)
    {
        try {

            $summery = $this->ordersReportSummery($shopId, $date, $to);

            $toCondition = " AND o.order_date>='" . $date . "' AND o.order_date<='" . $to . "'";
            $stmt = "SELECT o.order_date, sum(o.price) AS price, sum(o.discount) AS discount, sum(o.paid_amount) AS paid_amount, sum(o.price - o.discount - o.paid_amount) AS balance FROM `{$this->table}` AS o WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 GROUP BY o.order_date ORDER BY o.id asc';
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
}
