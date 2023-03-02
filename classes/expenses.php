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
	public function getExpensesSummeryReport($groupName, $date, $to = null) {
		try {
			
			$toCondition = "";
			if(!empty($to)) {
				$toCondition .= " e.exp_date>='".$date."' AND e.exp_date<='".$to."'";
			}
			else {
				$toCondition .=" e.exp_date>='".$date."'";
			}
			if(!empty($groupName)) {
				$final = " AND c.groupName IN ('";

				$final .= implode("','", $groupName);

				$final .= "')";

			} else {
				$final = "";
			}

			$stmt = "SELECT c.groupName as details, exp_date, sum(e.price) as price, c.full_name as title FROM `{$this->table}` as e inner join `category` as c on c.id = e.cat_id WHERE $toCondition $final group by DATE(exp_date), cat_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getExpensesForReport($groupName, $date, $to = null) {
		try {
			
			$toCondition = "";
			if(!empty($to)) {
				$toCondition .= " AND e.exp_date>='".$date."' AND e.exp_date<='".$to."'";
			}
			else {
				$toCondition .=" AND e.exp_date>='".$date."'";
			}
			$final = "'";

			$final .= implode("','", $groupName);

			$final .= "'";

			$stmt = "SELECT e.* FROM `{$this->table}` as e inner join `category` as c on c.id = e.cat_id WHERE c.groupName IN ($final) $toCondition";
			
			$prepare = $this->dbh->prepare($stmt);
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

			$category = new Categories();
			$c = $category->getCategory($array['cat_id']);
			$doubleEntry = new DoubleEntry();
			$store = new Store();
			$storeDATA = $store->getStore($array['shop_id']);

			$makeTransaction = [
				'description' => $array['title'].' - '.$array['description'],
				'transaction_date' => $storeDATA['sale_date'],
				'reference' => 'EXP-'.$result,
				'shopId' => $array['shop_id'],
				'created_by' => $_SESSION['user_credentials']['id'],
				'order_ref' => null,
				'supply_ref' => null,
			];
	
			$makeTransactionId = $doubleEntry->makeTransaction($makeTransaction);

			$entry = [
				'transaction_id' => $makeTransactionId,
				'account_id' => $c['account_id'],
				'entry_type' => 'D',
				'description' => '',
				'amount' => $array['price'], // 2000
				'payment_mode'=> 1,
				'user_id' => $_SESSION['user_credentials']['id'],
			];
	
			$a[] = $doubleEntry->makeEntry($entry);
	
			$entry = [
				'transaction_id' => $makeTransactionId,
				'account_id' => $storeDATA['cash'],
				'entry_type' => 'C',
				'description' => '',
				'amount' => $array['price'], // 2000
				'payment_mode'=> 1,
				'user_id' => $_SESSION['user_credentials']['id'],
			];
	
			$a[] = $doubleEntry->makeEntry($entry);

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