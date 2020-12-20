<?php

class Expenses extends Connection
{
    
    private $table = 'expenses';

    public function getShopExpenses($shop_id, $date, $to = null) {
		try {
			
			$toCondition = "";
            if(!empty($to)) {
                $toCondition .= " AND exp_date>='".$date."' AND exp_date<='".$to."'";
            }
            else {
                $toCondition .=" AND exp_date>='".$date."'";
			}
			

			$stmt = "SELECT * FROM `{$this->table}` WHERE `shop_id`=:shop_id $toCondition ";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shop_id',$shop_id,PDO::PARAM_STR);
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
	public function createExpense($array) {
		try {
			$stmt = "INSERT INTO `{$this->table}` (`title`,`cat_id`,`price`,`description`, `details`,`exp_date`,`shop_id`) VALUES (:title,:cat_id,:price,:description, :details, :exp_date, :shop_id)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':title',$array['title'],PDO::PARAM_STR);
            $prepare->bindParam(':cat_id',$array['cat_id'],PDO::PARAM_INT);
            $prepare->bindParam(':price',$array['price'],PDO::PARAM_INT);
            $prepare->bindParam(':description',$array['description'],PDO::PARAM_STR);
            $prepare->bindParam(':details',$array['details'],PDO::PARAM_STR);
            $prepare->bindParam(':exp_date',$array['exp_date'],PDO::PARAM_STR);
            $prepare->bindParam(':shop_id',$array['shop_id'],PDO::PARAM_INT);
            $prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function deleteExpense($array) {
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