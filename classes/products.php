<?php

class Products extends Connection
{

	private $table = 'products';
	private $table_st = 'store_products';
	private $pc_table = 'products_code';

	public function getOwnerProductsPagination($owner_id, $params, $shopId = null, $mobileCol = false)
	{
		try {

			$searchQry = "";
			$sortByQry = "";

			$prefix = "%";

			if (!empty($params['correction'])) {
				$prefix = "";
			}

			$type = "";

			if (!empty($params['type'])) {
				$type = " AND p.product_type = " . $params['type'] . " ";
			}

			$pin = "";
			if (!empty($params['pin'])) {
				$pin .= " AND p.pin > 0";
			}
			$dup = "";
			if (!empty($params['dup'])) {
				$dup .= " AND p.dup > 0";
			}
			$publisher_query = "";
			if (!empty($params['publisher_id'])) {
				$publisher_query = " AND p.publisher_id = '" . $params['publisher_id'] . "' ";
			}

			$status_query = "";
			if ($params['status'] == 0) {
				$status_query = " AND p.is_active = 0 ";
			} else {
				$status_query = ' AND p.is_active = 1';
			}
			if ($params['searchBy'] == 'id' && !empty($params["search"])) {
				$searchQry = "AND (p.id = " . $params["search"] . " OR p.code = " . $params["search"] . ")";
			} else if ($params['searchBy'] == 'cource' && !empty($params["courceId"])) {
				$searchQry = "AND c.program_id = " . $params["courceId"];
			} else if (!empty($params['searchBy']) && $params['searchBy'] == 'multi') {
				if (!empty($params['full_name'])) {
					$searchQry .= " AND p.full_name LIKE '" . $prefix . $params["full_name"] . "%'";
				}
				if (!empty($params['author'])) {
					$searchQry .= " AND p.author LIKE '" . $prefix . $params["author"] . "%'";
				}
				if (!empty($params['board'])) {
					$searchQry .= " AND p.board LIKE '" . $prefix . $params["board"] . "%'";
				}
				if (!empty($params['group'])) {
					$searchQry = " AND p.group LIKE '" . $prefix . $params["group"] . "%'";
				}
			} else if (!empty($params['searchBy']) && !empty($params["search"])) {
				$name = $params['searchBy'];
				if ($name == 'group') {
					$searchQry = "AND p.group LIKE '" . $prefix . $params["search"] . "%'";
				} else if ($name == 'author') {
					$searchQry = "AND p.author LIKE '" . $prefix . $params["search"] . "%'";
				} else if ($name == 'board') {
					$searchQry = "AND p.board LIKE '" . $prefix . $params["search"] . "%'";
				} else if ($name == 'category') {
					$searchQry = "AND p.cat LIKE '" . $prefix . $params["search"] . "%'";
				} else if ($name == 'subCategory') {
					$searchQry = "AND p.sub_cat LIKE '" . $prefix . $params["search"] . "%'";
				} else $searchQry = "AND (p.id = '" . $params["search"] . "' OR p.code = '" . $params["search"] . "' OR p.full_name LIKE '" . $prefix . $params["search"] . "%' OR p.group LIKE '" . $prefix . $params["search"] . "%' OR p.description LIKE '" . $prefix . $params["search"] . "%' OR p.board LIKE '" . $prefix . $params["search"] . "%' OR p.author LIKE '" . $prefix . $params["search"] . "%' OR p.price LIKE '" . $prefix . $params["search"] . "%' OR pc.code LIKE '" . $prefix . $params["search"] . "%' ) ";
			} else {
				$searchQry = "AND (p.id = '" . $params["search"] . "' OR p.code = '" . $params["search"] . "' OR p.full_name LIKE '" . $prefix . $params["search"] . "%' OR p.group LIKE '" . $prefix . $params["search"] . "%' OR p.description LIKE '" . $prefix . $params["search"] . "%' OR p.board LIKE '" . $prefix . $params["search"] . "%' OR p.author LIKE '" . $prefix . $params["search"] . "%' OR p.price LIKE '" . $prefix . $params["search"] . "%' OR pc.code LIKE '" . $prefix . $params["search"] . "%' ) ";
			}

			$catQry = "";

			if (!empty($params['cat_ids'])) {
				$catQry = "AND p.cat_id IN (" . implode(',', $params['cat_ids']) . ") ";
			}

			if (!empty($params['sortByField'])) {
				$name = $params['sortByField'];
				$order = $params['sortByOrder'];
				if ($name == 'title') {
					$sortByQry = " ORDER BY p.full_name " . $params['sortByOrder'];
				}
				if ($name == 'group') {
					$sortByQry = " ORDER BY p.group " . $params['sortByOrder'];
				}
				if ($name == 'author') {
					$sortByQry = " ORDER BY p.author " . $params['sortByOrder'];
				}
				if ($name == 'price') {
					$sortByQry = " ORDER BY p.price " . $params['sortByOrder'];
				}
				if ($name == 'stock') {
					$sortByQry = " ORDER BY p.in_hand " . $params['sortByOrder'];
				}
			}

			$innerJoin = "";
			if (!empty($shopId)) {
				$innerJoin .= " INNER JOIN {$this->table_st} as sp on sp.product_id = p.id and shopId=$shopId and sp.status = 1 ";
			}

			$column = "";

			$minQry = "";
			if (!empty($shopId)) {
				$column = ", (sp.qty - sp.stock_out) as qty, sp.min_qty ";
				if (!empty($params['minQty'])) {
					$minQry = " HAVING qty <= sp.min_qty order by p.priority desc";
				}
			}

			$mobileCols = "p.id,p.full_name";
			$allCols = "group_concat(pc.code) as other_codes, p.author, p.barcode, p.code, p.cat_id, p.board, p.group, p.id, p.pprice, p.publisher_id, concat(p.id, ' | ', p.full_name) as full_name, pub.full_name as publisherName, pub.discount_type, pub.discount_amount, CONVERT(case when (pub.discount_amount > 0) then (p.price * (1 - (pub.discount_amount / 100)) ) else p.price end, DECIMAL) as price, is_active $column";

			$mainCols = "";
			if (!empty($mobileCol)) {
				$mainCols = $mobileCols;
			} else {
				$mainCols = $allCols;
			}

			$stmt = "SELECT count(b.id) as count FROM (SELECT $mainCols  FROM `{$this->table}` as p $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id  WHERE p.`owner_id`=:owner_id $status_query $publisher_query $dup $type $pin $searchQry $catQry GROUP BY p.id $sortByQry $minQry) AS b";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$total = $prepare->fetch(PDO::FETCH_ASSOC);
			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = empty($total) ? 0 : $total['count'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset =  ((!empty($currentPage) ? $currentPage : 1) - 1) * $no_of_records_per_page;

			$stmt = "SELECT *, price, concat(`id`, '|', `price`, '|', `full_name`, '|', COALESCE(`publisherName`, ''), '|', COALESCE(`author`, ''), '|', COALESCE(`board`, ''), '|', COALESCE(`code`, ''), '|', COALESCE(`barcode`, ''), '|', COALESCE(`other_codes`, '')) as searchString FROM (SELECT $mainCols  FROM `{$this->table}` as p $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id  WHERE p.`owner_id`=:owner_id $status_query $publisher_query $dup $type $pin $searchQry $catQry GROUP BY p.id $sortByQry $minQry order by code desc LIMIT :offset, :perPage) AS b";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'params' => $params, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getOrderProductsPagination($owner_id, $customer_id, $params, $shopId)
	{
		try {

			// var_dump($params);

			$searchQry = "";
			$sortByQry = "";

			$prefix = "%";

			$searchQry = "AND (p.id = '" . $params["search"] . "' OR p.code = '" . $params["search"] . "' OR p.full_name LIKE '" . $prefix . $params["search"] . "%' OR p.group LIKE '" . $prefix . $params["search"] . "%' OR p.description LIKE '" . $prefix . $params["search"] . "%' OR p.board LIKE '" . $prefix . $params["search"] . "%' OR p.author LIKE '" . $prefix . $params["search"] . "%' OR p.price LIKE '" . $prefix . $params["search"] . "%' ) ";

			$innerJoin = "";
			if (!empty($shopId)) {
				$innerJoin .= "INNER JOIN order_items as oi on oi.product_id = p.id INNER JOIN orders as o on o.id = oi.order_id and o.status IN (2, 5, 6, 7, 8, 9)";
			}

			$column = "";

			$stmt = "SELECT count(p.id) as count FROM `{$this->table}` as p $innerJoin WHERE p.owner_id=:owner_id and o.customer_id=:customer_id $searchQry  group by p.id, oi.price";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
			$prepare->execute();
			$total = $prepare->fetch(PDO::FETCH_ASSOC);
			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = empty($total) ? 0 : $total['count'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset =  ((!empty($currentPage) ? $currentPage : 1) - 1) * $no_of_records_per_page;




			$stmt = "SELECT p.*, concat(p.id, ' | ', p.full_name) as full_name, sum(oi.quantity) as maxQty, customer_id, (oi.price - oi.discount) as price, oi.discount, oi.order_id  FROM `{$this->table}` as p $innerJoin WHERE p.owner_id=:owner_id and o.customer_id=:customer_id $searchQry group by p.id, oi.price LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getSupplyProductsPagination($owner_id, $supplier_id, $params, $shopId)
	{
		try {

			// var_dump($params);

			$searchQry = "";
			$sortByQry = "";

			$prefix = "%";

			$searchQry = "AND (p.id = '" . $params["search"] . "' OR p.code = '" . $params["search"] . "' OR p.full_name LIKE '" . $prefix . $params["search"] . "%' OR p.group LIKE '" . $prefix . $params["search"] . "%' OR p.description LIKE '" . $prefix . $params["search"] . "%' OR p.board LIKE '" . $prefix . $params["search"] . "%' OR p.author LIKE '" . $prefix . $params["search"] . "%' OR p.price LIKE '" . $prefix . $params["search"] . "%' ) ";

			$innerJoin = "";
			if (!empty($shopId)) {
				$innerJoin .= "INNER JOIN supply_items as oi on oi.product_id = p.id INNER JOIN supply as o on o.id = oi.supply_id and o.status = 2";
			}

			$column = "";

			$stmt = "SELECT count(p.id) as count FROM `{$this->table}` as p $innerJoin WHERE p.owner_id=:owner_id and o.supplier_id=:supplier_id $searchQry  group by p.id, oi.price";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':supplier_id', $supplier_id, PDO::PARAM_STR);
			$prepare->execute();
			$total = $prepare->fetch(PDO::FETCH_ASSOC);
			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = empty($total) ? 0 : $total['count'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset =  ((!empty($currentPage) ? $currentPage : 1) - 1) * $no_of_records_per_page;




			$stmt = "SELECT p.*, concat(p.id, ' | ', p.full_name) as full_name, sum(oi.quantity) as maxQty, supplier_id, oi.price as price, 0 as discount, oi.supply_id, oi.id, oi.product_id  FROM `{$this->table}` as p $innerJoin WHERE p.owner_id=:owner_id and o.supplier_id=:supplier_id $searchQry group by p.id, oi.price LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':supplier_id', $supplier_id, PDO::PARAM_STR);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function assignProductsPublisher($owner_id, $params, $shopId = null)
	{
		try {

			$searchQry = "";
			$sortByQry = "";

			$prefix = "%";

			if (!empty($params['correction'])) {
				$prefix = "";
			}

			$pin = "";
			if (!empty($params['pin'])) {
				$pin .= " AND p.pin > 0";
			}
			$publisher_query = "";
			if (!empty($params['publisher_id'])) {
				$publisher_query = " AND p.publisher_id = '" . $params['publisher_id'] . "' ";
			}
			if ($params['searchBy'] == 'id' && !empty($params["search"])) {
				$searchQry = "AND (p.id = " . $params["search"] . " OR p.code = " . $params["search"] . ")";
			} else if ($params['searchBy'] == 'cource' && !empty($params["courceId"])) {
				$searchQry = "AND c.program_id = " . $params["courceId"];
			} else if (!empty($params['searchBy']) && $params['searchBy'] == 'multi') {
				if (!empty($params['full_name'])) {
					$searchQry .= " AND p.full_name LIKE '" . $prefix . $params["full_name"] . "%'";
				}
				if (!empty($params['author'])) {
					$searchQry .= " AND p.author LIKE '" . $prefix . $params["author"] . "%'";
				}
				if (!empty($params['board'])) {
					$searchQry .= " AND p.board LIKE '" . $prefix . $params["board"] . "%'";
				}
				if (!empty($params['group'])) {
					$searchQry = " AND p.group LIKE '" . $prefix . $params["group"] . "%'";
				}
			} else if (!empty($params['searchBy']) && !empty($params["search"])) {
				$name = $params['searchBy'];
				if ($name == 'group') {
					$searchQry = "AND p.group LIKE '" . $prefix . $params["search"] . "%'";
				} else if ($name == 'author') {
					$searchQry = "AND p.author LIKE '" . $prefix . $params["search"] . "%'";
				} else if ($name == 'board') {
					$searchQry = "AND p.board LIKE '" . $prefix . $params["search"] . "%'";
				} else if ($name == 'category') {
					$searchQry = "AND p.cat LIKE '" . $prefix . $params["search"] . "%'";
				} else if ($name == 'subCategory') {
					$searchQry = "AND p.sub_cat LIKE '" . $prefix . $params["search"] . "%'";
				} else $searchQry = "AND (p.id = '" . $params["search"] . "' OR p.code = '" . $params["search"] . "' OR p.full_name LIKE '" . $prefix . $params["search"] . "%' OR p.group LIKE '" . $prefix . $params["search"] . "%' OR p.description LIKE '" . $prefix . $params["search"] . "%' OR p.board LIKE '" . $prefix . $params["search"] . "%' OR p.author LIKE '" . $prefix . $params["search"] . "%' OR p.price LIKE '" . $prefix . $params["search"] . "%' OR pc.code LIKE '" . $prefix . $params["search"] . "%' ) ";
			} else {
				$searchQry = "AND (p.id = '" . $params["search"] . "' OR p.code = '" . $params["search"] . "' OR p.full_name LIKE '" . $prefix . $params["search"] . "%' OR p.group LIKE '" . $prefix . $params["search"] . "%' OR p.description LIKE '" . $prefix . $params["search"] . "%' OR p.board LIKE '" . $prefix . $params["search"] . "%' OR p.author LIKE '" . $prefix . $params["search"] . "%' OR p.price LIKE '" . $prefix . $params["search"] . "%' OR pc.code LIKE '" . $prefix . $params["search"] . "%' ) ";
			}

			if (!empty($params['sortByField'])) {
				$name = $params['sortByField'];
				$order = $params['sortByOrder'];
				if ($name == 'title') {
					$sortByQry = " ORDER BY p.full_name " . $params['sortByOrder'];
				}
				if ($name == 'group') {
					$sortByQry = " ORDER BY p.group " . $params['sortByOrder'];
				}
				if ($name == 'author') {
					$sortByQry = " ORDER BY p.author " . $params['sortByOrder'];
				}
				if ($name == 'price') {
					$sortByQry = " ORDER BY p.price " . $params['sortByOrder'];
				}
				if ($name == 'stock') {
					$sortByQry = " ORDER BY p.in_hand " . $params['sortByOrder'];
				}
			}

			$innerJoin = "";
			if (!empty($shopId)) {
				$innerJoin .= " INNER JOIN {$this->table_st} as sp on sp.product_id = p.id and shopId=$shopId and sp.status = 1 ";
			}

			$column = "";

			if (!empty($shopId)) {
				$column = ", (sp.qty - sp.stock_out) as qty ";
			}

			// $stmt = "SELECT count(p.id) as count FROM `{$this->table}` as p $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id  LEFT JOIN program_books as c ON c.product_id = p.id WHERE p.owner_id=:owner_id $publisher_query $pin $searchQry";
			// $prepare = $this->dbh->prepare($stmt);
			// $prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			// $prepare->execute();
			// $total = $prepare->fetch(PDO::FETCH_ASSOC);
			// $no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			// $total_rows = empty($total) ? 0 : $total['count'];
			// $total_pages = ceil($total_rows / $no_of_records_per_page);
			// $currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			// $offset =  ((!empty($currentPage) ? $currentPage : 1) -1) * $no_of_records_per_page;




			$stmt = "UPDATE `{$this->table}` as p  $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id SET p.publisher_id=:publisher_id WHERE p.`owner_id`=:owner_id and p.is_active = 1  $publisher_query  $pin $searchQry";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':publisher_id', $params['selectedPublisherId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getOwnerProducts($owner_id)
	{
		try {
			$stmt = "SELECT p.*, concat(p.id, ' | ', p.full_name) as full_name, pub.discount_type, pub.discount_amount, CONVERT(case when (pub.discount_amount > 0) then (p.price * (1 - (pub.discount_amount / 100)) ) else p.price end, DECIMAL) as price FROM `{$this->table}` as p left join publishers as pub on pub.id = p.publisher_id WHERE p.`owner_id`=:owner_id and p.is_active = 1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function searchProducts($shopId, $search)
	{
		$searchQuery = "(p.full_name LIKE '%" . $search . "%' OR p.code LIKE '%" . $search . "%' OR p.group LIKE '%" . $search . "%' OR p.description LIKE '%" . $search . "%' OR p.board LIKE '%" . $search . "%' OR p.author LIKE '%" . $search . "%' OR p.price LIKE '%" . $search . "%' OR pc.code LIKE '%" . $search . "%' ) ";
		$stmt = "SELECT p.*, concat(p.id, ' | ', p.full_name) as full_name, FROM  `{$this->table}` as p LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id WHERE $searchQuery and p.is_active GROUP BY p.id LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		// $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	public function searchProductGroups($ownerId, $search)
	{
		$stmt = "SELECT `group` FROM  `{$this->table}` WHERE owner_id=:owner_id and is_active = 1 and `group` LIKE '%" . $search . "%' group by `group` LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':owner_id', $ownerId, PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	public function searchProductAuthors($ownerId, $search)
	{
		$stmt = "SELECT `author` FROM  `{$this->table}` WHERE owner_id=:owner_id and is_active = 1 and `author` LIKE '%" . $search . "%' group by `author` LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':owner_id', $ownerId, PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	public function searchProductBoards($ownerId, $search)
	{
		$stmt = "SELECT `board` FROM  `{$this->table}` WHERE owner_id=:owner_id and is_active = 1 and `board` LIKE '%" . $search . "%' group by `board` LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':owner_id', $ownerId, PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}
	public function getStoreProducts($owner_id, $shopId = null)
	{
		$shopCondition = "";
		if ($shopId) {
			$shopCondition .= " AND st.shopId = $shopId";
		}

		try {
			$stmt = "SELECT st.*, s.code, s.id as product_id, s.full_name, s.group, s.publisher_id, s.author, pub.full_name as publisherName FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS s ON s.id = st.product_id LEFT JOIN publishers as pub on s.publisher_id = pub.id WHERE st.`owner_id`=:owner_id and s.is_active = 1  and st.status = 1 " . $shopCondition . " order by s.id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getStoreProductsPagination($owner_id, $params, $shopId = null)
	{
		$shopCondition = "";
		if ($shopId) {
			$shopCondition .= " AND st.shopId = $shopId";
		}

		$searchQry = "";

		if (!empty($params["search"])) {
			$searchQry = "AND (p.id = '" . $params["search"] . "' OR p.code = '" . $params["search"] . "' OR p.full_name LIKE '%" . $params["search"] . "%' OR p.group LIKE '%" . $params["search"] . "%' OR p.description LIKE '%" . $params["search"] . "%' OR p.board LIKE '%" . $params["search"] . "%' OR p.author LIKE '%" . $params["search"] . "%' OR p.price LIKE '%" . $params["search"] . "%' OR pc.code LIKE '" . $params["search"] . "' ) ";
		}
		$publisher_query = "";

		if (!empty($params["publisher_id"])) {
			$publisher_query = " AND p.publisher_id = '" . $params["publisher_id"] . "'";
		}


		try {

			$stmt1 = "SELECT count(st.id) as count FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS p ON p.id = st.product_id LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id WHERE p.is_active = 1 and st.`owner_id`=:owner_id and st.status = 1 $shopCondition $searchQry $publisher_query order by p.id";
			$prepare = $this->dbh->prepare($stmt1);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$total = $prepare->fetch(PDO::FETCH_ASSOC);
			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = empty($total) ? 0 : $total['count'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset =  ((!empty($currentPage) ? $currentPage : 1) - 1) * $no_of_records_per_page;


			$stmt = "SELECT st.*, p.code, p.id as product_id, p.full_name, p.group, p.publisher_id, p.author FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS p ON p.id = st.product_id LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id WHERE p.is_active = 1 and st.`owner_id`=:owner_id and st.status = 1 $shopCondition $searchQry $publisher_query order by p.id desc LIMIT :offset, :perPage";
			$prepare2 = $this->dbh->prepare($stmt);
			$prepare2->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare2->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare2->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare2->execute();
			$result = $prepare2->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getProduct($id, $owner_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id AND owner_id = :owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			if (!empty($result)) {
				$programs = new Programs();
				$result['codes'] = $this->getProductCodes($id);
				$result['programs'] = $programs->getBookPrograms($id);
			}
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getCategoryProducts($owner_id, $ids, $shopId)
	{
		try {
			$result = $this->getOwnerProductsPagination($owner_id, ['page' => 1, 'perPage' => 1000, 'cat_ids' => $ids, 'status' => 1], $shopId);
			$res = [];
			foreach ($result['records'] as $key => $value) {
				$res[$value['cat_id']][] = $value;
			}
			return $res;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getProductCodes($id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->pc_table}` WHERE product_id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getStoreProduct($id, $owner_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE id=:id and status = 1 AND owner_id = :owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getOwnerStoreProduct($shopId, $product_id, $owner_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE product_id=:product_id and status = 1 AND shopId=:shopId AND owner_id = :owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':product_id', $product_id, PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function alreadyExistsProduct($array)
	{
		$product_id = $array['product_id'];
		$shopId = $array['shopId'];
		try {
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE status = 1 and shopId=:shopId AND product_id = :product_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $product_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	// "10321","10322","10323","10324","10325","10326","10327","10328","10329","10330","10331","10332","10333","10334","10335","10336","10337","10338","10339","10340","10341","10342","10343","10344","10345","10346","10347","10348","10349","10350","10351","10352","10353","10354","10355","10356","10357","10358","10359","10371","10374","10376","10403","11011","11796","12638","12643","12644","12645","12646","12696","13062","13063","13064","13065","13066","13067","13068","13069","13070","13071","13072","13073","13074","13075","13076","13077","13079","13080","13081","13082","13083","13084","13085","13087","13088","13089","13090","13091","13092","13093","13094","13095","13096","13097","13098","13099","13100","13101","13102","13103","13104","13105","13107","13108","13109","13110","13111","13112","13113","13114","13115","13116","13117","13118","13119","13120","13121","13122","13123","13124","13125","13126","13127","13128","13129","13130","13131","13132","13133","13134","13135","13136","13137","13138","13139","13140","13141","13142","13143","13144","13145","13146","13147","13148","13149","13150","13151","13152","13153","13154","13155","13156","13157","13158","13159","13160","13161","13162","13163","13164","13165","13166","13167","13168","13169","13170","13171","13172","13173","13174","13175","13176","13177","13178","13179","13180","13181","13182","13183","13184","13185","13186","13187","13188","13189","13190","13191","13192","13193","13194","13195","13196","13197","13198","13199","13200","13201","13202","13203","13204","13205","13206","13207","13208","13209","13210","13211","13212","13213","13214","13215","13216","13217","13218","13219","13220","13221","13222","13223","13225","13226","13227","13228","13229","13230","13231","13232","13233","13234","13235","13236","13238","13239","13240","13241","13242","13243","13244","13245","13246","13247","13248","13249","13250","13251","13252","13253","13254","13255","13256","13257","13258","13259","13260","13261","13262","13263","13264","13265","13268","13269","13270","13271","13272","13273","13274","13275","13276","13277","13278","13279","13280","13281","13282","13283","13284","13285","13286","13287","13288","13289","13290","13291","13292","13293","13294","13295","13296","13297","13298","13299","13300","13301","13302","13303","13304","13305","13306","13307","13308","13309","13310","13311","13312","13313","13314","13315","13316","13317","13318","13321","13322","13323","13324","13325","13328","13329","13330","13331","13336","13337","13338","13339","13340","13341","13342","13343","13344","13345","13346","13347","13348","13349","13350","13351","13352","13353","13354","13355","13356","13357","13358","13359","13360","13361","13362","13363","13364","13365","13366","13367","13368","13369","13373","13374","13375","13376","13377","13378","13379","13380","13381","13382","13383","13384","13385","13386","13387","13388","13389","13390","13391","13393","13394","13395","13396","13397","13398","13399","13400","13401","13402","13403","13404","13405","13406","13407","13408","13409","13410","13411","13412","13413","13414","13415","13416","13417","13418","13419","13420","13421","13422","13423","13424","13425","13426","13427","13428","13429","13430","13431","13433","13434","13435","13436","13437","13438","13439","13441","13442","13443","13444","13445","13446","13447","13448","13451","13452","13453","13454","13455","13456","13457","13458","13459","13460","13461","13462","13463","13465","13466","13467","13468","13469","13470","13471","13472","13473","13474","13475","13476","13477","13478","13479","13480","13481","13482","13483","13484","13488","13492","13493","13498","13499","13500","13501","13503","13505","13506","13507","13508","13509","13510","13511","13512"

	public function updateProductPublisher()
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET  `group`='Stationery', `publisher_id`=5 WHERE id IN (10321,10322,10323,10324,10325,10326,10327,10328,10329,10330,10331,10332,10333,10334,10335,10336,10337,10338,10339,10340,10341,10342,10343,10344,10345,10346,10347,10348,10349,10350,10351,10352,10353,10354,10355,10356,10357,10358,10359,10371,10374,10376,10403,11011,11796,12638,12643,12644,12645,12646,12696,13062,13063,13064,13065,13066,13067,13068,13069,13070,13071,13072,13073,13074,13075,13076,13077,13079,13080,13081,13082,13083,13084,13085,13087,13088,13089,13090,13091,13092,13093,13094,13095,13096,13097,13098,13099,13100,13101,13102,13103,13104,13105,13107,13108,13109,13110,13111,13112,13113,13114,13115,13116,13117,13118,13119,13120,13121,13122,13123,13124,13125,13126,13127,13128,13129,13130,13131,13132,13133,13134,13135,13136,13137,13138,13139,13140,13141,13142,13143,13144,13145,13146,13147,13148,13149,13150,13151,13152,13153,13154,13155,13156,13157,13158,13159,13160,13161,13162,13163,13164,13165,13166,13167,13168,13169,13170,13171,13172,13173,13174,13175,13176,13177,13178,13179,13180,13181,13182,13183,13184,13185,13186,13187,13188,13189,13190,13191,13192,13193,13194,13195,13196,13197,13198,13199,13200,13201,13202,13203,13204,13205,13206,13207,13208,13209,13210,13211,13212,13213,13214,13215,13216,13217,13218,13219,13220,13221,13222,13223,13225,13226,13227,13228,13229,13230,13231,13232,13233,13234,13235,13236,13238,13239,13240,13241,13242,13243,13244,13245,13246,13247,13248,13249,13250,13251,13252,13253,13254,13255,13256,13257,13258,13259,13260,13261,13262,13263,13264,13265,13268,13269,13270,13271,13272,13273,13274,13275,13276,13277,13278,13279,13280,13281,13282,13283,13284,13285,13286,13287,13288,13289,13290,13291,13292,13293,13294,13295,13296,13297,13298,13299,13300,13301,13302,13303,13304,13305,13306,13307,13308,13309,13310,13311,13312,13313,13314,13315,13316,13317,13318,13321,13322,13323,13324,13325,13328,13329,13330,13331,13336,13337,13338,13339,13340,13341,13342,13343,13344,13345,13346,13347,13348,13349,13350,13351,13352,13353,13354,13355,13356,13357,13358,13359,13360,13361,13362,13363,13364,13365,13366,13367,13368,13369,13373,13374,13375,13376,13377,13378,13379,13380,13381,13382,13383,13384,13385,13386,13387,13388,13389,13390,13391,13393,13394,13395,13396,13397,13398,13399,13400,13401,13402,13403,13404,13405,13406,13407,13408,13409,13410,13411,13412,13413,13414,13415,13416,13417,13418,13419,13420,13421,13422,13423,13424,13425,13426,13427,13428,13429,13430,13431,13433,13434,13435,13436,13437,13438,13439,13441,13442,13443,13444,13445,13446,13447,13448,13451,13452,13453,13454,13455,13456,13457,13458,13459,13460,13461,13462,13463,13465,13466,13467,13468,13469,13470,13471,13472,13473,13474,13475,13476,13477,13478,13479,13480,13481,13482,13483,13484,13488,13492,13493,13498,13499,13500,13501,13503,13505,13506,13507,13508,13509,13510,13511,13512)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function updateProductPrice($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET  `price`=:price WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$prepare->rowCount();
			return $array['product_id'];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateProduct($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET `full_name`=:full_name, `price`=:price, `pprice`=:pprice, `code`=:code, `description`=:description, `group`=:group, `board`=:board, `author`=:author, `image`=:image, `publisher_id`=:publisher_id, `cat_id`=:cat_id WHERE id=:id AND owner_id=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
			$prepare->bindParam(':pprice', $array['pprice'], PDO::PARAM_STR);
			$prepare->bindParam(':image', $array['image'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
			$prepare->bindParam(':group', $array['group'], PDO::PARAM_STR);
			$prepare->bindParam(':board', $array['board'], PDO::PARAM_STR);
			$prepare->bindParam(':author', $array['author'], PDO::PARAM_STR);
			$prepare->bindParam(':publisher_id', $array['publisher_id'], PDO::PARAM_INT);
			$prepare->bindParam(':cat_id', $array['cat_id'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function setPriority($id, $priority)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET `priority`=:priority WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':priority', $priority, PDO::PARAM_INT);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function setBookmark($id, $pin)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET `pin`=:pin WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':pin', $pin, PDO::PARAM_INT);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function setInactive($id, $action)
	{
		echo $action;
		try {
			$stmt = "UPDATE `{$this->table}` SET `is_active`=:is_active WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->bindParam(':is_active', $action, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function setDuplicate($id, $pin)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET `dup`=:pin WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':pin', $pin, PDO::PARAM_INT);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function updateProductCode($array)
	{
		try {
			$stmt = "UPDATE `{$this->pc_table}` SET `code`=:code WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateStoreProduct($array)
	{
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `min_qty`=:min_qty, `location`=:location, shopId=:shopId  WHERE id=:id";

			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':min_qty', $array['min_qty'], PDO::PARAM_STR);
			$prepare->bindParam(':location', $array['location'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteStoreProduct($array)
	{
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `status`=2 WHERE id=:id";

			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function assignProduct($array)
	{
		$data = $this->alreadyExistsProduct($array);
		if (empty($data)) {
			return $this->assignProductToStore($array);
		} else {
			return $this->updateProductToStore($array, $data);
		}
	}
	public function assignProductToStore($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table_st}` (`qty`, `stock_out`, `product_id`, `shopId`, `owner_id`, `location`) VALUES (:qty, :stock_out, :product_id, :shopId, :owner_id, :location)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':qty', $array['qty'], PDO::PARAM_STR);
			$prepare->bindParam(':stock_out', $array['stock_out'], PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_INT);
			$prepare->bindParam(':location', $array['location'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function updateProductToStore($array, $data)
	{
		try {
			$data['qty'] += $array['qty'];
			$data['stock_out'] += $array['stock_out'];

			$stmt = "UPDATE `{$this->table_st}` SET `qty`=:qty, `stock_out`=:stock_out WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':qty', $data['qty'], PDO::PARAM_STR);
			$prepare->bindParam(':stock_out', $data['stock_out'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $data['id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function addProductQty($id, $qty, $shopId, $type = 1)
	{
		try {
			if ($type == 1 || $type == 3) {
				$stmt = "UPDATE `{$this->table_st}` SET `qty`=qty+:qty WHERE product_id=:id and shopId = :shopId";
			} elseif ($type == 2) {
				$stmt = "UPDATE `{$this->table_st}` SET `faulty_qty`=faulty_qty+:qty WHERE  product_id=:id and shopId = :shopId";
			}
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->bindParam(':qty', $qty, PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function resetCounters($owner_id, $shopId)
	{
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `qty`=0,`stock_out`=0,`min_qty`=2 WHERE owner_id=:owner_id and shopId=:shopId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function addProductSupply($array)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET `in_hand`=in_hand+:qty, pprice=:pprice, price=:price, barcode=:barcode WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->bindParam(':qty', $array['qty'], PDO::PARAM_INT);
			$prepare->bindParam(':pprice', $array['pprice'], PDO::PARAM_INT);
			$prepare->bindParam(':price', $array['price'], PDO::PARAM_INT);
			$prepare->bindParam(':barcode', $array['barcode'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		}
	}

	public function subProductQty($array)
	{
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `stock_out`=stock_out+:stock_out WHERE product_id=:product_id and shopId=:shopId and owner_id=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':stock_out', $array['quantity'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createProduct($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `owner_id`, `user_id`, `price`, `pprice`, `image`, `code`, `barcode`, `description`, `group`, `board`, `author`, `publisher_id`, `cat_id`, `note`, `product_type`) VALUES (:full_name, :owner_id, :user_id, :price, :pprice, :image, :code, :barcode, :description, :group, :board, :author, :publisher_id, :cat_id, :note, :product_type)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_INT);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_INT);
			$prepare->bindParam(':price', $array['price'], PDO::PARAM_INT);
			$prepare->bindParam(':pprice', $array['pprice'], PDO::PARAM_INT);
			$prepare->bindParam(':image', $array['image'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':barcode', $array['barcode'], PDO::PARAM_STR);
			$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
			$prepare->bindParam(':board', $array['board'], PDO::PARAM_STR);
			$prepare->bindParam(':author', $array['author'], PDO::PARAM_STR);
			$prepare->bindParam(':group', $array['group'], PDO::PARAM_STR);
			$prepare->bindParam(':publisher_id', $array['publisher_id'], PDO::PARAM_STR);
			$prepare->bindParam(':cat_id', $array['cat_id'], PDO::PARAM_STR);
			$prepare->bindParam(':note', $array['note'], PDO::PARAM_STR);
			$prepare->bindParam(':product_type', $array['product_type'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createProductCode($array)
	{
		try {
			$stmt = "INSERT INTO `{$this->pc_table}` (`code`, `product_id`) VALUES (:code, :product_id)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteProductCode($array)
	{
		try {
			$stmt = "DELETE FROM `{$this->pc_table}` WHERE id=:id LIMIT 1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
