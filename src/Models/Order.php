<?php

declare(strict_types=1);

namespace App\Models;

class Order extends AbstractModel
{
    private ?int $id = null;
    private float $totalAmount = 0.0;
    private string $currencyLabel = 'USD';
    private string $currencySymbol = '$';
    private string $status = 'pending';
    private array $items = [];

    public static function getTableName(): string
    {
        return 'orders';
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'totalAmount' => $this->totalAmount,
            'currency' => [
                'label' => $this->currencyLabel,
                'symbol' => $this->currencySymbol
            ],
            'status' => $this->status,
            'items' => $this->items
        ];
    }

    /**
     * Create order from cart items
     * 
     * @param array $items Cart items with structure:
     *   [
     *     'productId' => 'ps-5',
     *     'quantity' => 2,
     *     'selectedAttributes' => ['Color' => 'Green', 'Capacity' => '512G']
     *   ]
     * @return Order
     */
    public function createOrder(array $items): self
    {
        $this->db->beginTransaction();

        try {
            // Calculate total and validate items
            $totalAmount = 0.0;
            $orderItems = [];

            foreach ($items as $item) {
                $productId = $item['productId'];
                $quantity = (int) ($item['quantity'] ?? 1);
                $selectedAttributes = $item['selectedAttributes'] ?? [];

                // Get product price
                $stmt = $this->db->prepare(
                    "SELECT pp.amount, pp.currency_label, pp.currency_symbol, p.in_stock
                     FROM product_prices pp
                     JOIN products p ON pp.product_id = p.id
                     WHERE pp.product_id = :product_id
                     LIMIT 1"
                );
                $stmt->execute(['product_id' => $productId]);
                $priceData = $stmt->fetch();

                if (!$priceData) {
                    throw new \RuntimeException("Product not found: {$productId}");
                }

                if (!$priceData['in_stock']) {
                    throw new \RuntimeException("Product out of stock: {$productId}");
                }

                $unitPrice = (float) $priceData['amount'];
                $totalAmount += $unitPrice * $quantity;

                // Store currency from first item (assuming single currency)
                if (empty($this->currencyLabel)) {
                    $this->currencyLabel = $priceData['currency_label'];
                    $this->currencySymbol = $priceData['currency_symbol'];
                }

                $orderItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'selected_attributes' => json_encode($selectedAttributes)
                ];
            }

            // Create order
            $stmt = $this->db->prepare(
                "INSERT INTO orders (total_amount, currency_label, currency_symbol, status)
                 VALUES (:total_amount, :currency_label, :currency_symbol, :status)"
            );
            $stmt->execute([
                'total_amount' => $totalAmount,
                'currency_label' => $this->currencyLabel,
                'currency_symbol' => $this->currencySymbol,
                'status' => 'pending'
            ]);

            $orderId = (int) $this->db->lastInsertId();

            // Create order items
            $stmt = $this->db->prepare(
                "INSERT INTO order_items (order_id, product_id, quantity, unit_price, selected_attributes)
                 VALUES (:order_id, :product_id, :quantity, :unit_price, :selected_attributes)"
            );

            foreach ($orderItems as $item) {
                $stmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'selected_attributes' => $item['selected_attributes']
                ]);
            }

            $this->db->commit();

            // Set order properties
            $this->id = $orderId;
            $this->totalAmount = $totalAmount;
            $this->items = $orderItems;
            $this->status = 'pending';

            return $this;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get order by ID
     */
    public function getById(int $orderId): ?self
    {
        $row = $this->findById($orderId);
        if (!$row) {
            return null;
        }

        $order = new self();
        $order->id = (int) $row['id'];
        $order->totalAmount = (float) $row['total_amount'];
        $order->currencyLabel = $row['currency_label'];
        $order->currencySymbol = $row['currency_symbol'];
        $order->status = $row['status'];

        // Load order items
        $stmt = $this->db->prepare(
            "SELECT * FROM order_items WHERE order_id = :order_id"
        );
        $stmt->execute(['order_id' => $orderId]);
        $order->items = $stmt->fetchAll();

        return $order;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getCurrencyLabel(): string
    {
        return $this->currencyLabel;
    }

    public function getCurrencySymbol(): string
    {
        return $this->currencySymbol;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
