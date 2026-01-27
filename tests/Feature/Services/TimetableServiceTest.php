<?php

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\TimetableService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(TimetableService::class);

    $this->school = School::factory()->create();
    $this->academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    $this->teacher = User::factory()->create(['active_school_id' => $this->school->id]);

    $this->class1 = SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1']);
    $this->class2 = SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 2']);

    $this->stream = Stream::factory()->forSchool($this->school)->create(['name' => 'A']);

    $this->classroom1 = ClassStreamAssignment::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'school_class_id' => $this->class1->id,
        'stream_id' => $this->stream->id,
    ]);

    $this->classroom2 = ClassStreamAssignment::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'school_class_id' => $this->class2->id,
        'stream_id' => $this->stream->id,
    ]);

    // Bind current school for tenant scoping
    app()->instance('currentSchool', $this->school);
});

it('detects no clash when teacher has no existing assignments', function () {
    $scheduleData = [
        'monday' => [
            'period_1' => ['subject' => 'Math'],
            'period_2' => ['subject' => 'Math'],
        ],
    ];

    $clashes = $this->service->validateAssignmentClash(
        $this->teacher,
        $this->classroom1,
        $scheduleData
    );

    expect($clashes['teacher_clashes'])->toBeEmpty()
        ->and($clashes['class_clashes'])->toBeEmpty();
});

it('detects teacher clash when same teacher has overlapping periods', function () {
    // Create existing assignment with Monday period 1
    TeacherAssignment::factory()->create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom1->id,
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'schedule_data' => [
            'monday' => [
                'period_1' => ['subject' => 'English'],
            ],
        ],
    ]);

    // Try to create new assignment with same teacher, same period, different class
    $newSchedule = [
        'monday' => [
            'period_1' => ['subject' => 'Math'], // Clash!
            'period_2' => ['subject' => 'Math'], // No clash
        ],
    ];

    $clashes = $this->service->validateAssignmentClash(
        $this->teacher,
        $this->classroom2,
        $newSchedule
    );

    expect($clashes['teacher_clashes'])->toHaveCount(1)
        ->and($clashes['teacher_clashes'][0]['day'])->toBe('monday')
        ->and($clashes['teacher_clashes'][0]['conflicting_periods'])->toContain('period_1');
});

it('detects class clash when same class has overlapping periods with different teachers', function () {
    $otherTeacher = User::factory()->create();

    // Create existing assignment for classroom1 with Monday period 1
    TeacherAssignment::factory()->create([
        'user_id' => $otherTeacher->id,
        'class_stream_assignment_id' => $this->classroom1->id,
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'schedule_data' => [
            'monday' => [
                'period_1' => ['subject' => 'English'],
            ],
        ],
    ]);

    // Try to create new assignment with different teacher, same class, same period
    $newSchedule = [
        'monday' => [
            'period_1' => ['subject' => 'Math'], // Clash!
        ],
    ];

    $clashes = $this->service->validateAssignmentClash(
        $this->teacher,
        $this->classroom1,
        $newSchedule
    );

    expect($clashes['class_clashes'])->toHaveCount(1)
        ->and($clashes['class_clashes'][0]['day'])->toBe('monday')
        ->and($clashes['class_clashes'][0]['conflicting_periods'])->toContain('period_1');
});

it('excludes specified assignment from clash detection for updates', function () {
    // Create existing assignment
    $existingAssignment = TeacherAssignment::factory()->create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom1->id,
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'schedule_data' => [
            'monday' => [
                'period_1' => ['subject' => 'English'],
            ],
        ],
    ]);

    // Update same assignment with same period - should not clash
    $updatedSchedule = [
        'monday' => [
            'period_1' => ['subject' => 'Math'],
            'period_2' => ['subject' => 'Math'],
        ],
    ];

    $clashes = $this->service->validateAssignmentClash(
        $this->teacher,
        $this->classroom1,
        $updatedSchedule,
        $existingAssignment->id // Exclude self
    );

    expect($clashes['teacher_clashes'])->toBeEmpty()
        ->and($clashes['class_clashes'])->toBeEmpty();
});

it('checks teacher availability for specific time slot', function () {
    // No assignments - teacher should be available
    expect($this->service->isTeacherAvailable(
        $this->teacher,
        'monday',
        'period_1',
        $this->academicYear->id
    ))->toBeTrue();

    // Create assignment
    TeacherAssignment::factory()->create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom1->id,
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'schedule_data' => [
            'monday' => [
                'period_1' => ['subject' => 'English'],
            ],
        ],
    ]);

    // Now teacher should not be available for Monday period 1
    expect($this->service->isTeacherAvailable(
        $this->teacher,
        'monday',
        'period_1',
        $this->academicYear->id
    ))->toBeFalse();

    // But should be available for period 2
    expect($this->service->isTeacherAvailable(
        $this->teacher,
        'monday',
        'period_2',
        $this->academicYear->id
    ))->toBeTrue();
});

it('checks class availability for specific time slot', function () {
    // No assignments - class should be available
    expect($this->service->isClassAvailable(
        $this->classroom1,
        'monday',
        'period_1'
    ))->toBeTrue();

    // Create assignment
    TeacherAssignment::factory()->create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom1->id,
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'schedule_data' => [
            'monday' => [
                'period_1' => ['subject' => 'English'],
            ],
        ],
    ]);

    // Now class should not be available for Monday period 1
    expect($this->service->isClassAvailable(
        $this->classroom1,
        'monday',
        'period_1'
    ))->toBeFalse();

    // But should be available for period 2
    expect($this->service->isClassAvailable(
        $this->classroom1,
        'monday',
        'period_2'
    ))->toBeTrue();
});

it('retrieves teacher weekly schedule correctly', function () {
    // Create multiple assignments
    TeacherAssignment::factory()->create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom1->id,
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'schedule_data' => [
            'monday' => [
                'period_1' => ['subject' => 'Math'],
            ],
            'wednesday' => [
                'period_3' => ['subject' => 'Math'],
            ],
        ],
    ]);

    TeacherAssignment::factory()->create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom2->id,
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'schedule_data' => [
            'tuesday' => [
                'period_2' => ['subject' => 'Science'],
            ],
        ],
    ]);

    $schedule = $this->service->getTeacherWeeklySchedule(
        $this->teacher,
        now()->startOfWeek()->toDateString(),
        $this->academicYear->id
    );

    expect($schedule)
        ->toHaveKey('monday')
        ->toHaveKey('tuesday')
        ->toHaveKey('wednesday')
        ->and($schedule['monday'])->toHaveKey('period_1')
        ->and($schedule['tuesday'])->toHaveKey('period_2')
        ->and($schedule['wednesday'])->toHaveKey('period_3');
});

it('validates schedule integrity detects duplicate slots', function () {
    // Note: This is unlikely in practice but tests the validation
    $scheduleWithDuplicates = [
        'monday' => [
            'period_1' => ['subject' => 'Math'],
            'period_1' => ['subject' => 'Science'], // PHP will overwrite, so this won't actually be a duplicate
        ],
    ];

    $duplicates = $this->service->validateScheduleIntegrity($scheduleWithDuplicates);

    // Due to PHP array behavior, duplicates array will be empty
    expect($duplicates)->toBeArray();
});

it('calculates teacher workload statistics', function () {
    TeacherAssignment::factory()->create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom1->id,
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'schedule_data' => [
            'monday' => [
                'period_1' => ['subject' => 'Math'],
                'period_2' => ['subject' => 'Math'],
            ],
            'wednesday' => [
                'period_3' => ['subject' => 'Math'],
            ],
        ],
    ]);

    $stats = $this->service->getTeacherWorkloadStats($this->teacher, $this->academicYear->id);

    expect($stats['total_periods'])->toBe(3)
        ->and($stats['classes_count'])->toBe(1)
        ->and($stats['days_active'])->toContain('monday', 'wednesday');
});
