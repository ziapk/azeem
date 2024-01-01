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
