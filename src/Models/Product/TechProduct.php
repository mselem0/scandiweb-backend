<?php

declare(strict_types=1);

namespace App\Models\Product;

class TechProduct extends AbstractProduct
{
    protected string $type = 'tech';

    /**
     * Get the product type
     * @return string
     */
    public function getType(): string
    {
        return 'tech';
    }

    /**
     * Get tech-specific data
     * @return array
     */
    public function getTypeSpecificData(): array
    {
        return [
            'productType' => 'tech',
            // ..
        ];
    }

    /**
     * Create TechProduct from database row
     * @param array $data
     * @return TechProduct
     */
    public static function fromArray(array $data): self
    {
        $product = new self();
        $product->id = $data['id'] ?? '';
        $product->categoryId = (int) ($data['category_id'] ?? 0);
        $product->name = $data['name'] ?? '';
        $product->description = $data['description'] ?? '';
        $product->inStock = (bool) ($data['in_stock'] ?? true);
        $product->brand = $data['brand'] ?? null;
        $product->type = 'tech';
        return $product;
    }

    /**
     * Get available capacities
     * @return array
     */
    public function getAvailableCapacities(): array
    {
        foreach ($this->attributes as $attr) {
            if (strtolower($attr['name']) === 'capacity') {
                return array_column($attr['items'], 'displayValue');
            }
        }
        return [];
    }

    /**
     * Get available colors
     * @return array
     */
    public function getAvailableColors(): array
    {
        foreach ($this->attributes as $attr) {
            if (strtolower($attr['name']) === 'color') {
                return $attr['items'];
            }
        }
        return [];
    }
}
