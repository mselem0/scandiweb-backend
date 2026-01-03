<?php

declare(strict_types=1);

namespace App\Models\Attribute;

class SwatchAttribute extends AbstractAttribute
{
    protected string $type = 'swatch';

    /**
     * Get attribute type
     */
    public function getAttributeType(): string
    {
        return 'swatch';
    }

    /**
     * Format item for swatch display
     * Swatch attributes include color value for display
     */
    public function formatItemForDisplay(array $item): array
    {
        return [
            'id' => $item['id'] ?? '',
            'displayValue' => $item['displayValue'] ?? '',
            'value' => $item['value'] ?? '',
            // Value is the hex color for swatch types
        ];
    }

    /**
     * Create SwatchAttribute from array
     */
    public static function fromArray(array $data): self
    {
        $attribute = new self();
        $attribute->id = $data['id'] ?? '';
        $attribute->name = $data['name'] ?? '';
        $attribute->type = 'swatch';
        $attribute->items = $data['items'] ?? [];
        return $attribute;
    }

    /**
     * Check if a color value is valid hex
     */
    public function isValidHexColor(string $color): bool
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1;
    }
}
