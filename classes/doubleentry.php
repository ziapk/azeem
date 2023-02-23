<?php 

/**
* 
*/
class DoubleEntry extends Connection
{
	private $table = 'accounts';
	private $table_types = 'account_types';
	private $table_units = 'units';
	private $table_suppliers = 'suppliers';
	private $table_transactions = 'transactions';
	private $table_ledger_entries = 'ledger_entries';
	private $table_balance = 'current_balance';
	private $table_demands = 'demands';
	private $table_demandItems = 'demand_items';
	private $table_ds = 'demand_status';
	private $table_ds_history = 'demand_status_history';

	

	public function getAccounts() {
		try {
			$stmt = "SELECT * from `$this->table` where status = 1 order by code asc";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getStatus() {
		try {
			$stmt = "SELECT * from `$this->table_ds` order by sortorder asc";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getAccountLeafs() {
		try {
			$stmt = "SELECT t1.id, t1.account_type, t1.code, t1.title FROM accounts AS t1 LEFT JOIN accounts as t2 ON t1.id = t2.parent_id WHERE t2.id IS NULL and t1.status = 1 LIMIT 10";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getSuppliers() {
		try {
			$stmt = "SELECT * from `$this->table_suppliers` where flag = 1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getAccountsByIds($idArray) {
		try {
			$ids = implode(',', $idArray);
			$stmt = "SELECT * from `$this->table`  where status = 1 and id IN (".$ids.")";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function getDemands() {
		try {
			$stmt = "SELECT b.*, ds.title as statusName from `$this->table_demands` as b left join `$this->table_ds` as ds on ds.id = b.status where 1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getUserDemandsForApproval($id = false) {
		try {
			if($id) {
				$stmt = "SELECT b.*, ds.title as statusName from `$this->table_demands` as b left join `$this->table_ds` as ds on ds.id = b.status left join `$this->table_ds_history` as dh on dh.demand_id = b.id where dh.user_id = :id";
			}
			else {
				$stmt = "SELECT b.*, ds.title as statusName from `$this->table_demands` as b left join `$this->table_ds` as ds on ds.id = b.status where 1";
			}
			$prepare = $this->dbh->prepare($stmt);
			if($id) {
				$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			}
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	
	public function getJournals($arr = []) {
		try {
			$where = "";
			if(!empty($arr['from']) && !empty($arr['to'])) {

				$to = $arr['to'];
				$from = $arr['from'];
				$account_id = $arr['account_id'];

				$where .= "where t.transaction_date between '$from' AND '$to'";
				if(!empty($account_id)) {
					$where .=" AND a.transaction_id = (select transaction_id from `$this->table_ledger_entries` where account_id = $account_id and transaction_id = a.transaction_id)";
				}
			}
			 
			$stmt = "SELECT a.*, t.transaction_date, t.reference, a.description, t.description as v_description from `$this->table_ledger_entries` as a left join `$this->table_transactions` as t on t.id = a.transaction_id $where";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getTrialBalanceReport($array) {
		try {

			$fromDate = !empty($array['fromDate']) ? $array['fromDate']: '';
			$toDate = !empty($array['toDate']) ? $array['toDate'] : '';

			$stmt = "
			
			SELECT a.*, a.account_id, t.transaction_date, base.title as accountTitle, base.code as accountCode, t.reference, SUM(CASE a.entry_type WHEN 'D' THEN a.amount * -1 WHEN 'C' THEN a.amount * 1 ELSE 0 END) AS amount, SUM(CASE WHEN a.entry_type = 'D' THEN a.amount ELSE 0 END) AS debitAmount, SUM(CASE WHEN a.entry_type = 'C' THEN a.amount ELSE 0 END) AS creditAmount FROM `$this->table_ledger_entries` as a left join `$this->table_transactions` as t on t.id = a.transaction_id left join `$this->table` as base on base.id = a.account_id and base.status = 1 where t.transaction_date between :fromDate and :toDate GROUP BY a.account_id;
			";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':fromDate', $fromDate, PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $toDate, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getPLStatementReport($array) {
		try {

			$fromDate = !empty($array['fromDate']) ? Settings::dateForSql($array['fromDate']): '';
			$toDate = !empty($array['toDate']) ? Settings::dateForSql($array['toDate']) : '';

			$stmt = "SELECT SQL_NO_CACHE a.code, e.account_id, a.account_type, a.title, t.transaction_date, SUM(CASE WHEN e.entry_type='D' THEN e.amount ELSE 0 END) AS debitAmount, SUM(CASE WHEN e.entry_type='C' THEN e.amount ELSE 0 END) AS creditAmount FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 WHERE t.transaction_date >= :fromDate AND t.transaction_date <= :toDate and a.account_type in (4, 5) GROUP BY e.account_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':fromDate', $fromDate, PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $toDate, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function resetAccountChildrens($parent_id) {
		try {
			$stmt = "UPDATE `$this->table` SET parent_id=account_type where parent_id=:parent_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			echo $result;
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateDemandProcess($array) {
		try {
			$stmt = "UPDATE `$this->table_demands` SET flag=:flag where id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->bindParam(':flag', $array['flag'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getAccountSiblings($parent_id) {
		try {
			$stmt = "SELECT count(id) as total from `$this->table` where parent_id=:parent_id and `status` = 1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}


	
	public function searchAccountLeafs($search) {
		$stmt = "SELECT t1.id as account_id, t1.account_type, t1.code, t1.title FROM `$this->table` AS t1 LEFT JOIN `$this->table` as t2 ON t1.id = t2.parent_id WHERE t2.id IS NULL and (t1.title LIKE '%".$search."%' or t1.code LIKE '%".$search."%') and t1.status = 1 LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
	}
	
	public function getBalanceSheet() {
		$stmt = "SELECT * FROM `{$this->table_ledger_entries}`";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
	}
	
	public function getAccountTypes() {
		try {
			$stmt = "SELECT * from `$this->table_types` where 1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function getUnits() {
		try {
			$stmt = "SELECT * from `$this->table_units` where 1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getAccountType($id) {
		try {
			$stmt = "SELECT * from `$this->table_types` where id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
			
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}




	// insert method

	public function insertUnit($array) {
		$title = $array['title'];
		$code = $array['code'];
		try {
			$stmt = "INSERT INTO `{$this->table_units}` (`title`, `code`) VALUES (:title, :code)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function insertStatus($array) {
		$title = $array['title'];
		$code = $array['code'];
		$sortorder = $array['sortorder'];
		try {
			$stmt = "INSERT INTO `{$this->table_ds}` (`title`, `code`, `sortorder`) VALUES (:title, :code, :sortorder)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':sortorder', $sortorder, PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateStatus($array) {
		$id = $array['id'];
		$title = $array['title'];
		$code = $array['code'];
		$sortorder = $array['sortorder'];
		$email = $array['email'];
		$address = $array['address'];
		try {
			$stmt = "UPDATE `{$this->table_ds}` SET `title`=:title, `code`=:code, `sortorder`=:sortorder WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':sortorder', $sortorder, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function insertAccountType($array) {
		$title = $array['title'];
		$code = $array['code'];
		try {
			$stmt = "INSERT INTO `{$this->table_types}` (`title`, `code`) VALUES (:title, :code)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function insertSupplier($array) {
		$title = $array['title'];
		$short_title = $array['short_title'];
		$phone = $array['phone'];
		$email = $array['email'];
		$address = $array['address'];
		try {
			$stmt = "INSERT INTO `{$this->table_suppliers}` (`title`, `short_title`, `phone`, `email`, `address`) VALUES (:title, :short_title, :phone, :email, :address)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':short_title', $short_title, PDO::PARAM_STR);
			$prepare->bindParam(':phone', $phone, PDO::PARAM_STR);
			$prepare->bindParam(':email', $email, PDO::PARAM_STR);
			$prepare->bindParam(':address', $address, PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}





	// update method
	public function updateSupplier($array) {
		$id = $array['id'];
		$title = $array['title'];
		$short_title = $array['short_title'];
		$phone = $array['phone'];
		$email = $array['email'];
		$address = $array['address'];
		try {
			$stmt = "UPDATE `{$this->table_suppliers}` SET `title`=:title, `short_title`=:short_title, `phone`=:phone, `email`=:email, `address`=:address WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':short_title', $short_title, PDO::PARAM_STR);
			$prepare->bindParam(':phone', $phone, PDO::PARAM_STR);
			$prepare->bindParam(':email', $email, PDO::PARAM_STR);
			$prepare->bindParam(':address', $address, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	
	public function deleteAccount($array) {
		$id = $array['id'];
		
		try {
			$stmt = "UPDATE `{$this->table}` SET `status`=3 WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			echo $result;
			$this->resetAccountChildrens($id);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	
	// update method
	public function updateAccountType($array) {
		$id = $array['id'];
		$title = $array['title'];
		$code = $array['code'];
		try {
			$stmt = "UPDATE `{$this->table_types}` SET `title`=:title, `code`=:code WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateUnit($array) {
		$id = $array['id'];
		$title = $array['title'];
		$code = $array['code'];
		try {
			$stmt = "UPDATE `{$this->table_units}` SET `title`=:title, `code`=:code WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}



	// insert method

	public function insertAccount($array) {
		try {
			$stmt = "INSERT INTO `{$this->table}` (`title`, `code`, `account_type`, `group_id`, `status`, `parent_id`, `created_by`) VALUES (:title, :code, :account_type, :group_id, :status, :parent_id, :created_by)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':account_type', $array['account_type'], PDO::PARAM_STR);
			$prepare->bindParam(':group_id', $array['group_id'], PDO::PARAM_STR);
			$prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
			$prepare->bindParam(':parent_id', $array['parent_id'], PDO::PARAM_STR);
			$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}


	public function createDemand($array) {
		try {
			$stmt = "INSERT INTO `{$this->table_demands}` (`title`, `department`, `wing`, `created_by`) VALUES (:title, :department, :wing, :created_by)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':department', $array['department'], PDO::PARAM_STR);
			$prepare->bindParam(':wing', $array['wing'], PDO::PARAM_STR);
			$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function inactiveDemandStatus($array) {
		try {

			$stmt = "UPDATE `{$this->table_ds_history}` SET `flag`=0  WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['demand_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function addDemandStatus($array) {
		try {
			$stmt = "INSERT INTO `{$this->table_ds_history}` (`demand_id`, `demand_status_id`, `user_id`, `flag`) VALUES (:demand_id, :demand_status_id, :user_id, :flag)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':demand_id', $array['demand_id'], PDO::PARAM_STR);
			$prepare->bindParam(':demand_status_id', $array['demand_status_id'], PDO::PARAM_STR);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
			$prepare->bindParam(':flag', $array['flag'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createDemandItem($array) {
		try {
			$stmt = "INSERT INTO `{$this->table_demandItems}` (`title`, `code`, `qty`, `deno`, `price`, `demand_id`) VALUES (:title, :code, :qty, :deno, :price, :demand_id)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':qty', $array['qty'], PDO::PARAM_STR);
			$prepare->bindParam(':deno', $array['deno'], PDO::PARAM_STR);
			$prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
			$prepare->bindParam(':demand_id', $array['demand_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}





	// update method
	public function updateAccount($array) {
		$id = $array['id'];
		try {
			$stmt = "UPDATE `{$this->table}` SET `title`=:title, `code`=:code, `status`=:status WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	// insert method
	public function makeTransaction($array) {
		try {
			$stmt = "INSERT INTO `{$this->table_transactions}` (`description`, `reference`, `transaction_date`, `created_by`) VALUES (:description, :reference, :transaction_date, :created_by)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
			$prepare->bindParam(':reference', $array['reference'], PDO::PARAM_STR);
			$prepare->bindParam(':transaction_date', $array['transaction_date'], PDO::PARAM_STR);
			$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	// insert method
	public function makeEntry($array) {
		try {
			$stmt = "INSERT INTO `{$this->table_ledger_entries}` (`transaction_id`, `account_id`, `entry_type`, `amount`, `user_id`, `description`) VALUES (:transaction_id, :account_id, :entry_type, :amount, :user_id, :description)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':transaction_id', $array['transaction_id'], PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_STR);
			$prepare->bindParam(':entry_type', $array['entry_type'], PDO::PARAM_STR);
			$prepare->bindParam(':amount', $array['amount'], PDO::PARAM_STR);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
			$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}


	
}