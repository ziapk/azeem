<?php

class Products extends Connection
{

	private $table = 'products';
	private $table_st = 'store_products';
	private $pc_table = 'products_code';
	// private $table_ex = 'products_exchange_logs';
	// private $users = 'users';
	private $table_rack = 'racks';
	private $table_rack_products = 'rack_products';

	// backup old search query
	// public function getOwnerProductsPagination($owner_id, $params, $shopId = null, $mobileCol = false)
	// {
	// $dbh = $this->connectionPool->getConnection();
	// try {

	// 		$searchQry = "";
	// 		$sortByQry = "";

	// 		$prefix = "%";

	// 		if (!empty($params['correction'])) {
	// 			$prefix = "";
	// 		}

	// 		$type = "";

	// 		if (!empty($params['type'])) {
	// 			$type = " AND p.product_type = " . $params['type'] . " ";
	// 		}

	// 		$pin = "";
	// 		if (!empty($params['pin'])) {
	// 			$pin .= " AND p.pin > 0";
	// 		}
	// 		$dup = "";
	// 		if (!empty($params['dup'])) {
	// 			$dup .= " AND p.dup > 0";
	// 		}
	// 		$publisher_query = "";
	// 		if (!empty($params['publisher_id'])) {
	// 			$publisher_query = " AND p.publisher_id = '" . $params['publisher_id'] . "' ";
	// 		}

	// 		if (!empty($params['product_type'])) {
	// 			$publisher_query = " AND p.product_type = '" . $params['product_type'] . "' ";
	// 		}

	// 		$status_query = "";
	// 		if ($params['status'] == 0) {
	// 			$status_query = " AND p.is_active = 0 ";
	// 		} else {
	// 			$status_query = ' AND p.is_active = 1';
	// 		}
	// 		if ($params['searchBy'] == 'id' && !empty($params["search"])) {
	// 			$searchQry = "AND (p.id = " . $params["search"] . " OR p.code = " . $params["search"] . ")";
	// 		} else if ($params['searchBy'] == 'cource' && !empty($params["courceId"])) {
	// 			$searchQry = "AND c.program_id = " . $params["courceId"];
	// 		} else if (!empty($params['searchBy']) && $params['searchBy'] == 'multi') {
	// 			if (!empty($params['full_name'])) {
	// 				$searchQry .= " AND p.full_name LIKE '" . $prefix . $params["full_name"] . "%'";
	// 			}
	// 			if (!empty($params['author'])) {
	// 				$searchQry .= " AND p.author LIKE '" . $prefix . $params["author"] . "%'";
	// 			}
	// 			if (!empty($params['board'])) {
	// 				$searchQry .= " AND p.board LIKE '" . $prefix . $params["board"] . "%'";
	// 			}
	// 			if (!empty($params['group'])) {
	// 				$searchQry = " AND p.group LIKE '" . $prefix . $params["group"] . "%'";
	// 			}
	// 		} else if (!empty($params['searchBy']) && !empty($params["search"])) {
	// 			$name = $params['searchBy'];
	// 			if ($name == 'group') {
	// 				$searchQry = "AND p.group LIKE '" . $prefix . $params["search"] . "%'";
	// 			} else if ($name == 'author') {
	// 				$searchQry = "AND p.author LIKE '" . $prefix . $params["search"] . "%'";
	// 			} else if ($name == 'board') {
	// 				$searchQry = "AND p.board LIKE '" . $prefix . $params["search"] . "%'";
	// 			} else if ($name == 'category') {
	// 				$searchQry = "AND p.cat LIKE '" . $prefix . $params["search"] . "%'";
	// 			} else if ($name == 'subCategory') {
	// 				$searchQry = "AND p.sub_cat LIKE '" . $prefix . $params["search"] . "%'";
	// 			} else if ($name == 'publisher') {
	// 				$searchQry = "AND pub.full_name LIKE '" . $prefix . $params["search"] . "%'";
	// 			} else $searchQry = "AND (p.id = '" . $params["search"] . "' OR p.code = '" . $params["search"] . "' OR p.full_name LIKE '" . $prefix . $params["search"] . "%' OR p.group LIKE '" . $prefix . $params["search"] . "%' OR p.description LIKE '" . $prefix . $params["search"] . "%' OR p.board LIKE '" . $prefix . $params["search"] . "%' OR p.author LIKE '" . $prefix . $params["search"] . "%' OR p.price LIKE '" . $prefix . $params["search"] . "%' OR pc.code LIKE '" . $prefix . $params["search"] . "%' ) ";
	// 		} else {
	// 			$searchQry = "AND (p.id = '" . $params["search"] . "' OR p.code = '" . $params["search"] . "' OR p.full_name LIKE '" . $prefix . $params["search"] . "%' OR p.group LIKE '" . $prefix . $params["search"] . "%' OR p.description LIKE '" . $prefix . $params["search"] . "%' OR p.board LIKE '" . $prefix . $params["search"] . "%' OR p.author LIKE '" . $prefix . $params["search"] . "%' OR p.price LIKE '" . $prefix . $params["search"] . "%' OR pc.code LIKE '" . $prefix . $params["search"] . "%' ) ";
	// 		}

	// 		$catQry = "";

	// 		if (!empty($params['cat_ids'])) {
	// 			$catQry = "AND p.cat_id IN (" . implode(',', $params['cat_ids']) . ") ";
	// 		}

	// 		if (!empty($params['sortByField'])) {
	// 			$name = $params['sortByField'];
	// 			$order = $params['sortByOrder'];
	// 			if ($name == 'title') {
	// 				$sortByQry = " ORDER BY p.full_name " . $params['sortByOrder'];
	// 			}
	// 			if ($name == 'group') {
	// 				$sortByQry = " ORDER BY p.group " . $params['sortByOrder'];
	// 			}
	// 			if ($name == 'author') {
	// 				$sortByQry = " ORDER BY p.author " . $params['sortByOrder'];
	// 			}
	// 			if ($name == 'price') {
	// 				$sortByQry = " ORDER BY p.price " . $params['sortByOrder'];
	// 			}
	// 			if ($name == 'stock') {
	// 				$sortByQry = " ORDER BY p.in_hand " . $params['sortByOrder'];
	// 			}
	// 		}

	// 		$innerJoin = "";
	// 		if (!empty($shopId)) {
	// 			$innerJoin .= " INNER JOIN {$this->table_st} as sp on sp.product_id = p.id and shopId=$shopId and sp.status = 1 ";
	// 		}

	// 		$column = "";

	// 		$minQry = "";
	// 		if (!empty($shopId)) {
	// 			$column = ", (sp.qty - sp.stock_out) as qty, sp.min_qty ";
	// 			if (!empty($params['minQty'])) {
	// 				$minQry = " HAVING qty <= sp.min_qty order by p.priority desc, price desc, code desc ";
	// 			}
	// 		} else {
	// 			$minQry = " order by code desc, price desc ";
	// 		}

	// 		$mobileCols = "p.id,p.full_name";
	// 		$allCols = "group_concat(r.title) as rackNumbers, p.priority, group_concat(pc.code) as other_codes, p.author, p.barcode, p.code, p.cat_id, p.board, p.group, p.id, p.pprice, p.publisher_id, concat(p.id, ' | ', p.full_name) as full_name, pub.full_name as publisherName, pub.discount_type, pub.discount_amount, CONVERT(case when (pub.discount_amount > 0) then (p.price * (1 - (pub.discount_amount / 100)) ) else p.price end, DECIMAL) as price, is_active, product_type $column";

	// 		$mainCols = "";
	// 		if (!empty($mobileCol)) {
	// 			$mainCols = $mobileCols;
	// 		} else {
	// 			$mainCols = $allCols;
	// 		}

	// 		$stmt = "SELECT count(b.id) as count FROM (SELECT $mainCols  FROM `{$this->table}` as p $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN {$this->table_rack_products} as rp ON rp.product_id = p.id LEFT JOIN `{$this->table_rack}` as r on r.id = rp.rack_id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id  WHERE p.`owner_id`=:owner_id $status_query $publisher_query $dup $type $pin $searchQry $catQry GROUP BY p.id $sortByQry $minQry) AS b";
	// 		$prepare = $dbh->prepare($stmt);
	// 		$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
	// 		$prepare->execute();
	// 		$total = $prepare->fetch(PDO::FETCH_ASSOC);
	// 		$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
	// 		$total_rows = empty($total) ? 0 : $total['count'];
	// 		$total_pages = ceil($total_rows / $no_of_records_per_page);
	// 		$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
	// 		$offset =  ((!empty($currentPage) ? $currentPage : 1) - 1) * $no_of_records_per_page;

	// 		$stmt = "SELECT *, price, concat(`id`, '|', `price`, '|', `full_name`, '|', COALESCE(`publisherName`, ''), '|', COALESCE(`author`, ''), '|', COALESCE(`board`, ''), '|', COALESCE(`code`, ''), '|', COALESCE(`barcode`, ''), '|', COALESCE(`other_codes`, '')) as searchString FROM (SELECT $mainCols  FROM `{$this->table}` as p $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN {$this->table_rack_products} as rp ON rp.product_id = p.id LEFT JOIN `{$this->table_rack}` as r on r.id = rp.rack_id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id  WHERE p.`owner_id`=:owner_id $status_query $publisher_query $dup $type $pin $searchQry $catQry GROUP BY p.id $sortByQry $minQry LIMIT :offset, :perPage) AS b";
	// 		$prepare = $dbh->prepare($stmt);
	// 		$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
	// 		$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
	// 		$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
	// 		$prepare->execute();
	// 		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
	// 		return ['page' => $currentPage, 'params' => $params, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
	// 	} catch (PDOException $e) {
	// 		die("Error!: " . $e->getMessage() . "<br/>");
	// 	}
	// }

	// new
	public function getOwnerProductsPagination($owner_id, $params, $shopId = null, $mobileCol = false)
	{
		$dbh = $this->connectionPool->getConnection();
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
				$pin .= " AND sp.pin > 0";
			}
			$dup = "";
			if (!empty($params['dup'])) {
				$dup .= " AND p.dup > 0";
			}
			$publisher_query = "";
			if (!empty($params['publisher_id'])) {
				$publisher_query = " AND p.publisher_id = '" . $params['publisher_id'] . "' ";
			}

			if (!empty($params['product_type'])) {
				$publisher_query = " AND p.product_type = '" . $params['product_type'] . "' ";
			}

			$status_query = " AND p.is_active IN (" . implode(',', $params['status']) . ") ";

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
				} else if ($name == 'publisher') {
					$searchQry = "AND pub.full_name LIKE '" . $prefix . $params["search"] . "%'";
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
					$sortByQry = " ORDER BY p.full_name " . $order;
				}
				if ($name == 'group') {
					$sortByQry = " ORDER BY p.group " . $order;
				}
				if ($name == 'author') {
					$sortByQry = " ORDER BY p.author " . $order;
				}
				if ($name == 'price') {
					$sortByQry = " ORDER BY p.price " . $order;
				}
				if ($name == 'stock') {
					$sortByQry = " ORDER BY p.publisher_id asc, qty " . $order;
				}
			}

			$innerJoin = " INNER JOIN {$this->table} as p on sp.product_id = p.id and sp.owner_id=:owner_id and sp.status = 1 ";
			if (!empty($shopId)) {
				$innerJoin .= " AND sp.shopId = $shopId ";
			}

			$column = "";

			$column = ", (sp.qty - sp.stock_out) as qty, sp.min_qty ";
			if (!empty($params['minQty'])) {
				$minQry = " HAVING qty <= sp.min_qty order by p.priority desc, price desc, code desc ";
			}

			$mobileCols = "p.id,p.full_name";
			$allCols = "group_concat(DISTINCT r.title ORDER BY r.title ASC) as rackNumbers, p.priority, p.wh_price, p.image, group_concat(DISTINCT pc.code ORDER BY pc.code ASC) as other_codes, p.author, p.barcode, p.code, p.cat_id, p.board, p.group, p.id, p.pprice, sp.pin, p.publisher_id, concat(p.id, ' | ', p.full_name) as full_name, sp.pack_qty, sp.pack_size, pub.full_name as publisherName, pub.discount_type, pub.discount_amount, CONVERT(case when (pub.discount_amount > 0) then (p.price * (1 - (pub.discount_amount / 100)) ) else p.price end, DECIMAL) as price, is_active, product_type $column";

			$mainCols = "";
			if (!empty($mobileCol)) {
				$mainCols = $mobileCols;
			} else {
				$mainCols = $allCols;
			}

			$stmt = "SELECT count(b.id) as count FROM (SELECT $mainCols FROM `{$this->table_st}` as sp $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN {$this->table_rack_products} as rp ON rp.product_id = p.id LEFT JOIN `{$this->table_rack}` as r on r.id = rp.rack_id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id  WHERE p.`owner_id`=:owner_id $status_query $publisher_query $dup $type $pin $searchQry $catQry GROUP BY p.id $sortByQry $minQry) AS b";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$total = $prepare->fetch(PDO::FETCH_ASSOC);

			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = empty($total) ? 0 : $total['count'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset =  ((!empty($currentPage) ? $currentPage : 1) - 1) * $no_of_records_per_page;

			$stmt = "SELECT *, price, concat(`id`, '|', `price`, '|', `full_name`, '|', COALESCE(`publisherName`, ''), '|', COALESCE(`author`, ''), '|', COALESCE(`board`, ''), '|', COALESCE(`code`, ''), '|', COALESCE(`barcode`, ''), '|', COALESCE(`other_codes`, '')) as searchString FROM (SELECT $mainCols  FROM `{$this->table_st}` as sp $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN {$this->table_rack_products} as rp ON rp.product_id = p.id LEFT JOIN `{$this->table_rack}` as r on r.id = rp.rack_id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id  WHERE p.`owner_id`=:owner_id $status_query $publisher_query $dup $type $pin $searchQry $catQry GROUP BY p.id $sortByQry $minQry LIMIT :offset, :perPage) AS b";
			// if ($name == 'stock') {
			// 	echo $stmt;
			// 	exit;
			// }
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'params' => $params, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getOwnerProductsByPriority($owner_id, $shopId = null)
	{
		$dbh = $this->connectionPool->getConnection();
		try {


			$innerJoin = "";
			if (!empty($shopId)) {
				$innerJoin .= " INNER JOIN {$this->table_st} as sp on sp.product_id = p.id and shopId=$shopId and sp.status = 1 ";
			}

			$minQry = "";

			if (!empty($shopId)) {
				$minQry = " HAVING qty <= sp.min_qty order by p.priority desc, code desc ";
			}

			$allCols = "p.*, (sp.qty - sp.stock_out) as qty, sp.min_qty, concat(p.id, ' | ', p.full_name) as full_name";

			$stmt = "SELECT $allCols  FROM `{$this->table}` as p $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN {$this->table_rack_products} as rp ON rp.product_id = p.id LEFT JOIN `{$this->table_rack}` as r on r.id = rp.rack_id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id  WHERE p.`owner_id`=:owner_id and p.priority = 1 GROUP BY p.id $minQry";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getRackByTitle($title, $shopId = null)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt = "SELECT rp.*, r.title  FROM `{$this->table_rack_products}` as rp left join `{$this->table_rack}` as r on rp.rack_id=r.id where r.shop_id=:shop_id and r.title=:title";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $title, PDO::PARAM_STR);
			$prepare->bindParam(':shop_id', $shopId, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			$final = [];
			$count = 0;
			foreach ($result as $value) {
				$final[$value['title']] = !empty($final[$value['title']]) ? $final[$value['title']] : $value;
				$final[$value['title']]['products'][] = $value['product_id'];
				$count++;
			}
			return $final;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getRackProductsPagination($owner_id, $params = [], $shopId = null)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt = "SELECT rp.*, r.title  FROM `{$this->table_rack_products}` as rp left join `{$this->table_rack}` as r on rp.rack_id=r.id where r.shop_id=:shop_id and owner_id=:owner_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':shop_id', $shopId, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			$final = [];
			$count = 0;
			foreach ($result as $value) {
				$final[$value['title']] = !empty($final[$value['title']]) ? $final[$value['title']] : $value;
				$final[$value['title']]['products'][] = $value['product_id'];
				$count++;
			}
			return $final;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getOrderProductsPagination($owner_id, $customer_id, $params, $shopId)
	{
		$dbh = $this->connectionPool->getConnection();
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
			$prepare = $dbh->prepare($stmt);
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
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':customer_id', $customer_id, PDO::PARAM_STR);
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

	public function getSupplyProductsPagination($owner_id, $supplier_id, $params, $shopId)
	{
		$dbh = $this->connectionPool->getConnection();
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
			$prepare = $dbh->prepare($stmt);
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
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':supplier_id', $supplier_id, PDO::PARAM_STR);
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

	public function assignProductsPublisher($owner_id, $params, $shopId = null)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$searchQry = "";

			$prefix = "%";

			if (!empty($params['correction'])) {
				$prefix = "";
			}

			$pin = "";
			if (!empty($params['pin'])) {
				$pin .= " AND sp.pin > 0";
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

			$innerJoin = "";
			if (!empty($shopId)) {
				$innerJoin .= " INNER JOIN {$this->table_st} as sp on sp.product_id = p.id and shopId=$shopId and sp.status = 1 ";
			}


			$stmt = "UPDATE `{$this->table}` as p  $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id SET p.publisher_id=:publisher_id WHERE p.`owner_id`=:owner_id and p.is_active = 1  $publisher_query  $pin $searchQry";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':publisher_id', $params['selectedPublisherId'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getOwnerProducts($owner_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT p.*, concat(p.id, ' | ', p.full_name) as full_name, pub.discount_type, pub.discount_amount, CONVERT(case when (pub.discount_amount > 0) then (p.price * (1 - (pub.discount_amount / 100)) ) else p.price end, DECIMAL) as price FROM `{$this->table}` as p left join publishers as pub on pub.id = p.publisher_id WHERE p.`owner_id`=:owner_id and p.is_active = 1";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function searchProducts($shopId, $search)
	{
		$dbh = $this->connectionPool->getConnection();
		$searchQuery = "(p.full_name LIKE '%" . $search . "%' OR p.code LIKE '%" . $search . "%' OR p.group LIKE '%" . $search . "%' OR p.description LIKE '%" . $search . "%' OR p.board LIKE '%" . $search . "%' OR p.author LIKE '%" . $search . "%' OR p.price LIKE '%" . $search . "%' OR pc.code LIKE '%" . $search . "%' ) ";
		$stmt = "SELECT p.*, concat(p.id, ' | ', p.full_name) as full_name, FROM  `{$this->table}` as p LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id WHERE $searchQuery and p.is_active GROUP BY p.id LIMIT 10";
		$prepare = $dbh->prepare($stmt);
		// $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		$this->connectionPool->releaseConnection($dbh);
		return $result;
	}

	public function searchProductGroups($ownerId, $search)
	{
		$dbh = $this->connectionPool->getConnection();
		$stmt = "SELECT `group` FROM  `{$this->table}` WHERE owner_id=:owner_id and is_active = 1 and `group` LIKE '%" . $search . "%' group by `group` LIMIT 10";
		$prepare = $dbh->prepare($stmt);
		$prepare->bindParam(':owner_id', $ownerId, PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		$this->connectionPool->getConnection($dbh);
		return $result;
	}

	public function searchProductAuthors($ownerId, $search)
	{
		$dbh = $this->connectionPool->getConnection();
		$stmt = "SELECT `author` FROM  `{$this->table}` WHERE owner_id=:owner_id and is_active = 1 and `author` LIKE '%" . $search . "%' group by `author` LIMIT 10";
		$prepare = $dbh->prepare($stmt);
		$prepare->bindParam(':owner_id', $ownerId, PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		$this->connectionPool->releaseConnection($dbh);
		return $result;
	}

	public function searchProductBoards($ownerId, $search)
	{
		$dbh = $this->connectionPool->getConnection();
		$stmt = "SELECT `board` FROM  `{$this->table}` WHERE owner_id=:owner_id and is_active = 1 and `board` LIKE '%" . $search . "%' group by `board` LIMIT 10";
		$prepare = $dbh->prepare($stmt);
		$prepare->bindParam(':owner_id', $ownerId, PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		$this->connectionPool->releaseConnection($dbh);
		return $result;
	}
	public function getStoreProducts($owner_id, $shopId = null)
	{
		$shopCondition = "";
		if ($shopId) {
			$shopCondition .= " AND st.shopId = $shopId";
		}

		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT st.*, s.code, s.id as product_id, s.full_name, s.group, s.publisher_id, s.author, pub.full_name as publisherName FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS s ON s.id = st.product_id LEFT JOIN publishers as pub on s.publisher_id = pub.id WHERE st.`owner_id`=:owner_id and s.is_active = 1  and st.status = 1 " . $shopCondition . " order by s.id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
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


		$dbh = $this->connectionPool->getConnection();
		try {

			$stmt1 = "SELECT count(*) as count FROM (SELECT st.id FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS p ON p.id = st.product_id LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id WHERE p.is_active = 1 and st.`owner_id`=:owner_id and st.status = 1 $shopCondition $searchQry $publisher_query group by pc.product_id, st.product_id order by p.id) AS a";
			$prepare = $dbh->prepare($stmt1);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$total = $prepare->fetch(PDO::FETCH_ASSOC);
			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = empty($total) ? 0 : $total['count'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset =  ((!empty($currentPage) ? $currentPage : 1) - 1) * $no_of_records_per_page;

			$stmt = "SELECT st.*, p.code, p.id as product_id, p.full_name, p.group, p.publisher_id, p.author, p.price FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS p ON p.id = st.product_id LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id WHERE p.is_active = 1 and st.`owner_id`=:owner_id and st.status = 1 $shopCondition $searchQry $publisher_query group by st.product_id, pc.product_id order by p.id desc LIMIT :offset, :perPage";
			$prepare2 = $dbh->prepare($stmt);
			$prepare2->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare2->bindParam(':offset', $offset, PDO::PARAM_INT);
			$prepare2->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
			$prepare2->execute();
			$result = $prepare2->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getProduct($id, $owner_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id AND owner_id = :owner_id";
			$prepare = $dbh->prepare($stmt);
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
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function getCategoryProducts($owner_id, $ids, $shopId)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$result = $this->getOwnerProductsPagination($owner_id, ['page' => 1, 'perPage' => 1000, 'cat_ids' => $ids, 'status' => [1]], $shopId);
			$res = [];
			foreach ($result['records'] as $key => $value) {
				$res[$value['cat_id']][] = $value;
			}
			return $res;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getProductCodes($id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->pc_table}` WHERE product_id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getStoreProduct($id, $owner_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE id=:id and status = 1 AND owner_id = :owner_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function getOwnerStoreProduct($shopId, $product_id, $owner_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE product_id=:product_id and status = 1 AND shopId=:shopId AND owner_id = :owner_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':product_id', $product_id, PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function alreadyExistsProduct($array)
	{
		$product_id = $array['product_id'];
		$shopId = $array['shopId'];
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE status = 1 and shopId=:shopId AND product_id = :product_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $product_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	// "10321","10322","10323","10324","10325","10326","10327","10328","10329","10330","10331","10332","10333","10334","10335","10336","10337","10338","10339","10340","10341","10342","10343","10344","10345","10346","10347","10348","10349","10350","10351","10352","10353","10354","10355","10356","10357","10358","10359","10371","10374","10376","10403","11011","11796","12638","12643","12644","12645","12646","12696","13062","13063","13064","13065","13066","13067","13068","13069","13070","13071","13072","13073","13074","13075","13076","13077","13079","13080","13081","13082","13083","13084","13085","13087","13088","13089","13090","13091","13092","13093","13094","13095","13096","13097","13098","13099","13100","13101","13102","13103","13104","13105","13107","13108","13109","13110","13111","13112","13113","13114","13115","13116","13117","13118","13119","13120","13121","13122","13123","13124","13125","13126","13127","13128","13129","13130","13131","13132","13133","13134","13135","13136","13137","13138","13139","13140","13141","13142","13143","13144","13145","13146","13147","13148","13149","13150","13151","13152","13153","13154","13155","13156","13157","13158","13159","13160","13161","13162","13163","13164","13165","13166","13167","13168","13169","13170","13171","13172","13173","13174","13175","13176","13177","13178","13179","13180","13181","13182","13183","13184","13185","13186","13187","13188","13189","13190","13191","13192","13193","13194","13195","13196","13197","13198","13199","13200","13201","13202","13203","13204","13205","13206","13207","13208","13209","13210","13211","13212","13213","13214","13215","13216","13217","13218","13219","13220","13221","13222","13223","13225","13226","13227","13228","13229","13230","13231","13232","13233","13234","13235","13236","13238","13239","13240","13241","13242","13243","13244","13245","13246","13247","13248","13249","13250","13251","13252","13253","13254","13255","13256","13257","13258","13259","13260","13261","13262","13263","13264","13265","13268","13269","13270","13271","13272","13273","13274","13275","13276","13277","13278","13279","13280","13281","13282","13283","13284","13285","13286","13287","13288","13289","13290","13291","13292","13293","13294","13295","13296","13297","13298","13299","13300","13301","13302","13303","13304","13305","13306","13307","13308","13309","13310","13311","13312","13313","13314","13315","13316","13317","13318","13321","13322","13323","13324","13325","13328","13329","13330","13331","13336","13337","13338","13339","13340","13341","13342","13343","13344","13345","13346","13347","13348","13349","13350","13351","13352","13353","13354","13355","13356","13357","13358","13359","13360","13361","13362","13363","13364","13365","13366","13367","13368","13369","13373","13374","13375","13376","13377","13378","13379","13380","13381","13382","13383","13384","13385","13386","13387","13388","13389","13390","13391","13393","13394","13395","13396","13397","13398","13399","13400","13401","13402","13403","13404","13405","13406","13407","13408","13409","13410","13411","13412","13413","13414","13415","13416","13417","13418","13419","13420","13421","13422","13423","13424","13425","13426","13427","13428","13429","13430","13431","13433","13434","13435","13436","13437","13438","13439","13441","13442","13443","13444","13445","13446","13447","13448","13451","13452","13453","13454","13455","13456","13457","13458","13459","13460","13461","13462","13463","13465","13466","13467","13468","13469","13470","13471","13472","13473","13474","13475","13476","13477","13478","13479","13480","13481","13482","13483","13484","13488","13492","13493","13498","13499","13500","13501","13503","13505","13506","13507","13508","13509","13510","13511","13512"

	public function updateProductPublisher()
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET  `group`='Stationery', `publisher_id`=5 WHERE id IN (10321,10322,10323,10324,10325,10326,10327,10328,10329,10330,10331,10332,10333,10334,10335,10336,10337,10338,10339,10340,10341,10342,10343,10344,10345,10346,10347,10348,10349,10350,10351,10352,10353,10354,10355,10356,10357,10358,10359,10371,10374,10376,10403,11011,11796,12638,12643,12644,12645,12646,12696,13062,13063,13064,13065,13066,13067,13068,13069,13070,13071,13072,13073,13074,13075,13076,13077,13079,13080,13081,13082,13083,13084,13085,13087,13088,13089,13090,13091,13092,13093,13094,13095,13096,13097,13098,13099,13100,13101,13102,13103,13104,13105,13107,13108,13109,13110,13111,13112,13113,13114,13115,13116,13117,13118,13119,13120,13121,13122,13123,13124,13125,13126,13127,13128,13129,13130,13131,13132,13133,13134,13135,13136,13137,13138,13139,13140,13141,13142,13143,13144,13145,13146,13147,13148,13149,13150,13151,13152,13153,13154,13155,13156,13157,13158,13159,13160,13161,13162,13163,13164,13165,13166,13167,13168,13169,13170,13171,13172,13173,13174,13175,13176,13177,13178,13179,13180,13181,13182,13183,13184,13185,13186,13187,13188,13189,13190,13191,13192,13193,13194,13195,13196,13197,13198,13199,13200,13201,13202,13203,13204,13205,13206,13207,13208,13209,13210,13211,13212,13213,13214,13215,13216,13217,13218,13219,13220,13221,13222,13223,13225,13226,13227,13228,13229,13230,13231,13232,13233,13234,13235,13236,13238,13239,13240,13241,13242,13243,13244,13245,13246,13247,13248,13249,13250,13251,13252,13253,13254,13255,13256,13257,13258,13259,13260,13261,13262,13263,13264,13265,13268,13269,13270,13271,13272,13273,13274,13275,13276,13277,13278,13279,13280,13281,13282,13283,13284,13285,13286,13287,13288,13289,13290,13291,13292,13293,13294,13295,13296,13297,13298,13299,13300,13301,13302,13303,13304,13305,13306,13307,13308,13309,13310,13311,13312,13313,13314,13315,13316,13317,13318,13321,13322,13323,13324,13325,13328,13329,13330,13331,13336,13337,13338,13339,13340,13341,13342,13343,13344,13345,13346,13347,13348,13349,13350,13351,13352,13353,13354,13355,13356,13357,13358,13359,13360,13361,13362,13363,13364,13365,13366,13367,13368,13369,13373,13374,13375,13376,13377,13378,13379,13380,13381,13382,13383,13384,13385,13386,13387,13388,13389,13390,13391,13393,13394,13395,13396,13397,13398,13399,13400,13401,13402,13403,13404,13405,13406,13407,13408,13409,13410,13411,13412,13413,13414,13415,13416,13417,13418,13419,13420,13421,13422,13423,13424,13425,13426,13427,13428,13429,13430,13431,13433,13434,13435,13436,13437,13438,13439,13441,13442,13443,13444,13445,13446,13447,13448,13451,13452,13453,13454,13455,13456,13457,13458,13459,13460,13461,13462,13463,13465,13466,13467,13468,13469,13470,13471,13472,13473,13474,13475,13476,13477,13478,13479,13480,13481,13482,13483,13484,13488,13492,13493,13498,13499,13500,13501,13503,13505,13506,13507,13508,13509,13510,13511,13512)";
			$prepare = $dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function deleteRackProduct($rack_id, $product_id)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "DELETE FROM `{$this->table_rack_products}` WHERE rack_id=:rack_id and product_id=:product_id LIMIT 1";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':rack_id', $rack_id, PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $product_id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function updateProductWHPrice($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET  `wh_price`=:wh_price WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':wh_price', $array['wh_price'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$prepare->rowCount();
			return $array['product_id'];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function updateProductPrice($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET  `price`=:price WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$prepare->rowCount();
			return $array['product_id'];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function updateProductPPrice($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET  `pprice`=:pprice WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':pprice', $array['pprice'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$prepare->rowCount();
			return $array['product_id'];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function updateProductAuthor($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET  `author`=:author WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':author', $array['author'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$prepare->rowCount();
			return $array['product_id'];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function updateProductName($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET `full_name`=:full_name WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$prepare->rowCount();
			return $array['product_id'];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function updateProductPublisherId($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET `publisher_id`=:publisher_id WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':publisher_id', $array['publisher_id'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['product_id'], PDO::PARAM_STR);
			$prepare->execute();
			$prepare->rowCount();
			return $array['product_id'];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function updateProduct($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET `full_name`=:full_name, `wh_price`=:wh_price, `price`=:price, `pprice`=:pprice, `code`=:code, `description`=:description, `group`=:group, `board`=:board, `author`=:author, `image`=:image, `publisher_id`=:publisher_id, `cat_id`=:cat_id, pack_size=:pack_size, pack_qty=:pack_qty, product_type=:product_type WHERE id=:id AND owner_id=:owner_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':wh_price', $array['wh_price'], PDO::PARAM_STR);
			$prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
			$prepare->bindParam(':pprice', $array['pprice'], PDO::PARAM_STR);
			$prepare->bindParam(':image', $array['image'], PDO::PARAM_STR);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
			$prepare->bindParam(':group', $array['group'], PDO::PARAM_STR);
			$prepare->bindParam(':board', $array['board'], PDO::PARAM_STR);
			$prepare->bindParam(':author', $array['author'], PDO::PARAM_STR);
			$prepare->bindParam(':publisher_id', $array['publisher_id'], PDO::PARAM_INT);
			$prepare->bindParam(':pack_size', $array['pack_size'], PDO::PARAM_STR);
			$prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
			$prepare->bindParam(':cat_id', $array['cat_id'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_INT);
			$prepare->bindParam(':product_type', $array['product_type'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function setPriority($id, $priority)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET `priority`=:priority WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':priority', $priority, PDO::PARAM_INT);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function setBookmark($id, $pin, $shopId)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `pin`=:pin WHERE product_id=:id and shopId=:shopId";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_INT);
			$prepare->bindParam(':pin', $pin, PDO::PARAM_INT);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function setInactive($id, $action)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET `is_active`=:is_active WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->bindParam(':is_active', $action, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function setDuplicate($id, $pin)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table}` SET `dup`=:pin WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':pin', $pin, PDO::PARAM_INT);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function updateProductCode($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->pc_table}` SET `code`=:code WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function updateStoreProduct($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `min_qty`=:min_qty, `location`=:location, pack_size=:pack_size, pack_qty=:pack_qty, shopId=:shopId  WHERE id=:id";

			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':min_qty', $array['min_qty'], PDO::PARAM_STR);
			$prepare->bindParam(':location', $array['location'], PDO::PARAM_STR);
			$prepare->bindParam(':pack_size', $array['pack_size'], PDO::PARAM_STR);
			$prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function deleteStoreProduct($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `status`=2 WHERE id=:id";

			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
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
		$dbh = $this->connectionPool->getConnection();
		try {
			$minQty = !empty($array['minQty']);
			if ($minQty) {
				$stmt = "INSERT INTO `{$this->table_st}` (`qty`, `stock_out`, `product_id`, `shopId`, `owner_id`, `location`, `min_qty`, `pack_size`, `pack_qty`) VALUES (:qty, :stock_out, :product_id, :shopId, :owner_id, :location, :min_qty, :pack_size, :pack_qty)";
			} else {
				$stmt = "INSERT INTO `{$this->table_st}` (`qty`, `stock_out`, `product_id`, `shopId`, `owner_id`, `location`, `pack_size`, `pack_qty`) VALUES (:qty, :stock_out, :product_id, :shopId, :owner_id, :location, :pack_size, :pack_qty)";
			}
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':qty', $array['qty'], PDO::PARAM_STR);
			$prepare->bindParam(':stock_out', $array['stock_out'], PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_INT);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_INT);
			$prepare->bindParam(':location', $array['location'], PDO::PARAM_STR);
			$prepare->bindParam(':pack_size', $array['pack_size'], PDO::PARAM_STR);
			$prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
			if ($minQty) {
				$prepare->bindParam(':min_qty', $array['minQty'], PDO::PARAM_STR);
			}
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function updateProductToStore($array, $data)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$data['qty'] += $array['qty'];
			$data['stock_out'] += $array['stock_out'];
			$data['pack_size'] += $array['pack_size'];
			$data['pack_qty'] += $array['pack_qty'];
			$data['minQty'] += $array['minQty'];
			$minQty = !empty($array['minQty']);
			if ($minQty) {
				$stmt = "UPDATE `{$this->table_st}` SET `qty`=:qty, `stock_out`=:stock_out, `min_qty`=:min_qty, `pack_size`=:pack_size, `pack_qty`=:pack_qty WHERE id=:id";
			} else {
				$stmt = "UPDATE `{$this->table_st}` SET `qty`=:qty, `stock_out`=:stock_out, `pack_size`=:pack_size, `pack_qty`=:pack_qty WHERE id=:id";
			}
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':qty', $data['qty'], PDO::PARAM_STR);
			$prepare->bindParam(':stock_out', $data['stock_out'], PDO::PARAM_STR);
			if ($minQty) {
				$prepare->bindParam(':min_qty', $data['minQty'], PDO::PARAM_STR);
			}
			$prepare->bindParam(':id', $data['id'], PDO::PARAM_INT);
			$prepare->bindParam(':pack_size', $data['pack_size'], PDO::PARAM_STR);
			$prepare->bindParam(':pack_qty', $data['pack_qty'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: here " . $e->getMessage() . "<br/>");
		}
	}

	public function maintainProductQty($array = [])
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			
			$stmt = "UPDATE `{$this->table_st}` SET `qty`=:qty + COALESCE(stock_out, 0)) WHERE product_id=:product_id and shopId = :shopId";

			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_INT);
			$prepare->bindParam(':qty', $array['qty'], PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $array['shop_id'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	// public function createExchange($array)
	// {
	// 	$dbh = $this->connectionPool->getConnection();
	// 	try {

	// 		$stmt = "INSERT INTO `{$this->table_ex}` (from_id, from_ex_qty, from_qty, to_id, to_ex_qty, to_qty, shop_id, owner_id, created_by) VALUES (:from_id, :from_ex_qty, :from_qty, :to_id, :to_ex_qty, :to_qty, :shop_id, :owner_id, :created_by)";
	// 		$prepare = $dbh->prepare($stmt);
	// 		$prepare->bindParam(':from_id', $array['fromId'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':from_ex_qty', $array['existFromQty'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':from_qty', $array['qty'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':to_id', $array['toId'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':to_ex_qty', $array['existToQty'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':to_qty', $array['qty'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':shop_id', $array['shopId'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_INT);
	// 		$prepare->execute();
	// 		$result = $dbh->lastInsertId();
	// 		return $result;
	// 	} catch (PDOException $e) {
	// 		die("Error!: " . $e->getMessage() . "<br/>");
	// 	} finally {
	// 		$this->connectionPool->releaseConnection($dbh);
	// 	}
	// }

	// public function getStockHistoryPagination($params)
	// {
	// 	$dbh = $this->connectionPool->getConnection();
	// 	try {

	// 		$stmt = "SELECT COUNT(id) as total FROM `{$this->table_ex}` where shop_id=:shopId and owner_id=:owner_id";
	// 		$prepare = $dbh->prepare($stmt);
	// 		$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':owner_id', $params['owner_id'], PDO::PARAM_INT);
	// 		$prepare->execute();
	// 		$result = $prepare->fetch(PDO::FETCH_ASSOC);

	// 		$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
	// 		$total_rows = $result['total'];
	// 		$total_pages = ceil($total_rows / $no_of_records_per_page);
	// 		$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
	// 		$offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
	// 		$search = "(from_id LIKE '%" . $params["search"] . "%' OR to_id LIKE '%" . $params["search"] . "%' OR p1.full_name LIKE '%" . $params["search"] . "%' OR p2.full_name LIKE '%" . $params["search"] . "%') ";
			
	// 		$stmt = "SELECT p1.full_name as fromProduct, p2.full_name as toProduct, u.full_name as createdBy, ex.*, from_ex_qty - from_qty as from_after_qty, to_ex_qty - to_qty as to_after_qty FROM `{$this->table_ex}` as ex left join `{$this->users}` as u on u.id=ex.created_by left join `{$this->table}` as p1 on p1.id=ex.from_id left join `{$this->table}` as p2 on p2.id=ex.to_id WHERE $search and ex.shop_id=:shopId and ex.owner_id=:owner_id LIMIT :offset, :perPage";
	// 		$prepare = $dbh->prepare($stmt);
	// 		$prepare->bindParam(':shopId', $params['shopId'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
	// 		$prepare->bindParam(':owner_id', $params['owner_id'], PDO::PARAM_INT);
	// 		$prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
	// 		$prepare->execute();
	// 		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
	// 		return ['page' => $currentPage, 'closing_total' => 0, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
	// 	} catch (PDOException $e) {
	// 		die("Error!: " . $e->getMessage() . "<br/>");
	// 	} finally {
	// 		$this->connectionPool->releaseConnection($dbh);
	// 	}
	// }


	public function addProductQty($id, $array, $shopId, $type = 1)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$packQuery = "";
			if (!empty($array['pack_size'])) {
				$packQuery .= ", pack_size=:pack_size ";
			}
			if (!empty($array['pack_qty'])) {
				$packQuery .= ", pack_qty=pack_qty+:pack_qty ";
			}

			if ($type == 1 || $type == 3) {
				$stmt = "UPDATE `{$this->table_st}` SET `qty`=qty+:qty $packQuery WHERE product_id=:id and shopId = :shopId";
			} elseif ($type == 2) {
				$stmt = "UPDATE `{$this->table_st}` SET `faulty_qty`=faulty_qty+:qty $packQuery WHERE  product_id=:id and shopId = :shopId";
			}
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_INT);
			$prepare->bindParam(':qty', $array['qty'], PDO::PARAM_INT);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_INT);
			if (!empty($array['pack_size'])) {
				$prepare->bindParam(':pack_size', $array['pack_size'], PDO::PARAM_STR);
			}
			if (!empty($array['pack_qty'])) {
				$prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
			}
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
	public function resetCounters($owner_id, $shopId)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `qty`=0,`stock_out`=0,`min_qty`=2 WHERE owner_id=:owner_id and shopId=:shopId";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':shopId', $shopId, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function addProductSupply($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$packQuery = "";
			if (!empty($array['pack_size'])) {
				$packQuery .= ", pack_size=:pack_size ";
			}
			if (!empty($array['pack_qty'])) {
				$packQuery .= ", pack_qty=pack_qty+:pack_qty ";
			}

			$stmt = "UPDATE `{$this->table}` SET `in_hand`=in_hand+:qty, pprice=:pprice, price=:price, barcode=:barcode $packQuery WHERE id=:id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
			$prepare->bindParam(':qty', $array['qty'], PDO::PARAM_INT);
			$prepare->bindParam(':pprice', $array['pprice'], PDO::PARAM_INT);
			$prepare->bindParam(':price', $array['price'], PDO::PARAM_INT);
			$prepare->bindParam(':barcode', $array['barcode'], PDO::PARAM_STR);
			if (!empty($array['pack_size'])) {
				$prepare->bindParam(':pack_size', $array['pack_size'], PDO::PARAM_STR);
			}
			if (!empty($array['pack_qty'])) {
				$prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
			}
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		}
	}

	public function subProductQty($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {

			$packQuery = "";
			if (!empty($array['pack_qty'])) {
				$packQuery .= ", `pack_qty`=pack_qty+:pack_qty";
			}

			$stmt = "UPDATE `{$this->table_st}` SET `stock_out`=stock_out+:stock_out $packQuery WHERE product_id=:product_id and shopId=:shopId and owner_id=:owner_id";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':shopId', $array['shopId'], PDO::PARAM_STR);
			$prepare->bindParam(':stock_out', $array['quantity'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_STR);
			if (!empty($array['pack_qty'])) {
				$prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
			}
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function createProduct($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `owner_id`, `user_id`,`wh_price`, `price`, `pprice`, `image`, `code`, `barcode`, `description`, `group`, `board`, `author`, `publisher_id`, `cat_id`, `note`, `product_type`) VALUES (:full_name, :owner_id, :user_id, :wh_price, :price, :pprice, :image, :code, :barcode, :description, :group, :board, :author, :publisher_id, :cat_id, :note, :product_type)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_INT);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_INT);
			$prepare->bindParam(':wh_price', $array['wh_price'], PDO::PARAM_INT);
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
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function createRack($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_rack}` (`title`, `owner_id`, `shop_id`, `status`) VALUES (:title, :owner_id, :shop_id, :status)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_INT);
			$prepare->bindParam(':shop_id', $array['shop_id'], PDO::PARAM_INT);
			$prepare->bindParam(':status', $array['status'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}

	public function createRackProducts($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->table_rack_products}` (`rack_id`, `product_id`, `status`) VALUES (:rack_id, :product_id, :status)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':rack_id', $array['rack_id'], PDO::PARAM_STR);
			$prepare->bindParam(':product_id', $array['product_id'], PDO::PARAM_INT);
			$prepare->bindParam(':status', $array['status'], PDO::PARAM_INT);
			$prepare->execute();
			$result = $dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}


	public function createProductCode($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "INSERT INTO `{$this->pc_table}` (`code`, `product_id`) VALUES (:code, :product_id)";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
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

	public function deleteProductCode($array)
	{
		$dbh = $this->connectionPool->getConnection();
		try {
			$stmt = "DELETE FROM `{$this->pc_table}` WHERE id=:id LIMIT 1";
			$prepare = $dbh->prepare($stmt);
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}


	public function updateProductGivenFields($array)
	{
		$dbh = $this->connectionPool->getConnection();
			
			$updateQuery = [];

			if(!empty($array['qty'])) {
				$updateQuery[] = "`in_hand` = :in_hand";
			}
			if(!empty($array['full_name'])) {
				$updateQuery[] = "`full_name` = :full_name";
			}
			if(!empty($array['barcode'])) {
				$updateQuery[] = "`barcode` = :barcode";
			}
			if(!empty($array['code'])) {
				$updateQuery[] = "`code` = :code";
			}
			if(!empty($array['group'])) {
				$updateQuery[] = "`group` = :group";
			}
			if(!empty($array['description'])) {
				$updateQuery[] = "`description` = :description";
			}
			if(!empty($array['note'])) {
				$updateQuery[] = "`note` = :note";
			}
			if(!empty($array['wh_price'])) {
				$updateQuery[] = "`wh_price` = :wh_price";
			}
			if(!empty($array['price'])) {
				$updateQuery[] = "`price` = :price";
			}
			if(!empty($array['pprice'])) {
				$updateQuery[] = "`pprice` = :pprice";
			}
			if(!empty($array['min_qty'])) {
				$updateQuery[] = "`min_qty` = :min_qty";
			}
			if(!empty($array['pack_size'])) {
				$updateQuery[] = "`pack_size` = :pack_size";
			}
			if(!empty($array['pack_price'])) {
				$updateQuery[] = "`pack_price` = :pack_price";
			}
			if(!empty($array['pack_qty'])) {
				$updateQuery[] = "`pack_qty` = :pack_qty";
			}
			if(!empty($array['board'])) {
				$updateQuery[] = "`board` = '".$array['board']."'";
			}
			if(!empty($array['author'])) {
				$updateQuery[] = "`author` = :author";
			}
			if(!empty($array['publisher_id'])) {
				$updateQuery[] = "`publisher_id` = :publisher_id";
			}
			if(!empty($array['cat_id'])) {
				$updateQuery[] = "`cat_id` = :cat_id";
			}
			

		try {
			if(!empty($updateQuery)) {
				$stmt = "UPDATE `{$this->table}` SET  " . join(", ", $updateQuery) . " WHERE id=:id";
				$prepare = $dbh->prepare($stmt);

				if(!empty($array['qty'])) {
					$prepare->bindParam(':in_hand', $array['qty'], PDO::PARAM_STR);
				}
				if(!empty($array['full_name'])) {
					$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
				}
				if(!empty($array['barcode'])) {
					$prepare->bindParam(':barcode', $array['barcode'], PDO::PARAM_STR);
				}
				if(!empty($array['code'])) {
					$prepare->bindParam(':code', $array['code'], PDO::PARAM_STR);
				}
				if(!empty($array['group'])) {
					$prepare->bindParam(':group', $array['group'], PDO::PARAM_STR);
				}
				if(!empty($array['description'])) {
					$prepare->bindParam(':description', $array['description'], PDO::PARAM_STR);
				}
				if(!empty($array['note'])) {
					$prepare->bindParam(':note', $array['note'], PDO::PARAM_STR);
				}
				if(!empty($array['wh_price'])) {
					$prepare->bindParam(':wh_price', $array['wh_price'], PDO::PARAM_STR);
				}
				if(!empty($array['price'])) {
					$prepare->bindParam(':price', $array['price'], PDO::PARAM_STR);
				}
				if(!empty($array['pprice'])) {
					$prepare->bindParam(':pprice', $array['pprice'], PDO::PARAM_STR);
				}
				if(!empty($array['min_qty'])) {
					$prepare->bindParam(':min_qty', $array['min_qty'], PDO::PARAM_STR);
				}
				if(!empty($array['pack_size'])) {
					$prepare->bindParam(':pack_size', $array['pack_size'], PDO::PARAM_STR);
				}
				if(!empty($array['pack_price'])) {
					$prepare->bindParam(':pack_price', $array['pack_price'], PDO::PARAM_STR);
				}
				if(!empty($array['pack_qty'])) {
					$prepare->bindParam(':pack_qty', $array['pack_qty'], PDO::PARAM_STR);
				}
				if(!empty($array['board'])) {
					$prepare->bindParam(':board', $array['board'], PDO::PARAM_STR);
				}
				if(!empty($array['author'])) {
					$prepare->bindParam(':author', $array['author'], PDO::PARAM_STR);
				}
				if(!empty($array['publisher_id'])) {
					$prepare->bindParam(':publisher_id', $array['publisher_id'], PDO::PARAM_STR);
				}
				if(!empty($array['cat_id'])) {
					$prepare->bindParam(':cat_id', $array['cat_id'], PDO::PARAM_STR);
				}
				$prepare->bindParam(':id', $array['product_id'], PDO::PARAM_STR);
				$prepare->execute();
				$prepare->rowCount();
			}
			return $array['product_id'];
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		} finally {
			$this->connectionPool->releaseConnection($dbh);
		}
	}
}
