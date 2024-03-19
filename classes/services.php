<?php

class Services extends Connection
{

    private $table = 'services';

    public function getOwnerServices($owner_id)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "SELECT * FROM `{$this->table}` WHERE flag=1 and `owner_id`=:owner_id";
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
    public function updateService($array)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "UPDATE `{$this->table}` SET full_name=:full_name WHERE id=:id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
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
    public function deleteService($array)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "UPDATE `{$this->table}` SET flag=0 where id=:id";
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

    public function getServicesPagination($params)
    {
        $dbh = $this->connectionPool->getConnection();
        try {

            $stmt = "SELECT COUNT(id) as total FROM `{$this->table}` where flag=1 and `owner_id`=:owner_id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':owner_id', $params['owner_id'], PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);

            $no_of_records_per_page = $params['perPage'] ? $params['perPage'] : 10;
            $total_rows = $result['total'];
            $total_pages = ceil($total_rows / $no_of_records_per_page);
            $currentPage = $total_pages >= $params['page'] ? $params['page'] : $total_pages;
            $offset = (($currentPage - 1) < 0 ? 0 : ($currentPage - 1)) * $no_of_records_per_page;
            $search = "(full_name LIKE '%" . $params["search"] . "%') ";
            $stmt = "SELECT * FROM `{$this->table}` WHERE flag=1 and $search and `owner_id`=:owner_id LIMIT :offset, :perPage";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':offset', $offset, PDO::PARAM_INT);
            $prepare->bindParam(':owner_id', $params['owner_id'], PDO::PARAM_INT);
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

    public function getServices($owner_id)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "SELECT * FROM `{$this->table}` where owner_id=:owner_id and flag = 1";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':owner_id', $owner_id, PDO::PARAM_INT);
            $prepare->execute();
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function getService($id)
    {
        $dbh = $this->connectionPool->getConnection();
        try {

            $stmt = "SELECT * FROM `{$this->table}` where flag=1 and id = :id";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':id', $id, PDO::PARAM_STR);
            $prepare->execute();
            $result = $prepare->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }

    public function createService($array)
    {
        $dbh = $this->connectionPool->getConnection();
        try {
            $stmt = "INSERT INTO `{$this->table}` (`full_name`, `owner_id`, `shop_id`) VALUES (:full_name, :owner_id, :shop_id)";
            $prepare = $dbh->prepare($stmt);
            $prepare->bindParam(':full_name', $array['full_name'], PDO::PARAM_STR);
            $prepare->bindParam(':owner_id', $array['owner_id'], PDO::PARAM_STR);
            $prepare->bindParam(':shop_id', $array['shop_id'], PDO::PARAM_STR);
            $prepare->execute();
            $result = $dbh->lastInsertId();
            return $result;
        } catch (PDOException $e) {
            die("Error!: " . $e->getMessage() . "<br/>");
        } finally {
            $this->connectionPool->releaseConnection($dbh);
        }
    }
}
