<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Config\Database;

abstract class AbstractModel
{
    protected PDO $db;
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get Table Name
     * @return string
     */
    abstract public static function getTableName(): string;

    /**
     * Model -> Array
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Get a record by id
     * @param mixed $id
     * @return mixed
     */
    public function findById($id): ?array
    {
        $table = static::getTableName();
        $primaryKey = static::$primaryKey;

        $stmt = $this->db->prepare(
            "SELECT * FROM {$table} WHERE {$primaryKey} = :id"
        );
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get All Records
     * @return array
     */
    public function findAll(): array
    {
        $table = static::getTableName();
        $stmt = $this->db->query("SELECT * FROM {$table}");
        return $stmt->fetchAll();
    }

    /**
     * Get records by a field
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public function findBy(string $field, $value): array
    {
        $table = static::getTableName();
        $stmt = $this->db->prepare(
            "SELECT * FROM {$table} WHERE {$field} = :value"
        );
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }

    /**
     * Store a new record
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $table = static::getTableName();
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $stmt = $this->db->prepare(
            "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})"
        );
        $stmt->execute($data);

        return $this->db->lastInsertId();
    }
}
