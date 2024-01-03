<?php


class Connection
{
	private $host = 'localhost';
	private $dbname = 'reclydmy_azeem';
	private $user = 'reclydmy_pos';
	private $pass = ';4B)pQC=K0&v';


	// private $host = 'localhost';
	// private $dbname = 'reclydmy_azeem';
	// private $user = 'root';
	// private $pass = 'root';
	// private $sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

	public $dbh;

	function __construct()
	{
		$this->connection();
	}
	private function connection()
	{
		try {
			// , PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="' . $this->sql_mode . '"'
			$this->dbh = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbname, $this->user, $this->pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="' . $this->sql_mode . '"'));
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
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
