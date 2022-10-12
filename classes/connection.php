<?php 


class Connection 
{
	private $host = 'localhost';
	private $dbname = 'azeem';
	private $user = 'root';
	private $pass = '';

	public $dbh;
	
	function __construct()
	{
		$this->connection();
	}
	private function connection()
	{
		try{
			$this->dbh = new PDO('mysql:host='.$this->host.';dbname='.$this->dbname, $this->user, $this->pass,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));	
		}catch (PDOException $e){
			print "Error!: " . $e->getMessage() . "<br/>";
		    die();
		}
	}
	public function normalToPassword($password)
	{
		return md5($password);
	}
	
}