<?php

use App\Models\Role;
use App\Models\School;
use App\Models\SchoolInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->role = Role::factory()->create(['name' => 'teacher', 'label' => 'Teacher']);
});

it('marks a new user as email verified on invitation acceptance', function () {
    $invitation = SchoolInvitation::create([
        'school_id' => $this->school->id,
        'email' => 'newuser@example.com',
        'name' => 'New Teacher',
        'role_id' => $this->role->id,
        'token' => Str::random(64),
        'expires_at' => now()->addDays(7),
    ]);

    $response = $this->post(route('invitations.store', $invitation->token), [
        'name' => 'New Teacher',
        'password' => 'password123',
    ]);

    $user = User::where('email', 'newuser@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->hasVerifiedEmail())->toBeTrue();
});

it('marks an existing unverified user as verified on invitation acceptance', function () {
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'email_verified_at' => null,
    ]);

    $invitation = SchoolInvitation::create([
        'school_id' => $this->school->id,
        'email' => 'existing@example.com',
        'role_id' => $this->role->id,
        'token' => Str::random(64),
        'expires_at' => now()->addDays(7),
    ]);

    $this->post(route('invitations.store', $invitation->token));

    $existingUser->refresh();

    expect($existingUser->hasVerifiedEmail())->toBeTrue();
});

it('attaches the user to the school with the correct role', function () {
    $invitation = SchoolInvitation::create([
        'school_id' => $this->school->id,
        'email' => 'staff@example.com',
        'name' => 'Staff Member',
        'role_id' => $this->role->id,
        'token' => Str::random(64),
        'expires_at' => now()->addDays(7),
    ]);

    $this->post(route('invitations.store', $invitation->token), [
        'name' => 'Staff Member',
        'password' => 'password123',
    ]);

    $user = User::where('email', 'staff@example.com')->first();

    expect($user->schools)->toHaveCount(1);
    expect($user->schools->first()->id)->toBe($this->school->id);
});

it('marks the invitation as accepted', function () {
    $invitation = SchoolInvitation::create([
        'school_id' => $this->school->id,
        'email' => 'invited@example.com',
        'name' => 'Invited User',
        'role_id' => $this->role->id,
        'token' => Str::random(64),
        'expires_at' => now()->addDays(7),
    ]);

    $this->post(route('invitations.store', $invitation->token), [
        'name' => 'Invited User',
        'password' => 'password123',
    ]);

    $invitation->refresh();

    expect($invitation->accepted_at)->not->toBeNull();
});

it('rejects expired invitations', function () {
    $invitation = SchoolInvitation::create([
        'school_id' => $this->school->id,
        'email' => 'expired@example.com',
        'role_id' => $this->role->id,
        'token' => Str::random(64),
        'expires_at' => now()->subDay(),
    ]);

    $response = $this->post(route('invitations.store', $invitation->token), [
        'name' => 'Expired User',
        'password' => 'password123',
    ]);

    $response->assertGone();
});
