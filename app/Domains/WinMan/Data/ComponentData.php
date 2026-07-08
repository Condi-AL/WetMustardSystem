<?php

namespace App\Domains\WinMan\Data;

/**
 * Read-only view of a live WinMan Work In Progress component line (scope §11.3).
 *
 * Represents the live BOM explosion at query time. Classification and issue
 * rules are captured verbatim for later review; they must NOT be interpreted
 * using Mint-specific assumptions.
 */
final readonly class ComponentData
{
    public function __construct(
        public int $winmanWorkInProgress,
        public string $itemType,
        public int $winmanComponentProduct,
        public string $winmanComponentProductId,
        public string $componentDescription,
        public ?int $classification,
        public float $quantity,
        public float $quantityIssued,
        public float $quantityOutstanding,
    ) {
    }

    public static function fromRow(object $row): self
    {
        return new self(
            winmanWorkInProgress: (int) $row->WorkInProgress,
            itemType: (string) $row->ItemType,
            winmanComponentProduct: (int) $row->Product,
            winmanComponentProductId: (string) $row->ProductId,
            componentDescription: (string) $row->ProductDescription,
            classification: $row->Classification !== null ? (int) $row->Classification : null,
            quantity: (float) $row->Quantity,
            quantityIssued: (float) $row->QuantityIssued,
            quantityOutstanding: (float) $row->QuantityOutstanding,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'winman_work_in_progress' => $this->winmanWorkInProgress,
            'item_type' => $this->itemType,
            'winman_component_product' => $this->winmanComponentProduct,
            'winman_component_product_id' => $this->winmanComponentProductId,
            'component_description' => $this->componentDescription,
            'classification' => $this->classification,
            'quantity' => $this->quantity,
            'quantity_issued' => $this->quantityIssued,
            'quantity_outstanding' => $this->quantityOutstanding,
        ];
    }
}
