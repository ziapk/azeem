<?php

class Users extends Connection
{
	private $table = 'users';
	private $table_clients = 'clients';
	
    public function login($email, $password) {
		try {
            $password = $this->normalToPassword($password);
			$stmt = "SELECT * FROM `{$this->table}` WHERE `email`=:email AND `password`=:password";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':email',$email,PDO::PARAM_STR);
			$prepare->bindParam(':password',$password, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			if($result) {
				return $this->checkContract($result);
			}
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function checkContract($userInfo) {
		try {
			$stmt = "SELECT * FROM `{$this->table_clients}` WHERE `end_date` >= CURDATE() AND `shopId`=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId',$userInfo['shopId'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			if($result) {
				return ['user' => $userInfo, 'shopInfo' => $result];				
			}
			else {
				return 'Contact expired';
			}
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}