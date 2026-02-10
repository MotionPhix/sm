<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'invoice_id' => Invoice::factory(),
            'student_id' => Student::factory(),
            'amount' => fake()->randomFloat(2, 1000, 20000),
            'payment_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'mobile_money', 'cheque', 'card', 'other']),
            'reference_number' => fake()->optional(0.7)->bothify('PAY-####-????'),
            'notes' => fake()->optional(0.3)->sentence(),
            'recorded_by' => User::factory(),
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }

    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'school_id' => $invoice->school_id,
        ]);
    }

    public function forStudent(Student $student): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $student->id,
            'school_id' => $student->school_id,
        ]);
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash',
            'reference_number' => null,
        ]);
    }

    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'bank_transfer',
            'reference_number' => fake()->bothify('TRX-##########'),
        ]);
    }

    public function mobileMoney(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'mobile_money',
            'reference_number' => fake()->numerify('MM##########'),
        ]);
    }

    public function cheque(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cheque',
            'reference_number' => fake()->bothify('CHQ-######'),
        ]);
    }
}
