<?php

class Orders extends Connection
{
    
    private $table = 'orders';
    private $table_sub = 'order_items';
    private $table_transaction = 'transaction';
    private $table_customers = 'customers';
    
	public function searchCustomer($shopId, $search) {
		$stmt = "SELECT * FROM `{$this->table}`  WHERE shopId=:shopId AND (full_name LIKE '".$search."%' OR code LIKE '".$search."%' OR phoneNumber LIKE '".$search."%') LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
    }

    public function createOrder($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table}` (`user_id`, `customer_id`, `status`, `price`, `discount`, `shopId`, `order_date`) VALUES (:user_id, :customer_id, :status, :price, :discount, :shopId, :order_date)";
            $prepare = $this->dbh->prepare($stmt);        
            $prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
            $prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
            $prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->bindParam(':discount', $array['discount'], PDO::PARAM_STR);
            $prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
            $prepare->bindParam(':order_date', $array['order_date'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
    }

    public function createOrderDetails($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table_sub}` (`order_id`, `product_id`, `quantity`, `price`) VALUES (:order_id, :product_id, :quantity, :price)";
            $prepare = $this->dbh->prepare($stmt);        
            $prepare->bindParam(':order_id', $array['order_id'], PDO::PARAM_STR);
            $prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
            $prepare->bindParam(':quantity', $array['quantity'], PDO::PARAM_STR);
            $prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            $prod = new Products();
            $prod->subProductQty($array['product_id'], $array['quantity']);
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

    public function getOrder($id) {
        try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
            $result['order'] = $prepare->fetch(PDO::FETCH_ASSOC);
            if(!empty($result['order'])) {
                $stmt = "SELECT item.*, p.full_name AS product_title FROM `{$this->table_sub}` as item LEFT JOIN products as p ON item.product_id = p.id WHERE item.order_id=:id";
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
    

}