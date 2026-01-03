<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Product\AbstractProduct;
use App\Models\Product\GenericProduct;

/**
 * Product Resolver
 * 
 * Handles fetching product data for GraphQL.
 * Demonstrates proper OOP by using the polymorphic product models.
 */
class ProductResolver
{
    private AbstractProduct $productModel;

    public function __construct()
    {
        // Use GenericProduct as entry point to access factory methods
        $this->productModel = new GenericProduct();
    }

    /**
     * Get all products
     * 
     * @return array
     */
    public function getAll(): array
    {
        $products = $this->productModel->getAll();

        return array_map(function (AbstractProduct $product) {
            return $product->toArray();
        }, $products);
    }

    /**
     * Get products by category
     * 
     * @param string $categoryName
     * @return array
     */
    public function getByCategory(string $categoryName): array
    {
        $products = $this->productModel->getByCategory($categoryName);

        return array_map(function (AbstractProduct $product) {
            return $product->toArray();
        }, $products);
    }

    /**
     * Get single product by ID
     * 
     * @param string $id
     * @return array|null
     */
    public function getById(string $id): ?array
    {
        $product = $this->productModel->getById($id);

        if ($product === null) {
            return null;
        }

        return $product->toArray();
    }
}
