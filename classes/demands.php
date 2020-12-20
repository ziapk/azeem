<?php

class Demands extends Connection
{
    
    private $table = 'demands';

    public function getOwnerDemands($owner_id) {
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `owner_id`=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function getStoreDemands($owner_id, $shopId) {
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `shopId`=:shopId and `owner_id`=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getStoreDemand($id, $owner_id) {
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
	public function checkDemandRequested($array) {
		
		$id = $array['id'];
		$owner_id = $array['owner_id'];

		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id AND owner_id = :owner_id AND flag = 0";
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


	public function assignDemand($array) {

		$res = ['status' => 503];

		$checkDemandRequested = $this->checkDemandRequested($array);


		


		if(empty($checkDemandRequested)) {
			$res['message'] = 'Invalid Values';
			return $res;
		}

		if($checkDemandRequested['shopId'] == $array['warehouse_id']) {
			$res['message'] = "Shop and warehouse should't be same";
			return $res;
		}

		$storeObj = new Store();
		$store = $storeObj->getOwnerStore($array['warehouse_id'], $array['owner_id']);

		if(empty($store)) {
			$res['message'] = 'Invalid Store';
			return $res;
		}


		$prodcutObj = new Products();
		$dropoffShop = $prodcutObj->getOwnerStoreProduct($checkDemandRequested['shopId'], $checkDemandRequested['product_id'], $array['owner_id']);
		$pickupShop = $prodcutObj->getOwnerStoreProduct($array['warehouse_id'], $checkDemandRequested['product_id'], $array['owner_id']);

		if(empty($pickupShop)) {

			$res['message'] = 'Product not available at warehouse';
			return $res;
		}

		if(empty($dropoffShop)) {
			$res['message'] = 'Invalid Product for select store';
			return $res;
		}


		$result = false;

		

		// now decide about action

		if($array['flag'] == 1) {
			// assign

			// prepare data

			// pickup from stock
			$pick = [
				'qty' => 0,
				'stock_out' => $array['assign_qty']
			];
			$prodcutObj->updateProductToStore($pick, $pickupShop);


			// pickup from stock
			$drop = [
				'qty' => $array['assign_qty'],
				'stock_out' => 0,
			];
			$prodcutObj->updateProductToStore($drop, $dropoffShop);

			$result = $this->updateDemondData($array);
		}
		else if($array['flag'] == 2) {
			$result = $this->updateDemondData($array);
		}

		if($result) {
			$res['status'] = 200;
			$res['message'] = 'Successfully Done!';
		}
		else {
			$res['status'] = 200;
			$res['message'] = 'Nothing changed!';
		}

		return $result;

	}

	public function updateDemondData($array) {
		try {
			$assign_qty = $array['assign_qty'];
			$assign_date = $array['assign_date'];
			$warehouse_id = $array['warehouse_id'];
			$flag = $array['flag'];
			$id = $array['id'];

			$stmt = "UPDATE `{$this->table}` SET `assign_qty`=:assign_qty, `assign_date`=:assign_date, `warehouse_id`=:warehouse_id, `flag`=:flag WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':assign_qty',$assign_qty,PDO::PARAM_INT);
            $prepare->bindParam(':assign_date',$assign_date,PDO::PARAM_INT);
            $prepare->bindParam(':warehouse_id',$warehouse_id,PDO::PARAM_INT);
            $prepare->bindParam(':flag',$flag,PDO::PARAM_INT);
            $prepare->bindParam(':id',$id,PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
		return 'update';
	}

	

	public function createDemand($array) {
		try {
			$stmt = "INSERT INTO `{$this->table}` (`product_id`, `demand_date`, `shopId`, `owner_id`, `demand_qty`) VALUES (:product_id, :demand_date, :shopId, :owner_id, :demand_qty)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':product_id',$array['product_id'],PDO::PARAM_STR);
            $prepare->bindParam(':demand_date',$array['demand_date'],PDO::PARAM_STR);
            $prepare->bindParam(':shopId',$array['shopId'],PDO::PARAM_STR);
            $prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_STR);
            $prepare->bindParam(':demand_qty',$array['demand_qty'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}