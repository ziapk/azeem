<?php

class Programs extends Connection
{

	private $table = 'programs';
	private $pb_table = 'program_books';
	private $booktable = 'products';

	public function getProgramsPagination($params)
	{
		try {

			$stmt = "SELECT COUNT(id) as total FROM `{$this->table}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);

			$no_of_records_per_page = $params['perPage'] ? $params['perPage'] : 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
			$search = "(degree LIKE '%" . $params["search"] . "%' OR program LIKE '%" . $params["search"] . "%' OR class LIKE '%" . $params["search"] . "%') ";
			$stmt = "SELECT * FROM `{$this->table}` WHERE $search LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getPrograms()
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getProgram($id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` where id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateProgram($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET degree=:degree, program=:program, class=:class, pin=:pin WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->bindParam(':degree', $array['degree'], PDO::PARAM_STR);
			$prepare->bindParam(':program', $array['program'], PDO::PARAM_STR);
			$prepare->bindParam(':class', $array['class'], PDO::PARAM_STR);
			$prepare->bindParam(':pin', $array['pin'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createProgram($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table}` (`degree`, `program`, `class`, `pin`) VALUES (:degree, :program, :class, :pin)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':degree', $array['degree'], PDO::PARAM_STR);
			$prepare->bindParam(':program', $array['program'], PDO::PARAM_STR);
			$prepare->bindParam(':class', $array['class'], PDO::PARAM_STR);
			$prepare->bindParam(':pin', $array['pin'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getPinPrograms()
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` where pin=1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $key => $row) {
				$result[$key]['items'] = $this->getProgramBooks(['program_id' => $row['id']]);
			}
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getProgramBooks($array)
	{
		try {
			$stmt = "SELECT p.*, concat(p.id, ' | ', p.full_name) as full_name FROM `{$this->booktable}` AS p LEFT JOIN `{$this->pb_table}` AS pb ON pb.product_id = p.id WHERE pb.`program_id`=:program_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':program_id', $array['program_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getBookPrograms($id)
	{
		try {
			$stmt = "SELECT p.*, pb.id as program_book_id FROM `{$this->table}` AS p LEFT JOIN `{$this->pb_table}` AS pb ON pb.program_id = p.id WHERE pb.`product_id`=:product_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':product_id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}



	public function createProgramBook($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->pb_table}` (`program_id`, `product_id`) VALUES (:program_id, :product_id)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':program_id', $array['program_id'], PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteProgram($id)
	{
		try {
			$this->deleteProgramBooks(['program_id' => $id]);
			$stmt = "DELETE FROM `{$this->table}` WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteProgramBooks($array)
	{
		try {
			$stmt = "DELETE FROM `{$this->pb_table}` WHERE program_id=:program_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':program_id', $array['program_id'], PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteProgramBook($array)
	{
		try {
			$stmt = "DELETE FROM `{$this->pb_table}` WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}


	public function searchProgamField($field, $search)
	{
		$stmt = "SELECT {$field} as title FROM  `{$this->table}` WHERE {$field} LIKE '%" . $search . "%' group by {$field} LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}
}
