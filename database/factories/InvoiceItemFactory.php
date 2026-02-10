<?php

namespace Database\Factories;

use App\Models\FeeItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 500, 10000);
        $quantity = fake()->numberBetween(1, 5);
        $amount = $unitPrice * $quantity;

        return [
            'invoice_id' => Invoice::factory(),
            'fee_item_id' => FeeItem::factory(),
            'description' => fake()->sentence(3),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => $amount,
        ];
    }

    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => $invoice->id,
        ]);
    }

    public function forFeeItem(FeeItem $feeItem): static
    {
        return $this->state(fn (array $attributes) => [
            'fee_item_id' => $feeItem->id,
            'description' => $feeItem->name,
        ]);
    }

    public function tuitionFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Tuition Fee',
            'quantity' => 1,
            'unit_price' => fake()->randomFloat(2, 15000, 50000),
        ]);
    }

    public function examFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Exam Fee',
            'quantity' => 1,
            'unit_price' => fake()->randomFloat(2, 2000, 5000),
        ]);
    }
}
