<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class OrderInputTypes
{
    private static ?InputObjectType $attributeInput = null;
    private static ?InputObjectType $orderItemInput = null;

    /**
     * Get Selected Attribute Input Type
     */
    public static function selectedAttributeInput(): InputObjectType
    {
        if (self::$attributeInput === null) {
            self::$attributeInput = new InputObjectType([
                'name' => 'SelectedAttributeInput',
                'description' => 'A selected attribute for a cart item',
                'fields' => [
                    'attributeId' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'The attribute ID (e.g., "Size", "Color")'
                    ],
                    'attributeItemId' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'The selected item ID (e.g., "40", "Green")'
                    ]
                ]
            ]);
        }
        return self::$attributeInput;
    }

    /**
     * Get Order Item Input Type
     */
    public static function orderItemInput(): InputObjectType
    {
        if (self::$orderItemInput === null) {
            self::$orderItemInput = new InputObjectType([
                'name' => 'OrderItemInput',
                'description' => 'An item in the order',
                'fields' => [
                    'productId' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'The product ID'
                    ],
                    'quantity' => [
                        'type' => Type::nonNull(Type::int()),
                        'description' => 'Quantity of this item'
                    ],
                    'selectedAttributes' => [
                        'type' => Type::listOf(self::selectedAttributeInput()),
                        'description' => 'Selected attributes for this item'
                    ]
                ]
            ]);
        }
        return self::$orderItemInput;
    }
}
