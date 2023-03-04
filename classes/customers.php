<?php

class Customers extends Connection
{
    
    private $table = 'customers';
    private $table_discount = 'customer_discount';
    private $table_publisher = 'publishers';
    
	public function searchCustomer($shopId, $search, $accountsOnly = false) {
		$accountCond = '';
		if($accountsOnly) {
			$accountCond = 'and account_id > 0';
		}
		$stmt = "SELECT * FROM `{$this->table}`  WHERE flag=1 $accountCond and shopId=:shopId AND (full_name LIKE '%".$search."%' OR title LIKE '%".$search."%' OR company LIKE '%".$search."%' OR code LIKE '".$search."%' OR phoneNumber LIKE '%".$search."%') LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $key => $c) {
			$result[$key]['discount_array'] = $this->getCustomerDiscounts(['customer_id'=> $c['id'], 'shopId'=> $c['shopId']]);
		}
		return $result;		
	}
	
	public function createCustomer($array) {
		try {
			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `address`,`type`, `company`, `title`, `phoneNumber`, `shopId`, `account_id`, `code`) VALUES (:full_name, :address, :type, :company, :title, :phoneNumber, :shopId, :account_id, :code)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
            $prepare->bindParam(':phoneNumber',$array['phoneNumber'],PDO::PARAM_STR);
            $prepare->bindParam(':company',$array['company'],PDO::PARAM_STR);
            $prepare->bindParam(':title',$array['title'],PDO::PARAM_STR);
            $prepare->bindParam(':address',$array['address'],PDO::PARAM_STR);
            $prepare->bindParam(':type',$array['type'],PDO::PARAM_INT);
            $prepare->bindParam(':shopId',$array['shopId'],PDO::PARAM_INT);
            $prepare->bindParam(':account_id',$array['account_id'],PDO::PARAM_INT);
            $prepare->bindParam(':code',$array['code'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function linkAccountCustomer($array) {
		try {
			$stmt = "UPDATE `{$this->table}` SET account_id=:account_id WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
            $prepare->bindParam(':account_id',$array['account_id'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function updateCustomer($array) {
		try {
			$stmt = "UPDATE `{$this->table}` SET full_name=:full_name, address=:address, phoneNumber=:phoneNumber, company=:company, title=:title, code=:code WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_STR);
            $prepare->bindParam(':code',$array['code'],PDO::PARAM_STR);
            $prepare->bindParam(':phoneNumber',$array['phoneNumber'],PDO::PARAM_STR);
            $prepare->bindParam(':company',$array['company'],PDO::PARAM_STR);
            $prepare->bindParam(':title',$array['title'],PDO::PARAM_STR);
            $prepare->bindParam(':address',$array['address'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteCustomer($array) {
		try {
			$stmt = "UPDATE `{$this->table}` SET flag=0 WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getCustomers($shopId = null) {
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `shopId`=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteCustomerDiscounts($array) {
		try {
			$stmt = "DELETE FROM `{$this->table_discount}` WHERE customer_id=:customer_id and shopId=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':customer_id',$array['customer_id'],PDO::PARAM_INT);
			$prepare->bindParam(':shopId',$array['shopId'],PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createCustomerDiscounts($array) {
		try {
			$stmt = "INSERT INTO `{$this->table_discount}` (`user_id`, `shopId`, `customer_id`, `publisher_id`, `discount_type`, `discount_value`) VALUES (:user_id, :shopId, :customer_id, :publisher_id, :discount_type, :discount_value)";
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':user_id',$array['user_id'],PDO::PARAM_STR);
            $prepare->bindParam(':shopId',$array['shopId'],PDO::PARAM_STR);
            $prepare->bindParam(':customer_id',$array['customer_id'],PDO::PARAM_STR);
            $prepare->bindParam(':publisher_id',$array['publisher_id'],PDO::PARAM_STR);
            $prepare->bindParam(':discount_type',$array['discount_type'],PDO::PARAM_STR);
            $prepare->bindParam(':discount_value',$array['discount_value'],PDO::PARAM_STR);
            $prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getCustomerDiscounts($arr) {
		try {
			$shopId = $arr['shopId'];
			$customer_id = $arr['customer_id'];
			$stmt = "SELECT d.*, d.publisher_id as id, p.full_name  FROM `{$this->table_discount}` as d left join `{$this->table_publisher}` as p on d.publisher_id=p.id WHERE customer_id=:customer_id and `shopId`=:shopId";
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

	public function getUserByAccount($id) {
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `account_id`=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			$de = new DoubleEntry();
			if(!empty($result['account_id'])) {
				$result['account']=$de->getAccount($result['account_id']);
			}
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getCustomer($id) {
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `id`=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			$de = new DoubleEntry();
			if(!empty($result['account_id'])) {
				$result['account']=$de->getAccount($result['account_id']);
			}
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function getCustomersPagination($params) {
		try {
			
			$stmt = "SELECT COUNT(id) as total FROM `{$this->table}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			
			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage-1) < 0 ? 0 : ($currentPage-1)) * $no_of_records_per_page;
			$search = " AND (full_name LIKE '%".$params["search"]."%' OR phoneNumber LIKE '%".$params["search"]."%' OR address LIKE '%".$params["search"]."%' ) ";
			$stmt = "SELECT * FROM `{$this->table}` WHERE shopId=:shopId $search LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':offset',$offset,PDO::PARAM_INT);
			$prepare->bindParam(':perPage',$no_of_records_per_page,PDO::PARAM_INT);
			$prepare->bindParam(':shopId',$params['shopId'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords'=> $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];

		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

}