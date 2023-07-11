<?php

class Publishers extends Connection
{

	private $table = 'publishers';
	private $table_products = 'products';
	private $table_stproducts = 'store_products';

	public function getPublishersPagination($params)
	{
		try {

			$search = "(pu.full_name LIKE '%" . $params["search"] . "%' ) ";

			$stmt = "select count(total) as count from (SELECT count(p.id) as total, pu.* FROM `{$this->table}` as pu left join `{$this->table_products}` as p on p.publisher_id=pu.id left join `{$this->table_stproducts}` as st on  st.product_id = p.id and st.shopId = :shopId WHERE $search  group by p.publisher_id, pu.full_name, pu.id, pu.discount_type, pu.discount_amount, pu.discount_status) as t";
			// $stmt = "SELECT count(pu.id) FROM `{$this->table}` as pu left join `{$this->table_products}` as p on p.publisher_id=pu.id left join `{$this->table_stproducts}` as st on  st.product_id = p.id WHERE $search";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare->execute();
			$total = $prepare->fetch(PDO::FETCH_ASSOC);
			// print_r($total);
			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = empty($total) ? 0 : $total['count'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset =  ((!empty($currentPage) ? $currentPage : 1) - 1) * $no_of_records_per_page;
			$stmt = "SELECT count(p.id) as total, pu.* FROM `{$this->table}` as pu left join `{$this->table_products}` as p on p.publisher_id=pu.id left join `{$this->table_stproducts}` as st on  st.product_id = p.id  and st.shopId = :shopId  WHERE $search group by p.publisher_id, pu.full_name, pu.id, pu.discount_type, pu.discount_amount, pu.discount_status order by pu.id asc LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getPublishers($ownerId)
	{
		$stmt = "SELECT * FROM  `{$this->table}` where owner_id=:owner_id";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':owner_id', $ownerId, PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	public function updatePublisher($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET full_name=:full_name, discount_type=:discount_type, discount_amount=:discount_amount, discount_status=:discount_status, pin=:pin WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->bindParam(':discount_type', $array['discount_type'], PDO::PARAM_STR);
			$prepare->bindParam(':discount_amount', $array['discount_amount'], PDO::PARAM_STR);
			$prepare->bindParam(':discount_status', $array['discount_status'], PDO::PARAM_STR);
			$prepare->bindParam(':pin', $array['pin'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function createPublisher($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `discount_type`, `discount_amount`, `discount_status`, `owner_id`, `pin`) VALUES (:full_name, :discount_type, :discount_amount, :discount_status, :owner_id, :pin)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':discount_type', $array['discount_type'], PDO::PARAM_STR);
			$prepare->bindParam(':discount_amount', $array['discount_amount'], PDO::PARAM_STR);
			$prepare->bindParam(':discount_status', $array['discount_status'], PDO::PARAM_STR);
			$prepare->bindParam(':pin', $array['pin'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function deletePublisher($array)
	{
		try {
			$stmt = "DELETE FROM `{$this->table}` WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			return $prepare->execute();
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
