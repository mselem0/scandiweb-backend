<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\GraphQL\Resolvers\OrderResolver;
use App\GraphQL\Types\OrderInputTypes;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL Mutation Type
 * 
 * Defines all available mutations in the schema
 */
class MutationType extends ObjectType
{
    public function __construct()
    {
        $orderResolver = new OrderResolver();

        parent::__construct([
            'name' => 'Mutation',
            'description' => 'Root mutation type',
            'fields' => [
                // Create a new order
                'createOrder' => [
                    'type' => new ObjectType([
                        'name' => 'OrderResult',
                        'description' => 'Result of order creation',
                        'fields' => [
                            'id' => [
                                'type' => Type::nonNull(Type::int()),
                                'description' => 'The created order ID'
                            ],
                            'totalAmount' => [
                                'type' => Type::nonNull(Type::float()),
                                'description' => 'Total order amount'
                            ],
                            'currency' => [
                                'type' => Type::nonNull(Type::string()),
                                'description' => 'Currency label'
                            ],
                            'status' => [
                                'type' => Type::nonNull(Type::string()),
                                'description' => 'Order status'
                            ],
                            'itemCount' => [
                                'type' => Type::nonNull(Type::int()),
                                'description' => 'Number of items in order'
                            ]
                        ]
                    ]),
                    'description' => 'Create a new order from cart items',
                    'args' => [
                        'items' => [
                            'type' => Type::nonNull(
                                Type::listOf(OrderInputTypes::orderItemInput())
                            ),
                            'description' => 'Items to include in the order'
                        ]
                    ],
                    'resolve' => function ($root, array $args) use ($orderResolver) {
                        return $orderResolver->createOrder($args['items']);
                    }
                ]
            ]
        ]);
    }
}
