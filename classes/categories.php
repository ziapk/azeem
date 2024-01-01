<?php

class Categories extends Connection
{

	private $table = 'category';
	private $table_accounts = 'accounts';

	public function getOwnerCategories($owner_id)
	{
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];

			$stmt = "SELECT * FROM `{$this->table}` WHERE flag=1 and `owner_id`=:owner_id and shopId=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getGroupNames($owner_id)
	{
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];

			$stmt = "SELECT DISTINCT groupName FROM `{$this->table}` WHERE flag=1 and `owner_id`=:owner_id and shopId=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function updateCategory($array)
	{
		try {
			$img = "";
			if (!empty($array['image'])) {
				$img = ", image=:image";
			}
			print_r($array);
			$stmt = "UPDATE `{$this->table}` SET full_name=:full_name, groupName=:groupName, cat_type=:cat_type $img WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->bindParam(':cat_type', $array['cat_type'], PDO::PARAM_STR);
			$prepare->bindParam(':groupName', $array['groupName'], PDO::PARAM_STR);
			if (!empty($img)) {
				$prepare->bindParam(':image', $array['image'], PDO::PARAM_STR);
			}
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function deleteCategory($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET flag=0 where id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function linkAccount($array)
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

	public function getCategoriesPagination($params)
	{
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];
			$stmt = "SELECT COUNT(id) as total FROM `{$this->table}` where flag=1 and `owner_id`=:owner_id and shopId=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $params['owner_id'], PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);

			$no_of_records_per_page = $params['perPage'] ? $params['perPage'] : 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
			$search = "(c.full_name LIKE '%" . $params["search"] . "%' or c.groupName LIKE '%" . $params["search"] . "%') ";
			$stmt = "SELECT c.*, a.title, a.code, a.opening_balance FROM `{$this->table}` as c left join `{$this->table_accounts}` as a on c.`account_id`=a.id WHERE flag=1 and $search and `owner_id`=:owner_id and c.shopId=:shopId LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':owner_id', $params['owner_id'], PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getCategories($type, $owner_id)
	{
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];

			$where = "";
			if ($type == 'pro') {
				$where = 'and flag=1 and cat_type = 2';
			};
			if ($type == 'exp') {
				$where = 'and flag=1 and cat_type = 1';
			}
			$stmt = "SELECT * FROM `{$this->table}` where owner_id=:owner_id and shopId=:shopId " . $where;
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getCategory($id)
	{
		try {

			$stmt = "SELECT * FROM `{$this->table}` where flag=1 and id = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function expenseByAccount($id)
	{
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];

			$stmt = "SELECT * FROM `{$this->table}` where flag=1 and account_id=:id and shopId=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function createCategory($array)
	{
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];

			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `cat_type`, `groupName`, `owner_id`, `shopId`, `account_id`, `image`) VALUES (:full_name, :cat_type, :groupName, :owner_id, :shopId, :account_id, :image)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':cat_type', $array['cat_type'], PDO::PARAM_STR);
			$prepare->bindParam(':groupName', $array['groupName'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_STR);
			$prepare->bindParam(':image', $array['image'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
