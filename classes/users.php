<?php

class Users extends Connection
{
	private $table = 'users';
	private $table_clients = 'clients';
	private $table_shop = 'store';

	public function login($email, $password)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$password = $this->normalToPassword($password);
			$stmt = "SELECT * FROM `{$this->table}` WHERE `email`=:email AND `password`=:password";
			$prepare = $dbh->prepare($stmt);
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
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function refreshSession($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$sess = UserInfo();
			$sess['user']['shopId'] = $id;
			$sess['shop'] = $this->getShop($sess['user']);
			return $sess;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getUser($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `id`=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			if ($result) {
				$owner = $result['role'] === 'owner' ? $result['id'] : $result['created_by'];
				return $this->checkContract($owner, $result);
			}
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getUserSelected($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `id`=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getUsers()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE 1";
			$prepare = $dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}


	public function getShop($userInfo)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table_shop}` WHERE `id`=:shopId";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $userInfo['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function updateProfile($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET `full_name`=:full_name, `city`=:city, `cnic`=:cnic, `phoneNumber1`=:phoneNumber1, `phoneNumber2`=:phoneNumber2, `phoneNumber3`=:phoneNumber3, `photo`=:photo, `shopId`=:shopId, `role`=:role WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':city', $array['city'], PDO::PARAM_STR);
			$prepare->bindParam(':cnic', $array['cnic'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber1', $array['phoneNumber1'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber2', $array['phoneNumber2'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber3', $array['phoneNumber3'], PDO::PARAM_STR);
			$prepare->bindParam(':photo', $array['photo'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':role', $array['role'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function createProfile($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$password = $this->normalToPassword($array['password']);
			$stmt = "INSERT INTO `{$this->table}` (`password`,`full_name`,`city`,`cnic`,`phoneNumber1`,`phoneNumber2`,`phoneNumber3`,`photo`,`shopId`,`role`, `created_by`, `email`, `status`) values (:password, :full_name, :city, :cnic, :phoneNumber1, :phoneNumber2, :phoneNumber3, :photo, :shopId, :role, :created_by, :email, :status)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':city', $array['city'], PDO::PARAM_STR);
			$prepare->bindParam(':cnic', $array['cnic'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber1', $array['phoneNumber1'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber2', $array['phoneNumber2'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber3', $array['phoneNumber3'], PDO::PARAM_STR);
			$prepare->bindParam(':photo', $array['photo'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':role', $array['role'], PDO::PARAM_STR);
			$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
			$prepare->bindParam(':password', $password, PDO::PARAM_STR);
			$prepare->bindParam(':email', $array['email'], PDO::PARAM_STR);
			$prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function runQuery($stmt)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$prepare = $dbh->prepare($stmt);
			$dbh->beginTransaction();
			$prepare->execute();
			$dbh->commit();
			$update = $prepare->rowCount();
			$lastId = $dbh->lastInsertId();
			$rows = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['update' => $update, 'lastId' => $lastId, 'rows' => $rows];
		} catch (PDOException $e) {
			$dbh->rollback();
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}



	public function checkContract($owner_id, $userInfo)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table_clients}` WHERE `end_date` >= CURDATE() AND `owner_id`=:owner_id";
			$prepare = $dbh->prepare($stmt);
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
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
}
