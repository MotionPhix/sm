<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\FeeItem;
use App\Models\FeeStructure;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeStructureFactory extends Factory
{
    protected $model = FeeStructure::class;

    public function definition(): array
    {
        $school = School::factory()->create();
        $academicYear = AcademicYear::factory()->forSchool($school)->create();
        $schoolClass = SchoolClass::factory()->forSchool($school)->create();

        return [
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'school_class_id' => $schoolClass->id,
            'term_id' => null,
            'fee_item_id' => FeeItem::factory()->forSchool($school)->create()->id,
            'amount' => $this->faker->numberBetween(5000, 50000),
            'quantity' => 1,
            'notes' => null,
            'is_active' => true,
        ];
    }

    public function withTerm(Term $term): self
    {
        return $this->state(fn (array $attributes) => [
            'term_id' => $term->id,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
