<?php

namespace Tests\Feature\Onboarding;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class SchoolSetupFlowTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_access_school_setup_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/onboarding/school-setup');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('onboarding/CreateSchool'));
    }

    public function test_can_fetch_school_types(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/onboarding/school-types');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['*' => ['value', 'label']]]);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_can_fetch_countries(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/onboarding/countries');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['*' => ['value', 'label']]]);
    }

    public function test_can_fetch_regions_for_country(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/onboarding/regions?country=Malawi');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['*' => ['value', 'label']]]);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_can_fetch_districts_for_region(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/onboarding/districts?country=Malawi&region=Northern');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['*' => ['value', 'label']]]);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_user_can_create_school(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/onboarding/school-setup', [
                'name' => 'Test School',
                'type' => 'public_primary',
                'country' => 'Malawi',
                'region' => 'Northern',
                'district' => 'Chitipa',
                'email' => 'school@example.com',
                'phone' => '0123456789',
            ]);

        $response->assertRedirect('/onboarding/classes');

        $this->assertDatabaseHas('schools', [
            'name' => 'Test School',
            'type' => 'public_primary',
            'district' => 'Chitipa',
            'country' => 'Malawi',
        ]);

        // Verify user is attached to school
        $school = School::where('name', 'Test School')->first();
        $this->assertTrue($this->user->schools()->where('school_id', $school->id)->exists());
    }

    public function test_school_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/onboarding/school-setup', [
                'name' => '',
                'type' => '',
                'country' => '',
                'region' => '',
                'district' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'type', 'country', 'region', 'district']);
    }

    public function test_user_can_add_classes(): void
    {
        $school = School::factory()->create();
        $this->user->schools()->attach($school, ['role_id' => 1]);
        $this->user->update(['active_school_id' => $school->id]);

        $response = $this->actingAs($this->user)
            ->post('/onboarding/classes', [
                'classes' => [
                    ['name' => 'Grade 1', 'order' => 1],
                    ['name' => 'Grade 2', 'order' => 2],
                ],
            ]);

        $response->assertRedirect('/onboarding/streams');

        $this->assertDatabaseHas('school_classes', [
            'school_id' => $school->id,
            'name' => 'Grade 1',
            'order' => 1,
        ]);

        $this->assertDatabaseHas('school_classes', [
            'school_id' => $school->id,
            'name' => 'Grade 2',
            'order' => 2,
        ]);
    }

    public function test_user_can_add_streams(): void
    {
        $school = School::factory()->create();
        $this->user->schools()->attach($school, ['role_id' => 1]);
        $this->user->update(['active_school_id' => $school->id]);

        $response = $this->actingAs($this->user)
            ->post('/onboarding/streams', [
                'streams' => [
                    ['name' => 'Stream A'],
                    ['name' => 'Stream B'],
                ],
            ]);

        $response->assertRedirect('/onboarding/subjects');

        $this->assertDatabaseHas('streams', [
            'school_id' => $school->id,
            'name' => 'Stream A',
        ]);
    }

    public function test_user_can_add_subjects(): void
    {
        $school = School::factory()->create();
        $this->user->schools()->attach($school, ['role_id' => 1]);
        $this->user->update(['active_school_id' => $school->id]);

        $response = $this->actingAs($this->user)
            ->post('/onboarding/subjects', [
                'subjects' => [
                    ['name' => 'Mathematics', 'code' => 'MTH'],
                    ['name' => 'English', 'code' => 'ENG'],
                ],
            ]);

        $response->assertRedirect('/admin/dashboard');

        $this->assertDatabaseHas('subjects', [
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MTH',
        ]);
    }

    public function test_complete_onboarding_flow(): void
    {
        // Step 1: Create school
        $this->actingAs($this->user)
            ->post('/onboarding/school-setup', [
                'name' => 'Complete Flow School',
                'type' => 'public_primary',
                'country' => 'Malawi',
                'region' => 'Northern',
                'district' => 'Chitipa',
            ]);

        $school = School::where('name', 'Complete Flow School')->first();
        $this->assertNotNull($school);

        // Verify user is attached
        $this->assertTrue($this->user->schools()->where('school_id', $school->id)->exists());

        // Step 2: Add classes
        $this->actingAs($this->user)
            ->post('/onboarding/classes', [
                'classes' => [
                    ['name' => 'Grade 1', 'order' => 1],
                ],
            ]);

        $this->assertDatabaseHas('school_classes', [
            'school_id' => $school->id,
            'name' => 'Grade 1',
        ]);

        // Step 3: Add streams
        $this->actingAs($this->user)
            ->post('/onboarding/streams', [
                'streams' => [
                    ['name' => 'Stream A'],
                ],
            ]);

        $this->assertDatabaseHas('streams', [
            'school_id' => $school->id,
            'name' => 'Stream A',
        ]);

        // Step 4: Add subjects
        $this->actingAs($this->user)
            ->post('/onboarding/subjects', [
                'subjects' => [
                    ['name' => 'Mathematics', 'code' => 'MTH'],
                ],
            ]);

        $this->assertDatabaseHas('subjects', [
            'school_id' => $school->id,
            'name' => 'Mathematics',
        ]);

        // Verify onboarding is complete
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        // Should redirect to dashboard since onboarding is now complete
        $response->assertStatus(200);
    }
}
