<?php


class Connection
{
	private static $instance = null;
	public $connectionPool;

	public function __construct()
	{
		// Initialize the connection pool
		$this->connectionPool = ConnectionPool::getInstance();
	}

	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new Connection();
		}
		return self::$instance;
	}

	public function executeQuery($query, $params = [], $rows = 'many', $debug = false)
	{
		// Acquire a connection from the pool
		$connection = $this->connectionPool->getConnection();

		try {
			if ($debug) {
				print_r($query);
				print_r($params);
			}
			$statement = $connection->prepare($query);
			foreach ($params as $key => &$value) {
				$statement->bindParam($key, $value, PDO::PARAM_STR);
			}
			$statement->execute();
			return $rows == 'many' ? $statement->fetchAll(PDO::FETCH_ASSOC) : $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			die("Error executing query: " . $e->getMessage());
		} finally {
			// Release the connection back to the pool
			$this->connectionPool->releaseConnection($connection);
		}
	}

	public function executeUpdate($query, $params = [])
	{
		// Acquire a connection from the pool
		$connection = $this->connectionPool->getConnection();

		try {
			$statement = $connection->prepare($query);
			foreach ($params as $row) {
				$statement->bindParam($row[0], $row[1], $row[2]);
			}
			$statement->execute();
			return $statement->rowCount(); // Return the number of rows affected by the update
		} catch (PDOException $e) {
			die("Error executing update query: " . $e->getMessage());
		} finally {
			// Release the connection back to the pool
			$this->connectionPool->releaseConnection($connection);
		}
	}

	public function drawInvoice($id)
	{
		ob_start();
		$_GET['id'] = $id;
		$_GET['detail'] = 'true';
		$_GET['largeView'] = 'large';
		include dirname(__FILE__) . '/../print/index.php';
		$html = ob_get_clean();
		ob_clean();
		return $html;
	}
	public function drawSupply($id)
	{
		ob_start();
		$_GET['id'] = $id;
		$_GET['detail'] = 'true';
		$_GET['largeView'] = 'large';
		include dirname(__FILE__) . '/../print/supply.php';
		$html = ob_get_clean();
		ob_clean();
		return $html;
	}

	public function drawReceiving($id)
	{
		ob_start();
		$_GET['id'] = $id;
		$_GET['detail'] = 'true';
		$_GET['largeView'] = 'large';
		include dirname(__FILE__) . '/../print/receiving.php';
		$html = ob_get_clean();
		ob_clean();
		return $html;
	}
	public function drawReturn($id)
	{
		ob_start();
		$_GET['id'] = $id;
		$_GET['detail'] = 'true';
		$_GET['largeView'] = 'large';
		include dirname(__FILE__) . '/../print/return.php';
		$html = ob_get_clean();
		ob_clean();
		return $html;
	}

	public function drawLedger($id, $type = 'c', $from = null, $to = null)
	{
		ob_start();
		$id = $id;
		$type = $type;
		$from = $from;
		$to = $to;
		$subtitle = "Ledger Summery Between " . $from . " and " . $to;
		include dirname(__FILE__) . '/../pages/chart-of-accounts/summeryContent.php';
		$html = ob_get_clean();
		ob_end_clean();
		return $html;
	}

	public function normalToPassword($password)
	{
		return md5($password);
	}
}
