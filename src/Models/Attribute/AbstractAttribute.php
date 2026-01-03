<?php

declare(strict_types=1);

namespace App\Models\Attribute;

use App\Models\AbstractModel;

abstract class AbstractAttribute extends AbstractModel
{
    protected string $id = '';
    protected string $name = '';
    protected string $type = '';
    protected array $items = [];

    public static function getTableName(): string
    {
        return 'attributes';
    }

    /**
     * Get the attribute type
     */
    abstract public function getAttributeType(): string;

    /**
     * Format item for display
     * Each type can format items differently
     */
    abstract public function formatItemForDisplay(array $item): array;

    /**
     * Convert to array for GraphQL
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'items' => array_map(
                fn($item) => $this->formatItemForDisplay($item),
                $this->items
            )
        ];
    }

    /**
     * Factory method - creates appropriate attribute type
     */
    public static function createFromArray(array $data): AbstractAttribute
    {
        $type = $data['type'] ?? 'text';

        $typeClassMap = [
            'text' => TextAttribute::class,
            'swatch' => SwatchAttribute::class,
        ];

        $class = $typeClassMap[$type] ?? TextAttribute::class;
        return $class::fromArray($data);
    }

    /**
     * Load attribute items
     */
    protected function loadItems(): void
    {
        $stmt = $this->db->prepare(
            "SELECT item_id as id, display_value as displayValue, value
             FROM attribute_items
             WHERE attribute_id = :attribute_id"
        );
        $stmt->execute(['attribute_id' => $this->id]);
        $this->items = $stmt->fetchAll();
    }

    /**
     * Get attributes for a product
     * 
     * @param string $productId
     * @return AbstractAttribute[]
     */
    public function getForProduct(string $productId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.name, a.type
             FROM attributes a
             JOIN product_attributes pa ON a.id = pa.attribute_id
             WHERE pa.product_id = :product_id"
        );
        $stmt->execute(['product_id' => $productId]);
        $rows = $stmt->fetchAll();

        return array_map(function ($row) {
            $attribute = self::createFromArray($row);
            $attribute->loadItems();
            return $attribute;
        }, $rows);
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
