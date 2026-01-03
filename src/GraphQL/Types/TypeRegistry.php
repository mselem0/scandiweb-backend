<?php

declare(strict_types=1);

namespace App\GraphQl\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class TypeRegistry
{
    private static ?TypeRegistry $instance = null;
    private ?ObjectType $categoryType = null;
    private ?ObjectType $productType = null;
    private ?ObjectType $attributeType = null;
    private ?ObjectType $attributeItemType = null;
    private ?ObjectType $priceType = null;
    private ?ObjectType $currencyType = null;

    // Singleton
    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get Category Type
     */
    public function category(): ObjectType
    {
        if ($this->categoryType === null) {
            $this->categoryType = new ObjectType([
                'name' => 'Category',
                'description' => 'A product category',
                'fields' => [
                    'id' => [
                        'type' => Type::int(),
                        'description' => 'Category ID'
                    ],
                    'name' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Category name'
                    ]
                ]
            ]);
        }
        return $this->categoryType;
    }

    /**
     * Get Currency Type
     */
    public function currency(): ObjectType
    {
        if ($this->currencyType === null) {
            $this->currencyType = new ObjectType([
                'name' => 'Currency',
                'description' => 'Currency information',
                'fields' => [
                    'label' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Currency code (e.g., USD)'
                    ],
                    'symbol' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Currency symbol (e.g., $)'
                    ]
                ]
            ]);
        }
        return $this->currencyType;
    }

    /**
     * Get Price Type
     */
    public function price(): ObjectType
    {
        if ($this->priceType === null) {
            $this->priceType = new ObjectType([
                'name' => 'Price',
                'description' => 'Product price',
                'fields' => [
                    'amount' => [
                        'type' => Type::nonNull(Type::float()),
                        'description' => 'Price amount'
                    ],
                    'currency' => [
                        'type' => Type::nonNull($this->currency()),
                        'description' => 'Price currency'
                    ]
                ]
            ]);
        }
        return $this->priceType;
    }

    /**
     * Get Attribute Item Type
     */
    public function attributeItem(): ObjectType
    {
        if ($this->attributeItemType === null) {
            $this->attributeItemType = new ObjectType([
                'name' => 'AttributeItem',
                'description' => 'A single attribute option/value',
                'fields' => [
                    'id' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Item ID'
                    ],
                    'displayValue' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Display value for the item'
                    ],
                    'value' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Actual value (can be text or color hex)'
                    ]
                ]
            ]);
        }
        return $this->attributeItemType;
    }

    /**
     * Get Attribute Type
     */
    public function attribute(): ObjectType
    {
        if ($this->attributeType === null) {
            $this->attributeType = new ObjectType([
                'name' => 'AttributeSet',
                'description' => 'A product attribute (e.g., Size, Color)',
                'fields' => [
                    'id' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Attribute ID'
                    ],
                    'name' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Attribute name'
                    ],
                    'type' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Attribute type (text or swatch)'
                    ],
                    'items' => [
                        'type' => Type::nonNull(Type::listOf($this->attributeItem())),
                        'description' => 'Available options for this attribute'
                    ]
                ]
            ]);
        }
        return $this->attributeType;
    }

    /**
     * Get Product Type
     */
    public function product(): ObjectType
    {
        if ($this->productType === null) {
            $this->productType = new ObjectType([
                'name' => 'Product',
                'description' => 'A product in the catalog',
                'fields' => [
                    'id' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Product ID (e.g., huarache-x-stussy-le)'
                    ],
                    'name' => [
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'Product name'
                    ],
                    'description' => [
                        'type' => Type::string(),
                        'description' => 'Product description (HTML)'
                    ],
                    'inStock' => [
                        'type' => Type::nonNull(Type::boolean()),
                        'description' => 'Whether the product is in stock'
                    ],
                    'brand' => [
                        'type' => Type::string(),
                        'description' => 'Product brand'
                    ],
                    'category' => [
                        'type' => Type::string(),
                        'description' => 'Category name'
                    ],
                    'gallery' => [
                        'type' => Type::nonNull(Type::listOf(Type::string())),
                        'description' => 'Product image URLs'
                    ],
                    'prices' => [
                        'type' => Type::nonNull(Type::listOf($this->price())),
                        'description' => 'Product prices in different currencies'
                    ],
                    'attributes' => [
                        'type' => Type::nonNull(Type::listOf($this->attribute())),
                        'description' => 'Product attributes (size, color, etc.)'
                    ]
                ]
            ]);
        }
        return $this->productType;
    }
}
