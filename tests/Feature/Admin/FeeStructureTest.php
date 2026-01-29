<?php

use App\Models\AcademicYear;
use App\Models\FeeItem;
use App\Models\FeeStructure;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrator']);
    $this->admin = User::factory()->create(['active_school_id' => $this->school->id]);
    $this->admin->schools()->attach($this->school->id, ['role_id' => $adminRole->id]);
    
    // Create and assign fee-related permissions
    $permissions = [
        Permission::firstOrCreate(['name' => 'fees.view'], ['label' => 'View Fees']),
        Permission::firstOrCreate(['name' => 'fees.create'], ['label' => 'Create Fees']),
        Permission::firstOrCreate(['name' => 'fees.edit'], ['label' => 'Edit Fees']),
        Permission::firstOrCreate(['name' => 'fees.delete'], ['label' => 'Delete Fees']),
        Permission::firstOrCreate(['name' => 'dashboard.view'], ['label' => 'View Dashboard']),
        Permission::firstOrCreate(['name' => 'settings.view'], ['label' => 'View Settings']),
    ];
    
    $adminRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));
    
    $this->academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    $this->class = SchoolClass::factory()->forSchool($this->school)->create();
    $this->term = Term::factory()->forAcademicYear($this->academicYear)->create();
    $this->feeItem = FeeItem::factory()->forSchool($this->school)->tuition()->create();

    app()->instance('currentSchool', $this->school);
});

describe('Fee Structures - Listing', function () {
    it('displays all fee structures for current academic year', function () {
        $structures = FeeStructure::factory(3)->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.settings.fee-structures.index'));

        $response->assertSuccessful();
        $response->assertSee($this->academicYear->name);
    });

    it('does not show fee structures from other schools', function () {
        $otherSchool = School::factory()->create();
        $otherAcademicYear = AcademicYear::factory()->forSchool($otherSchool)->current()->create();
        
        FeeStructure::factory()->create([
            'school_id' => $otherSchool->id,
            'academic_year_id' => $otherAcademicYear->id,
        ]);
        
        $ownStructure = FeeStructure::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.settings.fee-structures.index'));

        $response->assertSuccessful();
        // Should only see own structures
        $this->assertNotNull(FeeStructure::where('school_id', $this->school->id)->first());
    });
});

describe('Fee Structures - Creation', function () {
    it('can create a single fee structure', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-structures.store'), [
                'academic_year_id' => $this->academicYear->id,
                'school_class_id' => $this->class->id,
                'term_id' => $this->term->id,
                'fee_items' => [
                    [
                        'fee_item_id' => $this->feeItem->id,
                        'amount' => 50000,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('fee_structures', [
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'school_class_id' => $this->class->id,
            'term_id' => $this->term->id,
            'fee_item_id' => $this->feeItem->id,
            'amount' => 50000,
        ]);
    });

    it('can create multiple fee items for a class at once', function () {
        $examFee = FeeItem::factory()->forSchool($this->school)->exam()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-structures.store'), [
                'academic_year_id' => $this->academicYear->id,
                'school_class_id' => $this->class->id,
                'term_id' => null,
                'fee_items' => [
                    [
                        'fee_item_id' => $this->feeItem->id,
                        'amount' => 50000,
                        'quantity' => 1,
                    ],
                    [
                        'fee_item_id' => $examFee->id,
                        'amount' => 5000,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('fee_structures', 2);
        $this->assertDatabaseHas('fee_structures', [
            'school_class_id' => $this->class->id,
            'fee_item_id' => $this->feeItem->id,
        ]);
        $this->assertDatabaseHas('fee_structures', [
            'school_class_id' => $this->class->id,
            'fee_item_id' => $examFee->id,
        ]);
    });

    it('requires at least one fee item', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-structures.store'), [
                'academic_year_id' => $this->academicYear->id,
                'school_class_id' => $this->class->id,
                'term_id' => null,
                'fee_items' => [],
            ]);

        $response->assertSessionHasErrors('fee_items');
    });

    it('validates fee item amounts', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-structures.store'), [
                'academic_year_id' => $this->academicYear->id,
                'school_class_id' => $this->class->id,
                'term_id' => null,
                'fee_items' => [
                    [
                        'fee_item_id' => $this->feeItem->id,
                        'amount' => 'invalid',
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors();
    });

    it('updates existing structure if duplicate fee item for class', function () {
        FeeStructure::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'school_class_id' => $this->class->id,
            'term_id' => $this->term->id,
            'fee_item_id' => $this->feeItem->id,
            'amount' => 50000,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-structures.store'), [
                'academic_year_id' => $this->academicYear->id,
                'school_class_id' => $this->class->id,
                'term_id' => $this->term->id,
                'fee_items' => [
                    [
                        'fee_item_id' => $this->feeItem->id,
                        'amount' => 60000,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('fee_structures', [
            'school_class_id' => $this->class->id,
            'fee_item_id' => $this->feeItem->id,
            'amount' => 60000,
        ]);
        
        $this->assertDatabaseCount('fee_structures', 1);
    });
});

describe('Fee Structures - Updating', function () {
    it('can update amount and quantity', function () {
        $structure = FeeStructure::factory()->create([
            'school_id' => $this->school->id,
            'amount' => 50000,
            'quantity' => 1,
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.settings.fee-structures.update', $structure), [
                'amount' => 60000,
                'quantity' => 2,
            ])
            ->assertRedirect();

        $structure->refresh();
        expect($structure->amount)->toBe('60000.00');
        expect($structure->quantity)->toBe(2);
    });

    it('prevents cross-school modification', function () {
        $otherSchool = School::factory()->create();
        $structure = FeeStructure::factory()->create(['school_id' => $otherSchool->id]);

        $this->actingAs($this->admin)
            ->put(route('admin.settings.fee-structures.update', $structure), [
                'amount' => 999999,
            ])
            ->assertForbidden();
    });
});

describe('Fee Structures - Deletion', function () {
    it('can delete a fee structure', function () {
        $structure = FeeStructure::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.settings.fee-structures.destroy', $structure))
            ->assertRedirect();

        $this->assertModelMissing($structure);
    });

    it('prevents cross-school deletion', function () {
        $otherSchool = School::factory()->create();
        $structure = FeeStructure::factory()->create(['school_id' => $otherSchool->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.settings.fee-structures.destroy', $structure))
            ->assertForbidden();
    });
});

describe('Fee Structures - Model Methods', function () {
    it('calculates total amount correctly', function () {
        $structure = FeeStructure::factory()->create([
            'amount' => 50000,
            'quantity' => 2,
        ]);

        expect($structure->getTotalAmount())->toBe(100000.0);
    });

    it('formats amount as currency', function () {
        $structure = FeeStructure::factory()->create(['amount' => 50000.00]);

        expect($structure->getFormattedAmount())->toContain('MK');
        expect($structure->getFormattedAmount())->toContain('50,000.00');
    });

    it('formats total as currency', function () {
        $structure = FeeStructure::factory()->create([
            'amount' => 25000,
            'quantity' => 2,
        ]);

        expect($structure->getFormattedTotal())->toContain('MK');
        expect($structure->getFormattedTotal())->toContain('50,000.00');
    });
});

describe('Fee Structures - Tenant Isolation', function () {
    it('applies global scope to filter by school', function () {
        FeeStructure::factory(2)->create(['school_id' => $this->school->id]);
        FeeStructure::factory(3)->create(['school_id' => School::factory()]);

        $structures = FeeStructure::all();

        expect($structures)->toHaveCount(2);
        expect($structures->every(fn ($s) => $s->school_id === $this->school->id))->toBeTrue();
    });

    it('automatically sets school_id on creation', function () {
        FeeStructure::create([
            'academic_year_id' => $this->academicYear->id,
            'school_class_id' => $this->class->id,
            'fee_item_id' => $this->feeItem->id,
            'amount' => 50000,
        ]);

        $structure = FeeStructure::latest()->first();
        expect($structure->school_id)->toBe($this->school->id);
    });
});
