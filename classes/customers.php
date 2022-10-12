<?php

class Customers extends Connection
{
    
    private $table = 'customers';
    
	public function searchCustomer($shopId, $search) {
		$stmt = "SELECT * FROM `{$this->table}`  WHERE shopId=:shopId AND (full_name LIKE '".$search."%' OR code LIKE '".$search."%' OR phoneNumber LIKE '".$search."%') LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
	}
	
	public function createCustomer($array) {
		try {
			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `address`,`type`,`phoneNumber`, `shopId`) VALUES (:full_name, :address, :type, :phoneNumber, :shopId)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
            $prepare->bindParam(':phoneNumber',$array['phoneNumber'],PDO::PARAM_STR);
            $prepare->bindParam(':address',$array['address'],PDO::PARAM_STR);
            $prepare->bindParam(':type',$array['type'],PDO::PARAM_INT);
            $prepare->bindParam(':shopId',$array['shopId'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateCustomer($array) {
		try {
			$stmt = "UPDATE `{$this->table}` SET full_name=:full_name, address=:address, phoneNumber=:phoneNumber, code=:code WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_STR);
            $prepare->bindParam(':code',$array['code'],PDO::PARAM_STR);
            $prepare->bindParam(':phoneNumber',$array['phoneNumber'],PDO::PARAM_STR);
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
			$stmt = "DELETE FROM `{$this->table}` WHERE id=:id";
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

	public function getCustomer($id) {
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `id`=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
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