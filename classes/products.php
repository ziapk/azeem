<?php

class Products extends Connection
{
    
    private $table = 'products';
    private $table_st = 'store_products';
    private $pc_table = 'products_code';

    public function getOwnerProductsPagination($owner_id, $params, $shopId = null) {
		try {

			$searchQry = "";
			$sortByQry = "";

			$pin = "";
			if(!empty($params['pin'])) {
				$pin .= " AND p.pin > 0";
			}
			
			if($params['searchBy'] == 'id' && !empty($params["search"])) {
				$searchQry = "AND (p.id = ".$params["search"]." OR p.code = ".$params["search"].")";	
			}
			else if($params['searchBy'] == 'cource' && !empty($params["courceId"])) {
				$searchQry = "AND c.program_id = ".$params["courceId"];	
			}
			else if(!empty($params['searchBy']) && $params['searchBy'] == 'multi') {
				if(!empty($params['full_name'])) {
					$searchQry .= " AND p.full_name LIKE '%".$params["full_name"]."%'";	
				}
				if(!empty($params['author'])) {
					$searchQry .= " AND p.author LIKE '%".$params["author"]."%'";	
				}
				if(!empty($params['board'])) {
					$searchQry .= " AND p.board LIKE '%".$params["board"]."%'";	
				}
				if(!empty($params['group'])) {
					$searchQry = " AND p.group LIKE '%".$params["group"]."%'";	
				}
			}
			else if(!empty($params['searchBy']) && !empty($params["search"]) ) {
				$name = $params['searchBy'];
				if($name == 'group') {
					$searchQry = "AND p.group LIKE '%".$params["search"]."%'";	
				}
				else if($name == 'author') {
					$searchQry = "AND p.author LIKE '%".$params["search"]."%'";	
				}
				else if($name == 'board') {
					$searchQry = "AND p.board LIKE '%".$params["search"]."%'";	
				}
				else if($name == 'category') {
					$searchQry = "AND p.cat LIKE '%".$params["search"]."%'";	
				}
				else if($name == 'subCategory') {
					$searchQry = "AND p.sub_cat LIKE '%".$params["search"]."%'";	
				}
				else $searchQry = "AND (p.id = '".$params["search"]."' OR p.code = '".$params["search"]."' OR p.full_name LIKE '%".$params["search"]."%' OR p.group LIKE '%".$params["search"]."%' OR p.description LIKE '%".$params["search"]."%' OR p.board LIKE '%".$params["search"]."%' OR p.author LIKE '%".$params["search"]."%' OR p.price LIKE '%".$params["search"]."%' OR pc.code LIKE '%".$params["search"]."%' ) ";
			}
			else {
				$searchQry = "AND (p.id = '".$params["search"]."' OR p.code = '".$params["search"]."' OR p.full_name LIKE '%".$params["search"]."%' OR p.group LIKE '%".$params["search"]."%' OR p.description LIKE '%".$params["search"]."%' OR p.board LIKE '%".$params["search"]."%' OR p.author LIKE '%".$params["search"]."%' OR p.price LIKE '%".$params["search"]."%' OR pc.code LIKE '%".$params["search"]."%' ) ";
			}

			if(!empty($params['sortByField'])) {
				$name = $params['sortByField'];
				$order = $params['sortByOrder'];
				if($name == 'title') {
					$sortByQry= " ORDER BY p.full_name ".$params['sortByOrder'];
				}
				if($name == 'group') {
					$sortByQry= " ORDER BY p.group ".$params['sortByOrder'];
				}
				if($name == 'author') {
					$sortByQry= " ORDER BY p.author ".$params['sortByOrder'];
				}
				if($name == 'price') {
					$sortByQry= " ORDER BY p.price ".$params['sortByOrder'];
				}
				if($name == 'stock') {
					$sortByQry= " ORDER BY p.in_hand ".$params['sortByOrder'];
				}
			}

			$innerJoin = "";
			if(!empty($shopId)) {
				$innerJoin .= " INNER JOIN {$this->table_st} as sp on sp.product_id = p.id and shopId=$shopId and sp.status = 1 ";
			}

			$column = "";

			if(!empty($shopId)) {
				$column = ", (sp.qty - sp.stock_out) as qty ";
			}
			
			$stmt = "SELECT count(p.id) as count FROM `{$this->table}` as p $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id  LEFT JOIN program_books as c ON c.product_id = p.id WHERE p.owner_id=:owner_id $pin $searchQry";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$total = $prepare->fetch(PDO::FETCH_ASSOC);
			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = empty($total) ? 0 : $total['count'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset =  ((!empty($currentPage) ? $currentPage : 1) -1) * $no_of_records_per_page;
			

			

			$stmt = "SELECT p.*, pub.discount_type, pub.discount_amount, CONVERT(case when (pub.discount_amount > 0) then (p.price * (1 - (pub.discount_amount / 100)) ) else p.price end, DECIMAL) as price $column  FROM `{$this->table}` as p $innerJoin LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id LEFT JOIN program_books as c ON c.product_id = p.id LEFT JOIN publishers as pub on p.publisher_id = pub.id  WHERE p.`owner_id`=:owner_id  $pin $searchQry  GROUP BY p.id $sortByQry LIMIT :offset, :perPage";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->bindParam(':offset',$offset,PDO::PARAM_INT);
			$prepare->bindParam(':perPage',$no_of_records_per_page,PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords'=> $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];

		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function getOwnerProducts($owner_id) {
		try {
			$stmt = "SELECT p.*, pub.discount_type, pub.discount_amount, CONVERT(case when (pub.discount_amount > 0) then (p.price * (1 - (pub.discount_amount / 100)) ) else p.price end, DECIMAL) as price FROM `{$this->table}` as p left join publishers as pub on pub.id = p.publisher_id WHERE p.`owner_id`=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function searchProducts($shopId, $search) {
		$searchQuery = "(p.full_name LIKE '%".$search."%' OR p.code LIKE '%".$search."%' OR p.group LIKE '%".$search."%' OR p.description LIKE '%".$search."%' OR p.board LIKE '%".$search."%' OR p.author LIKE '%".$search."%' OR p.price LIKE '%".$search."%' OR pc.code LIKE '%".$search."%' ) ";
		$stmt = "SELECT p.* FROM  `{$this->table}` as p LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id WHERE $searchQuery GROUP BY p.id LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		// $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
	}
	
	public function searchProductGroups($ownerId, $search) {
		$stmt = "SELECT `group` FROM  `{$this->table}` WHERE owner_id=:owner_id and `group` LIKE '%".$search."%' group by `group` LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':owner_id',$ownerId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
	}

	public function searchProductAuthors($ownerId, $search) {
		$stmt = "SELECT `author` FROM  `{$this->table}` WHERE owner_id=:owner_id and `author` LIKE '%".$search."%' group by `author` LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':owner_id',$ownerId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
	}
	
	public function searchProductBoards($ownerId, $search) {
		$stmt = "SELECT `board` FROM  `{$this->table}` WHERE owner_id=:owner_id and `board` LIKE '%".$search."%' group by `board` LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':owner_id',$ownerId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
	}
    public function getStoreProducts($owner_id, $shopId = null) {
		$shopCondition = "";
		if($shopId) {
			$shopCondition .= " AND st.shopId = $shopId";			
		}

		try {
			$stmt = "SELECT st.*, s.full_name, s.group FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS s ON s.id = st.product_id WHERE st.`owner_id`=:owner_id and st.status = 1 ".$shopCondition." order by s.id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getStoreProductsPagination($owner_id, $params, $shopId = null) {
		$shopCondition = "";
		if($shopId) {
			$shopCondition .= " AND st.shopId = $shopId";			
		}

		$searchQry = "";

		if(!empty($params["search"])) {
			$searchQry = "AND (p.id = '".$params["search"]."' OR p.code = '".$params["search"]."' OR p.full_name LIKE '%".$params["search"]."%' OR p.group LIKE '%".$params["search"]."%' OR p.description LIKE '%".$params["search"]."%' OR p.board LIKE '%".$params["search"]."%' OR p.author LIKE '%".$params["search"]."%' OR p.price LIKE '%".$params["search"]."%' OR pc.code LIKE '".$params["search"]."' ) ";
		}


		try {

			$stmt1 = "SELECT count(st.id) as count FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS p ON p.id = st.product_id LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id WHERE st.`owner_id`=:owner_id and st.status = 1 $shopCondition $searchQry order by p.id";
			$prepare = $this->dbh->prepare($stmt1);
			$prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$total = $prepare->fetch(PDO::FETCH_ASSOC);
			$no_of_records_per_page = !empty($params['perPage']) ? $params['perPage'] : 10;
			$total_rows = empty($total) ? 0 : $total['count'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset =  ((!empty($currentPage) ? $currentPage : 1) -1) * $no_of_records_per_page;


			$stmt = "SELECT st.*, p.code, p.id as product_id, p.full_name, p.group, p.author FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS p ON p.id = st.product_id LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id WHERE st.`owner_id`=:owner_id and st.status = 1 $shopCondition $searchQry order by p.id desc LIMIT :offset, :perPage";
			$prepare2 = $this->dbh->prepare($stmt);
			$prepare2->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare2->bindParam(':offset',$offset,PDO::PARAM_INT);
			$prepare2->bindParam(':perPage',$no_of_records_per_page,PDO::PARAM_INT);
			$prepare2->execute();
			$result = $prepare2->fetchAll(PDO::FETCH_ASSOC);
			return ['page' => $currentPage, 'totalRecords'=> $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function getProduct($id, $owner_id) {
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id AND owner_id = :owner_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
            $prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			if(!empty($result)) {
				$programs = new Programs();
				$result['codes'] = $this->getProductCodes($id);
				$result['programs'] = $programs->getBookPrograms($id);
			}
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function getProductCodes($id) {
		try {
			$stmt = "SELECT * FROM `{$this->pc_table}` WHERE product_id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getStoreProduct($id, $owner_id) {
		try {
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE id=:id and status = 1 AND owner_id = :owner_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_STR);
            $prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function getOwnerStoreProduct($shopId, $product_id, $owner_id) {
		try {
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE product_id=:product_id and status = 1 AND shopId=:shopId AND owner_id = :owner_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':product_id',$product_id,PDO::PARAM_STR);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
            $prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function alreadyExistsProduct($array) {
		$product_id = $array['product_id'];
		$shopId = $array['shopId'];
		try {
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE status = 1 and shopId=:shopId AND product_id = :product_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
            $prepare->bindParam(':product_id',$product_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateProduct($array) {
		try {
			$stmt = "UPDATE `{$this->table}` SET `full_name`=:full_name, `price`=:price, `pprice`=:pprice, `code`=:code, `description`=:description, `group`=:group, `board`=:board, `author`=:author, `image`=:image, `publisher_id`=:publisher_id WHERE id=:id AND owner_id=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
			$prepare->bindParam(':price',$array['price'],PDO::PARAM_STR);
			$prepare->bindParam(':pprice',$array['pprice'],PDO::PARAM_STR);
			$prepare->bindParam(':image',$array['image'],PDO::PARAM_STR);
			$prepare->bindParam(':code',$array['code'],PDO::PARAM_STR);
			$prepare->bindParam(':description',$array['description'],PDO::PARAM_STR);
			$prepare->bindParam(':group',$array['group'],PDO::PARAM_STR);
			$prepare->bindParam(':board',$array['board'],PDO::PARAM_STR);
			$prepare->bindParam(':author',$array['author'],PDO::PARAM_STR);
			$prepare->bindParam(':publisher_id',$array['publisher_id'],PDO::PARAM_STR);
			$prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
			$prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function setBookmark($id, $pin) {
		try {
			$stmt = "UPDATE `{$this->table}` SET `pin`=:pin WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':pin', $pin,PDO::PARAM_INT);
            $prepare->bindParam(':id',$id,PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function updateProductCode($array) {
		try {
			$stmt = "UPDATE `{$this->pc_table}` SET `code`=:code WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':code',$array['code'],PDO::PARAM_STR);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function updateStoreProduct($array) {
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `min_qty`=:min_qty, `location`=:location, shopId=:shopId  WHERE id=:id";

			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':min_qty',$array['min_qty'],PDO::PARAM_STR);
			$prepare->bindParam(':location',$array['location'],PDO::PARAM_STR);
			$prepare->bindParam(':shopId',$array['shopId'],PDO::PARAM_STR);
			$prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function deleteStoreProduct($array) {
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `status`=2 WHERE id=:id";

			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function assignProduct($array) {
		$data = $this->alreadyExistsProduct($array);
		if(empty($data)) {
			return $this->assignProductToStore($array);
		}
		else {
			return $this->updateProductToStore($array, $data);
		}

	}
	public function assignProductToStore($array) {
		try {
			$stmt = "INSERT INTO `{$this->table_st}` (`qty`, `stock_out`, `product_id`, `shopId`, `owner_id`, `location`) VALUES (:qty, :stock_out, :product_id, :shopId, :owner_id, :location)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':qty',$array['qty'],PDO::PARAM_STR);
            $prepare->bindParam(':stock_out',$array['stock_out'],PDO::PARAM_STR);
            $prepare->bindParam(':product_id',$array['product_id'],PDO::PARAM_INT);
            $prepare->bindParam(':shopId',$array['shopId'],PDO::PARAM_INT);
            $prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_INT);
            $prepare->bindParam(':location',$array['location'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function updateProductToStore($array, $data) {
		try {
			$data['qty'] += $array['qty'];
			$data['stock_out'] += $array['stock_out'];

			$stmt = "UPDATE `{$this->table_st}` SET `qty`=:qty, `stock_out`=:stock_out WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':qty',$data['qty'],PDO::PARAM_STR);
            $prepare->bindParam(':stock_out',$data['stock_out'],PDO::PARAM_STR);
            $prepare->bindParam(':id',$data['id'],PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function addProductQty($id, $qty, $shopId, $type = 1) {
		try {
			if($type == 1 || $type == 3) {
				$stmt = "UPDATE `{$this->table_st}` SET `qty`=qty+:qty WHERE product_id=:id and shopId = :shopId";
			}
			elseif ($type == 2) {
				$stmt = "UPDATE `{$this->table_st}` SET `faulty_qty`=faulty_qty+:qty WHERE  product_id=:id and shopId = :shopId";
			}
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_INT);
            $prepare->bindParam(':qty',$qty,PDO::PARAM_INT);
            $prepare->bindParam(':shopId',$shopId,PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function addProductSupply($array) {
		try {
			$stmt = "UPDATE `{$this->table}` SET `in_hand`=in_hand+:qty, pprice=:pprice, price=:price, barcode=:barcode WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
            $prepare->bindParam(':qty',$array['qty'],PDO::PARAM_INT);
            $prepare->bindParam(':pprice',$array['pprice'],PDO::PARAM_INT);
            $prepare->bindParam(':price',$array['price'],PDO::PARAM_INT);
            $prepare->bindParam(':barcode',$array['barcode'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		}
	}

	public function subProductQty($array) {
		try {
			$stmt = "UPDATE `{$this->table_st}` SET `stock_out`=stock_out+:stock_out WHERE product_id=:product_id and shopId=:shopId and owner_id=:owner_id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':shopId',$array['shopId'],PDO::PARAM_STR);
			$prepare->bindParam(':stock_out',$array['quantity'],PDO::PARAM_STR);
			$prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_STR);
			$prepare->bindParam(':product_id',$array['product_id'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
				die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createProduct($array) {
		try {
			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `owner_id`, `user_id`, `price`, `pprice`, `image`, `code`, `barcode`, `description`, `group`, `board`, `author`, `publisher_id`) VALUES (:full_name, :owner_id, :user_id, :price, :pprice, :image, :code, :barcode, :description, :group, :board, :author, :publisher_id)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
			$prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_INT);
			$prepare->bindParam(':user_id',$array['user_id'],PDO::PARAM_INT);
			$prepare->bindParam(':price',$array['price'],PDO::PARAM_INT);
			$prepare->bindParam(':pprice',$array['pprice'],PDO::PARAM_INT);
			$prepare->bindParam(':image',$array['image'],PDO::PARAM_STR);
			$prepare->bindParam(':code',$array['code'],PDO::PARAM_STR);
			$prepare->bindParam(':barcode',$array['barcode'],PDO::PARAM_STR);
			$prepare->bindParam(':description',$array['description'],PDO::PARAM_STR);
			$prepare->bindParam(':board',$array['board'],PDO::PARAM_STR);
			$prepare->bindParam(':author',$array['author'],PDO::PARAM_STR);
			$prepare->bindParam(':group',$array['group'],PDO::PARAM_STR);
			$prepare->bindParam(':publisher_id',$array['publisher_id'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function createProductCode($array) {
		try {
			$stmt = "INSERT INTO `{$this->pc_table}` (`code`, `product_id`) VALUES (:code, :product_id)";
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':code',$array['code'],PDO::PARAM_STR);
            $prepare->bindParam(':product_id',$array['product_id'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	
	public function deleteProductCode($array) {
		try {
			$stmt = "DELETE FROM `{$this->pc_table}` WHERE id=:id LIMIT 1";
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

}