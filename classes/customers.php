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
}