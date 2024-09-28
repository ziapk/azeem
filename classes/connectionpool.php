<?php

class ConnectionPool
{
	private static $instance = null;
	private $pool = [];

	// private $maxConnections = 10; // Maximum number of connections in the pool

	private $host = 'localhost';
	private $dbname = 'reclydmy_azeem';
	private $user = 'reclydmy_pos';
	private $pass = ';4B)pQC=K0&v';

	// private $host = 'localhost';
	// private $dbname = 'reclydmy_shops';
	// private $user = 'reclydmy_shops';
	// private $pass = 'RpASZkt4KiTX';



	private function __construct()
	{
		// Initialize the connection pool
		$this->pool = $this->createConnection();
	}

	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new ConnectionPool();
		}
		return self::$instance;
	}

	public function getConnection()
	{
		return $this->pool;
	}

	public function releaseConnection($connection)
	{
		// Release the connection back to the pool
		// $this->pool[] = $connection;
	}

	private function createConnection()
	{
		try {
			$dbh = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbname, $this->user, $this->pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			return $dbh;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
