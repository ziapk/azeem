<?php

class Programs extends Connection
{

	private $table = 'programs';
	private $pb_table = 'program_books';
	private $booktable = 'products';

	public function getProgramsPagination($params)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$userInfo = UserInfo();
			$user = $userInfo['user'];

			$stmt = "SELECT COUNT(id) as total FROM `{$this->table}` where shopId=:shopId";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);

			$no_of_records_per_page = $params['perPage'] ? $params['perPage'] : 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
			$search = "shopId=:shopId and (degree LIKE '%" . $params["search"] . "%' OR program LIKE '%" . $params["search"] . "%' OR class LIKE '%" . $params["search"] . "%') ";
			$stmt = "SELECT * FROM `{$this->table}` WHERE $search LIMIT :offset, :perPage";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
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

	public function getPrograms()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];
			$stmt = "SELECT * FROM `{$this->table}` where shopId=:shopId";
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

	public function getProgram($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];
			$stmt = "SELECT * FROM `{$this->table}` where id=:id and shopId=:shopId";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function updateProgram($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];
			$stmt = "UPDATE `{$this->table}` SET degree=:degree, program=:program, class=:class, pin=:pin WHERE id=:id and shopId=:shopId";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':degree', $array['degree'], PDO::PARAM_STR);
			$prepare->bindParam(':program', $array['program'], PDO::PARAM_STR);
			$prepare->bindParam(':class', $array['class'], PDO::PARAM_STR);
			$prepare->bindParam(':pin', $array['pin'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function createProgram($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];
			$stmt = "INSERT INTO `{$this->table}` (`degree`, `program`, `class`, `pin`, `shopId`) VALUES (:degree, :program, :class, :pin, :shopId)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':degree', $array['degree'], PDO::PARAM_STR);
			$prepare->bindParam(':program', $array['program'], PDO::PARAM_STR);
			$prepare->bindParam(':class', $array['class'], PDO::PARAM_STR);
			$prepare->bindParam(':pin', $array['pin'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getPinPrograms()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$userInfo = UserInfo();
			$user = $userInfo['user'];
			$stmt = "SELECT * FROM `{$this->table}` where pin=1 and shopId=:shopId";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $user['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $key => $row) {
				$result[$key]['items'] = $this->getProgramBooks(['program_id' => $row['id']]);
			}
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getProgramBooks($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT p.*, concat(p.id, ' | ', p.full_name) as full_name FROM `{$this->booktable}` AS p LEFT JOIN `{$this->pb_table}` AS pb ON pb.product_id = p.id WHERE pb.`program_id`=:program_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':program_id', $array['program_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getBookPrograms($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT p.*, pb.id as program_book_id FROM `{$this->table}` AS p LEFT JOIN `{$this->pb_table}` AS pb ON pb.program_id = p.id WHERE pb.`product_id`=:product_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':product_id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}



	public function createProgramBook($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->pb_table}` (`program_id`, `product_id`) VALUES (:program_id, :product_id)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':program_id', $array['program_id'], PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function deleteProgram($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$this->deleteProgramBooks(['program_id' => $id]);
			$stmt = "DELETE FROM `{$this->table}` WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function deleteProgramBooks($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "DELETE FROM `{$this->pb_table}` WHERE program_id=:program_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':program_id', $array['program_id'], PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function deleteProgramBook($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "DELETE FROM `{$this->pb_table}` WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}


	public function searchProgamField($field, $search)
	{
		$stmt = "SELECT {$field} as title FROM  `{$this->table}` WHERE {$field} LIKE '%" . $search . "%' group by {$field} LIMIT 10";
		$prepare = $dbh->prepare($stmt);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}
}
