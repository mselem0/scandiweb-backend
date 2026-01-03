<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\GraphQL\Resolvers\CategoryResolver;
use App\GraphQL\Resolvers\ProductResolver;
use App\GraphQL\Types\TypeRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class QueryType extends ObjectType
{
    public function __construct()
    {
        $types = TypeRegistry::getInstance();
        $categoryResolver = new CategoryResolver();
        $productResolver = new ProductResolver();

        parent::__construct([
            'name' => 'Query',
            'description' => 'Root query type',
            'fields' => [
                // Get all categories
                'categories' => [
                    'type' => Type::nonNull(Type::listOf($types->category())),
                    'description' => 'Get all categories',
                    'resolve' => function () use ($categoryResolver) {
                        return $categoryResolver->getAll();
                    }
                ],

                // Get category by name
                'category' => [
                    'type' => $types->category(),
                    'description' => 'Get a category by name',
                    'args' => [
                        'name' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'Category name'
                        ]
                    ],
                    'resolve' => function ($root, array $args) use ($categoryResolver) {
                        return $categoryResolver->getByName($args['name']);
                    }
                ],

                // Get all products (optionally filtered by category)
                'products' => [
                    'type' => Type::nonNull(Type::listOf($types->product())),
                    'description' => 'Get products, optionally filtered by category',
                    'args' => [
                        'category' => [
                            'type' => Type::string(),
                            'description' => 'Filter by category name (optional)',
                            'defaultValue' => 'all'
                        ]
                    ],
                    'resolve' => function ($root, array $args) use ($productResolver) {
                        $category = $args['category'] ?? 'all';
                        return $productResolver->getByCategory($category);
                    }
                ],

                // Get single product by ID
                'product' => [
                    'type' => $types->product(),
                    'description' => 'Get a single product by ID',
                    'args' => [
                        'id' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'Product ID'
                        ]
                    ],
                    'resolve' => function ($root, array $args) use ($productResolver) {
                        return $productResolver->getById($args['id']);
                    }
                ]
            ]
        ]);
    }
}
