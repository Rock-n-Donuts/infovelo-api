<?php

namespace Rockndonuts\Hackqc\Models;

use PDO;
use PDOException;
use RuntimeException;

class DB
{
    private $dbHandle;

    public function __construct()
    {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $pwd = $_ENV['DB_PWD'];
        $dbName = $_ENV['DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        try {
            $this->dbHandle = new PDO(
                'mysql:host=' . $host . ';dbname=' . $dbName . ';charset=utf8mb4', $dbUser, $pwd
            );
        } catch (PDOException $e) {
            die("could not connect to the database".$e);
        }
    }

    /**
     * @param string $query
     * @param mixed $params
     * @return array|bool
     */
    public function get(string $query, mixed $params): array|bool
    {
        $statement = $this->dbHandle->prepare($query);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes a raw query
     * @param string $query The full query
     * @return array|false
     */
    public function executeQuery(string $query): bool|array
    {

        return $this->dbHandle->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array $data The data in an associative key array ['fieldName' => 'value']
     * @param string|null $table The table to insert to, if empty defaults to current Model's TABLE_NAME constant
     * @return string|bool
     */
    public function insert(array $data, ?string $table = null): string|bool
    {
        if (!$table) {
            $table = static::TABLE_NAME;
        }
        $fields = array_keys($data);
        $fieldString = implode(",", $fields);
        $placeHolder = str_repeat('?,', count($data) - 1) . '?';
        $query = <<<SQL
            INSERT INTO $table ($fieldString) VALUES ($placeHolder)
        SQL;

        $statement = $this->dbHandle->prepare($query);
        $statement->execute(array_values($data));

        return $this->dbHandle->lastInsertId();
    }

    /**
     * Finds all row for a given table
     * @param string|null $table
     * @param array|null $select
     * @return array|bool
     */
    public function findAll(?string $table = null, ?array $select = null): array|bool
    {
        if (!$table) {
            $table = static::TABLE_NAME;
        }

        $selectString = "*";
        if (!empty($select)) {
            $selectString = implode(",", $select);
        }
        $query = <<<SQL
            SELECT $selectString FROM $table
        SQL;

        return $this->dbHandle->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Finds rows for a given filter (where)
     * @param mixed $data The data as an associative array ['fieldName' => 'value']
     * @param array|null $select
     * @param string|null $table The table ti fetch from, defaults to Model's TABLE_NAME constant
     * @return array|bool
     */
    public function findBy(mixed $data, ?array $select = null, ?string $table = null): array|bool
    {
        if (empty($data)) {
            return $this->findAll($table, $select);
        }

        if (!$table) {
            $table = static::TABLE_NAME;
        }

        $fields = array_keys($data);
        $values = array_values($data);

        $whereString = "";
        foreach ($fields as $field) {
            $operator = "=";
            if ($field === "created_at") {
                $operator = ">=";
            }
            $whereString .= " " . $field . " $operator ?";
        }

        $selectString = "*";
        if (!empty($select)) {
            $selectString = implode(", ", $select);
        }
        $query = <<<SQL
            SELECT $selectString FROM $table
            WHERE $whereString
        SQL;

        $statement = $this->dbHandle->prepare($query);
        $statement->execute($values);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $objectId The object id to update
     * @param array $fields The fields to update in an associative array ['fieldName' => 'value']
     * @param string|null $table The table name, defaults to Model's TABLE_NAME
     * @param string|null $idKey The key name for the object id (defaults to 'id')
     * @return bool
     */
    public function update(int $objectId, array $fields, ?string $table = null, ?string $idKey = "id"): bool
    {
        if (!$table) {
            $table = static::TABLE_NAME;
        }

        $updateString = "";
        foreach ($fields as $fieldName => $fieldValue) {
            $updateString .= " $fieldName = :$fieldName, ";
        }
        $updateString = rtrim($updateString, ', ');

        $query = <<<SQL
            UPDATE $table SET $updateString WHERE $idKey = $objectId
        SQL;

        return $this->dbHandle->prepare($query)->execute($fields);
    }

    /**
     * Finds a single record
     * @throws RuntimeException Throw if more than one record found
     * @param array $array
     * @param array|null $select
     * @return array
     */
    public function findOneBy(array $array, ?array $select = null): array
    {
        $results = $this->findBy($array, $select);
        if (empty($results)) {
            return [];
        }

        if (count($results) > 1) {
            throw new RuntimeException('Too many results');
        }

        return $results[0];
    }


}
