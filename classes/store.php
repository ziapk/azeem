<?php

class Store extends Connection
{

	private $table = 'store';
	private $table_st = 'store_type';
	private $table_store_schema = 'store_schema';

	public function getStores()
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE 1";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getOwnerStores($userId)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE `owner_id`=:userId";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':userId', $userId, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getStore($id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
	public function getOwnerStore($id, $owner_id)
	{
		try {
			$stmt = "SELECT * FROM `{$this->table}` WHERE id=:id AND owner_id = :owner_id";
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

	public function generateStoreInit($shopId, $owner_id)
	{
		$configs = $this->getStoreSchema();
		$rows = [];
		foreach ($configs as $config) {
			if (strlen($config['code']) === 2) {
				$rows[$config['account_type']]['row'] = $config;
			} else if (strlen($config['code']) === 5) {
				$rows[$config['account_type']]['childs'][$config['code']]['row'] = $config;
			} else if (strlen($config['code']) === 8) {
				$rows[$config['account_type']]['childs'][substr($config['code'], 0, 5)]['nestedchilds'][] = $config;
			}
		}

		$de = new DoubleEntry();
		$sa = new ShopAccounts();
		$cat = new Categories();
		$usrs = new Users();
		$customer = new Customers();

		$owner = $usrs->getUser($owner_id);


		foreach ($rows as $row) {
			if (!empty($row['row'])) {
				$current = $row['row'];
				$parentAccount = $de->insertAccountDirect([
					"title" => $current['title'],
					"code" => $current['code'],
					"account_type" => $current['account_type'],
					"group_id" => null,
					"status" => 1,
					"parent_id" => null,
					"created_by" => $owner_id,
					"shopId" => $shopId,
					"opening_balance" => 0,
				]);

				if (!empty($current['shop_account_key'])) {
					$sa->createSA([
						"key_value" => $current['shop_account_key'],
						"label_value" => $current['shop_account_label'],
						"account_id" => $parentAccount,
						"shop_id" => $shopId,
					]);
				}

				if (!empty($row['childs'])) {
					foreach ($row['childs'] as $childRow) {

						$currentChild = $childRow['row'];

						$childAccount = $de->insertAccountDirect([
							"title" => $currentChild['title'],
							"code" => $currentChild['code'],
							"account_type" => $currentChild['account_type'],
							"group_id" => null,
							"status" => 1,
							"parent_id" => $parentAccount,
							"created_by" => $owner_id,
							"shopId" => $shopId,
							"opening_balance" => 0,
						]);


						if (!empty($currentChild['shop_account_key'])) {
							$sa->createSA([
								"key_value" => $currentChild['shop_account_key'],
								"label_value" => $currentChild['shop_account_label'],
								"account_id" => $childAccount,
								"shop_id" => $shopId,
							]);

							if ($currentChild['shop_account_key'] === 'receivable') {

								$accountData = [
									'title' => 'Customer - Walk In Customer',
									'code' => $currentChild['code'] . '-01',
									'account_type' => $currentChild['account_type'],
									'group_id' => null,
									'status' => 1,
									'parent_id' => $childAccount,
									'created_by' => $owner_id,
									'shopId' => $shopId,
									'opening_balance' => 0,
								];

								$accountId = $de->insertAccountDirect($accountData);

								// create payable Account first
								$customer->createCustomer([
									"full_name" => "Walk In Customer",
									"phoneNumber" => "",
									"company" => "",
									"email" => "",
									"title" => "",
									"address" => "",
									"type" => 2,
									"shopId" => $shopId,
									"account_id" => $accountId,
									"code" => 'wc',
									"default_discount" => 1,
									"linked_shop" => null
								]);
							}
						}

						if (!empty($childRow['nestedchilds'])) {
							foreach ($childRow['nestedchilds'] as $nestedchilds) {

								$currentNestedChild = $nestedchilds;

								$nestedChildAccount = $de->insertAccountDirect([
									"title" => $currentNestedChild['title'],
									"code" => $currentNestedChild['code'],
									"account_type" => $currentNestedChild['account_type'],
									"group_id" => null,
									"status" => 1,
									"parent_id" => $childAccount,
									"created_by" => $owner_id,
									"shopId" => $shopId,
									"opening_balance" => 0,
								]);

								if (!empty($currentNestedChild['shop_account_key'])) {
									$sa->createSA([
										"key_value" => $currentNestedChild['shop_account_key'],
										"label_value" => $currentNestedChild['shop_account_label'],
										"account_id" => $nestedChildAccount,
										"shop_id" => $shopId,
									]);

									if ($currentNestedChild['shop_account_key'] === 'payable_salary') {
										$cat->createCategory([
											"full_name" => $currentNestedChild['shop_account_label'],
											"cat_type" => 1,
											"groupName" => "General",
											"owner_id" => $owner_id,
											"account_id" => $nestedChildAccount,
											"image" => null,
										]);
									}
								}
							}
						}
					}
				}
			}
		}

		return $rows;
	}
	public function updateStore($array)
	{
		try {
			$imgTxt = "";
			if (!empty($array['image'])) {
				$imgTxt = ", image=:image ";
			}
			$stmt = "UPDATE `{$this->table}` SET full_name=:full_name, store_type=:store_type,status=:status, location=:location, city=:city, company_email=:company_email, company_ledger_inbox=:company_ledger_inbox, postalCode=:postalCode, phoneNumber1=:phoneNumber1, phoneNumber2=:phoneNumber2, phoneNumber3=:phoneNumber3, sale_terms=:sale_terms, sale_terms_lg=:sale_terms_lg, invoice_prefix=:invoice_prefix $imgTxt WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':store_type', $array['store_type'], PDO::PARAM_STR);
			$prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
			$prepare->bindParam(':location', $array['location'], PDO::PARAM_STR);
			$prepare->bindParam(':city', $array['city'], PDO::PARAM_STR);
			$prepare->bindParam(':company_email', $array['company_email'], PDO::PARAM_STR);
			$prepare->bindParam(':company_ledger_inbox', $array['company_ledger_inbox'], PDO::PARAM_STR);
			$prepare->bindParam(':postalCode', $array['postalCode'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber1', $array['phoneNumber1'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber2', $array['phoneNumber2'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber3', $array['phoneNumber3'], PDO::PARAM_STR);
			$prepare->bindParam(':sale_terms', $array['sale_terms'], PDO::PARAM_STR);
			$prepare->bindParam(':sale_terms_lg', $array['sale_terms_lg'], PDO::PARAM_STR);
			$prepare->bindParam(':invoice_prefix', $array['invoice_prefix'], PDO::PARAM_STR);
			if (!empty($array['image'])) {
				$prepare->bindParam(':image', $array['image'], PDO::PARAM_STR);
			}
			$prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function createStore($array)
	{
		try {

			$owner_id = $array['owner_id'];
			$users = new Users();

			if (!empty($array['shopUsers'])) {

				foreach ($array['shopUsers'] as $usr) {

					if ($usr['role'] == 'owner' && !empty($usr['role']) && !empty($usr['full_name']) && !empty($usr['password']) && !empty($usr['email'])) {
						$owner_id = $users->createProfile([
							"full_name" => $usr['full_name'],
							"city" => $array['city'],
							"cnic" => "",
							"phoneNumber1" => "",
							"phoneNumber2" => "",
							"phoneNumber3" => "",
							"photo" => "avatar1.jpg",
							"shopId" => null,
							"role" => $usr['role'],
							"created_by" => 1,
							"password" => $usr['password'],
							"email" => $usr['email'],
						]);
					}
				}
			}

			$array['last_bill_no'] = 1;
			$array['sale_date'] = date('Y-m-d');

			$stmt = "INSERT INTO `{$this->table}` (full_name, store_type, status, location, city, company_email, company_ledger_inbox, postalCode, phoneNumber1, phoneNumber2, phoneNumber3, image, owner_id, client_id, user_id, last_bill_no, sale_date, invoice_prefix) VALUES (:full_name, :store_type, :status, :location, :city, :company_email, :company_ledger_inbox, :postalCode, :phoneNumber1, :phoneNumber2, :phoneNumber3, :image, :owner_id, :client_id, :user_id, :last_bill_no, :sale_date, :invoice_prefix)";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
			$prepare->bindParam(':store_type', $array['store_type'], PDO::PARAM_STR);
			$prepare->bindParam(':status', $array['status'], PDO::PARAM_STR);
			$prepare->bindParam(':location', $array['location'], PDO::PARAM_STR);
			$prepare->bindParam(':city', $array['city'], PDO::PARAM_STR);
			$prepare->bindParam(':company_email', $array['company_email'], PDO::PARAM_STR);
			$prepare->bindParam(':company_ledger_inbox', $array['company_ledger_inbox'], PDO::PARAM_STR);
			$prepare->bindParam(':postalCode', $array['postalCode'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber1', $array['phoneNumber1'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber2', $array['phoneNumber2'], PDO::PARAM_STR);
			$prepare->bindParam(':phoneNumber3', $array['phoneNumber3'], PDO::PARAM_STR);
			$prepare->bindParam(':image', $array['image'], PDO::PARAM_STR);
			$prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':client_id', $owner_id, PDO::PARAM_STR);
			$prepare->bindParam(':user_id', $array['user_id'], PDO::PARAM_STR);
			$prepare->bindParam(':last_bill_no', $array['last_bill_no'], PDO::PARAM_STR);
			$prepare->bindParam(':sale_date', $array['sale_date'], PDO::PARAM_STR);
			$prepare->bindParam(':invoice_prefix', $array['invoice_prefix'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $this->dbh->lastInsertId();
			if (!empty($array['shopUsers'])) {
				foreach ($array['shopUsers'] as $usr) {
					if ($usr['role'] != 'owner' && !empty($usr['role']) &&  !empty($usr['full_name']) && !empty($usr['password']) && !empty($usr['email'])) {
						$users->createProfile([
							"full_name" => $usr['full_name'],
							"city" => $array['city'],
							"cnic" => "",
							"phoneNumber1" => "",
							"phoneNumber2" => "",
							"phoneNumber3" => "",
							"photo" => "avatar1.jpg",
							"shopId" => $result,
							"role" => $usr['role'],
							"created_by" => $owner_id,
							"password" => $usr['password'],
							"email" => $usr['email'],
						]);
					} else if ($usr['role'] == 'owner' && !empty($usr['role']) &&  !empty($usr['full_name']) && !empty($usr['password']) && !empty($usr['email'])) {
						$users->updateProfile([
							"full_name" => $usr['full_name'],
							"city" => $array['city'],
							"cnic" => "",
							"phoneNumber1" => "",
							"phoneNumber2" => "",
							"phoneNumber3" => "",
							"photo" => "avatar1.jpg",
							"shopId" => $result,
							"role" => $usr['role'],
							"id" => $owner_id,
						]);
					}
				}
			}
			$this->generateStoreInit($result, $owner_id);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getStoreSchema()
	{
		try {
			$stmt = "SELECT * FROM `{$this->table_store_schema}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function updateAccounts($id, $array)
	{

		try {
			$stmt = "UPDATE `{$this->table}` SET cash=:cash, payable=:payable, receiving=:receiving, receivable=:receivable, expense=:expense, sale_discount=:sale_discount, purchase_discount=:purchase_discount, sale_returns=:sale_returns, purchase_returns=:purchase_returns, assets=:assets WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':cash', $array['cash'], PDO::PARAM_STR);
			$prepare->bindParam(':payable', $array['payable'], PDO::PARAM_STR);
			$prepare->bindParam(':receiving', $array['receiving'], PDO::PARAM_STR);
			$prepare->bindParam(':receivable', $array['receivable'], PDO::PARAM_STR);
			$prepare->bindParam(':expense', $array['expense'], PDO::PARAM_STR);
			$prepare->bindParam(':sale_discount', $array['sale_discount'], PDO::PARAM_STR);
			$prepare->bindParam(':purchase_discount', $array['purchase_discount'], PDO::PARAM_STR);
			$prepare->bindParam(':sale_returns', $array['sale_returns'], PDO::PARAM_STR);
			$prepare->bindParam(':purchase_returns', $array['purchase_returns'], PDO::PARAM_STR);
			$prepare->bindParam(':assets', $array['assets'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function closeStoreSale($params)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET sale_date=:sale_date WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':sale_date', $params['sale_date'], PDO::PARAM_STR);
			$prepare->bindParam(':id', $params['shopId'], PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			$data = [
				'shop_id' => $params['shopId'],
				'owner_id' => $params['owner_id'],
				'amount' => $params['closing'],
				'sale_date' => $params['sale_date'],
			];
			$de = new DoubleEntry();
			$cl = $de->insertOB($data);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function enableStoreSale($id, $sale_date_show)
	{
		try {
			$stmt = "UPDATE `{$this->table}` SET sale_date_show=:sale_date_show WHERE id=:id";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->bindParam(':sale_date_show', $sale_date_show, PDO::PARAM_STR);
			$prepare->bindParam(':id', $id, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->rowCount();
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	public function getStoreTypes()
	{
		try {
			$stmt = "SELECT * FROM `{$this->table_st}`";
			$prepare = $this->dbh->prepare($stmt);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch (PDOException $e) {
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}
}
