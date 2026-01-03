<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Order;
use RuntimeException;

/**
 * Order Resolver
 * 
 * Handles order creation mutation
 */
class OrderResolver
{
    private Order $orderModel;

    public function __construct()
    {
        $this->orderModel = new Order();
    }

    /**
     * Create a new order from cart items
     * 
     * @param array $items Cart items from GraphQL input
     * @return array Order result
     */
    public function createOrder(array $items): array
    {
        if (empty($items)) {
            throw new RuntimeException('Cannot create order with no items');
        }

        // Transform GraphQL input to model format
        $orderItems = array_map(function ($item) {
            $selectedAttributes = [];

            if (!empty($item['selectedAttributes'])) {
                foreach ($item['selectedAttributes'] as $attr) {
                    $selectedAttributes[$attr['attributeId']] = $attr['attributeItemId'];
                }
            }

            return [
                'productId' => $item['productId'],
                'quantity' => $item['quantity'],
                'selectedAttributes' => $selectedAttributes
            ];
        }, $items);

        // Create the order
        $order = $this->orderModel->createOrder($orderItems);

        // Return result
        return [
            'id' => $order->getId(),
            'totalAmount' => $order->getTotalAmount(),
            'currency' => $order->getCurrencyLabel(),
            'status' => $order->getStatus(),
            'itemCount' => count($order->getItems())
        ];
    }

    /**
     * Get order by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $order = $this->orderModel->getById($id);

        if ($order === null) {
            return null;
        }

        return $order->toArray();
    }
}
