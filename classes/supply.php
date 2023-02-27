<?php

class Supply extends Connection
{
    
    private $table = 'supply';
    private $table_sub = 'supply_items';
    private $table_transaction = 'supply_transaction';
    private $table_suppliers = 'suppliers';
    
	public function searchCustomer($shopId, $search) {
		$stmt = "SELECT * FROM `{$this->table}`  WHERE shopId=:shopId AND (full_name LIKE '".$search."%' OR code LIKE '".$search."%' OR phoneNumber LIKE '".$search."%') LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
    }

    public function addColumn() {
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
            $this->addColumn();
            $stmt = "INSERT INTO `{$this->table}` (`user_id`, `supplier_id`, `status`, `price`, `discount`, `shopId`, `supply_date`, `ref_no`) VALUES (:user_id, :supplier_id, :status, :price, :discount, :shopId, :supply_date, :ref_no)";
            $prepare = $this->dbh->prepare($stmt);        
            $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
            $prepare->bindParam(':supplier_id', $array['supplier_id'], PDO::PARAM_STR);
            $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->bindParam(':supply_date', $array['supply_date'], PDO::PARAM_STR);
            $prepare->bindParam(':ref_no', $array['ref_no'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }

    public function createSupplyDetails($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_sub}` (`supply_id`, `product_id`, `quantity`, `price`) VALUES (:supply_id, :product_id, :quantity, :price)";
            $prepare = $this->dbh->prepare($stmt);        
            $prepare->bindParam(':supply_id', $array['supply_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
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

    public function getOrder($id) {
        try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
            $result['order'] = $prepare->fetch(PDO::FETCH_ASSOC);
            if(!empty($result['order'])) {
                $stmt = "SELECT item.*, p.full_name AS product_title FROM `{$this->table_sub}` as item LEFT JOIN products as p ON item.product_id = p.id WHERE item.supply_id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':id',$id,PDO::PARAM_STR);
                $prepare->execute();
                $result['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
                $c = new Customers();
                $result['customer'] = $c->getCustomer($result['order']['customer_id']);
            }
            return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }

    public function getOrders($id) {
        try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE supplier_id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
            $result['orders'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
            foreach($result['orders'] as $key => $order) {
                $stmt = "SELECT item.*, p.full_name AS product_title FROM `{$this->table_sub}` as item LEFT JOIN products as p ON item.product_id = p.id WHERE item.supply_id=:id";
                $prepare = $this->dbh->prepare($stmt);
                $prepare->bindParam(':id',$order['id'],PDO::PARAM_STR);
                $prepare->execute();
                $result['orders'][$key]['order_items'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
                $result['supplier'] = $this->getSupplierById($id);
            }
            return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }

    public function getSupplierById($id) {
        try {
			$stmt = "SELECT * FROM `{$this->table_suppliers}` WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id,PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }

    public function userOrders($shopId, $date, $to = null) {
        try {
            $toCondition = "";
            if(!empty($to)) {
                $toCondition .= " AND o.order_date>='".$date."' AND o.order_date<='".$to."'";
            }
            else {
                $toCondition .=" AND o.order_date>='".$date."'";
            }

            
            
			$stmt = "SELECT o.*, full_name FROM `{$this->table}` AS o LEFT JOIN customers AS c ON c.id = o.customer_id WHERE o.shopId=:shopId ".$toCondition.' and o.flag = 1 ORDER BY id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
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

    public function getSupplierTransactions($params) {
		try {
			
			$stmt = "SELECT COUNT(id) as total FROM `{$this->table_transaction}` WHERE supplier_id = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id',$params['id'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			
			$no_of_records_per_page = 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage-1) < 0 ? 0 : ($currentPage-1)) * $no_of_records_per_page;
			$search = "(id LIKE '%".$params["search"]."%' OR amount LIKE '%".$params["search"]."%' OR payment_date LIKE '%".$params["search"]."%' ) ";
			$stmt = "SELECT * FROM `{$this->table_transaction}` WHERE supplier_id=:id AND $search LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':id', $params['id'], PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords'=> $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];

		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
    

}