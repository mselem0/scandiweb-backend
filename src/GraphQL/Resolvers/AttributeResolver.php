<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Attribute\AbstractAttribute;
use App\Models\Attribute\TextAttribute;

/**
 * Attribute Resolver
 * 
 * Handles fetching attribute data for GraphQL.
 * Attributes are resolved separately from products as required.
 */
class AttributeResolver
{
    private AbstractAttribute $attributeModel;

    public function __construct()
    {
        // Use TextAttribute as entry point
        $this->attributeModel = new TextAttribute();
    }

    /**
     * Get attributes for a product
     * 
     * @param string $productId
     * @return array
     */
    public function getForProduct(string $productId): array
    {
        $attributes = $this->attributeModel->getForProduct($productId);

        return array_map(function (AbstractAttribute $attr) {
            return $attr->toArray();
        }, $attributes);
    }

    /**
     * Get all attributes
     * 
     * @return array
     */
    public function getAll(): array
    {
        $rows = $this->attributeModel->findAll();

        return array_map(function (array $row) {
            $attr = AbstractAttribute::createFromArray($row);
            return $attr->toArray();
        }, $rows);
    }
}
