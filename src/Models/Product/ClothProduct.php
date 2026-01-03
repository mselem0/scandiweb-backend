<?php

declare(strict_types=1);

namespace App\Models\Product;

class ClothProduct extends AbstractProduct
{
    protected string $type = 'clothes';

    /**
     * Get the product type
     * @return string
     */
    public function getType(): string
    {
        return 'clothes';
    }

    /**
     * Get cloth-specific data
     * @return array
     */
    public function getTypeSpecificData(): array
    {
        return [
            'productType' => 'clothes',
            // ...
        ];
    }

    /**
     * Create ClothProduct from database row
     * @param array $data
     * @return ClothProduct
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
        $product->type = 'clothes';
        return $product;
    }

    /**
     * Get available sizes
     * @return array
     */
    public function getAvailableSizes(): array
    {
        foreach ($this->attributes as $attr) {
            if (strtolower($attr['name']) === 'size') {
                return array_column($attr['items'], 'displayValue');
            }
        }
        return [];
    }

    /**
     * Check if a  size is available
     * @param string $size
     * @return bool
     */
    public function hasSizeAvailable(string $size): bool
    {
        $sizes = $this->getAvailableSizes();
        return in_array($size, $sizes, true);
    }
}
