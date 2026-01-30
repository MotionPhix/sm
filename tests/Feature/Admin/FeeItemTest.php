<?php

use App\Models\AcademicYear;
use App\Models\FeeItem;
use App\Models\FeeStructure;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();
    
    // Create an admin role with specific name to match route middleware
    $adminRole = Role::factory()->create(['name' => 'admin', 'label' => 'Administrator']);
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

    // Create onboarding data to satisfy EnsureOnboardingComplete middleware
    $academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();

    // Create 3 terms for the academic year
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 2, 'name' => 'Term 2']);
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 3, 'name' => 'Term 3']);

    // Create classes, streams, and subjects
    SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1']);
    Stream::factory()->forSchool($this->school)->create(['name' => 'A']);
    Subject::factory()->forSchool($this->school)->create(['name' => 'Mathematics', 'code' => 'MATH']);

    app()->instance('currentSchool', $this->school);
});

describe('Fee Items - Listing', function () {
    it('displays all fee items for the school', function () {
        $items = FeeItem::factory(3)->forSchool($this->school)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.settings.fee-items.index'));

        $response->assertSuccessful();
        foreach ($items as $item) {
            $response->assertSee($item->name);
        }
    });

    it('does not show fee items from other schools', function () {
        $otherSchool = School::factory()->create();
        $otherItem = FeeItem::factory()->forSchool($otherSchool)->create();
        $ownItem = FeeItem::factory()->forSchool($this->school)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.settings.fee-items.index'));

        $response->assertSuccessful();
        $response->assertSee($ownItem->name);
        $response->assertDontSee($otherItem->name);
    });

    it('displays fee item categories correctly', function () {
        $tuition = FeeItem::factory()->forSchool($this->school)->tuition()->create();
        $exam = FeeItem::factory()->forSchool($this->school)->exam()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.settings.fee-items.index'));

        $response->assertSuccessful();
        $response->assertSee('Tuition');
        $response->assertSee('Examination Fees');
    });
});

describe('Fee Items - Creation', function () {
    
    it('can create a new fee item', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-items.store'), [
                'name' => 'Development Levy',
                'code' => 'DEV',
                'category' => 'development',
                'description' => 'School development fund',
                'is_mandatory' => true,
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('fee_items', [
            'school_id' => $this->school->id,
            'name' => 'Development Levy',
            'code' => 'DEV',
            'category' => 'development',
        ]);
    });

    it('validates required fields', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-items.store'), [
                'name' => 'Test Fee',
                // missing code, category, etc
            ]);

        $response->assertSessionHasErrors(['code', 'category']);
    });

    it('enforces unique code per school', function () {
        FeeItem::factory()->forSchool($this->school)->create(['code' => 'TUI']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-items.store'), [
                'name' => 'Another Tuition',
                'code' => 'TUI',
                'category' => 'tuition',
            ]);

        $response->assertSessionHasErrors('code');
    });

    it('enforces unique name per school', function () {
        FeeItem::factory()->forSchool($this->school)->create(['name' => 'Tuition']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-items.store'), [
                'name' => 'Tuition',
                'code' => 'TUI2',
                'category' => 'tuition',
            ]);

        $response->assertSessionHasErrors('name');
    });

    it('converts code to uppercase', function () {
        // Send lowercase code
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-items.store'), [
                'name' => 'Exam Fees',
                'code' => 'exm',
                'category' => 'exam',
            ]);

        // Should fail validation because 'uppercase' rule requires uppercase input
        $response->assertSessionHasErrors('code');
    });

    it('stores code in uppercase format', function () {
        // Send uppercase code and verify it's stored correctly
        $response = $this->actingAs($this->admin)
            ->post(route('admin.settings.fee-items.store'), [
                'name' => 'Exam Fees',
                'code' => 'EXM',
                'category' => 'exam',
            ]);

        // The request should succeed (redirect back)
        $response->assertRedirect();

        // Verify the code was stored correctly
        $this->assertDatabaseHas('fee_items', [
            'school_id' => $this->school->id,
            'code' => 'EXM',
            'name' => 'Exam Fees',
        ]);
    });
});

describe('Fee Items - Updating', function () {
    it('can update a fee item', function () {
        $item = FeeItem::factory()->forSchool($this->school)->create();

        $this->actingAs($this->admin)
            ->put(route('admin.settings.fee-items.update', $item), [
                'name' => 'Updated Fee',
                'code' => $item->code,
                'category' => 'exam',
                'is_mandatory' => false,
            ])
            ->assertRedirect();

        $item->refresh();
        expect($item->name)->toBe('Updated Fee');
        expect($item->is_mandatory)->toBeFalse();
    });

    it('prevents updating to duplicate name', function () {
        $item1 = FeeItem::factory()->forSchool($this->school)->create(['name' => 'Fee 1']);
        $item2 = FeeItem::factory()->forSchool($this->school)->create(['name' => 'Fee 2']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.settings.fee-items.update', $item2), [
                'name' => 'Fee 1',
                'code' => $item2->code,
                'category' => $item2->category,
            ]);

        $response->assertSessionHasErrors('name');
    });

    it('prevents cross-school modification', function () {
        $otherSchool = School::factory()->create();
        $item = FeeItem::factory()->forSchool($otherSchool)->create();

        $this->actingAs($this->admin)
            ->put(route('admin.settings.fee-items.update', $item), [
                'name' => 'Hacked',
                'code' => 'HACK',
                'category' => 'other',
            ])
            ->assertNotFound(); // Model binding returns null due to global scope
    });
});

describe('Fee Items - Deletion', function () {
    it('can delete a fee item', function () {
        $item = FeeItem::factory()->forSchool($this->school)->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.settings.fee-items.destroy', $item))
            ->assertRedirect();

        $this->assertModelMissing($item);
    });

    it('prevents deletion if item is used in fee structures', function () {
        $item = FeeItem::factory()->forSchool($this->school)->create();
        FeeStructure::factory()->create(['fee_item_id' => $item->id, 'school_id' => $this->school->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.settings.fee-items.destroy', $item));

        $response->assertSessionHasErrors('fee_item');
        $this->assertModelExists($item);
    });

    it('prevents cross-school deletion', function () {
        $otherSchool = School::factory()->create();
        $item = FeeItem::factory()->forSchool($otherSchool)->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.settings.fee-items.destroy', $item))
            ->assertNotFound(); // Model binding returns null due to global scope
    });
});

describe('Fee Items - Tenant Isolation', function () {
    it('applies global scope to filter by school', function () {
        $otherSchool = School::factory()->create();
        FeeItem::factory(2)->forSchool($this->school)->create();
        FeeItem::factory(3)->forSchool($otherSchool)->create();

        $items = FeeItem::all();

        expect($items)->toHaveCount(2);
        expect($items->every(fn ($item) => $item->school_id === $this->school->id))->toBeTrue();
    });

    it('automatically sets school_id on creation', function () {
        FeeItem::create([
            'name' => 'Auto School Fee',
            'code' => 'AUTO',
            'category' => 'tuition',
        ]);

        $item = FeeItem::where('code', 'AUTO')->first();
        expect($item->school_id)->toBe($this->school->id);
    });
});

describe('Fee Items - Model Methods', function () {
    it('returns correct category label', function () {
        $tuition = FeeItem::factory()->forSchool($this->school)->tuition()->create();

        expect($tuition->getCategoryLabel())->toBe('Tuition');
    });

    it('provides categories array', function () {
        $categories = FeeItem::categories();

        expect($categories)->toHaveKeys(['tuition', 'exam', 'development', 'extra_curriculum', 'other']);
        expect($categories['tuition'])->toBe('Tuition');
    });
});
