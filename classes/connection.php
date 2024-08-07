<?php


class Connection extends ConnectionPool
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
	public function drawLocker($id)
	{
		ob_start();
		$_GET['id'] = $id;
		$_GET['detail'] = 'true';
		$_GET['largeView'] = 'large';
		include dirname(__FILE__) . '/../print/deposit.php';
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
