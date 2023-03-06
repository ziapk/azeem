<?php

class Orders extends Connection
{
    
    private $table = 'orders';
    private $table_sub = 'order_items';
    private $table_pro = 'products';
    private $table_rp = 'product_returns';
    private $table_transaction = 'transaction';
    private $table_customers = 'customers';
    private $table_ro = 'return_orders';
    
	public function searchCustomer($shopId, $search) {
		$stmt = "SELECT * FROM `{$this->table}`  WHERE shopId=:shopId AND (full_name LIKE '".$search."%' OR code LIKE '".$search."%' OR phoneNumber LIKE '".$search."%') LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
    }

    public function updateOrderAdjustment ($array) {
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
            if(!empty($array['id'])) {
                $stmt = "UPDATE `{$this->table}` SET `user_id`=:user_id, `customer_id`=:customer_id, `status`=:status, `price`=:price, `paid_amount`=:paid_amount, `discount`=:discount, `shopId`=:shopId, `order_date`=:order_date, `gst`=:gst, `service_charges`=:service_charges, `summery`=:summery, `ref_no`=:ref_no WHERE id=:id";
                $prepare = $this->dbh->prepare($stmt);        
                $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
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
                $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
                $prepare->execute();
                $result = $prepare->rowCount();
                return $result;
            } else {
                $stmt = "INSERT INTO `{$this->table}` (`user_id`, `customer_id`, `status`, `price`, `paid_amount`, `discount`, `shopId`, `order_date`, `gst`, `service_charges`, `summery`, `ref_no`) VALUES (:user_id, :customer_id, :status, :price, :paid_amount, :discount, :shopId, :order_date, :gst, :service_charges, :summery, :ref_no)";
                $prepare = $this->dbh->prepare($stmt);        
                $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
                $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
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
                $prepare->execute();
                $result = $this->dbh->lastInsertId();
                return $result;
            }
        } catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }
    
    public function orderReturnAll($array, $action, $type = 1)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_rp}` (`user_id`, `shopId`, `order_id`, `product_id`, `quantity`, `type`) VALUES (:user_id, :shopId, :order_id, :product_id, :quantity, :type)";
            $prepare = $this->dbh->prepare($stmt);        
            $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->bindParam(':order_id', $array['order_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':type', $array['type'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            $products = new Products();
            $products->addProductQty($array['product_id'], $array['quantity'], $array['shopId'], $type);
            // $this->orderReturn($array['order_id'], ($action+4));
            return $result;
        } catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }

    public function addColumn($columnName) {
        try {
            $stmt = "ALTER TABLE `{$this->table}` ADD COLUMN IF NOT EXISTS `{$columnName}` varchar(20) NULL DEFAULT NULL AFTER `reference`";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->execute();
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

    public function createOrderDetails($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_sub}` (`order_id`, `product_id`, `quantity`, `price`, `discount`) VALUES (:order_id, :product_id, :quantity, :price, :discount)";
            $prepare = $this->dbh->prepare($stmt);        
            $prepare->bindParam(':order_id', $array['order_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            if($array['status'] != 1) {
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

    public function getOrder($id) {
        try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id ";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
            $result['order'] = $prepare->fetch(PDO::FETCH_ASSOC);
            if(!empty($result['order'])) {
                $stmt = "SELECT item.*, p.full_name AS product_title FROM `{$this->table_sub}` AS item LEFT JOIN products AS p ON item.product_id = p.id WHERE item.order_id=:id";
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

    public function getCustomerById($id, $shopId) {
        try {
			$stmt = "SELECT * FROM `{$this->table_customers}` WHERE id=:id AND shopId = :shopId";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
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
    
    public function ordersReport($shopId, $date, $to) {
        try {

            $toCondition = " AND o.order_date>='".$date."' AND o.order_date<='".$to."'";
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
    
    public function inventoryReturnReport($shopId, $date, $to, $type) {
        try {
            $toCondition = " AND rp.datetime>='".$date."' AND rp.datetime<='".$to."'";
			$stmt = "SELECT sum(rp.quantity) AS quantity, sum(rp.quantity * oi.price) AS total, p.full_name AS product_name, oi.price FROM `{$this->table}` AS o LEFT JOIN `{$this->table_rp}` AS rp on rp.order_id = o.id LEFT JOIN `{$this->table_pro}` AS p on rp.product_id = p.id LEFT JOIN `{$this->table_sub}` AS oi on oi.order_id = o.id  WHERE o.shopId=:shopId and oi.product_id=rp.product_id ".$toCondition.' and o.flag = 2 and rp.type =:type group by rp.product_id ORDER BY rp.id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
            $prepare->bindParam(':type',$type,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }
    
    public function returnToHeadoffice($id) {
        try {
            $stmt = "UPDATE `{$this->table_rp}` SET type=3 WHERE id=:id AND type=2";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
            return $result;
		} catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
		}
    }
    
    public function getReturnRecord($id) {
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

    public function getFaultyReturnProducts($ids) {
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
    
    public function getReturnRecords() {
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
    
    public function getFaultyProductsById($array) {
        try {
            $ids = implode(',', $array);
            $stmt = "SELECT id FROM `{$this->table_rp}` WHERE type=2 AND product_id IN (".$ids.")";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }

    public function getFaultyProducts() {
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

    public function makeReturn($full_name, $ids) {
        $idJson = json_encode($ids);

        try {
            $stmt = "INSERT INTO `{$this->table_ro}` (`full_name`, `ids`) VALUES (:full_name, :ids)";
            $prepare = $this->dbh->prepare($stmt);        
            $prepare->bindParam(':full_name', $full_name, PDO::PARAM_STR);
            $prepare->bindParam(':ids', $idJson, PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            foreach ($ids as $id) {
                $this->returnToHeadoffice($id);
            }
            return $result;
        } catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }

    
    public function ordersReportSummery($shopId, $date, $to) {
        try {

            $toCondition = " AND o.order_date>='".$date."' AND o.order_date<='".$to."'";
			$stmt = "SELECT count(id) AS total, SUM(price) AS gross, SUM(discount) AS dist, SUM(paid_amount) AS paid, SUM(`price` - `discount` - `paid_amount`) AS balance FROM `{$this->table}` AS o WHERE o.shopId=:shopId ".$toCondition.' and o.flag = 1 ORDER BY id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }
    
    public function ordersReportProductWise($shopId, $date, $to) {
        try {

            $summery = $this->ordersReportSummery($shopId, $date, $to);
            
            $toCondition = " AND o.order_date>='".$date."' AND o.order_date<='".$to."'";
			$stmt = "SELECT oi.product_id, oi.price AS price, sum(oi.quantity) AS quantity, p.full_name  FROM `{$this->table}` AS o LEFT JOIN `{$this->table_sub}` AS oi ON oi.order_id=o.id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id  WHERE o.shopId=:shopId ".$toCondition.' and o.flag = 1 GROUP BY oi.product_id ORDER BY o.id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['summery' => $summery, 'rows'=> $result];
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }
    
    public function returnReportProductWise($shopId, $date, $to) {
        try {

            $toCondition = " AND o.order_date>='".$date."' AND o.order_date<='".$to."'";
			$stmt = "SELECT rp.product_id, oi.price, sum(rp.quantity) AS quantity, rp.type, p.full_name  FROM `{$this->table}` AS o LEFT JOIN `{$this->table_rp}` AS rp ON rp.order_id = o.id LEFT JOIN `{$this->table_sub}` AS oi ON o.id=oi.order_id LEFT JOIN `{$this->table_pro}` AS p on p.id=oi.product_id  WHERE o.shopId=:shopId and rp.product_id=oi.product_id ".$toCondition.' and o.flag = 2 and o.status IN (5,6,7) GROUP BY rp.product_id, rp.type ORDER BY o.id desc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }
    
    public function ordersReportDateWise($shopId, $date, $to) {
        try {

            $summery = $this->ordersReportSummery($shopId, $date, $to);
            
            $toCondition = " AND o.order_date>='".$date."' AND o.order_date<='".$to."'";
			$stmt = "SELECT o.order_date, sum(o.price) AS price, sum(o.discount) AS discount, sum(o.paid_amount) AS paid_amount, sum(o.price - o.discount - o.paid_amount) AS balance FROM `{$this->table}` AS o WHERE o.shopId=:shopId ".$toCondition.' and o.flag = 1 GROUP BY o.order_date ORDER BY o.id asc';
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['summery' => $summery, 'rows'=> $result];
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }
    
    public function getCustomerOrders($shopId, $customer_id) {
        try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE shopId=:shopId AND customer_id=:customer_id AND flag = 1 ORDER BY id desc";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
            $prepare->bindParam(':customer_id',$customer_id,PDO::PARAM_STR);
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

        if(!empty($customer)) {

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
        }
        else {
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