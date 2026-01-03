<?php

declare(strict_types=1);

namespace App\Models\Attribute;

class TextAttribute extends AbstractAttribute
{
    protected string $type = 'text';

    /**
     * Get attribute type
     */
    public function getAttributeType(): string
    {
        return 'text';
    }

    /**
     * Format item for text display
     * Text attributes show displayValue as-is
     */
    public function formatItemForDisplay(array $item): array
    {
        return [
            'id' => $item['id'] ?? '',
            'displayValue' => $item['displayValue'] ?? $item['value'] ?? '',
            'value' => $item['value'] ?? ''
        ];
    }

    /**
     * Create TextAttribute from array
     */
    public static function fromArray(array $data): self
    {
        $attribute = new self();
        $attribute->id = $data['id'] ?? '';
        $attribute->name = $data['name'] ?? '';
        $attribute->type = 'text';
        $attribute->items = $data['items'] ?? [];
        return $attribute;
    }
}
