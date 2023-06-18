<?php

class Supply extends Connection
{

    private $table = 'supply';
    private $table_sub = 'supply_items';
    private $table_rs = 'supply_returns';
    private $table_transaction = 'supply_transaction';
    private $table_suppliers = 'suppliers';

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

    public function addColumn()
    {
        try {
            $stmt = "ALTER TABLE `{$this->table}` ADD COLUMN IF NOT EXISTS `ref_no` varchar(20) NULL DEFAULT NULL AFTER `supply_date`";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->execute();
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function createSupply($array)
    {
        try {
            if (!empty($array['id'])) {
                // $this->addColumn();
                $stmt = "UPDATE `{$this->table}` SET `supplier_id`=:supplier_id, supplier_type=:supplier_type, `status`=:status, `price`=:price, `payment_amount`=:payment_amount, `payment_with_credit`=:payment_with_credit, `discount`=:discount, `supply_date`=:supply_date, `ref_no`=:ref_no WHERE id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':supplier_id', $array['supplier_id'], PDO::PARAM_STR);
                $prepare->bindParam(':supplier_type', $array['supplier_type'], PDO::PARAM_STR);
                $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
                $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
                $prepare->bindParam(':payment_amount', $array['payment_amount'], PDO::PARAM_STR);
                $prepare->bindParam(':payment_with_credit', $array['payment_with_credit'], PDO::PARAM_STR);
                $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
                $prepare->bindParam(':supply_date', $array['supply_date'], PDO::PARAM_STR);
                $prepare->bindParam(':ref_no', $array['ref_no'], PDO::PARAM_STR);
                $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
                $prepare->execute();
                return $array['id'];
            } else {
                // $this->addColumn();
                $stmt = "INSERT INTO `{$this->table}` (`user_id`, `supplier_id`, `supplier_type`, `status`, `price`, `payment_amount`, `payment_with_credit`, `discount`, `shopId`, `supply_date`, `ref_no`) VALUES (:user_id, :supplier_id, :supplier_type, :status, :price, :payment_amount, :payment_with_credit, :discount, :shopId, :supply_date, :ref_no)";
                $prepare = $this->dbh->prepare($stmt);
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
                $prepare->execute();
                $result = $this->dbh->lastInsertId();
                return $result;
            }
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function createSupplyDetails($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_sub}` (`supply_id`, `product_id`, `quantity`, `price`, `discount`) VALUES (:supply_id, :product_id, :quantity, :price, :discount)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':supply_id', $array['supply_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function deleteSupplyDetails($supply_id)
    {
        try {
            $stmt = "DELETE FROM `{$this->table_sub}` where supply_id = :supply_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':supply_id', $supply_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function makeTransaction($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_transaction}` (`user_id`, `supplier_id`, `amount`, `payment_date`, `supply_id`, `transaction_type`, `shopId`) VALUES (:user_id, :supplier_id, :amount, :payment_date, :supply_id, :transaction_type, :shopId)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
            $prepare->bindParam(':supplier_id', $array['supplier_id'], PDO::PARAM_STR);
            $prepare->bindParam(':amount', $array['amount'], PDO::PARAM_STR);
            $prepare->bindParam(':payment_date', $array['payment_date'], PDO::PARAM_STR);
            $prepare->bindParam(':supply_id', $array['supply_id'], PDO::PARAM_STR);
            $prepare->bindParam(':transaction_type', $array['transaction_type'], PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getOrder($id)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table}` WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
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
                $stmt = "SELECT item.*, p.full_name, p.code, p.barcode, p.id FROM `{$this->table_sub}` as item LEFT JOIN products as p ON item.product_id = p.id WHERE item.supply_id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':id', $id, PDO::PARAM_STR);
                $prepare->execute();
                $result['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getOrders($id)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table}` WHERE supplier_id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result['orders'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result['orders'] as $key => $order) {
                $stmt = "SELECT item.*, p.full_name AS product_title FROM `{$this->table_sub}` as item LEFT JOIN products as p ON item.product_id = p.id WHERE item.supply_id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':id', $order['id'], PDO::PARAM_STR);
                $prepare->execute();
                $result['orders'][$key]['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
                $result['supplier'] = $this->getSupplierById($id);
            }
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getSupplierById($id)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table_suppliers}` WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function userOrders($shopId, $date, $to = null)
    {
        try {
            $toCondition = "";
            if (!empty($to)) {
                $toCondition .= " AND DATE(o.supply_date)>='" . $date . "' AND DATE(o.supply_date)<='" . $to . "'";
            } else {
                $toCondition .= " AND DATE(o.supply_date)>='" . $date . "'";
            }

            $stmt = "SELECT o.*, c.full_name as c_full_name, s.name as s_full_name FROM `{$this->table}` AS o LEFT JOIN customers AS c ON o.supplier_type = 2 and c.id = o.supplier_id LEFT JOIN suppliers AS s ON o.supplier_type = 1 and s.id = o.supplier_id WHERE o.shopId=:shopId " . $toCondition . ' and o.flag = 1 ORDER BY id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function manageWallet($array)
    {

        $supplier = $this->getSupplierById($array['id']);
        $amount = $supplier['wallet'] + $array['wallet'];
        try {
            $stmt = "UPDATE `{$this->table_suppliers}` SET `wallet`=:wallet WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':wallet', $amount, PDO::PARAM_INT);
            $prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
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

    public function getSupplierTransactions($params)
    {
        try {

            $stmt = "SELECT COUNT(id) as total FROM `{$this->table_transaction}` WHERE supplier_id = :id";
            $prepare = $this->dbh->prepare($stmt);
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
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
            $prepare->bindParam(':id', $params['id'], PDO::PARAM_INT);
            $prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getSupplyItemsByProductIds($productIds, $supplier_id)
    {
        try {
            $products = [];
            $price = [];
            foreach ($productIds as $value) {
                $arr = explode('_', $value);
                $products[] = $arr[0];
                $price[] = $arr[1];
            }
            $stmt = "SELECT sp.supplier_id, s.* FROM `{$this->table_sub}` as s left join `{$this->table}` as sp on sp.id = s.supply_id where s.product_id IN (" . implode(', ', $products) . ") and s.price IN (" . implode(', ', $price) . ") and sp.supplier_id=:supplier_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':supplier_id', $supplier_id, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function orderReturnAll($array, $action, $type = 1)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_rs}` (`user_id`, `shopId`, `supply_id`, `product_id`, `quantity`, `price`, `type`) VALUES (:user_id, :shopId, :supply_id, :product_id, :quantity, :price, :type)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->bindParam(':supply_id', $array['supply_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':type', $array['type'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            $products = new Products();
            $array['quantity'] = (-1 * $array['quantity']);
            $products->addProductQty($array['product_id'], $array['quantity'], $array['shopId'], $type);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
}
