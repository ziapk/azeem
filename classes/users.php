<?php

class Users extends Connection
{
	private $table = 'users';
	private $table_clients = 'clients';
	private $table_shop = 'store';

	public function login($email, $password)
	{
		try {
			$password = $this->normalToPassword($password);
			$stmt = "SELECT * FROM `{$this->table}` WHERE `email`=:email AND `password`=:password";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':email', $email, PDO::PARAM_STR);
			$prepare->bindParam(':password', $password, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			if ($result) {
				$owner = $result['role'] === 'owner' ? $result['id'] : $result['created_by'];
				return $this->checkContract($owner, $result);
			}
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getUser($id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `id`=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			if ($result) {
				$owner = $result['role'] === 'owner' ? $result['id'] : $result['created_by'];
				return $this->checkContract($owner, $result);
			}
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getUserSelected($id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `id`=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getUsers()
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE 1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}


	public function getShop($userInfo)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table_shop}` WHERE `id`=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $userInfo['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateProfile($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET `full_name`=:full_name, `city`=:city, `cnic`=:cnic, `phoneNumber1`=:phoneNumber1, `phoneNumber2`=:phoneNumber2, `phoneNumber3`=:phoneNumber3, `photo`=:photo WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':city', $array['city'], PDO::PARAM_STR);
			$prepare->bindParam(':cnic', $array['cnic'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber1', $array['phoneNumber1'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber2', $array['phoneNumber2'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber3', $array['phoneNumber3'], PDO::PARAM_STR);
			$prepare->bindParam(':photo', $array['photo'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function runQuery($stmt)
	{
		try {
			$prepare = $this->dbh->prepare($stmt);
			$this->dbh->beginTransaction();
			$prepare->execute();
			$this->dbh->commit();
			$update = $prepare->rowCount();
			$lastId = $this->dbh->lastInsertId();
			$rows = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['update' => $update, 'lastId' => $lastId, 'rows' => $rows];
		} catch (PDOException $e) {
			$this->dbh->rollback();
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}



	public function checkContract($owner_id, $userInfo)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table_clients}` WHERE `end_date` >= CURDATE() AND `owner_id`=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			if ($result || $userInfo['role'] === 'superadmin') {
				return ['user' => $userInfo, 'shopInfo' => $result, 'shop' => $this->getShop($userInfo)];
			} else {
				return 'Contact expired';
			}
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
