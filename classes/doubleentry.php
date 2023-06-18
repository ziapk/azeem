<?php

/**
 * 
 */
class DoubleEntry extends Connection
{
	private $table = 'accounts';
	private $table_modes = 'payment_modes';
	private $table_types = 'account_types';
	private $table_units = 'units';
	private $table_suppliers = 'suppliers';
	private $table_transactions = 'account_transactions';
	private $table_ledger_entries = 'account_ledger_entries';
	private $table_balance = 'current_balance';
	private $table_demands = 'demands';
	private $table_demandItems = 'demand_items';
	private $table_ds = 'demand_status';
	private $table_ds_history = 'demand_status_history';
	private $table_ob = 'opening_balances';


	public function getAccounts($shopId = [])
	{
		try {
			$shopIdCond = '';
			if (!empty($shopId)) {
				$shopIdCond = "and shopId IN (" . implode(', ', $shopId) . ")";
			}
			$stmt = "SELECT * from `$this->table` where status = 1 $shopIdCond order by code asc";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function addColumn($columnName, $table)
	{
		try {
			$stmt = "ALTER TABLE `{$table}` ADD COLUMN IF NOT EXISTS `{$columnName}` varchar(20) NULL DEFAULT NULL AFTER `reference`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function searchAccounts($shopId = null, $search = "")
	{
		try {
			$shopIdCond = '';
			if (!empty($shopId)) {
				$shopIdCond = " and shopId=$shopId ";
			}

			$stmt = "SELECT * FROM `$this->table` WHERE (title LIKE '%" . $search . "%' or code LIKE '%" . $search . "%') and status = 1 $shopIdCond LIMIT 10";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getAccount($id)
	{
		try {
			$stmt = "SELECT * from `$this->table` where status = 1 and id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getStatus()
	{
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

	public function getAccountLeafs($shopId = null)
	{
		try {
			// $this->addColumn('order_ref', $this->table_transactions);
			// $this->addColumn('supply_ref', $this->table_transactions);
			$shopIdCond = " and t1.shopId=$shopId ";
			$stmt = "SELECT t1.id, t1.account_type, t1.code, t1.title FROM accounts AS t1 LEFT JOIN accounts as t2 ON t1.id = t2.parent_id WHERE t2.id IS NULL and t1.status = 1 $shopIdCond LIMIT 10";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getSuppliers()
	{
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

	public function getAccountsByIds($idArray)
	{
		try {
			$ids = implode(',', $idArray);
			$stmt = "SELECT * from `$this->table`  where status = 1 and id IN (" . $ids . ")";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getDemands()
	{
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
	public function getUserDemandsForApproval($id = false)
	{
		try {
			if ($id) {
				$stmt = "SELECT b.*, ds.title as statusName from `$this->table_demands` as b left join `$this->table_ds` as ds on ds.id = b.status left join `$this->table_ds_history` as dh on dh.demand_id = b.id where dh.user_id = :id";
			} else {
				$stmt = "SELECT b.*, ds.title as statusName from `$this->table_demands` as b left join `$this->table_ds` as ds on ds.id = b.status where 1";
			}
			$prepare = $this->dbh->prepare($stmt);
			if ($id) {
				$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			}
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}


	public function getJournals($arr = [], $id = null)
	{
		try {
			$type = "";

			if (!empty($arr['type'])) {
				$type .= 'AND t.transsaction_type IN ("' . implode('","', $arr['type']) . '") ';
			}

			$where = "where t.shopId IN (" . implode(', ', $id) . ") and t.flag=1 " . $type;
			if (!empty($arr['from']) && !empty($arr['to'])) {

				$to = $arr['to'];
				$from = $arr['from'];
				$account_id = $arr['account_id'];

				$where .= " and t.transaction_date between '$from' AND '$to'";
				if (!empty($account_id)) {
					$where .= " AND a.transaction_id = (select transaction_id from `$this->table_ledger_entries` where account_id = $account_id and transaction_id = a.transaction_id)";
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

	public function getLedgerByAccount($arr = [])
	{
		try {
			$countwhere = "where t.flag=1 ";
			$where = "where t.flag=1 ";
			$account_id = $arr['account_id'];

			$type = $arr['type'];

			$str = "(acc_account_transactions.debitAmount - acc_account_transactions.creditAmount)";
			if ($type == 's') {
				$str = "(acc_account_transactions.creditAmount - acc_account_transactions.debitAmount)";
			}

			if (!empty($arr['from']) && !empty($arr['to'])) {

				$to = $arr['to'];
				$from = $arr['from'];
				$where .= " and t.transaction_date between '$from' AND '$to'";
				$countwhere .= " and t.transaction_date between '$from' AND '$to'";
			}

			if (!empty($account_id)) {
				$where .= " and a.id = $account_id";
				$countwhere .= " and e.account_id = $account_id";
			}


			$stmt = "SELECT SUM(CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debit, SUM(CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS credit, count(e.id) as total from `$this->table_ledger_entries` as e left join `$this->table_transactions` as t on t.id = e.transaction_id $countwhere";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$summery = $prepare->fetch(PDO::FETCH_ASSOC);


			$stmt = "SELECT transaction_id, transaction_date, order_ref, transsaction_type, v_description, debitAmount, creditAmount, balance  FROM
			(SELECT
			*
			,COALESCE(debitAmount)  as debits
			,COALESCE(creditAmount) as credits
			,(@running_balance := IF(@curr_account_id < account_id,         opening_balance,@running_balance)) prev_runnng_bal
			,(@curr_account_id := IF(@curr_account_id < account_id,account_id,@curr_account_id)) curr_account_id
			,(@running_balance := @running_balance + $str) as balance
			FROM (SELECT t.transsaction_type, e.transaction_id, e.payment_mode, a.parent_id, a.code, e.account_id, a.opening_balance, a.account_type, a.title, e.entry_type, t.transaction_date, amount, t.order_ref,  t.description as v_description, (CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debitAmount, (CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS creditAmount FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 $where) as acc_account_transactions,(SELECT @running_balance := 0,@curr_account_id := 0) r
			ORDER BY transaction_id) A";




			// $stmt = "SELECT a.*, t.transaction_date, t.reference, a.description, t.description as v_description, m.title as payment_mode from `$this->table_ledger_entries` as a left join `$this->table_transactions` as t on t.id = a.transaction_id left join `$this->table_modes` as m on m.id = a.payment_mode $where order by id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['rows' => $result, 'summery' => $summery];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getLedgerByTID($id)
	{
		try {

			$stmt = "SELECT t.transaction_date, t.transsaction_type, m.title as pay_via, e.transaction_id, e.payment_mode, a.parent_id, a.code, e.account_id, a.opening_balance, a.account_type, a.title, e.entry_type, t.transaction_date, amount, t.order_ref,  t.description as v_description, (CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debitAmount, (CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS creditAmount FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 left join `{$this->table_modes}` as m on m.id=e.payment_mode where t.id = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getPaymentTransactionsByAccountId($order_id, $account_id)
	{
		try {

			$stmt = "SELECT e.payment_mode, e.amount FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 WHERE t.order_ref = :order_id and e.account_id=:account_id and e.entry_type = 'C' and t.flag =1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':order_id', $order_id, PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $account_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getOpeningBalance($account_id, $type = '')
	{
		try {

			$stmt = "SELECT a.opening_balance, a.id, SUM(CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debitAmount, SUM(CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS creditAmount FROM `$this->table_ledger_entries` as e LEFT JOIN `$this->table` as a ON a.id = e.account_id and a.status = 1 left join `{$this->table_transactions}` as t on t.id=e.transaction_id WHERE t.flag = 1 and a.id = :account_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':account_id', $account_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);

			if ($type == 'c') {
				$result['debitAmount'] += $result['opening_balance'];
			} else {
				$result['creditAmount'] += $result['opening_balance'];
			}

			$paid = $type == 's' ? $result['debitAmount'] : $result['creditAmount'];
			$amount = $type == 's' ? $result['creditAmount'] : $result['debitAmount'];

			$result['paid'] = $paid;
			$result['amount'] = $amount;
			$result['balance'] = ($amount - $paid);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getOpeningBalances($account_ids, $type = '')
	{
		try {
			$account_ids = implode(',', $account_ids);
			$stmt = "SELECT a.opening_balance, a.id, SUM(CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debitAmount, SUM(CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS creditAmount FROM `$this->table` as a LEFT JOIN `$this->table_ledger_entries` as e ON a.id = e.account_id and a.status = 1 left join `{$this->table_transactions}` as t on t.id=e.transaction_id WHERE  (a.id IN ($account_ids) and t.flag is null) or (a.id IN ($account_ids) and t.flag = 1) GROUP BY a.id";
			$prepare = $this->dbh->prepare($stmt);
			// $prepare->bindParam(':account_id', $account_id, PDO::PARAM_STR);
			$prepare->execute();
			$results = $prepare->fetchAll(PDO::FETCH_ASSOC);

			$arr = [];

			foreach ($results as $key => $result) {


				if ($type == 'c') {
					$result['debitAmount'] += $result['opening_balance'];
				} else {
					$result['creditAmount'] += $result['opening_balance'];
				}

				$paid = $type == 's' ? $result['debitAmount'] : $result['creditAmount'];
				$amount = $type == 's' ? $result['creditAmount'] : $result['debitAmount'];

				$result['paid'] = $paid;
				$result['amount'] = $amount;
				$result['balance'] = ($amount - $paid);

				$arr[$result['id']] = $result;
			}
			return $arr;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getClosingBalanceReport($array)
	{
		try {
			$fromDate = !empty($array['fromDate']) ? $array['fromDate'] : '';
			$toDate = !empty($array['toDate']) ? $array['toDate'] : '';
			$shopId = !empty($array['shopId']) ? $array['shopId'] : '';
			$parent_ids = !empty($array['parent_ids']) ? $array['parent_ids'] : [];
			$account_ids = !empty($array['account_ids']) ? $array['account_ids'] : [];

			$shopIdCondition = "";
			$accountCondition = "";

			if (!empty($shopId)) {
				$shopIdCondition = "and t.shopId = $shopId";
			}

			if (!empty($account_ids)) {
				$accountCondition = "and (a.parent_id in (" . implode(',', $parent_ids) . ") or a.id in (" . implode(',', $account_ids) . "))";
			}

			$stmt = "SELECT t.transsaction_type,  e.transaction_id, e.payment_mode, a.parent_id, a.code, e.account_id, a.account_type, a.title, e.entry_type, t.transaction_date, amount FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 WHERE (t.flag=1 $shopIdCondition $accountCondition) and (DATE(t.transaction_date) BETWEEN :fromDate AND :toDate)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':fromDate', $fromDate, PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $toDate, PDO::PARAM_STR);
			$prepare->execute();
			$result['rows'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
			$result['opening_balance'] = $this->getOBForReport($array);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getTrialBalanceReport($array)
	{
		try {

			$fromDate = !empty($array['fromDate']) ? $array['fromDate'] : '';
			$toDate = !empty($array['toDate']) ? $array['toDate'] : '';

			$stmt = "
			
			SELECT a.*, a.account_id, t.transaction_date, base.title as accountTitle, base.code as accountCode, t.reference, SUM(CASE a.entry_type WHEN 'D' THEN a.amount * -1 WHEN 'C' THEN a.amount * 1 ELSE 0 END) AS amount, SUM(CASE WHEN a.entry_type = 'D' THEN a.amount ELSE 0 END) AS debitAmount, SUM(CASE WHEN a.entry_type = 'C' THEN a.amount ELSE 0 END) AS creditAmount FROM `$this->table_ledger_entries` as a left join `$this->table_transactions` as t on t.id = a.transaction_id left join `$this->table` as base on base.id = a.account_id and base.status = 1 where t.flag=1 and t.transaction_date between :fromDate and :toDate GROUP BY a.account_id order by base.code;
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

	public function getPLStatementReport($array)
	{
		try {

			$fromDate = !empty($array['fromDate']) ? $array['fromDate'] : '';
			$toDate = !empty($array['toDate']) ? $array['toDate'] : '';
			// $fromDate = '2023-01-01';
			// $toDate = '2023-12-01';;
			$shopId = !empty($array['shopId']) ? $array['shopId'] : '';
			$parent_ids = !empty($array['parent_ids']) ? $array['parent_ids'] : [];
			$account_ids = !empty($array['account_ids']) ? $array['account_ids'] : [];


			$shopIdCondition = "";
			$accountCondition = "";

			if (!empty($shopId)) {
				$shopIdCondition = "and t.shopId = $shopId";
			}

			if (!empty($account_ids)) {
				$accountCondition = "and (a.parent_id in (" . implode(',', $parent_ids) . ") or a.id in (" . implode(',', $account_ids) . "))";
			}

			$stmt = "SELECT SQL_NO_CACHE a.code, e.account_id, a.account_type, a.title, t.transaction_date, SUM(CASE WHEN e.entry_type='D' THEN e.amount ELSE 0 END) AS debitAmount, SUM(CASE WHEN e.entry_type='C' THEN e.amount ELSE 0 END) AS creditAmount, e.transaction_id, e.payment_mode, a.parent_id, a.code, e.account_id, a.account_type, a.title, e.entry_type, t.transaction_date FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 WHERE (t.flag=1 $shopIdCondition $accountCondition) and (DATE(t.transaction_date) BETWEEN :fromDate AND :toDate) GROUP BY e.account_id";
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

	public function resetAccountChildrens($parent_id)
	{
		try {
			$stmt = "UPDATE `$this->table` SET parent_id=account_type where parent_id=:parent_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateDemandProcess($array)
	{
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

	public function getAccountSiblings($parent_id)
	{
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



	public function searchAccountLeafs($search, $shopId = null)
	{
		try {
			$shopIdCond = "";
			if (!empty($shopId)) {
				$shopIdCond = " and t1.shopId=$shopId ";
			}
			$stmt = "SELECT t1.id as account_id, t1.account_type, t1.code, t1.title FROM `$this->table` AS t1 LEFT JOIN `$this->table` as t2 ON t1.id = t2.parent_id WHERE t2.id IS NULL and (t1.title LIKE '%" . $search . "%' or t1.code LIKE '%" . $search . "%') and t1.status = 1 $shopIdCond LIMIT 10";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getBalanceSheet()
	{
		$stmt = "SELECT e.* FROM `{$this->table_ledger_entries}` as e left join `{$this->table_transactions}` as t on t.id = e.transaction_id where flag=1";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	public function getAccountTypes()
	{
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

	public function getUnits()
	{
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

	public function getAccountType($id)
	{
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

	public function getPaymentModes($params)
	{
		try {

			$stmt = "SELECT COUNT(id) as total FROM `{$this->table_modes}` where `shopId`=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);

			$no_of_records_per_page = $params['perPage'] ? $params['perPage'] : 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
			$search = "(title LIKE '%" . $params["search"] . "%' or code LIKE '%" . $params["search"] . "%') ";
			$stmt = "SELECT * FROM `{$this->table_modes}` WHERE $search and `shopId`=:shopId order by id asc LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createPaymentMode($array)
	{
		$title = $array['title'];
		$code = $array['code'];
		$status = $array['status'];
		$is_default = $array['is_default'];
		$shopId = $array['shopId'];
		$owner_id = $array['owner_id'];
		try {
			$stmt = "INSERT INTO `{$this->table_modes}` (`title`, `code`, `status`, `is_default`, `shopId`, `owner_id`) VALUES (:title, :code, :status, :is_default, :shopId, :owner_id)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':status', $status, PDO::PARAM_STR);
			$prepare->bindParam(':is_default', $is_default, PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateAll()
	{
		try {
			$stmt = "UPDATE `{$this->table_modes}` SET `is_default`=0";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updatePaymentMode($array)
	{
		$id = $array['id'];
		$title = $array['title'];
		$code = $array['code'];
		$is_default = $array['is_default'];
		$status = $array['status'];
		$this->updateAll();
		try {
			$stmt = "UPDATE `{$this->table_modes}` SET `title`=:title, `code`=:code, `is_default`=:is_default, `status`=:status WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':status', $status, PDO::PARAM_STR);
			$prepare->bindParam(':is_default', $is_default, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deletePaymentMode($array)
	{
		$id = $array['id'];
		try {
			$stmt = "UPDATE `{$this->table_modes}` SET `status`=3 WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	// insert method

	public function insertUnit($array)
	{
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

	public function insertStatus($array)
	{
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

	public function updateStatus($array)
	{
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
	public function updateTransactions($array)
	{
		$amount = $array['amount'];
		$transaction_id = $array['transaction_id'];
		try {
			$stmt = "UPDATE `{$this->table_ledger_entries}` SET `amount`=:amount WHERE `transaction_id` = :transaction_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':amount', $amount, PDO::PARAM_STR);
			$prepare->bindParam(':transaction_id', $transaction_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function insertAccountType($array)
	{
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

	public function insertSupplier($array)
	{
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
	public function updateSupplier($array)
	{
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


	public function deleteAccount($array)
	{
		$id = $array['id'];

		try {
			$stmt = "UPDATE `{$this->table}` SET `status`=3 WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			$this->resetAccountChildrens($id);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}


	// update method
	public function updateAccountType($array)
	{
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

	public function updateUnit($array)
	{
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

	public function insertAccount($array)
	{

		$getSiblings = $this->getAccountSiblings($array['parent_id']);
		$count = $getSiblings['total'];
		if (!empty($getSiblings) && $count > 0) {
			if ($count < 9) {
				$array['code'] .= '-0' . ($count + 1);
			} else {
				$array['code'] .= '-' . ($count + 1);
			}
		} else {
			$array['code'] = $array['code'] . '-01';
		}


		try {
			$stmt = "INSERT INTO `{$this->table}` (`title`, `code`, `account_type`, `group_id`, `status`, `parent_id`, `created_by`, `shopId`, `opening_balance`) VALUES (:title, :code, :account_type, :group_id, :status, :parent_id, :created_by, :shopId, :opening_balance)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':account_type', $array['account_type'], PDO::PARAM_STR);
			$prepare->bindParam(':group_id', $array['group_id'], PDO::PARAM_STR);
			$prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
			$prepare->bindParam(':parent_id', $array['parent_id'], PDO::PARAM_STR);
			$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':opening_balance', $array['opening_balance'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}


	public function createDemand($array)
	{
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

	public function inactiveDemandStatus($array)
	{
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

	public function addDemandStatus($array)
	{
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

	public function createDemandItem($array)
	{
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
	public function updateAccount($array)
	{
		$id = $array['id'];
		try {
			$stmt = "UPDATE `{$this->table}` SET `title`=:title, `code`=:code, `account_type`=:account_type, `opening_balance`=:opening_balance, `parent_id`=:parent_id, `status`=:status WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':account_type', $array['account_type'], PDO::PARAM_STR);
			$prepare->bindParam(':opening_balance', $array['opening_balance'], PDO::PARAM_STR);
			$prepare->bindParam(':parent_id', $array['parent_id'], PDO::PARAM_STR);
			$prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function setOpeningBalance($id, $opening_balance)
	{
		try {
			$opening_balance = !empty($opening_balance) ? $opening_balance : 0;
			$stmt = "UPDATE `{$this->table}` SET `opening_balance`=:opening_balance WHERE `id` = :id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':opening_balance', $opening_balance, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteTransaction($id)
	{
		try {
			$stmt = "UPDATE `{$this->table_transactions}` SET flag=2 where id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			echo $result;
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteTransactionByOrderId($id)
	{
		if (!empty($id)) {
			try {
				$stmt = "UPDATE `{$this->table_transactions}` SET flag=2 where order_ref=:id";
				$prepare = $this->dbh->prepare($stmt);
				$prepare->bindParam(':id', $id, PDO::PARAM_INT);
				$prepare->execute();
				$result = $prepare->rowCount();
				return $result;
			} catch (PDOException $e) {
				die("Error!: " . $e->getMessage() . "<br/>");
			}
		}
	}
	public function deleteTransactionBySupplyId($id)
	{
		if (!empty($id)) {
			try {
				$stmt = "UPDATE `{$this->table_transactions}` SET flag=2 where supply_ref=:id";
				$prepare = $this->dbh->prepare($stmt);
				$prepare->bindParam(':id', $id, PDO::PARAM_INT);
				$prepare->execute();
				$result = $prepare->rowCount();
				return $result;
			} catch (PDOException $e) {
				die("Error!: " . $e->getMessage() . "<br/>");
			}
		}
	}

	public function getOBForReport($array)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table_ob}` WHERE shop_id=:shop_id and (DATE(sale_date) BETWEEN :fromDate AND :toDate)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':fromDate', $array['fromDate'], PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $array['toDate'], PDO::PARAM_STR);
			$prepare->bindParam(':shop_id', $array['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getOB($shop_id, $id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table_ob}` WHERE shop_id=:shop_id and `id`=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getOBs($shop_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table_ob}` WHERE shop_id=:shop_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function insertOB($array)
	{
		$sale_date = $array['sale_date'];
		$shop_id = $array['shop_id'];
		$owner_id = $array['owner_id'];
		$amount = $array['amount'];
		try {
			$stmt = "INSERT INTO `{$this->table_ob}` (`sale_date`, `shop_id`, `owner_id`, `amount`) VALUES (:sale_date, :shop_id, :owner_id, :amount)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':sale_date', $sale_date, PDO::PARAM_STR);
			$prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':amount', $amount, PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateOB($array)
	{
		$id = $array['id'];
		$sale_date = $array['sale_date'];
		$amount = $array['amount'];
		try {
			$stmt = "UPDATE `{$this->table_ob}` SET `sale_date`=:sale_date, `amount`=:amount WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':sale_date', $sale_date, PDO::PARAM_STR);
			$prepare->bindParam(':amount', $amount, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	// insert method
	public function makeTransaction($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table_transactions}` (`description`, `reference`, `transaction_date`, `created_by`, `shopId`, `order_ref`,`supply_ref`, `transsaction_type`) VALUES (:description, :reference, :transaction_date, :created_by, :shopId, :order_ref, :supply_ref, :transaction_type)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
			$prepare->bindParam(':reference', $array['reference'], PDO::PARAM_STR);
			$prepare->bindParam(':transaction_date', $array['transaction_date'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
			$prepare->bindParam(':order_ref', $array['order_ref'], PDO::PARAM_STR);
			$prepare->bindParam(':supply_ref', $array['supply_ref'], PDO::PARAM_STR);
			$prepare->bindParam(':transaction_type', $array['transaction_type'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	// insert method
	public function makeEntry($array)
	{
		try {
			$paymentMode = !empty($array['payment_mode']) ? $array['payment_mode'] : 1;
			$stmt = "INSERT INTO `{$this->table_ledger_entries}` (`transaction_id`, `account_id`, `entry_type`, `amount`, `user_id`, `description`, `payment_mode`) VALUES (:transaction_id, :account_id, :entry_type, :amount, :user_id, :description, :payment_mode)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':transaction_id', $array['transaction_id'], PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_STR);
			$prepare->bindParam(':entry_type', $array['entry_type'], PDO::PARAM_STR);
			$prepare->bindParam(':amount', $array['amount'], PDO::PARAM_STR);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
			$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
			$prepare->bindParam(':payment_mode', $paymentMode, PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
