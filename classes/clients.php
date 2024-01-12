<?php

class Clients extends Connection
{

	private $table = 'clients';

	public function getClient($id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getClientByOwnerId($owner_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE owner_id=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function updateClient($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET product_title=:product_title, tag_line=:tag_line,address=:address, phone_1=:phone_1, phone_2=:phone_2, phone_3=:phone_3 WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':product_title', $array['product_title'], PDO::PARAM_STR);
			$prepare->bindParam(':tag_line', $array['tag_line'], PDO::PARAM_STR);
			$prepare->bindParam(':address', $array['address'], PDO::PARAM_STR);
			$prepare->bindParam(':phone_1', $array['phone_1'], PDO::PARAM_STR);
			$prepare->bindParam(':phone_2', $array['phone_2'], PDO::PARAM_STR);
			$prepare->bindParam(':phone_3', $array['phone_3'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function createClient($array)
	{
		try {
			$shopId = 0;
			$stmt = "INSERT INTO `{$this->table}` (product_title, tag_line, start_date, end_date, address, phone_1, phone_2, phone_3, phone_4, image, owner_id, shopId) VALUES (:product_title, :tag_line, :start_date, :end_date, :address, :phone_1, :phone_2, :phone_3, :phone_4, :image, :owner_id, :shopId)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':product_title', $array['product_title'], PDO::PARAM_STR);
			$prepare->bindParam(':tag_line', $array['tag_line'], PDO::PARAM_STR);
			$prepare->bindParam(':start_date', $array['start_date'], PDO::PARAM_STR);
			$prepare->bindParam(':end_date', $array['end_date'], PDO::PARAM_STR);
			$prepare->bindParam(':address', $array['address'], PDO::PARAM_STR);
			$prepare->bindParam(':phone_1', $array['phone_1'], PDO::PARAM_STR);
			$prepare->bindParam(':phone_2', $array['phone_2'], PDO::PARAM_STR);
			$prepare->bindParam(':phone_3', $array['phone_3'], PDO::PARAM_STR);
			$prepare->bindParam(':phone_4', $array['phone_4'], PDO::PARAM_STR);
			$prepare->bindParam(':image', $array['image'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getClients()
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
