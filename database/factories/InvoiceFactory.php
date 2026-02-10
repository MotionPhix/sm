<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\School;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $totalAmount = fake()->randomFloat(2, 5000, 50000);
        $paidAmount = fake()->boolean(70) ? fake()->randomFloat(2, 0, $totalAmount) : 0;

        // Determine status based on paid amount
        $status = $this->determineStatus($totalAmount, $paidAmount);

        return [
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'term_id' => Term::factory(),
            'invoice_number' => $this->generateInvoiceNumber(),
            'issue_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+2 months'),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'status' => $status,
            'notes' => fake()->optional(0.3)->sentence(),
            'issued_by' => User::factory(),
        ];
    }

    private function determineStatus(float $total, float $paid): string
    {
        if ($paid >= $total) {
            return 'paid';
        }
        if ($paid > 0) {
            return 'partially_paid';
        }

        return fake()->randomElement(['draft', 'issued', 'overdue']);
    }

    private function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $sequence = fake()->unique()->numberBetween(1, 9999);

        return sprintf('INV-%d-%04d', $year, $sequence);
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
        ]);
    }

    public function forStudent(Student $student): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $student->id,
            'school_id' => $student->school_id,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'paid_amount' => 0,
        ]);
    }

    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'issued',
            'paid_amount' => 0,
        ]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(function (array $attributes) {
            $totalAmount = $attributes['total_amount'] ?? 10000;
            $paidAmount = fake()->randomFloat(2, 1, $totalAmount * 0.9);

            return [
                'status' => 'partially_paid',
                'paid_amount' => $paidAmount,
            ];
        });
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_amount' => $attributes['total_amount'],
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => fake()->dateTimeBetween('-2 months', '-1 day'),
            'paid_amount' => 0,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'cancellation_reason' => fake()->sentence(),
            'cancelled_by' => User::factory(),
        ]);
    }
}
