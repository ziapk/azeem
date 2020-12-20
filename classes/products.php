<?php

class Products extends Connection
{
    
    private $table = 'products';
    private $table_st = 'store_products';
    private $pc_table = 'products_code';

    public function getOwnerProductsPagination($owner_id, $params) {
		try {
			
			$stmt = "SELECT COUNT(id) as total FROM `{$this->table}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			
			$no_of_records_per_page = 10;
			$total_rows = $result['total'];
			$total_pages = ceil($total_rows / $no_of_records_per_page);
			$currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
			$offset = ($currentPage-1) * $no_of_records_per_page;
			$search = "AND (p.full_name LIKE '%".$params["search"]."%' OR p.code LIKE '%".$params["search"]."%' OR p.group LIKE '%".$params["search"]."%' OR p.description LIKE '%".$params["search"]."%' OR p.price LIKE '%".$params["search"]."%' OR pc.code LIKE '%".$params["search"]."%' ) ";
			$stmt = "SELECT p.* FROM `{$this->table}` as p LEFT JOIN {$this->pc_table} as pc ON pc.product_id = p.id  WHERE `owner_id`=:owner_id  $search GROUP BY p.id LIMIT :offset, :perPage";
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
			$stmt = "SELECT * FROM `{$this->table}` WHERE `owner_id`=:owner_id";
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
		$stmt = "SELECT * FROM  `{$this->table}` WHERE full_name LIKE '".$search."%' LIMIT 10";
		$prepare = $this->dbh->prepare($stmt);
		$prepare->bindParam(':shopId',$shopId,PDO::PARAM_STR);
		//$prepare->bindParam(':search',$search,PDO::PARAM_STR);
		$prepare->execute();
		$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
		return $result;		
	}
	public function searchProductGroups($ownerId, $search) {
		$stmt = "SELECT * FROM  `{$this->table}` WHERE owner_id=:owner_id and `group` LIKE '%".$search."%' LIMIT 10";
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
			$stmt = "SELECT st.*, s.full_name, s.group FROM `{$this->table_st}` as st LEFT JOIN `{$this->table}` AS s ON s.id = st.product_id WHERE st.`owner_id`=:owner_id ".$shopCondition;
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':owner_id',$owner_id,PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
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
			$result['codes'] = $this->getProductCodes($id);
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
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE id=:id AND owner_id = :owner_id";
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
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE product_id=:product_id AND shopId=:shopId AND owner_id = :owner_id";
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
			$stmt = "SELECT * FROM `{$this->table_st}` WHERE shopId=:shopId AND product_id = :product_id";
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
			$stmt = "UPDATE `{$this->table}` SET `full_name`=:full_name, `price`=:price, `pprice`=:pprice, `code`=:code, `description`=:description, `group`=:group, `image`=:image, `in_hand`=:in_hand, `min_qty`=:min_qty, `pack_size`=:pack_size, `pack_price`=:pack_price WHERE id=:id AND owner_id=:owner_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
            $prepare->bindParam(':price',$array['price'],PDO::PARAM_STR);
            $prepare->bindParam(':pprice',$array['pprice'],PDO::PARAM_STR);
            $prepare->bindParam(':image',$array['image'],PDO::PARAM_STR);
            $prepare->bindParam(':code',$array['code'],PDO::PARAM_STR);
            $prepare->bindParam(':description',$array['description'],PDO::PARAM_STR);
            $prepare->bindParam(':group',$array['group'],PDO::PARAM_STR);
            $prepare->bindParam(':in_hand',$array['in_hand'],PDO::PARAM_STR);
            $prepare->bindParam(':min_qty',$array['min_qty'],PDO::PARAM_STR);
            $prepare->bindParam(':pack_size',$array['pack_size'],PDO::PARAM_STR);
            $prepare->bindParam(':pack_price',$array['pack_price'],PDO::PARAM_STR);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
            $prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_INT);
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
			$stmt = "UPDATE `{$this->table_st}` SET `qty`=:qty, `stock_out`=:stock_out, `min_qty`=:min_qty, `sale_price`=:sale_price, `packet`=:packet, `packet_price`=:packet_price, `location`=:location, `product_id`=:product_id WHERE id=:id AND owner_id=:owner_id";

            $prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':qty',$array['qty'],PDO::PARAM_STR);
            $prepare->bindParam(':stock_out',$array['stock_out'],PDO::PARAM_STR);
            $prepare->bindParam(':min_qty',$array['min_qty'],PDO::PARAM_STR);
            $prepare->bindParam(':sale_price',$array['sale_price'],PDO::PARAM_STR);
            $prepare->bindParam(':packet',$array['packet'],PDO::PARAM_STR);
            $prepare->bindParam(':packet_price',$array['packet_price'],PDO::PARAM_INT);
            $prepare->bindParam(':location',$array['location'],PDO::PARAM_INT);
            $prepare->bindParam(':product_id',$array['product_id'],PDO::PARAM_INT);
            $prepare->bindParam(':id',$array['id'],PDO::PARAM_INT);
            $prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_INT);
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
			$stmt = "INSERT INTO `{$this->table_st}` (`qty`, `stock_out`, `product_id`, `shopId`, `owner_id`) VALUES (:qty, :stock_out, :product_id, :shopId, :owner_id)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':qty',$array['qty'],PDO::PARAM_STR);
            $prepare->bindParam(':stock_out',$array['stock_out'],PDO::PARAM_STR);
            $prepare->bindParam(':product_id',$array['product_id'],PDO::PARAM_INT);
            $prepare->bindParam(':shopId',$array['shopId'],PDO::PARAM_INT);
            $prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_INT);
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
		return 'update';
	}
	
	public function subProductQty($id, $qty) {
		try {
			$stmt = "UPDATE `{$this->table}` SET `in_hand`=in_hand-:qty WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id',$id,PDO::PARAM_INT);
            $prepare->bindParam(':qty',$qty,PDO::PARAM_INT);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
		    die("Error!: " . $e->getMessage() . "<br/>");
		}
		return 'update';
	}

	public function createProduct($array) {
		try {
			$stmt = "INSERT INTO `{$this->table}` (`full_name`, `owner_id`, `user_id`, `price`, `pprice`, `image`, `code`, `description`, `group`, ` in_hand`, `min_qty`, `pack_size`, `pack_price`) VALUES (:full_name, :owner_id, :user_id, :price, :pprice, :image, :code, :description, :group, :in_hand, :min_qty, :pack_size, :pack_price)";
			$prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':full_name',$array['full_name'],PDO::PARAM_STR);
            $prepare->bindParam(':owner_id',$array['owner_id'],PDO::PARAM_INT);
            $prepare->bindParam(':user_id',$array['user_id'],PDO::PARAM_INT);
            $prepare->bindParam(':price',$array['price'],PDO::PARAM_INT);
            $prepare->bindParam(':pprice',$array['pprice'],PDO::PARAM_INT);
            $prepare->bindParam(':in_hand',$array['in_hand'],PDO::PARAM_INT);
            $prepare->bindParam(':min_qty',$array['min_qty'],PDO::PARAM_INT);
            $prepare->bindParam(':pack_size',$array['pack_size'],PDO::PARAM_INT);
            $prepare->bindParam(':pack_price',$array['pack_price'],PDO::PARAM_INT);
            $prepare->bindParam(':image',$array['image'],PDO::PARAM_STR);
            $prepare->bindParam(':code',$array['code'],PDO::PARAM_STR);
            $prepare->bindParam(':description',$array['description'],PDO::PARAM_STR);
            $prepare->bindParam(':group',$array['group'],PDO::PARAM_STR);
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