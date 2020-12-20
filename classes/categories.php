<?php

class Categories extends Connection
{
    
    private $table = 'category';

    public function getOwnerCategories($owner_id) {
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `owner_id`=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
    public function updateCategory($array) {
		try {
			$stmt = "UPDATE `{$this->table}` SET full_name=:full_name WHERE id=:id AND owner_id = :owner_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_STR);
            $prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function createCategory($array) {
		try {
			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `owner_id`) VALUES (:full_name, :owner_id)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
            $prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_STR);
            $prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function deleteCategory($array) {
		try {
			$stmt = "DELETE FROM `{$this->table}` WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}