<?php

/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 07/03/18
 * Time: 20:52
 * PHP version 7
 */

namespace App\Repository;

use PDO;

abstract class CrudRepository
{
    protected PDO $pdo;

    public const TABLE = '';

    public function __construct()
    {
        $connection = new Connection();
        $this->pdo = $connection->getPdoConnection();
    }

    protected function getModelClassname()
    {
        return 'App\\Model\\' . ucfirst(strtolower(static::TABLE)) . 'Model';
    }

    /**
     * Get all row from database.
     */
    public function selectAll(string $orderBy = '', string $direction = 'ASC'): array
    {
        $query = 'SELECT * FROM ' . static::TABLE;
        if ($orderBy) {
            $query .= ' ORDER BY ' . $orderBy . ' ' . $direction;
        }
        return $this->pdo->query($query)->fetchAll(
            PDO::FETCH_CLASS,
            $this->getModelClassname(),
            get_class_vars($this->getModelClassname())
        );
    }

    /**
     * Get one row from database by ID.
     *
     */
    public function selectOneById(int $id)
    {
        // prepared request
        $statement = $this->pdo->prepare("SELECT * FROM " . static::TABLE . " WHERE id=:id");
        $statement->execute([':id' => $id]);

        $statement->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->getModelClassname());
        return $statement->fetch();
    }

    /**
     * Insert new item in database
     */
    public function insert(array $item): int
    {
        $fieldNames = [];
        $fieldValues = [];
        $preparedValues = [];
        foreach ($item as $key => $value) {
            if ($key != 'id') {
                $fieldNames[] = "`$key`";
                $fieldValues[] = ":$key";
                $preparedValues[":$key"] = $value;
            }
        }
        $fieldNamesFlat = implode(',', $fieldNames);
        $fieldValuesFlat = implode(',', $fieldValues);
        $statement = $this->pdo->prepare("INSERT INTO " . static::TABLE .
            " ($fieldNamesFlat) VALUES ($fieldValuesFlat)");

        $statement->execute($preparedValues);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update item in database
     */
    public function update(array $item): bool
    {
        $fieldNames = [];
        $preparedValues = [];
        foreach ($item as $key => $value) {
            if ($key != 'id') {
                $fieldNames[] = "`$key` = :$key";
            }
            $preparedValues[":$key"] = $value;
        }
        $fieldNamesFlat = implode(',', $fieldNames);
        $statement = $this->pdo->prepare("UPDATE " . static::TABLE . " SET $fieldNamesFlat WHERE id=:id");

        return $statement->execute($preparedValues);
    }

    /**
     * Delete row form an ID
     */
    public function delete(int $id): void
    {
        // prepared request
        $statement = $this->pdo->prepare("DELETE FROM " . static::TABLE . " WHERE id=:id");
        $statement->execute([':id' => $id]);
    }
}
