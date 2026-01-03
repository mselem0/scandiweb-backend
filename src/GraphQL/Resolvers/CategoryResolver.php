<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Category;

/**
 * Category Resolver
 * 
 * Handles fetching category data for GraphQL
 */
class CategoryResolver
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    /**
     * Get all categories
     * 
     * @return array
     */
    public function getAll(): array
    {
        $categories = $this->categoryModel->getAll();

        return array_map(function ($category) {
            return $category->toArray();
        }, $categories);
    }

    /**
     * Get category by name
     * 
     * @param string $name
     * @return array|null
     */
    public function getByName(string $name): ?array
    {
        $category = $this->categoryModel->getByName($name);

        if ($category === null) {
            return null;
        }

        return $category->toArray();
    }
}
