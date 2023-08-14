<?php

class Suppliers extends Connection
{

	private $table = 'suppliers';

	public function searchSupplier($search, $shopId = null, $type = 1)
	{
		$shopCondition = "";
		if (!empty($shopId)) {
			$shopCondition = " and shopId=$shopId";
		}
		$stmt = "SELECT *, name as full_name FROM `{$this->table}`  WHERE flag=1 and type=:type and (name LIKE '" . $search . "%' OR contact LIKE '" . $search . "%' OR address LIKE '" . $search . "%') $shopCondition LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':type', $type, PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	public function createSupplier($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table}` (`name`, `contact`, `email`, `address`,`wallet`, `company`, `title`, `user_id`, `shopId`, `type`, `account_id`) VALUES (:name, :contact, :address, :wallet, :company, :title, :user_id, :shopId, :type, :account_id)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':name', $array['name'], PDO::PARAM_STR);
			$prepare->bindParam(':contact', $array['contact'], PDO::PARAM_STR);
			$prepare->bindParam(':email', $array['email'], PDO::PARAM_STR);
			$prepare->bindParam(':address', $array['address'], PDO::PARAM_STR);
			$prepare->bindParam(':wallet', $array['wallet'], PDO::PARAM_INT);
			$prepare->bindParam(':company', $array['company'], PDO::PARAM_STR);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':type', $array['type'], PDO::PARAM_INT);
			$prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function linkAccountSupplier($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET account_id=:account_id WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateSupplier($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET name=:name, contact=:contact, email=:email, address=:address, company=:company, title=:title WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':name', $array['name'], PDO::PARAM_STR);
			$prepare->bindParam(':contact', $array['contact'], PDO::PARAM_STR);
			$prepare->bindParam(':email', $array['email'], PDO::PARAM_STR);
			$prepare->bindParam(':address', $array['address'], PDO::PARAM_STR);
			$prepare->bindParam(':company', $array['company'], PDO::PARAM_STR);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteSupplier($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET flag=0 WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getSuppliers($array)
	{
		try {
			$stmt = "SELECT *  FROM `{$this->table}`where flag=1 and shopId=:shopId and type=:type";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':type', $array['type'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getSupplierOrders($shopId, $id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE flag=1 and shopId=:shopId AND id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getUserByAccount($id)
	{
		try {
			$stmt = "SELECT *, name as full_name  FROM `{$this->table}` WHERE flag=1 and `account_id`=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			$de = new DoubleEntry();
			if (!empty($result['account_id'])) {
				$result['account'] = $de->getAccount($result['account_id']);
			}
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getSupplier($id)
	{
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE flag=1 and `id`=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			$de = new DoubleEntry();
			if (!empty($result['account_id'])) {
				$result['account'] = $de->getAccount($result['account_id']);
			}
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getSuppliersPagination($params)
	{
		try {

			$stmt2 = "SELECT COUNT(id) as total, GROUP_CONCAT(account_id) as ids FROM `{$this->table}` where shopId=:shopId and type=:type and flag=1";
			$prepare2 = $this->dbh->prepare($stmt2);
			$prepare2->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare2->bindParam(':type', $params['type'], PDO::PARAM_INT);
			$prepare2->execute();
			$resultTotal = $prepare2->fetch(PDO::FETCH_ASSOC);

			$stmt = "SELECT COUNT(id) as total FROM `{$this->table}` where flag=1 and shopId=:shopId and type=:type";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':type', $params['type'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);

			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
			$search = "(name LIKE '%" . $params["search"] . "%' OR contact LIKE '%" . $params["search"] . "%' OR address LIKE '%" . $params["search"] . "%' ) ";
			$stmt = "SELECT * FROM `{$this->table}` WHERE $search and flag=1 and shopId=:shopId and type=:type LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':type', $params['type'], PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			$de = new DoubleEntry();

			$closing = $de->getOpeningBalances(explode(',', $resultTotal['ids']), 's');

			foreach ($result as $key => $supplier) {
				if (!empty($supplier['account_id'])) {
					$result[$key]['closing_balance'] = (!empty($closing[$supplier['account_id']]['balance'])) ? $closing[$supplier['account_id']]['balance'] : 0;
				}
			}

			$closingTotal = 0;
			foreach ($closing as $c) {
				$closingTotal += $c['balance'];
			}

			if (!function_exists("cmp2")) {
				function cmp2($a, $b)
				{
					return $b["closing_balance"] - $a["closing_balance"];
				}
			}

			usort($result, "cmp2");

			return ['page' => $currentPage, 'closing_total' => $closingTotal, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
