<?php

class Store extends Connection
{
    
    private $table = 'store';
    private $table_st = 'store_type';

    public function getOwnerStores($userId) {
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `owner_id`=:userId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':userId',$userId,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
    public function getStore($id) {
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getOwnerStore($id, $owner_id) {
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id AND owner_id = :owner_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
            $prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
    public function updateStore($array) {
		try {
			$stmt = "UPDATE `{$this->table}` SET full_name=:full_name, store_type=:store_type,status=:status, location=:location, city=:city, postalCode=:postalCode, phoneNumber1=:phoneNumber1, phoneNumber2=:phoneNumber2, phoneNumber3=:phoneNumber3, image=:image WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
            $prepare->bindParam(':store_type',$array['store_type'],PDO::PARAM_STR);
            $prepare->bindParam(':status',$array['status'],PDO::PARAM_STR);
            $prepare->bindParam(':location',$array['location'],PDO::PARAM_STR);
            $prepare->bindParam(':city',$array['city'],PDO::PARAM_STR);
            $prepare->bindParam(':postalCode',$array['postalCode'],PDO::PARAM_STR);
            $prepare->bindParam(':phoneNumber1',$array['phoneNumber1'],PDO::PARAM_STR);
            $prepare->bindParam(':phoneNumber2',$array['phoneNumber2'],PDO::PARAM_STR);
            $prepare->bindParam(':phoneNumber3',$array['phoneNumber3'],PDO::PARAM_STR);
            $prepare->bindParam(':image',$array['image'],PDO::PARAM_STR);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

    public function updateAccounts($id, $array) {
		
		try {
			$stmt = "UPDATE `{$this->table}` SET cash=:cash, payable=:payable, receiving=:receiving, receivable=:receivable, expense=:expense, sale_discount=:sale_discount, purchase_discount=:purchase_discount, sale_returns=:sale_returns, purchase_returns=:purchase_returns, assets=:assets WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':cash',$array['cash'],PDO::PARAM_STR);
            $prepare->bindParam(':payable',$array['payable'],PDO::PARAM_STR);
            $prepare->bindParam(':receiving',$array['receiving'],PDO::PARAM_STR);
            $prepare->bindParam(':receivable',$array['receivable'],PDO::PARAM_STR);
            $prepare->bindParam(':expense',$array['expense'],PDO::PARAM_STR);
            $prepare->bindParam(':sale_discount',$array['sale_discount'],PDO::PARAM_STR);
            $prepare->bindParam(':purchase_discount',$array['purchase_discount'],PDO::PARAM_STR);
            $prepare->bindParam(':sale_returns',$array['sale_returns'],PDO::PARAM_STR);
            $prepare->bindParam(':purchase_returns',$array['purchase_returns'],PDO::PARAM_STR);
            $prepare->bindParam(':assets',$array['assets'],PDO::PARAM_STR);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function closeStoreSale($id, $date) {
		try {
			$stmt = "UPDATE `{$this->table}` SET sale_date=:sale_date WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':sale_date',$date,PDO::PARAM_STR);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function enableStoreSale($id, $sale_date_show) {
		try {
			$stmt = "UPDATE `{$this->table}` SET sale_date_show=:sale_date_show WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':sale_date_show',$sale_date_show,PDO::PARAM_STR);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

    public function getStoreTypes() {
		try {
			$stmt = "SELECT * FROM `{$this->table_st}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}