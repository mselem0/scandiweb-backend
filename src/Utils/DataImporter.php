<?php

declare(strict_types=1);

namespace App\Utils;

use PDO;
use Exception;
use RuntimeException;
use App\Config\Database;

class DataImporter
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Import data from data.json file
     * @param string $path
     * @return array
     * @throws RuntimeException
     */
    public function import(string $path): array
    {
        $results = [
            'categories' => 0,
            'products' => 0,
            'attributes' => 0,
            'errors' => []
        ];

        // Read the JSON file
        $jsonContent = file_get_contents($path);
        if ($jsonContent === false) {
            throw new RuntimeException("Can't read this file : {$path}");
        }

        // Get data
        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON: " . json_last_error_msg());
        }
        $dataArray = $data['data'] ?? $data;

        try {
            $this->db->beginTransaction();

            // Start categories
            $results['categories'] = $this->importCategories($dataArray['categories'] ?? []);

            // Start products 
            $results['products'] = $this->importProducts($dataArray['products'] ?? []);

            $this->db->commit();
        } catch (RuntimeException $e) {
            $this->db->rollBack();
            $results['errors'][] = $e->getMessage();
        }
        return $results;
    }

    /**
     * Import categories
     * @param array $categories
     * @return int
     */
    private function importCategories(array $categories): int
    {
        $count = 0;
        $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (:name) ON DUPLICATE KEY UPDATE name = name");

        foreach ($categories as $category) {
            $name = is_array($category) ? $category['name'] : $category;
            $stmt->execute(['name' => $name]);
        }

        return $count;
    }

    /**
     * Import products
     * @param array $products
     * @return int
     */
    private function importProducts(array $products): int
    {
        $count = 0;

        // Insert product
        $stmt = $this->db->prepare(
            "INSERT INTO products (id, category_id, name, description, in_stock, brand, type)
            VALUES (:id, :category_id, :name, :description, :in_stock, :brand, :type)
            ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            description = VALUES(description),
            in_stock = VALUES(in_stock),
            brand = VALUES(brand),
            type = VALUES(type)"
        );

        foreach ($products as $product) {
            // Determine product type based on category
            $type = $this->determineProductType($product['category'] ?? 'all');

            // Get category ID
            $categoryId = $this->getCategoryId($product['category']) ?? null;

            if ($categoryId === null) {
                throw new Exception("Error couldn't find the category for product: {$product['id']}");
            }

            $stmt->execute([
                'id' => $product['id'],
                'category_id' => $categoryId,
                'name' => $product['name'],
                'description' => $product['description'] ?? '',
                'in_stock' => $product['inStock'] ? 1 : 0,
                'brand' => $product['brand'] ?? null,
                'type' => $type
            ]);

            // Import gallery images
            $this->importGallery($product['id'], $product['gallery'] ?? []);

            // Import prices
            $this->importPrices($product['id'], $product['prices'] ?? []);

            // Import attributes
            $this->importProductAttributes($product['id'], $product['attributes'] ?? []);

            $count++;
        }

        return $count;
    }

    /**
     * Determine product type
     * @param string $category
     * @return string
     */
    private function determineProductType(string $category): string
    {
        $typeMap = [
            'clothes' => 'clothes',
            'tech' => 'tech',
            'all' => 'generic'
        ];

        return $typeMap[$category] ?? 'generic';
    }

    /**
     * Get category ID by name
     * @param string $name
     * @return mixed
     */
    private function getCategoryId(string $name): mixed
    {
        $stmt = $this->db->prepare("SELECT id FROM categories WHERE name = :name");
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        return (int) $result['id'];
    }

    /**
     * Import product gallery images
     * @param string $productId
     * @param array $images
     * @return void
     */
    private function importGallery(string $productId, array $images): void
    {
        // Delete existing images
        $stmt = $this->db->prepare("DELETE FROM product_gallery WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);

        // Insert new images
        $stmt = $this->db->prepare(
            "INSERT INTO product_gallery (product_id, image_url, image_order)
             VALUES (:product_id, :image_url, :image_order)"
        );

        foreach ($images as $order => $url) {
            $stmt->execute([
                'product_id' => $productId,
                'image_url' => $url,
                'image_order' => $order
            ]);
        }
    }

    /**
     * Import product prices
     * @param string $productId
     * @param array $prices
     * @return void
     */
    private function importPrices(string $productId, array $prices): void
    {
        // Delete existing prices
        $stmt = $this->db->prepare("DELETE FROM product_prices WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);

        // Insert new prices
        $stmtPrice = $this->db->prepare(
            "INSERT INTO product_prices (product_id, amount, currency_id)
             VALUES (:product_id, :amount, :currency_id)"
        );

        // Insert new currency
        $stmtCheckCurrency = $this->db->prepare(
            "SELECT id FROM currencies WHERE currency_label = :currency_label"
        );
        $stmtAddCurrency = $this->db->prepare(
            "INSERT INTO currencies (currency_label, currency_symbol)
             VALUES (:currency_label, :currency_symbol)"
        );

        foreach ($prices as $price) {
            $stmtCheckCurrency->execute(['currency_label' => $price['currency']['label']]);
            $currency = $stmtCheckCurrency->fetch();
            if (!$currency) {
                $stmtAddCurrency->execute([
                    'currency_label' => $price['currency']['label'],
                    'currency_symbol' => $price['currency']['symbol']
                ]);

                $currencyId = (int) $this->db->lastInsertId();
            } else {
                $currencyId = (int) $currency['id'];
            }

            $stmtPrice->execute([
                'product_id' => $productId,
                'amount' => $price['amount'],
                'currency_id' => $currencyId
            ]);
        }
    }

    /**
     * Import product attributes
     * @param string $productId
     * @param array $attributes
     * @return void
     */
    private function importProductAttributes(string $productId, array $attributes): void
    {
        // Delete existing product-attribute links
        $stmt = $this->db->prepare("DELETE FROM product_attributes WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);

        foreach ($attributes as $attribute) {
            // Ensure attribute exists
            $this->ensureAttributeExists($attribute);

            // Link product to attribute
            $stmt = $this->db->prepare(
                "INSERT INTO product_attributes (product_id, attribute_id)
                 VALUES (:product_id, :attribute_id)
                 ON DUPLICATE KEY UPDATE product_id = product_id"
            );
            $stmt->execute([
                'product_id' => $productId,
                'attribute_id' => $attribute['id']
            ]);
        }
    }

    /**
     * Ensure attribute and its items exist
     */
    private function ensureAttributeExists(array $attribute): void
    {
        // Insert or update attribute
        $stmt = $this->db->prepare(
            "INSERT INTO attributes (id, name, type)
             VALUES (:id, :name, :type)
             ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type)"
        );
        $stmt->execute([
            'id' => $attribute['id'],
            'name' => $attribute['name'],
            'type' => $attribute['type'] ?? 'text'
        ]);

        // Delete existing items and reinsert
        $stmt = $this->db->prepare("DELETE FROM attribute_items WHERE attribute_id = :attribute_id");
        $stmt->execute(['attribute_id' => $attribute['id']]);

        // Insert attribute items
        $stmt = $this->db->prepare(
            "INSERT INTO attribute_items (attribute_id, item_id, display_value, value)
             VALUES (:attribute_id, :item_id, :display_value, :value)"
        );

        foreach ($attribute['items'] ?? [] as $item) {
            $stmt->execute([
                'attribute_id' => $attribute['id'],
                'item_id' => $item['id'],
                'display_value' => $item['displayValue'],
                'value' => $item['value']
            ]);
        }
    }
}
