<?php

class ShopAccounts extends Connection
{

    private $table = 'shopAccounts';

    public function getOwnerSA($shop_id)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table}` WHERE flag=1 and `shop_id`=:shop_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function updateSA($array)
    {
        try {
            $stmt = "UPDATE `{$this->table}` SET key_value=:key_value, label_value=:label_value, account_id=:account_id WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':key_value', $array['key_value'], PDO::PARAM_STR);
            $prepare->bindParam(':label_value', $array['label_value'], PDO::PARAM_STR);
            $prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_STR);
            $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function deleteSA($array)
    {
        try {
            $stmt = "UPDATE `{$this->table}` SET flag=0 where id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getSAPagination($params)
    {
        try {

            $stmt = "SELECT COUNT(id) as total FROM `{$this->table}` where flag=1 and `shop_id`=:shop_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shop_id', $params['shop_id'], PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);

            $no_of_records_per_page = $params['perPage'] ? $params['perPage'] : 10;
            $total_rows = $result['total'];
            $total_pages = ceil($total_rows / $no_of_records_per_page);
            $currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
            $offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
            $search = "(key_value LIKE '%" . $params["search"] . "%' or label_value LIKE '%" . $params["search"] . "%') ";
            $stmt = "SELECT * FROM `{$this->table}` WHERE flag=1 and $search and `shop_id`=:shop_id LIMIT :offset, :perPage";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
            $prepare->bindParam(':shop_id', $params['shop_id'], PDO::PARAM_INT);
            $prepare->bindParam(':perPage', $no_of_records_per_page, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return ['page' => $currentPage, 'totalRecords' => $total_rows, 'perPage' => $no_of_records_per_page, 'records' => $result];
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getSAs($shop_id)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table}` where shop_id=:shop_id and flag = 1";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getSA($id)
    {
        try {

            $stmt = "SELECT * FROM `{$this->table}` where flag=1 and id = :id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function createSA($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table}` (`key_value`, `label_value`, `account_id`, `shop_id`) VALUES (:key_value, :label_value, :account_id, :shop_id)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':key_value', $array['key_value'], PDO::PARAM_STR);
            $prepare->bindParam(':label_value', $array['label_value'], PDO::PARAM_STR);
            $prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_STR);
            $prepare->bindParam(':shop_id', $array['shop_id'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
}
