<?php

class Employees extends Connection
{

	private $table = 'employees';

	public function searchEmployee($shopId, $search, $accountsOnly = false)
	{
		$accountCond = '';
		if ($accountsOnly) {
			$accountCond = 'and account_id > 0';
		}
		$stmt = "SELECT * FROM `{$this->table}`  WHERE status=1 $accountCond and shop_id=:shop_id AND (full_name LIKE '%" . $search . "%' OR title LIKE '%" . $search . "%' OR company LIKE '%" . $search . "%' OR code LIKE '" . $search . "%' OR contact_1 LIKE '%" . $search . "%') LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':shop_id', $shopId, PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	public function createEmployee($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table}` (`shop_id`, `owner_id`,`full_name`, `email`, `designation`, `doj`, `contact_1`, `contact_2`, `emg_contact_1`, `emg_contact_2`, `salary`, `account_id`) VALUES (:shop_id, :owner_id, :full_name, :email, :designation, :doj, :contact_1, :contact_2, :emg_contact_1, :emg_contact_2, :salary, :account_id)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shop_id', $array['shop_id'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':email', $array['email'], PDO::PARAM_STR);
			$prepare->bindParam(':designation', $array['designation'], PDO::PARAM_STR);
			$prepare->bindParam(':doj', $array['doj'], PDO::PARAM_STR);
			$prepare->bindParam(':contact_1', $array['contact_1'], PDO::PARAM_STR);
			$prepare->bindParam(':contact_2', $array['contact_2'], PDO::PARAM_STR);
			$prepare->bindParam(':emg_contact_1', $array['emg_contact_1'], PDO::PARAM_STR);
			$prepare->bindParam(':emg_contact_2', $array['emg_contact_2'], PDO::PARAM_STR);
			$prepare->bindParam(':salary', $array['salary'], PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
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

	public function update($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET full_name=:full_name, email=:email, designation=:designation, doj=:doj, contact_1=:contact_1, contact_2=:contact_2, emg_contact_1=:emg_contact_1, emg_contact_2=:emg_contact_2, salary=:salary WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':email', $array['email'], PDO::PARAM_STR);
			$prepare->bindParam(':designation', $array['designation'], PDO::PARAM_STR);
			$prepare->bindParam(':doj', $array['doj'], PDO::PARAM_STR);
			$prepare->bindParam(':contact_1', $array['contact_1'], PDO::PARAM_STR);
			$prepare->bindParam(':contact_2', $array['contact_2'], PDO::PARAM_STR);
			$prepare->bindParam(':emg_contact_1', $array['emg_contact_1'], PDO::PARAM_STR);
			$prepare->bindParam(':emg_contact_2', $array['emg_contact_2'], PDO::PARAM_STR);
			$prepare->bindParam(':salary', $array['salary'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
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
	public function getEmployees($shopId = null)
	{
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `shop_id`=:shop_id and status=1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shop_id', $shopId, PDO::PARAM_STR);
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
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `account_id`=:id and status=1";
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
	public function getEmployee($id)
	{
		try {
			$stmt = "SELECT *  FROM `{$this->table}` WHERE `id`=:id and status=1";
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

	public function getEmployeesPagination($params)
	{
		try {

			$stmt = "SELECT COUNT(id) as total FROM `{$this->table}` where shop_id=:shop_id and status=1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shop_id', $params['shopId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);

			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
			$search = " AND (full_name LIKE '%" . $params["search"] . "%' OR contact_1 LIKE '%" . $params["search"] . "%' OR designation LIKE '%" . $params["search"] . "%' ) ";
			$stmt = "SELECT * FROM `{$this->table}` WHERE shop_id=:shop_id and status=1 $search LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->bindParam(':shop_id', $params['shopId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
