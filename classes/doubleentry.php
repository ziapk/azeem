<?php

/**
 * 
 */
class DoubleEntry extends Connection
{
	private $table_orders = 'orders';
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


	public function getAccounts()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];
			$stmt = "SELECT * from `$this->table` where status = 1 and shopId=:shopId order by code asc";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function addColumn($columnName, $table)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "ALTER TABLE `{$table}` ADD COLUMN IF NOT EXISTS `{$columnName}` varchar(20) NULL DEFAULT NULL AFTER `reference`";
			$prepare = $dbh->prepare($stmt);
			$prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function searchAccounts($shopId = null, $search = "")
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$shopIdCond = '';
			if (!empty($shopId)) {
				$shopIdCond = " and shopId=$shopId ";
			}

			$stmt = "SELECT * FROM `$this->table` WHERE (title LIKE '%" . $search . "%' or code LIKE '%" . $search . "%') and status = 1 $shopIdCond LIMIT 10";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getAccount($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * from `$this->table` where status = 1 and id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getStatus()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * from `$this->table_ds` order by sortorder asc";
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

	public function getAccountLeafs($shopId = null)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			// $this->addColumn('order_ref', $this->table_transactions);
			// $this->addColumn('supply_ref', $this->table_transactions);
			$shopIdCond = " and t1.shopId=$shopId ";
			$stmt = "SELECT t1.id, t1.account_type, t1.code, t1.title FROM accounts AS t1 LEFT JOIN accounts as t2 ON t1.id = t2.parent_id WHERE t2.id IS NULL and t1.status = 1 $shopIdCond LIMIT 10";
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

	public function getSuppliers()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * from `$this->table_suppliers` where flag = 1";
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

	public function getAccountsByIds($idArray)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$ids = implode(',', $idArray);
			$stmt = "SELECT * from `$this->table`  where status = 1 and id IN (" . $ids . ")";
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

	public function getDemands()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT b.*, ds.title as statusName from `$this->table_demands` as b left join `$this->table_ds` as ds on ds.id = b.status where 1";
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
	public function getUserDemandsForApproval($id = false)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			if ($id) {
				$stmt = "SELECT b.*, ds.title as statusName from `$this->table_demands` as b left join `$this->table_ds` as ds on ds.id = b.status left join `$this->table_ds_history` as dh on dh.demand_id = b.id where dh.user_id = :id";
			} else {
				$stmt = "SELECT b.*, ds.title as statusName from `$this->table_demands` as b left join `$this->table_ds` as ds on ds.id = b.status where 1";
			}
			$prepare = $dbh->prepare($stmt);
			if ($id) {
				$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			}
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}


	public function getJournals($arr = [], $id = null)
	{
		$dbh = $this->connectionPool->getConnection();
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

			$limitQry = "";

			// if (empty($arr)) {
			// 	$limitQry .= " LIMIT 500";
			// }

			$stmt = "SELECT a.*, t.transaction_date, t.reference, a.description, t.description as v_description from `$this->table_ledger_entries` as a left join `$this->table_transactions` as t on t.id = a.transaction_id $where order by t.id desc $limitQry";
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

	public function getPaymentsByAccounts($arr = [])
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$userInfo = UserInfo();
			$user = $userInfo['user'];

			$stmt = "SELECT a.title, m.title as mode, t.description as v_description, t.transsaction_type, t.transaction_date, e.* FROM `{$this->table_ledger_entries}` as e left join `{$this->table}` as a on a.id=e.account_id left join `{$this->table_transactions}` as t on t.id=e.transaction_id left join `{$this->table_modes}` as m on m.id=e.payment_mode WHERE e.account_id=:account_id and e.entry_type='D' and t.shopId=:shopId and t.flag=1 and date(transaction_date) between :fromDate and :toDate";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $arr['account_id'], PDO::PARAM_STR);
			$prepare->bindParam(':fromDate', $arr['from'], PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $arr['to'], PDO::PARAM_STR);
			$prepare->execute();
			$summery = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $summery;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	
	public function getAdjustsByAccounts($arr = [], $type="", $account_id = "")
	{
		$dbh = $this->connectionPool->getConnection();
		$arr['ttype'] = !empty($type) ? 'ADJUSTMENT' : '%%';
		$condition = "";
		if(!empty($account_id)) {
			$condition = " and a.id=:account_id";
		}
		else {
			$condition = " and a.parent_id=:account_id";
		}
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];
			$stmt = "SELECT a.title, m.title as mode, t.description as v_description, t.transsaction_type, t.transaction_date, e.* FROM `{$this->table_ledger_entries}` as e left join `{$this->table}` as a on a.id=e.account_id left join `{$this->table_transactions}` as t on t.id=e.transaction_id left join `{$this->table_modes}` as m on m.id=e.payment_mode WHERE e.account_id=a.id $condition and transsaction_type like :ttype and e.entry_type='D' and t.shopId=:shopId and t.flag=1 and date(transaction_date) between :fromDate and :toDate";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $arr['account_id'], PDO::PARAM_STR);
			$prepare->bindParam(':fromDate', $arr['from'], PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $arr['to'], PDO::PARAM_STR);
			$prepare->bindParam(':ttype', $arr['ttype'], PDO::PARAM_STR);
			$prepare->execute();
			$summery = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $summery;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getLedgerByAccount($arr = []) {
		$dbh = $this->connectionPool->getConnection();
		try {

			 // DEBUG - remove after fixing
			print_r("FROM: " . $arr['from']);
			print_r("TO: " . $arr['to']);
			print_r("account_id: " . $arr['account_id']);
			print_r("shopId: " . $arr['user']['shopId']);

			// Check actual column name in transactions table
			$check = $dbh->query("DESCRIBE `$this->table_transactions`");
			$cols = $check->fetchAll(PDO::FETCH_COLUMN);
			print_r("Columns: " . implode(', ', $cols));

			exit;

			$countwhere = "where t.flag=1 and t.shopId=:shopId";
			$where = "where t.flag=1 and t.shopId=:shopId";
			$account_id = $arr['account_id'];

			$type = $arr['type'];

			$str = "(acc_account_transactions.debitAmount - acc_account_transactions.creditAmount)";
			if ($type == 's' || $type == 'emp') {
				$str = "(acc_account_transactions.creditAmount - acc_account_transactions.debitAmount)";
			}

			if (!empty($account_id)) {
				$where .= " and a.id = $account_id";
				$countwhere .= " and e.account_id = $account_id";
			}

			// Add datetime range filter to SQL instead of PHP
			$hasDateRange = !empty($arr['from']) && !empty($arr['to']);
			if ($hasDateRange) {
				$where      .= " and DATE(t.datetime) >= :from AND DATE(t.datetime) <= :to";
				$countwhere .= " and DATE(t.datetime) >= :from AND DATE(t.datetime) <= :to";
			}

			// Summary / count query
			$stmt = "SELECT 
						SUM(CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debit, 
						SUM(CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS credit, 
						count(e.id) as total 
					FROM `$this->table_ledger_entries` as e 
					LEFT JOIN `$this->table_transactions` as t ON t.id = e.transaction_id 
					$countwhere";

			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $arr['user']['shopId'], PDO::PARAM_STR);
			if ($hasDateRange) {
				$prepare->bindParam(':from', $arr['from'], PDO::PARAM_STR);
				$prepare->bindParam(':to',   $arr['to'],   PDO::PARAM_STR);
			}
			$prepare->execute();
			$summery = $prepare->fetch(PDO::FETCH_ASSOC);

			if ($_GET['t'] == 'c') {
				$summery['debit'] += $arr['user']['opening_balance'];
			} else {
				$summery['credit'] += $arr['user']['opening_balance'];
			}

			$paid    = in_array($arr['type'], ['s', 'emp']) ? $summery['debit']  : $summery['credit'];
			$amount  = in_array($arr['type'], ['s', 'emp']) ? $summery['credit'] : $summery['debit'];
			$balance = ($amount - $paid);

			$summery['paid']    = $paid;
			$summery['due']     = $amount;
			$summery['balance'] = $balance;

			// Main ledger query with running balance
			$stmt = "SELECT 
						transaction_id, 
						title, 
						transaction_date, 
						datetime,
						order_ref, 
						order_custom_id, 
						supply_ref, 
						return_ref, 
						transsaction_type, 
						v_description, 
						debitAmount, 
						creditAmount, 
						balance, 
						previousBalance, 
						reference  
					FROM (
						SELECT
							*,
							COALESCE(debitAmount)  as debits,
							COALESCE(creditAmount) as credits,
							(@running_balance := IF(@curr_account_id < account_id, opening_balance, @running_balance)) prev_runnng_bal,
							(@curr_account_id := IF(@curr_account_id < account_id, account_id, @curr_account_id)) curr_account_id,
							(@running_balance := @running_balance) as previousBalance,
							(@running_balance := @running_balance + $str) as balance
						FROM (
							SELECT 
								t.transsaction_type, 
								t.reference, 
								e.transaction_id, 
								e.payment_mode, 
								a.parent_id, 
								a.code, 
								e.account_id, 
								a.opening_balance, 
								a.account_type, 
								a.title, 
								e.entry_type, 
								t.transaction_date,
								t.datetime,
								amount, 
								o.order_custom_id, 
								t.order_ref, 
								t.supply_ref, 
								t.return_ref, 
								t.description as v_description, 
								(CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debitAmount, 
								(CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS creditAmount 
							FROM `$this->table_transactions` t 
							LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id 
							LEFT JOIN `$this->table` a ON a.id = e.account_id AND a.status = 1 
							LEFT JOIN `$this->table_orders` as o ON o.id = t.order_ref 
							$where
						) as acc_account_transactions,
                    (SELECT @running_balance := 0, @curr_account_id := 0) r
                    ORDER BY transaction_id
                 ) A";

			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $arr['user']['shopId'], PDO::PARAM_STR);
			if ($hasDateRange) {
				$prepare->bindParam(':from', $arr['from'], PDO::PARAM_STR);
				$prepare->bindParam(':to',   $arr['to'],   PDO::PARAM_STR);
			}
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);

			$first = reset($result);
			return [
				'count'   => sizeof($result),
				'rows'    => $result,
				'first'   => $first,
				'summery' => $summery,
				'last'    => end($result)
			];

		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getOnlineLedgerByAccounts($arr = [])
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$where = "where m.code = 'ONLINE' and t.flag=1 ";
			$account_id = $arr['ids'];

			$type = $arr['type'];

			$str = "(acc_account_transactions.debitAmount - acc_account_transactions.creditAmount)";
			if ($type == 's') {
				$str = "(acc_account_transactions.creditAmount - acc_account_transactions.debitAmount)";
			}

			if (!empty($arr['from']) && !empty($arr['to'])) {

				$to = $arr['to'];
				$from = $arr['from'];
				$where .= " and t.transaction_date between '$from' AND '$to'";
			}

			if (!empty($account_id)) {
				$where .= " and a.id IN ( " . implode(', ', $account_id) . ")";
			}

			$stmt = "SELECT transaction_id, modeTitle, transaction_date, order_ref, supply_ref, return_ref, transsaction_type, v_description, debitAmount, creditAmount, balance, reference  FROM
			(SELECT
			*
			,COALESCE(debitAmount)  as debits
			,COALESCE(creditAmount) as credits
			,(@running_balance := IF(@curr_account_id < account_id, opening_balance,@running_balance)) prev_runnng_bal
			,(@curr_account_id := IF(@curr_account_id < account_id,account_id,@curr_account_id)) curr_account_id
			,(@running_balance := @running_balance + $str) as balance
			FROM (SELECT t.transsaction_type, m.title as modeTitle, t.reference, e.transaction_id, e.payment_mode, a.parent_id, a.code, e.account_id, a.opening_balance, a.account_type, a.title, e.entry_type, t.transaction_date, amount, t.order_ref, t.supply_ref, t.return_ref, t.description as v_description, (CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debitAmount, (CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS creditAmount FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table_modes` as m on m.id=e.payment_mode LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 $where) as acc_account_transactions,(SELECT @running_balance := 0,@curr_account_id := 0) r
			ORDER BY transaction_id) A";

			$prepare = $dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['rows' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getLedgerByTID($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt = "SELECT t.transaction_date, t.transsaction_type, m.title as pay_via, e.transaction_id, e.payment_mode, a.parent_id, a.code, e.account_id, a.opening_balance, a.account_type, a.title, e.entry_type, t.transaction_date, amount, t.order_ref,  t.description as v_description, (CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debitAmount, (CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS creditAmount FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 left join `{$this->table_modes}` as m on m.id=e.payment_mode where t.id = :id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getPaymentTransactionsByAccountId($order_id, $account_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt = "SELECT e.payment_mode, e.amount FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 WHERE t.order_ref = :order_id and e.account_id=:account_id and e.entry_type = 'C' and t.flag =1";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':order_id', $order_id, PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $account_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getReturnTransactionsByAccountId($order_id, $account_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt = "SELECT e.payment_mode, e.amount FROM `$this->table_transactions` t LEFT JOIN `$this->table_ledger_entries` e ON e.transaction_id = t.id LEFT JOIN `$this->table` a ON a.id = e.account_id and a.status = 1 WHERE t.return_ref = :order_id and e.account_id=:account_id and e.entry_type = 'C' and t.flag =1";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':order_id', $order_id, PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $account_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getOpeningBalance($account_id, $type = '')
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt = "SELECT a.opening_balance, a.id, SUM(CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debitAmount, SUM(CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS creditAmount FROM `$this->table_ledger_entries` as e LEFT JOIN `$this->table` as a ON a.id = e.account_id and a.status = 1 left join `{$this->table_transactions}` as t on t.id=e.transaction_id WHERE t.flag = 1 and a.id = :account_id";
			$prepare = $dbh->prepare($stmt);
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
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getOpeningBalances($ids, $type = '')
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$account_ids = implode(',', $ids);
			$stmt = "SELECT a.opening_balance, a.id, SUM(CASE WHEN e.entry_type = 'D' THEN e.amount ELSE 0 END) AS debitAmount, SUM(CASE WHEN e.entry_type = 'C' THEN e.amount ELSE 0 END) AS creditAmount FROM `$this->table` as a LEFT JOIN `$this->table_ledger_entries` as e ON a.id = e.account_id and a.status = 1 left join `{$this->table_transactions}` as t on t.id=e.transaction_id WHERE  (a.id IN ($account_ids) and t.flag is null) or (a.id IN ($account_ids) and t.flag = 1) GROUP BY a.id";
			$prepare = $dbh->prepare($stmt);
			// $prepare->bindParam(':account_id', $account_id, PDO::PARAM_STR);
			$prepare->execute();
			$results = $prepare->fetchAll(PDO::FETCH_ASSOC);

			$arr = [];
			$foundIds = [];
			foreach ($results as $key => $result) {
				$foundIds[] = $result['id'];
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
			$emptyAccounts = array_diff($ids, $foundIds);

			if (!empty($emptyAccounts)) {

				$account_ids = implode(',', $emptyAccounts);
				$stmt = "SELECT opening_balance, id FROM `$this->table` WHERE  id IN ($account_ids)";
				$prepare = $dbh->prepare($stmt);
				// $prepare->bindParam(':account_id', $account_id, PDO::PARAM_STR);
				$prepare->execute();
				$results = $prepare->fetchAll(PDO::FETCH_ASSOC);

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
			}
			return $arr;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getClosingBalanceReport($array)
	{
		$shopAccounts = new ShopAccounts();
		$accountsData = $shopAccounts->getSAs($array['shopId']);
		$store = [];
		foreach ($accountsData as $a) {
			$store[$a['key_value']] = $a['account_id'];
		}

		$storeInfo = new Store();
		$storeData = $storeInfo->getStore($array['shopId']);
		$array['parent_ids'][] = $store['locker'];
		$array['parent_ids'][] = $store['receivable'];
		$array['parent_ids'][] = $store['payable'];
		$array['account_ids'][] = $store['sale_discount'];
		$array['account_ids'][] = $store['receiving'];
		$array['account_ids'][] = $store['sale_returns'];
		$array['account_ids'][] = $store['purchase_returns'];
		$array['parent_ids'][] = $store['expense'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$array['fromDate'] = $fromDate = !empty($array['fromDate']) ? $array['fromDate'] : $storeData['sale_date'];
			$array['toDate'] = $toDate = !empty($array['toDate']) ? $array['toDate'] : $storeData['sale_date'];
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
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':fromDate', $fromDate, PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $toDate, PDO::PARAM_STR);
			$prepare->execute();
			$result['rows'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
			$result['opening_balance'] = $this->getOBForReport($array);

			$reportDataRaw = $result;

			$rows = [];
			$exp = $store['sale_discount'];
			$expHead = $store['expense'];
			$count = 0;
			$expenses = ['total' => [], 'rows' => []];
			$otherList = [];
			$otherTotals = [];
			$purchaseReturnsList = [];
			$exchange = 0;
			$payments = 0;
			$deposit = 0;
			$withdrawal = 0;
			$receivings = 0;
			$cashSale = 0;
			$sale_returns = 0;
			$purchase_returns = 0;
			$receivingList = 0;
			$final = [];
			$modes = [];
			$cashTotals = [];
			$modesList = $this->getPaymentModes(['page' => 1, 'perPage' => 10000, 'search' => '', 'shopId' => $array['shopId']]);
			$cashModeId = 0;
			foreach ($modesList['records'] as $key => $value) {
				$amodesList[$value['id']] = $value;
				if ($value['code'] == 'CASH') {
					$cashModeId = $value['id'];
				}
			}
			foreach ($reportDataRaw['rows'] as $key => $value) {
				if ($value['transsaction_type'] == 'SALE' || $value['transsaction_type'] == 'EXPENSE') {
					if ($value['parent_id'] == $store['receivable']) { // exclude expense
						if ($value['entry_type'] == 'D') {
							$rows[$value['account_id']]['row'] = $value;
							$rows[$value['account_id']]['totalCredit'] += $value['amount'];
						} else {
							$rows[$value['account_id']]['row'] = $value;
							$modes[$value['payment_mode']] += $value['amount'];
							$rows[$value['account_id']]['totalPaid'] += $value['amount'];
							$rows[$value['account_id']]['paid'][$value['payment_mode']] += $value['amount'];
						}
					} else if ($value['parent_id'] == $expHead) {
						if ($cashModeId == $value['payment_mode']) {
							$cashTotals["exp"] += $value['amount'];
						}
						if (empty($expenses['rows'][$value['transaction_date']]['row'][$value['account_id']][$value['payment_mode']])) {
							$expenses['rows'][$value['transaction_date']]['row'][$value['account_id']][$value['payment_mode']] = $value;
						} else {
							$expenses['rows'][$value['transaction_date']]['row'][$value['account_id']][$value['payment_mode']]['amount'] += $value['amount'];
						}
						$expenses['rows'][$value['transaction_date']]['total'][$value['payment_mode']] += $value['amount'];
						$expenses['total'][$value['payment_mode']] += $value['amount'];
					}
				} elseif (in_array($value['transsaction_type'], ['DIRECT_RECEIVING', 'CASH_RECEIVED'])) {
					if ($store['receivable'] == $value['parent_id']) {

						$k = "RECEIVING";

						$m = $value['payment_mode'];

						$otherTotals['accounts'][$value['account_id']][$k][$m] += $value['amount'];
						$otherTotals['totals'][$k][$m] += $value['amount'];

						if (empty($otherList[$value['account_id']][$k][$m])) {
							$otherList[$value['account_id']][$k][$m] = $value;
						} else {
							$otherList[$value['account_id']][$k][$m]['amount'] += $value['amount'];
						}

						if ($cashModeId == $value['payment_mode']) {
							$receivings += $value['amount'];
						}
					}
				} elseif (in_array($value['transsaction_type'], ['CASH_WITHDRAWAL'])) {
					$withdrawal += $value['amount'];
				} else {
					$consider = true;

					if (in_array($value['transsaction_type'], ['ROYALTY PAYMENT', 'PURCHASE_PAYMENT', 'DIRECT_PAYMENT'])) {
						if ($cashModeId == $value['payment_mode']) {
							$payments += $value['amount'];
						}
					}
					if (in_array($value['transsaction_type'], ['CASH_DEPOSIT'])) {
						if ($cashModeId == $value['payment_mode']) {
							$deposit += $value['amount'];
						}
						$consider = false;
					}
					if (in_array($value['transsaction_type'], ['EXCHANGE'])) {
						$exchange += $value['amount'];
					}
					if (in_array($value['transsaction_type'], ['SALE_RETURN'])) {
						if ($store['receivable'] == $value['parent_id'] && $value['entry_type'] == 'D') {
							if ($cashModeId == $value['payment_mode']) {
								$sale_returns += $value['amount'];
							}
						} else {
							$consider = false;
						}
					}
					if (in_array($value['transsaction_type'], ['PURCHASE_RETURN'])) {
						if ($value['entry_type'] == 'D' && $store['purchase_returns'] == $value['account_id']) {
							if ($cashModeId == $value['payment_mode']) {
								$payments += $value['amount'];
							} else {
								$consider = false;
							}
						} else {
							$consider = false;
						}
					}

					if (in_array($value['transsaction_type'], ['PURCHASE'])) {
						if ($value['entry_type'] == 'D') {
							if ($cashModeId == $value['payment_mode']) {
								$payments += $value['amount'];
							}
						} else {
							$consider = false;
						}
					}

					if ($consider) {
						$otherTotals['accounts'][$value['account_id']][$value['transsaction_type']][$value['payment_mode']] += $value['amount'];
						$otherTotals['totals'][$value['transsaction_type']][$value['payment_mode']] += $value['amount'];
						if (empty($otherList[$value['account_id']][$value['transsaction_type']][$value['payment_mode']])) {
							$otherList[$value['account_id']][$value['transsaction_type']][$value['payment_mode']] = $value;
						} else {
							$otherList[$value['account_id']][$value['transsaction_type']][$value['payment_mode']]['amount'] += $value['amount'];
						}
					}
				}

				$count++;
			}


			// $rows2 = [];
			$totals = ['expense' => 0, 'gross' => 0, 'net' => 0];
			$count = 0;
			$reportData1 = [];
			foreach ($rows as $accountId => $transaction) {
				// if(empty($transactions['isReceiving'])) {
				$final[$accountId]['id'] = $transaction['row']['transaction_id'];
				$final[$accountId]['code'] = $transaction['row']['code'];
				$final[$accountId]['title'] = $transaction['row']['title'];
				$final[$accountId]['grossCredit'] += $transaction['totalCredit'];
				$final[$accountId]['totalCredit'] += $transaction['totalCredit'];
				foreach ($modesList['records'] as $m) {
					if ($m['code'] == 'CASH') {
						$cashSale += $transaction['paid'][$m['id']];
					}
					$final[$accountId][$m['id']] += $transaction['paid'][$m['id']];
				}


				$final[$accountId]['totalPaid'] += $transaction['totalPaid'];
				$final[$accountId]['totalDiscount'] += $transaction['discount'];
				// }
				if (!empty($final)) {
					$reportData1 = array_values($final);
				}
			}
			$reportData = [];

			$count = 0;
			$footer = [];
			$finalSummeryDateWise = [];

			foreach ($reportData1 as $key => $value) {
				if (empty($value['title'])) {
					unset($reportData1);
				} else {
					$reportData['records'][$count] = $value;
					$reportData['records'][$count]['grossCreditSales'] = !empty($value['grossCredit'] - $value['totalPaid']) ? $value['grossCredit'] - $value['totalPaid'] + $value['totalDiscount'] : 0;
					$reportData['records'][$count]['grossCashSales'] = !empty($value['totalPaid']) ? $value['totalPaid'] + $value['totalDiscount'] : 0;
					// $reportData['records'][$count]['discount'] = $value['totalDiscount'];
					$reportData['records'][$count]['netCreditSales'] = $value['totalCredit'] - $value['totalPaid'];
					$reportData['records'][$count]['netCashSales'] = $value['totalPaid'];
					$reportData['records'][$count]['finalCashSales'] = $value['totalPaid'];

					foreach ($modesList['records'] as $m) {
						$reportData['records'][$count][$m['id']] = $value[$m['id']];
						$footer[$m['id']] += $value[$m['id']];
					}

					$footer['grossCreditSales'] += !empty($value['grossCredit'] - $value['totalPaid']) ? $value['grossCredit'] - $value['totalPaid'] + $value['totalDiscount'] : 0;
					$footer['grossCashSales'] += !empty($value['totalPaid']) ? $value['totalPaid'] + $value['totalDiscount'] : 0;
					$footer['discount'] += $value['totalDiscount'];
					$footer['netCreditSales'] += $value['totalCredit'] - $value['totalPaid'];
					$footer['netCashSales'] += $value['totalPaid'];
					$footer['finalCashSales'] += $value['totalPaid'];

					$finalSummeryDateWise[$value['transaction_date']] += $value['totalPaid'];
					$count++;
				}
			}

			$reportData['other']['expenses'] = $expenses;
			$reportData['other']['cashTotals'] = $cashTotals;
			$reportData['other']['footer'] = $footer;
			$reportData['other']['cashSale'] = $cashSale;
			$reportData['other']['otherList'] = $otherList;
			$reportData['other']['otherTotals'] = $otherTotals;
			$reportData['other']['receivings'] = $receivings;
			$reportData['other']['purchase_returns'] = $purchase_returns;
			$reportData['other']['sale_returns'] = $sale_returns;
			$reportData['other']['payments'] = $payments;
			$reportData['other']['deposit'] = $deposit;
			$reportData['other']['withdrawal'] = $withdrawal;
			$reportData['other']['opening_balance'] = $result['opening_balance'];


			$tsale = $reportData['other']['cashSale'];
			$creditsale = empty($footer['netCreditSales']) ? 0 : $footer['netCreditSales'];
			$texpense = $reportData['other']['cashTotals']["exp"];
			$cash = ($tsale + $receivings + $purchase_returns + $withdrawal);
			$deduction = ($sale_returns + $texpense + $payments + $deposit);
			$netCash = ($tsale + $receivings + $purchase_returns) - $deduction;

			$reportData['other']['cash'] = $cash;
			$reportData['other']['deduction'] = $deduction;
			$reportData['other']['netCash'] = $netCash;
			$reportData['other']['texpense'] = $texpense;
			$reportData['other']['creditsale'] = $creditsale;
			$reportData['other']['totalSale'] = $result['opening_balance']['amount'] + $cash;
			$reportData['other']['totalNetSale'] = $reportData['other']['totalSale'] - $deduction;

			return $reportData;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getClosingBalanceReportBKPORIGINAL($array)
	{
		$dbh = $this->connectionPool->getConnection();
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
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':fromDate', $fromDate, PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $toDate, PDO::PARAM_STR);
			$prepare->execute();
			$result['rows'] = $prepare->fetchAll(PDO::FETCH_ASSOC);
			$result['opening_balance'] = $this->getOBForReport($array);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getTrialBalanceReport($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$fromDate = !empty($array['fromDate']) ? $array['fromDate'] : '';
			$toDate = !empty($array['toDate']) ? $array['toDate'] : '';

			$stmt = "
			
			SELECT a.*, a.account_id, t.transaction_date, base.title as accountTitle, base.code as accountCode, t.reference, SUM(CASE a.entry_type WHEN 'D' THEN a.amount * -1 WHEN 'C' THEN a.amount * 1 ELSE 0 END) AS amount, SUM(CASE WHEN a.entry_type = 'D' THEN a.amount ELSE 0 END) AS debitAmount, SUM(CASE WHEN a.entry_type = 'C' THEN a.amount ELSE 0 END) AS creditAmount FROM `$this->table_ledger_entries` as a left join `$this->table_transactions` as t on t.id = a.transaction_id left join `$this->table` as base on base.id = a.account_id and base.status = 1 where t.flag=1 and t.transaction_date between :fromDate and :toDate GROUP BY a.account_id order by base.code;
			";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':fromDate', $fromDate, PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $toDate, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getPLStatementReport($array)
	{
		$dbh = $this->connectionPool->getConnection();
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
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':fromDate', $fromDate, PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $toDate, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function resetAccountChildrens($parent_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `$this->table` SET parent_id=account_type where parent_id=:parent_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function updateDemandProcess($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `$this->table_demands` SET flag=:flag where id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->bindParam(':flag', $array['flag'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getAccountSiblings($parent_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT count(id) as total from `$this->table` where parent_id=:parent_id and `status` = 1";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getAccountsByParentIds($parent_ids)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT id, title, parent_id from `$this->table` where parent_id IN (" . implode(',', $parent_ids) . ") and `status` = 1";
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



	public function searchAccountLeafs($search, $shopId = null)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$shopIdCond = "";
			if (!empty($shopId)) {
				$shopIdCond = " and t1.shopId=$shopId ";
			}
			$stmt = "SELECT t1.id as account_id, t1.account_type, t1.code, t1.title FROM `$this->table` AS t1 LEFT JOIN `$this->table` as t2 ON t1.id = t2.parent_id WHERE t2.id IS NULL and (t1.title LIKE '%" . $search . "%' or t1.code LIKE '%" . $search . "%') and t1.status = 1 $shopIdCond LIMIT 10";
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

	public function getBalanceSheet()
	{
		$dbh = $this->connectionPool->getConnection();
		$stmt = "SELECT e.* FROM `{$this->table_ledger_entries}` as e left join `{$this->table_transactions}` as t on t.id = e.transaction_id where flag=1";
		$prepare = $dbh->prepare($stmt);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		$this->connectionPool->releaseConnection($dbh);
		return $result;
	}

	public function getAccountTypes()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * from `$this->table_types` where 1";
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

	public function getUnits()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * from `$this->table_units` where 1";
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

	public function getAccountType($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * from `$this->table_types` where id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}




	// insert method

	public function getPaymentModes($params)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt = "SELECT COUNT(id) as total FROM `{$this->table_modes}` where `shopId`=:shopId";
			$prepare = $dbh->prepare($stmt);
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
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getDefaultPaymentMode($params)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt = "SELECT * FROM `{$this->table_modes}` WHERE `shopId`=:shopId and is_default=1";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
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
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_modes}` (`title`, `code`, `status`, `is_default`, `shopId`, `owner_id`) VALUES (:title, :code, :status, :is_default, :shopId, :owner_id)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':status', $status, PDO::PARAM_STR);
			$prepare->bindParam(':is_default', $is_default, PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function updateAll($owner_id, $shopId)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_modes}` SET `is_default`=0 where shopId=:shopId and owner_id=:owner_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function updatePaymentMode($array)
	{
		$id = $array['id'];
		$title = $array['title'];
		$code = $array['code'];
		$is_default = $array['is_default'];
		$status = $array['status'];
		$shopId = $array['shopId'];
		$owner_id = $array['owner_id'];
		$this->updateAll($owner_id, $shopId);
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_modes}` SET `title`=:title, `code`=:code, `is_default`=:is_default, `status`=:status WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
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
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function deletePaymentMode($array)
	{
		$id = $array['id'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_modes}` SET `status`=3 WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	// insert method

	public function insertUnit($array)
	{
		$title = $array['title'];
		$code = $array['code'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_units}` (`title`, `code`) VALUES (:title, :code)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function insertStatus($array)
	{
		$title = $array['title'];
		$code = $array['code'];
		$sortorder = $array['sortorder'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_ds}` (`title`, `code`, `sortorder`) VALUES (:title, :code, :sortorder)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':sortorder', $sortorder, PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
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
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_ds}` SET `title`=:title, `code`=:code, `sortorder`=:sortorder WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':sortorder', $sortorder, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function updateTransactions($array)
	{
		$amount = $array['amount'];
		$transaction_id = $array['transaction_id'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_ledger_entries}` SET `amount`=:amount WHERE `transaction_id` = :transaction_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':amount', $amount, PDO::PARAM_STR);
			$prepare->bindParam(':transaction_id', $transaction_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function insertAccountType($array)
	{
		$title = $array['title'];
		$code = $array['code'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_types}` (`title`, `code`) VALUES (:title, :code)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function insertSupplier($array)
	{
		$title = $array['title'];
		$short_title = $array['short_title'];
		$phone = $array['phone'];
		$email = $array['email'];
		$address = $array['address'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_suppliers}` (`title`, `short_title`, `phone`, `email`, `address`) VALUES (:title, :short_title, :phone, :email, :address)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':short_title', $short_title, PDO::PARAM_STR);
			$prepare->bindParam(':phone', $phone, PDO::PARAM_STR);
			$prepare->bindParam(':email', $email, PDO::PARAM_STR);
			$prepare->bindParam(':address', $address, PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
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
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_suppliers}` SET `title`=:title, `short_title`=:short_title, `phone`=:phone, `email`=:email, `address`=:address WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
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
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}


	public function deleteAccount($array)
	{
		$id = $array['id'];

		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET `status`=3 WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			$this->resetAccountChildrens($id);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}


	// update method
	public function updateAccountType($array)
	{
		$id = $array['id'];
		$title = $array['title'];
		$code = $array['code'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_types}` SET `title`=:title, `code`=:code WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function updateUnit($array)
	{
		$id = $array['id'];
		$title = $array['title'];
		$code = $array['code'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_units}` SET `title`=:title, `code`=:code WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':code', $code, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
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
			return $this->insertAccountDirect($array);
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function insertAccountDirect($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table}` (`title`, `code`, `account_type`, `group_id`, `status`, `parent_id`, `created_by`, `shopId`, `opening_balance`) VALUES (:title, :code, :account_type, :group_id, :status, :parent_id, :created_by, :shopId, :opening_balance)";
			$prepare = $dbh->prepare($stmt);
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
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}


	public function createDemand($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_demands}` (`title`, `department`, `wing`, `created_by`) VALUES (:title, :department, :wing, :created_by)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':department', $array['department'], PDO::PARAM_STR);
			$prepare->bindParam(':wing', $array['wing'], PDO::PARAM_STR);
			$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function inactiveDemandStatus($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt = "UPDATE `{$this->table_ds_history}` SET `flag`=0  WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['demand_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function addDemandStatus($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_ds_history}` (`demand_id`, `demand_status_id`, `user_id`, `flag`) VALUES (:demand_id, :demand_status_id, :user_id, :flag)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':demand_id', $array['demand_id'], PDO::PARAM_STR);
			$prepare->bindParam(':demand_status_id', $array['demand_status_id'], PDO::PARAM_STR);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
			$prepare->bindParam(':flag', $array['flag'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function createDemandItem($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_demandItems}` (`title`, `code`, `qty`, `deno`, `price`, `demand_id`) VALUES (:title, :code, :qty, :deno, :price, :demand_id)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':qty', $array['qty'], PDO::PARAM_STR);
			$prepare->bindParam(':deno', $array['deno'], PDO::PARAM_STR);
			$prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
			$prepare->bindParam(':demand_id', $array['demand_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}





	// update method
	public function updateAccount($array)
	{
		$id = $array['id'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET `title`=:title, `code`=:code, `account_type`=:account_type, `opening_balance`=:opening_balance, `parent_id`=:parent_id, `status`=:status WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
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
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function setOpeningBalance($id, $opening_balance)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$opening_balance = !empty($opening_balance) ? $opening_balance : 0;
			$stmt = "UPDATE `{$this->table}` SET `opening_balance`=:opening_balance WHERE `id` = :id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':opening_balance', $opening_balance, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function deleteTransaction($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_transactions}` SET flag=2 where id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			echo $result;
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function deleteTransactionByOrderId($id)
	{
		if (!empty($id)) {
			$dbh = $this->connectionPool->getConnection();
			try {
				$stmt = "UPDATE `{$this->table_transactions}` SET flag=2 where order_ref=:id";
				$prepare = $dbh->prepare($stmt);
				$prepare->bindParam(':id', $id, PDO::PARAM_INT);
				$prepare->execute();
				$result = $prepare->rowCount();
				return $result;
			} catch (PDOException $e) {
				die("Error!: " . $e->getMessage() . "<br/>");
			}
		}
	}
	public function deleteTransactionByReturnId($id)
	{
		if (!empty($id)) {
			$dbh = $this->connectionPool->getConnection();
			try {
				$stmt = "UPDATE `{$this->table_transactions}` SET flag=2 where return_ref=:id";
				$prepare = $dbh->prepare($stmt);
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
			$dbh = $this->connectionPool->getConnection();
			try {
				$stmt = "UPDATE `{$this->table_transactions}` SET flag=2 where supply_ref=:id";
				$prepare = $dbh->prepare($stmt);
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
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table_ob}` WHERE shop_id=:shop_id and flag=1 and (DATE(sale_date) BETWEEN :fromDate AND :toDate)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':fromDate', $array['fromDate'], PDO::PARAM_STR);
			$prepare->bindParam(':toDate', $array['toDate'], PDO::PARAM_STR);
			$prepare->bindParam(':shop_id', $array['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getDebitEntriesByOrderIds($ids = [], $accounts = [], $shopId)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT e.*, t.order_ref FROM `{$this->table_ledger_entries}` as e left join `{$this->table_transactions}` as t on t.id = e.transaction_id  WHERE t.flag=1 and t.shopId=:shop_id and t.order_ref is not null and order_ref in (" . implode(",", $ids) . ") and account_id in (" . implode(",", $accounts) . ") and e.entry_type = 'C'";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shop_id', $shopId, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getDebitEntriesByReturnIds($ids = [], $accounts = [], $shopId)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT e.*, t.return_ref FROM `{$this->table_ledger_entries}` as e left join `{$this->table_transactions}` as t on t.id = e.transaction_id  WHERE t.flag=1 and t.shopId=:shop_id and t.return_ref is not null and return_ref in (" . implode(",", $ids) . ") and account_id in (" . implode(",", $accounts) . ")  and e.entry_type = 'C'";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shop_id', $shopId, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getOB($shop_id, $id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table_ob}` WHERE shop_id=:shop_id and `id`=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getOBs($shop_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table_ob}` WHERE shop_id=:shop_id and flag=1";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function insertOB($array)
	{
		$sale_date = $array['sale_date'];
		$shop_id = $array['shop_id'];
		$owner_id = $array['owner_id'];
		$amount = $array['amount'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_ob}` (`sale_date`, `shop_id`, `owner_id`, `amount`) VALUES (:sale_date, :shop_id, :owner_id, :amount)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':sale_date', $sale_date, PDO::PARAM_STR);
			$prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':amount', $amount, PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function updateOB($array)
	{
		$id = $array['id'];
		$sale_date = $array['sale_date'];
		$amount = $array['amount'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_ob}` SET `sale_date`=:sale_date, `amount`=:amount WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':sale_date', $sale_date, PDO::PARAM_STR);
			$prepare->bindParam(':amount', $amount, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function deleteOB($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_ob}` SET `flag`=2 WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	// insert method
	public function makeTransaction($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$return_ref = !empty($array['return_ref']) ? $array['return_ref'] : null;
			$supply_ref = !empty($array['supply_ref']) ? $array['supply_ref'] : null;
			$order_ref = !empty($array['order_ref']) ? $array['order_ref'] : null;
			$salary_ref = !empty($array['salary_ref']) ? $array['salary_ref'] : null;
			$loan_ref = !empty($array['loan_ref']) ? $array['loan_ref'] : null;

			$stmt = "INSERT INTO `{$this->table_transactions}` (`description`, `reference`, `transaction_date`, `created_by`, `shopId`, `order_ref`,`supply_ref`, `return_ref`, `salary_ref`, `loan_ref`, `transsaction_type`) VALUES (:description, :reference, :transaction_date, :created_by, :shopId, :order_ref, :supply_ref, :return_ref, :salary_ref, :loan_ref, :transaction_type)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
			$prepare->bindParam(':reference', $array['reference'], PDO::PARAM_STR);
			$prepare->bindParam(':transaction_date', $array['transaction_date'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
			$prepare->bindParam(':order_ref', $order_ref, PDO::PARAM_STR);
			$prepare->bindParam(':supply_ref', $supply_ref, PDO::PARAM_STR);
			$prepare->bindParam(':return_ref', $return_ref, PDO::PARAM_STR);
			$prepare->bindParam(':salary_ref', $salary_ref, PDO::PARAM_STR);
			$prepare->bindParam(':loan_ref', $loan_ref, PDO::PARAM_STR);
			$prepare->bindParam(':transaction_type', $array['transaction_type'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	// insert method
	public function makeEntry($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$paymentMode = !empty($array['payment_mode']) ? $array['payment_mode'] : 1;
			$stmt = "INSERT INTO `{$this->table_ledger_entries}` (`transaction_id`, `account_id`, `entry_type`, `amount`, `user_id`, `description`, `payment_mode`) VALUES (:transaction_id, :account_id, :entry_type, :amount, :user_id, :description, :payment_mode)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':transaction_id', $array['transaction_id'], PDO::PARAM_STR);
			$prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_STR);
			$prepare->bindParam(':entry_type', $array['entry_type'], PDO::PARAM_STR);
			$prepare->bindParam(':amount', $array['amount'], PDO::PARAM_STR);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
			$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
			$prepare->bindParam(':payment_mode', $paymentMode, PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
}
