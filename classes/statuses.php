<?php

class Statuses extends Connection
{

    private $table = 'status';
    private $table_accounts = 'accounts';

    public function getOwnerStatus($shop_id)
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
    public function gettypes($shop_id)
    {
        try {
            $stmt = "SELECT DISTINCT `type` FROM `{$this->table}` WHERE flag=1 and `shop_id`=:shop_id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function update($array)
    {
        try {
            print_r($array);
            $stmt = "UPDATE `{$this->table}` SET title=:title, type=:type, progress_value=:progress_value WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
            $prepare->bindParam(':id', $array['id'], PDO::PARAM_STR);
            $prepare->bindParam(':progress_value', $array['progress_value'], PDO::PARAM_STR);
            $prepare->bindParam(':type', $array['type'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function delete($array)
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

    public function linkAccount($array)
    {
        try {
            $stmt = "UPDATE `{$this->table}` SET account_id=:account_id WHERE id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $array['id'], PDO::PARAM_INT);
            $prepare->bindParam(':account_id', $array['account_id'], PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->rowCount();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getStatusPagination($params)
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
            $search = "(c.title LIKE '%" . $params["search"] . "%' or c.type LIKE '%" . $params["search"] . "%') ";
            $stmt = "SELECT c.* FROM `{$this->table}` as c WHERE flag=1 and $search and `shop_id`=:shop_id LIMIT :offset, :perPage";
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

    public function status($type, $shop_id)
    {
        try {
            $where = "";
            if ($type == 'pro') {
                $where = 'and flag=1 and cat_type = 2';
            };
            if ($type == 'exp') {
                $where = 'and flag=1 and cat_type = 1';
            }
            $stmt = "SELECT * FROM `{$this->table}` where shop_id=:shop_id " . $where;
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function getStatusById($id)
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
    public function expenseByAccount($id)
    {
        try {
            $stmt = "SELECT * FROM `{$this->table}` where flag=1 and account_id=:id";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
    public function create($array)
    {
        try {
            $stmt = "INSERT INTO `{$this->table}` (`title`, `type`, `progress_value`, `shop_id`, `created_by`) VALUES (:title, :type, :progress_value, :shop_id, :created_by)";
            $prepare = $this->dbh->prepare($stmt);
            $prepare->bindParam(':title', $array['title'], PDO::PARAM_STR);
            $prepare->bindParam(':type', $array['type'], PDO::PARAM_STR);
            $prepare->bindParam(':progress_value', $array['progress_value'], PDO::PARAM_STR);
            $prepare->bindParam(':shop_id', $array['shop_id'], PDO::PARAM_STR);
            $prepare->bindParam(':created_by', $array['created_by'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $this->dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        }
    }
}
