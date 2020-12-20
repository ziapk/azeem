<?php

class Suppliers extends Connection
{
    
    private $table = 'suppliers';
    
	public function searchSupplier($search) {
		$stmt = "SELECT * FROM `{$this->table}`  WHERE (name LIKE '".$search."%' OR contact LIKE '".$search."%' OR address LIKE '".$search."%') LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
	}
	
	public function createSupplier($array) {
		try {
			print_r($array);
			$stmt = "INSERT INTO `{$this->table}` (`name`, `contact`, `address`,`wallet`) VALUES (:name, :contact, :address, :wallet)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':name',$array['name'],PDO::PARAM_STR);
            $prepare->bindParam(':contact',$array['contact'],PDO::PARAM_STR);
            $prepare->bindParam(':address',$array['address'],PDO::PARAM_STR);
            $prepare->bindParam(':wallet',$array['wallet'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteSupplier($array) {
		try {
			$stmt = "DELETE FROM `{$this->table}` WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getSuppliers($shopId = null) {
		try {
			$stmt = "SELECT *  FROM `{$this->table}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getSupplier($id) {
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
	public function getSuppliersPagination($params) {
		try {
			
			$stmt = "SELECT COUNT(id) as total FROM `{$this->table}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			
			$no_of_records_per_page = 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage-1) < 0 ? 0 : ($currentPage-1)) * $no_of_records_per_page;
			$search = "(name LIKE '%".$params["search"]."%' OR contact LIKE '%".$params["search"]."%' OR address LIKE '%".$params["search"]."%' ) ";
			$stmt = "SELECT * FROM `{$this->table}` WHERE $search LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':offset',$offset,PDO::PARAM_INT);
			$prepare->bindParam(':perPage',$no_of_records_per_page,PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords'=> $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];

		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}