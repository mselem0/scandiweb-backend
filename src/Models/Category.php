<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\AbstractModel;

class Category extends AbstractModel
{
    private ?int $id = null;
    private string $name = '';

    /**
     * Get Table Name
     * @return string
     */
    public static function getTableName(): string
    {
        return 'categories';
    }

    /**
     * Model -> Array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    /**
     * Category from -> a database row
     * @param array $data
     * @return Category
     */
    public static function fromArray(array $data): self
    {
        $category = new self();
        $category->id = isset($data['id']) ? (int) $data['id'] : null;
        $category->name = $data['name'] ?? '';
        return $category;
    }

    /**
     * Get all categories
     * @return Category[]
     */
    public function getAll(): array
    {
        $rows = $this->findAll();
        return array_map(fn($row) => self::fromArray($row), $rows);
    }

    /**
     * Get category by name
     * @param string $name
     * @return mixed
     */
    public function getByName(string $name): ?self
    {
        $rows = $this->findBy('name', $name);
        if (empty($rows)) {
            return null;
        }
        return self::fromArray($rows[0]);
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
