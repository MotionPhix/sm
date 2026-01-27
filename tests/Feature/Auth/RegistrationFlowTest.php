<?php

use App\Models\Role;
use App\Models\School;
use App\Models\SchoolInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create necessary roles
    Role::create(['name' => 'admin', 'label' => 'Administrator']);
    Role::create(['name' => 'teacher', 'label' => 'Teacher']);
});

describe('Admin Registration', function () {
    it('shows the admin registration page to guests', function () {
        $response = $this->get(route('admin.register.create'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('auth/AdminRegister'));
    });

    it('redirects authenticated users away from registration', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.register.create'));

        $response->assertRedirect();
    });

    it('registers a new admin user', function () {
        $response = $this->post(route('admin.register.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('onboarding.school-setup.create'));

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertAuthenticated();
    });

    it('validates required fields during registration', function () {
        $response = $this->post(route('admin.register.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    });

    it('validates unique email during registration', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('admin.register.store'), [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    });
});

describe('School Setup (Onboarding)', function () {
    it('shows the school setup page to authenticated users without a school', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('onboarding.school-setup.create'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('onboarding/CreateSchool'));
    });

    it('requires authentication for school setup', function () {
        $response = $this->get(route('onboarding.school-setup.create'));

        $response->assertRedirect(route('login'));
    });

    it('creates a new school and assigns admin role', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding.school-setup.store'), [
            'name' => 'Test Primary School',
            'type' => 'public_primary',
            'district' => 'Lilongwe',
            'country' => 'Malawi',
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('schools', [
            'name' => 'Test Primary School',
            'type' => 'public_primary',
        ]);

        // User should be attached to the school with admin role
        $school = School::where('name', 'Test Primary School')->first();
        expect($user->schools)->toHaveCount(1);
        expect($user->fresh()->active_school_id)->toBe($school->id);

        // Academic year should be created
        $this->assertDatabaseHas('academic_years', [
            'school_id' => $school->id,
            'is_current' => true,
        ]);
    });

    it('validates required fields during school setup', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('onboarding.school-setup.store'), []);

        $response->assertSessionHasErrors(['name', 'type']);
    });
});

describe('Invitation Acceptance', function () {
    it('shows the invitation acceptance page', function () {
        $school = School::factory()->create();
        $role = Role::where('name', 'teacher')->first();

        $invitation = SchoolInvitation::create([
            'school_id' => $school->id,
            'email' => 'invited@example.com',
            'name' => 'Invited User',
            'role_id' => $role->id,
            'token' => 'test-token-123',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->get(route('invitations.accept', 'test-token-123'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('auth/AcceptInvitation')
            ->has('email')
            ->has('school')
            ->has('role')
            ->where('userExists', false)
        );
    });

    it('returns 404 for invalid invitation token', function () {
        $response = $this->get(route('invitations.accept', 'invalid-token'));

        $response->assertNotFound();
    });

    it('returns 410 for expired invitation', function () {
        $school = School::factory()->create();
        $role = Role::where('name', 'teacher')->first();

        SchoolInvitation::create([
            'school_id' => $school->id,
            'email' => 'invited@example.com',
            'name' => 'Invited User',
            'role_id' => $role->id,
            'token' => 'expired-token',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->get(route('invitations.accept', 'expired-token'));

        $response->assertStatus(410);
    });

    it('creates a new user when accepting invitation', function () {
        $school = School::factory()->create();
        $role = Role::where('name', 'teacher')->first();

        SchoolInvitation::create([
            'school_id' => $school->id,
            'email' => 'newuser@example.com',
            'name' => 'New Teacher',
            'role_id' => $role->id,
            'token' => 'new-user-token',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('invitations.store', 'new-user-token'), [
            'name' => 'New Teacher',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard.redirect'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New Teacher',
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        expect($user->schools)->toHaveCount(1);
        expect($user->schools->first()->id)->toBe($school->id);

        $this->assertAuthenticated();
    });

    it('adds existing user to school when accepting invitation', function () {
        $school = School::factory()->create();
        $role = Role::where('name', 'teacher')->first();
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        SchoolInvitation::create([
            'school_id' => $school->id,
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'role_id' => $role->id,
            'token' => 'existing-user-token',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('invitations.store', 'existing-user-token'), []);

        $response->assertRedirect(route('dashboard.redirect'));

        $existingUser->refresh();
        expect($existingUser->schools)->toHaveCount(1);
        expect($existingUser->schools->first()->id)->toBe($school->id);
        expect($existingUser->active_school_id)->toBe($school->id);

        $this->assertAuthenticated();
    });

    it('marks invitation as accepted', function () {
        $school = School::factory()->create();
        $role = Role::where('name', 'teacher')->first();

        $invitation = SchoolInvitation::create([
            'school_id' => $school->id,
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role_id' => $role->id,
            'token' => 'mark-accepted-token',
            'expires_at' => now()->addDays(7),
        ]);

        $this->post(route('invitations.store', 'mark-accepted-token'), [
            'name' => 'Test User',
            'password' => 'password123',
        ]);

        $invitation->refresh();
        expect($invitation->accepted_at)->not->toBeNull();
    });
});

describe('School Selection', function () {
    it('shows school selection for users with multiple schools', function () {
        $user = User::factory()->create();
        $school1 = School::factory()->create();
        $school2 = School::factory()->create();
        $role = Role::where('name', 'admin')->first();

        $user->schools()->attach($school1->id, ['role_id' => $role->id]);
        $user->schools()->attach($school2->id, ['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('schools.select.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('schools/Select')
            ->has('schools', 2)
        );
    });

    it('allows user to select active school', function () {
        $user = User::factory()->create();
        $school1 = School::factory()->create();
        $school2 = School::factory()->create();
        $role = Role::where('name', 'admin')->first();

        $user->schools()->attach($school1->id, ['role_id' => $role->id]);
        $user->schools()->attach($school2->id, ['role_id' => $role->id]);

        $response = $this->actingAs($user)->post(route('schools.select.store'), [
            'school_id' => $school2->id,
        ]);

        $response->assertRedirect();

        $user->refresh();
        expect($user->active_school_id)->toBe($school2->id);
    });

    it('prevents selecting a school user is not part of', function () {
        $user = User::factory()->create();
        $school1 = School::factory()->create();
        $school2 = School::factory()->create();
        $role = Role::where('name', 'admin')->first();

        $user->schools()->attach($school1->id, ['role_id' => $role->id]);
        // User is NOT attached to school2

        $response = $this->actingAs($user)->post(route('schools.select.store'), [
            'school_id' => $school2->id,
        ]);

        $response->assertForbidden();
    });
});
