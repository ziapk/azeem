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

	public $dbh;

	function __construct()
	{
		$this->connection();
	}
	private function connection()
	{
		try {
			$this->dbh = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbname, $this->user, $this->pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
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

	public function normalToPassword($password)
	{
		return md5($password);
	}
}
