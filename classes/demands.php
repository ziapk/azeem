<?php

class Demands extends Connection
{

	private $table = 'demands';
	private $table_sub = 'demand_items';
	private $table_pro = 'products';

	public function getOwnerDemands($owner_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `owner_id`=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getStoreDemands($shop_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `shop_id`=:shop_id and flag < 4";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getUserDemands($shop_id, $user_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `shop_id`=:shop_id and created_by=:user_id and flag < 4";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
			$prepare->bindParam(':user_id', $user_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getStoreDemand($id, $owner_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id AND owner_id = :owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getDemandDetail($id, $owner_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id AND owner_id = :owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			$result['items'] = $this->getDemandItems($id);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getDemandItems($id)
	{
		try {
			$stmt = "SELECT a.*, concat(b.id, ' | ', b.full_name) as full_name FROM `{$this->table_sub}` as a left join {$this->table_pro} as b on a.product_id=b.id WHERE demand_id = :demand_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':demand_id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getDemandsItems($ids)
	{
		try {
			$stmt = "SELECT a.*, concat(b.id, ' | ', b.full_name) as full_name FROM `{$this->table_sub}` as a left join {$this->table_pro} as b on a.product_id=b.id WHERE demand_id IN (" . (implode(', ', $ids)) . ")";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}


	public function checkDemandRequested($array)
	{

		$id = $array['id'];
		$owner_id = $array['owner_id'];

		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id AND owner_id = :owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function assignStoreQty($array, $shop_id, $owner_id)
	{

		$productObj = new Products();
		$dropoffShop = $productObj->getOwnerStoreProduct($shop_id, $array['product_id'], $owner_id);

		if (empty($dropoffShop)) {
			$data = [
				'qty' => 0,
				'stock_out' => 0,
				'product_id' => $array['product_id'],
				'shopId' => $shop_id,
				'owner_id' => $owner_id,
			];

			$productObj->assignProduct($data);

			$dropoffShop = $productObj->getOwnerStoreProduct($shop_id, $array['product_id'], $owner_id);
		}

		// pickup from stock
		$drop = [
			'qty' => $array['assign_qty'],
			'stock_out' => 0,
		];
		return $productObj->updateProductToStore($drop, $dropoffShop);
	}

	public function cancelDemand($array)
	{
		try {
			$assign_date = $array['assign_date'];
			$flag = $array['flag'];
			$id = $array['id'];
			$stmt = "UPDATE `{$this->table}` SET `assign_date`=:assign_date, `flag`=:flag WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':assign_date', $assign_date, PDO::PARAM_STR);
			$prepare->bindParam(':flag', $flag, PDO::PARAM_INT);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
		return 'update';
	}

	public function assignDemand($array)
	{
		try {
			$assign_date = $array['assign_date'];
			$flag = $array['flag'];
			$id = $array['id'];
			$stmt = "UPDATE `{$this->table}` SET `assign_date`=:assign_date, `flag`=:flag WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':assign_date', $assign_date, PDO::PARAM_STR);
			$prepare->bindParam(':flag', $flag, PDO::PARAM_INT);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			foreach ($array['items'] as $item) {
				$this->updateDemandItems($item, $array['shop_id'], $array['owner_id']);
			}
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
		return 'update';
	}

	public function updateDemandItems($array, $shop_id, $owner_id)
	{
		try {
			$assign_qty = $array['assign_qty'];
			$id = $array['id'];

			$stmt = "UPDATE `{$this->table_sub}` SET `product_assign_qty`=:assign_qty WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':assign_qty', $assign_qty, PDO::PARAM_INT);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			$this->assignStoreQty($array, $shop_id, $owner_id);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
		return 'update';
	}



	public function modifyDemand($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET `title`=:title, `demand_date`=:demand_date WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':demand_date', $array['demand_date'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			$this->deleteDemandItems($array['id']);
			foreach ($array['items'] as $value) {
				$data = [
					'product_id' => $value['id'],
					'product_qty' => $value['qty'],
					'demand_id' => $array['id']
				];
				$this->createDemandItems($data);
			}
			return $array['id'];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createDemand($array, $isOwner)
	{
		try {
			$stmt = "INSERT INTO `{$this->table}` (`title`, `demand_date`, `shop_id`, `owner_id`, `created_by`) VALUES (:demand_title, :demand_date, :shop_id, :owner_id, :created_by)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':demand_title', $array['demand_title'], PDO::PARAM_STR);
			$prepare->bindParam(':demand_date', $array['demand_date'], PDO::PARAM_STR);
			$prepare->bindParam(':shop_id', $array['shop_id'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
			$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
			$prepare->execute();
			$array['id'] = $result = $this->dbh->lastInsertId();
			if (!empty($result)) {
				foreach ($array['items'] as $k => $value) {
					$data = [
						'product_id' => $value['id'],
						'product_qty' => $value['qty'],
						'assign_qty' => $value['qty'],
						'demand_id' => $result
					];
					$array['items'][$k] = $data;
					$array['items'][$k]['id'] = $this->createDemandItems($data);
				}
			}
			if (!empty($isOwner)) {
				$array['assign_date'] = $array['demand_date'];
				$array['flag'] = 1;
				$updated = $this->assignDemand($array);
			}
			return $array;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function createDemandItems($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table_sub}` (`product_id`, `product_qty`, `demand_id`) VALUES (:product_id, :product_qty, :demand_id)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
			$prepare->bindParam(':product_qty', $array['product_qty'], PDO::PARAM_STR);
			$prepare->bindParam(':demand_id', $array['demand_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function deleteDemandItems($id)
	{
		try {
			$stmt = "DELETE FROM `{$this->table_sub}` WHERE demand_id=:demand_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':demand_id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
