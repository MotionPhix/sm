<?php

use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $this->school = School::factory()->create();

    $adminRole = Role::factory()->create(['name' => 'admin', 'label' => 'Administrator']);
    $this->admin = User::factory()->create(['active_school_id' => $this->school->id]);
    $this->admin->schools()->attach($this->school->id, ['role_id' => $adminRole->id]);

    $permissions = [
        Permission::firstOrCreate(['name' => 'dashboard.view'], ['label' => 'View Dashboard']),
        Permission::firstOrCreate(['name' => 'settings.view'], ['label' => 'View Settings']),
    ];

    $adminRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    $academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 2, 'name' => 'Term 2']);
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 3, 'name' => 'Term 3']);
    SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1']);
    Stream::factory()->forSchool($this->school)->create(['name' => 'A']);
    Subject::factory()->forSchool($this->school)->create(['name' => 'Mathematics', 'code' => 'MATH']);

    app()->instance('currentSchool', $this->school);
});

it('displays the school profile page', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.settings.school-profile.show'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/settings/school-profile/Show')
        ->has('school')
        ->where('school.name', $this->school->name)
    );
});

it('displays the school profile edit modal', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.settings.school-profile.edit'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/settings/school-profile/Edit')
        ->has('school')
        ->has('schoolTypes')
    );
});

it('updates the school profile without a logo', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.settings.school-profile.update'), [
        'name' => 'Updated School Name',
        'code' => $this->school->code,
        'email' => 'updated@school.com',
        'phone' => '+265 111 222 333',
        'type' => 'public_secondary',
        'district' => 'Blantyre',
        'country' => 'Malawi',
    ]);

    $response->assertRedirect(route('admin.settings.school-profile.show'));

    $this->school->refresh();
    expect($this->school->name)->toBe('Updated School Name');
    expect($this->school->email)->toBe('updated@school.com');
});

it('uploads a logo when updating the school profile', function () {
    $logo = UploadedFile::fake()->image('logo.png', 200, 200);

    $response = $this->actingAs($this->admin)->put(route('admin.settings.school-profile.update'), [
        'name' => $this->school->name,
        'code' => $this->school->code,
        'email' => $this->school->email,
        'phone' => $this->school->phone,
        'type' => 'public_primary',
        'district' => $this->school->district,
        'country' => $this->school->country,
        'logo' => $logo,
    ]);

    $response->assertRedirect(route('admin.settings.school-profile.show'));

    $this->school->refresh();
    expect($this->school->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($this->school->logo_path);
});

it('replaces the old logo when uploading a new one', function () {
    // Upload first logo
    $firstLogo = UploadedFile::fake()->image('first.png', 200, 200);
    $this->actingAs($this->admin)->put(route('admin.settings.school-profile.update'), [
        'name' => $this->school->name,
        'code' => $this->school->code,
        'email' => $this->school->email,
        'phone' => $this->school->phone,
        'type' => 'public_primary',
        'district' => $this->school->district,
        'country' => $this->school->country,
        'logo' => $firstLogo,
    ]);

    $this->school->refresh();
    $oldPath = $this->school->logo_path;
    Storage::disk('public')->assertExists($oldPath);

    // Upload second logo
    $secondLogo = UploadedFile::fake()->image('second.png', 200, 200);
    $this->actingAs($this->admin)->put(route('admin.settings.school-profile.update'), [
        'name' => $this->school->name,
        'code' => $this->school->code,
        'email' => $this->school->email,
        'phone' => $this->school->phone,
        'type' => 'public_primary',
        'district' => $this->school->district,
        'country' => $this->school->country,
        'logo' => $secondLogo,
    ]);

    $this->school->refresh();
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($this->school->logo_path);
});

it('rejects invalid logo file types', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->actingAs($this->admin)->put(route('admin.settings.school-profile.update'), [
        'name' => $this->school->name,
        'code' => $this->school->code,
        'email' => $this->school->email,
        'phone' => $this->school->phone,
        'type' => 'public_primary',
        'district' => $this->school->district,
        'country' => $this->school->country,
        'logo' => $file,
    ]);

    $response->assertSessionHasErrors('logo');
});

it('passes logo_url in the show response when logo exists', function () {
    $this->school->update(['logo_path' => 'schools/1/logos/test.png']);

    $response = $this->actingAs($this->admin)->get(route('admin.settings.school-profile.show'));

    $response->assertInertia(fn ($page) => $page
        ->where('school.logo_url', fn ($url) => str_contains($url, 'schools/1/logos/test.png'))
    );
});
