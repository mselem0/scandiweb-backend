<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Models\AbstractModel;

abstract class AbstractProduct extends AbstractModel
{
    // Available Fields
    protected string $id = '';
    protected int $categoryId = 0;
    protected string $name = '';
    protected string $description = '';
    protected bool $inStock = true;
    protected ?string $brand = null;
    protected string $type = '';
    protected array $gallery = [];
    protected array $prices = [];
    protected array $attributes = [];
    protected ?string $categoryName = null;

    /**
     * Get Table Name
     * @return string
     */
    public static function getTableName(): string
    {
        return 'products';
    }

    /**
     * Get the product type
     *@return string
     */
    abstract public function getType(): string;

    /**
     * Get type-specific display data
     *@return array
     */
    abstract public function getTypeSpecificData(): array;

    /**
     * Convert to array
     *@return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'inStock' => $this->inStock,
            'brand' => $this->brand,
            'category' => $this->categoryName,
            'gallery' => $this->gallery,
            'prices' => $this->prices,
            'attributes' => $this->attributes,
            ...$this->getTypeSpecificData()
        ];
    }

    /**
     * Factory method - creates the appropriate product type
     * @param array $data
     * @return AbstractProduct
     */
    public static function createFromArray(array $data): AbstractProduct
    {
        $type = $data['type'] ?? 'generic';

        // Map of type to class - this is the only place we need mapping
        $typeClassMap = [
            'clothes' => ClothProduct::class,
            'tech' => TechProduct::class,
        ];

        $class = $typeClassMap[$type] ?? GenericProduct::class;
        return $class::fromArray($data);
    }

    /**
     * Load all related data (gallery, prices, attributes)
     * @return void
     */
    protected function loadRelatedData(): void
    {
        $this->loadGallery();
        $this->loadPrices();
        $this->loadAttributes();
        $this->loadCategoryName();
    }

    /**
     * Load gallery
     * @return void
     */
    protected function loadGallery(): void
    {
        $stmt = $this->db->prepare(
            "SELECT image_url FROM product_gallery 
             WHERE product_id = :product_id 
             ORDER BY image_order"
        );
        $stmt->execute(['product_id' => $this->id]);
        $this->gallery = array_column($stmt->fetchAll(), 'image_url');
    }

    /**
     * Load prices
     * @return void
     */
    protected function loadPrices(): void
    {
        $stmt = $this->db->prepare(
            "SELECT pp.amount, c.currency_label, c.currency_symbol
         FROM product_prices pp
         JOIN currencies c ON c.id = pp.currency_id
         WHERE pp.product_id = :product_id"
        );

        $stmt->execute(['product_id' => $this->id]);

        $rows = $stmt->fetchAll();

        $this->prices = array_map(function ($row) {
            return [
                'amount' => (float) $row['amount'],
                'currency' => [
                    'label'  => $row['currency_label'],
                    'symbol' => $row['currency_symbol']
                ]
            ];
        }, $rows);
    }


    /**
     * Load attributes
     * @return void
     */
    protected function loadAttributes(): void
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.name, a.type
             FROM attributes a
             JOIN product_attributes pa ON a.id = pa.attribute_id
             WHERE pa.product_id = :product_id"
        );
        $stmt->execute(['product_id' => $this->id]);
        $attributes = $stmt->fetchAll();

        $this->attributes = [];
        foreach ($attributes as $attr) {
            // Load attribute items
            $itemStmt = $this->db->prepare(
                "SELECT item_id as id, display_value as displayValue, value
                 FROM attribute_items
                 WHERE attribute_id = :attribute_id"
            );
            $itemStmt->execute(['attribute_id' => $attr['id']]);

            $this->attributes[] = [
                'id' => $attr['id'],
                'name' => $attr['name'],
                'type' => $attr['type'],
                'items' => $itemStmt->fetchAll()
            ];
        }
    }

    /**
     * Load category name
     * @return void
     */
    protected function loadCategoryName(): void
    {
        $stmt = $this->db->prepare(
            "SELECT name FROM categories WHERE id = :id"
        );
        $stmt->execute(['id' => $this->categoryId]);
        $result = $stmt->fetch();
        $this->categoryName = $result['name'] ?? null;
    }

    /**
     * Get all products
     * @return AbstractProduct[]
     */
    public function getAll(): array
    {
        $rows = $this->findAll();
        return array_map(function ($row) {
            $product = self::createFromArray($row);
            $product->loadRelatedData();
            return $product;
        }, $rows);
    }

    /**
     * Get products by category
     * 
     * @param string $categoryName
     * @return AbstractProduct[]
     */
    public function getByCategory(string $categoryName): array
    {
        if ($categoryName === 'all') {
            return $this->getAll();
        }

        $stmt = $this->db->prepare(
            "SELECT p.* FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE c.name = :category_name"
        );
        $stmt->execute(['category_name' => $categoryName]);
        $rows = $stmt->fetchAll();

        return array_map(function ($row) {
            $product = self::createFromArray($row);
            $product->loadRelatedData();
            return $product;
        }, $rows);
    }

    /**
     * Get single product by ID
     * @return mixed
     */
    public function getById(string $productId): ?AbstractProduct
    {
        $row = $this->findById($productId);
        if (!$row) {
            return null;
        }

        $product = self::createFromArray($row);
        $product->loadRelatedData();
        return $product;
    }

    // Common Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function getGallery(): array
    {
        return $this->gallery;
    }

    public function getPrices(): array
    {
        return $this->prices;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }
}
