<?php

class Customers extends Connection
{

	private $table = 'customers';
	private $table_discount = 'customer_discount';
	private $table_publisher = 'publishers';

	public function searchCustomer($shopId, $search, $accountsOnly = false)
	{
		$accountCond = '';
		if ($accountsOnly) {
			$accountCond = 'and account_id > 0';
		}
		$stmt = "SELECT * FROM `{$this->table}`  WHERE flag=1 $accountCond and shopId=:shopId AND (full_name LIKE '%" . $search . "%' OR title LIKE '%" . $search . "%' OR company LIKE '%" . $search . "%' OR code LIKE '" . $search . "%' OR phoneNumber LIKE '%" . $search . "%') LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $key => $c) {
			$result[$key]['discount_array'] = $this->getCustomerDiscounts(['customer_id' => $c['id'], 'shopId' => $c['shopId']]);
		}
		return $result;
	}

	public function createCustomer($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `address`,`type`, `company`, `email`, `title`, `phoneNumber`, `shopId`, `account_id`, `code`, `default_discount`) VALUES (:full_name, :address, :type, :company, :email, :title, :phoneNumber, :shopId, :account_id, :code, :default_discount)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber', $array['phoneNumber'], PDO::PARAM_STR);
			$prepare->bindParam(':company', $array['company'], PDO::PARAM_STR);
			$prepare->bindParam(':email', $array['email'], PDO::PARAM_STR);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':address', $array['address'], PDO::PARAM_STR);
			$prepare->bindParam(':type', $array['type'], PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_INT);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':default_discount', $array['default_discount'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function linkAccountCustomer($array)
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

	public function updateCustomer($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET full_name=:full_name, address=:address, phoneNumber=:phoneNumber, company=:company, email=:email, title=:title, code=:code, type=:type, default_discount=:default_discount WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber', $array['phoneNumber'], PDO::PARAM_STR);
			$prepare->bindParam(':company', $array['company'], PDO::PARAM_STR);
			$prepare->bindParam(':email', $array['email'], PDO::PARAM_STR);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':type', $array['type'], PDO::PARAM_STR);
			$prepare->bindParam(':address', $array['address'], PDO::PARAM_STR);
			$prepare->bindParam(':default_discount', $array['default_discount'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteCustomer($array)
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
	public function getCustomers($shopId = null)
	{
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `shopId`=:shopId and flag=1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteCustomerDiscounts($array)
	{
		try {
			$stmt = "DELETE FROM `{$this->table_discount}` WHERE customer_id=:customer_id and shopId=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createCustomerDiscounts($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table_discount}` (`user_id`, `shopId`, `customer_id`, `publisher_id`, `discount_type`, `discount_value`) VALUES (:user_id, :shopId, :customer_id, :publisher_id, :discount_type, :discount_value)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':customer_id', $array['customer_id'], PDO::PARAM_STR);
			$prepare->bindParam(':publisher_id', $array['publisher_id'], PDO::PARAM_STR);
			$prepare->bindParam(':discount_type', $array['discount_type'], PDO::PARAM_STR);
			$prepare->bindParam(':discount_value', $array['discount_value'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getCustomerDiscounts($arr)
	{
		try {
			$shopId = $arr['shopId'];
			$ownerId = $arr['ownerId'];
			$customer_id = $arr['customer_id'];
			$stmt = "SELECT d.*, d.publisher_id as id, p.full_name  FROM `{$this->table_discount}` as d left join `{$this->table_publisher}` as p on d.publisher_id=p.id WHERE customer_id=:customer_id and `shopId`=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);

			$stmt = "SELECT * FROM `{$this->table_publisher}` WHERE `owner_id`=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $ownerId, PDO::PARAM_STR);
			$prepare->execute();
			$result2 = $prepare->fetchAll(PDO::FETCH_ASSOC);

			$all = [];

			foreach ($result2 as $val) {
				$all[$val['id']] = [
					'id' => $val['id'],
					'publisher_id' => $val['id'],
					'full_name' => $val['full_name'],
					'discount_type' => $val['discount_type'],
					'discount_value' => $val['discount_amount'],
					'customer_id' => $customer_id,
					'status' => '1',
					'datetime' => date('Y-m-d H:i:s'),
				];
			}

			foreach ($result as $v) {
				$all[$v['id']]  = $v;
			}

			return array_values($all);
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getUserByAccount($id)
	{
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `account_id`=:id and flag=1";
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
	public function getCustomer($id)
	{
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `id`=:id and flag=1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			$de = new DoubleEntry();
			if (!empty($result['account_id'])) {
				$result['account'] = $de->getAccount($result['account_id']);
			}
			$result['discount_array'] = $this->getCustomerDiscounts(['customer_id' => $id, 'shopId' => $result['shopId']]);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getCustomersPagination($params)
	{
		try {

			$stmt2 = "SELECT COUNT(id) as total, GROUP_CONCAT(account_id) as ids FROM `{$this->table}` where shopId=:shopId and flag=1";
			$prepare2 = $this->dbh->prepare($stmt2);
			$prepare2->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare2->execute();
			$resultTotal = $prepare2->fetch(PDO::FETCH_ASSOC);

			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = $resultTotal['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
			$search = " AND (full_name LIKE '%" . $params["search"] . "%' OR phoneNumber LIKE '%" . $params["search"] . "%' OR address LIKE '%" . $params["search"] . "%' ) ";
			$stmt = "SELECT * FROM `{$this->table}` WHERE shopId=:shopId and flag=1 $search LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);

			$de = new DoubleEntry();

			// $ids = [];

			$closing = $de->getOpeningBalances(explode(',', $resultTotal['ids']), 'c');

			foreach ($result as $key => $customer) {
				if (!empty($customer['account_id'])) {
					$result[$key]['closing_balance'] = (!empty($closing[$customer['account_id']]['balance'])) ? $closing[$customer['account_id']]['balance'] : 0;
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
