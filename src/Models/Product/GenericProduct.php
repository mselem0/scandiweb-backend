<?php

declare(strict_types=1);

namespace App\Models\Product;

class GenericProduct extends AbstractProduct
{
    protected string $type = 'generic';

    /**
     * Get the product type
     * @return string
     */
    public function getType(): string
    {
        return 'generic';
    }

    /**
     * Get generic-specific data
     * @return array
     */
    public function getTypeSpecificData(): array
    {
        return [
            'productType' => 'generic'
        ];
    }

    /**
     * Create GenericProduct from database row
     * @param array $data
     * @return GenericProduct
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
        $product->type = 'generic';
        return $product;
    }
}
