# Gradebook, Assessments & Grading Module — Implementation Plan

## Context

The next chronological milestone per IMPLEMENTATION.md is **"Gradebook, Grading Scales, Reports"**. All dependencies are ready: academic years/terms, classes/streams, subjects, student enrollments, and teacher assignments exist. The `assessment_plans` table and `AssessmentPlan` model already exist but have no controller, factory, or UI. The `grade_scales` and `grade_scale_steps` tables already exist but have no models. Permissions are already seeded (`exams.*`, `grading.*`).

**Goal**: Build a minimum viable gradebook that lets admins configure assessment plans and grading scales, and lets teachers enter marks for their assigned classes/subjects.

---

## Phase 1: Database & Models

### 1a. Migration: Add `subject_id` to `teacher_assignments`
- **File**: New migration `add_subject_id_to_teacher_assignments_table`
- Add `subject_id` (nullable FK to subjects, cascadeOnDelete)
- Add unique constraint: `(user_id, class_stream_assignment_id, subject_id)`
- Reason: Teachers need to be linked to specific subjects for gradebook authorization

### 1b. Migration: Create `grades` table
- **File**: New migration `create_grades_table`
- Schema: `id, school_id (FK), student_id (FK), assessment_plan_id (FK), class_stream_assignment_id (FK), score (decimal 5,2 nullable), remarks (text nullable), entered_by (FK users), entered_at (timestamp), is_locked (boolean default false), timestamps`
- Unique: `(student_id, assessment_plan_id)`
- Indexes: `(school_id, assessment_plan_id)`, `(school_id, class_stream_assignment_id)`

### 1c. New Models
- **`app/Models/Grade.php`** — TenantScoped, fillable, casts, relationships: school, student, assessmentPlan, classroom, enteredBy
- **`app/Models/GradeScale.php`** — TenantScoped, relationships: school, steps (HasMany)
- **`app/Models/GradeScaleStep.php`** — No TenantScope (scoped via parent), relationships: gradeScale

### 1d. Update Existing Models
- **`TeacherAssignment`** — Add `subject_id` to fillable, add `subject()` BelongsTo
- **`Subject`** — Add `teacherAssignments()` HasMany, `assessmentPlans()` HasMany
- **`AssessmentPlan`** — Add `grades()` HasMany
- **`Student`** — Add `grades()` HasMany
- **`ClassStreamAssignment`** — Add `grades()` HasMany

---

## Phase 2: Factories

- **`database/factories/AssessmentPlanFactory.php`** — definition + forSchool, forTerm, forSubject states
- **`database/factories/GradeFactory.php`** — definition + forStudent, forAssessment, locked states
- **`database/factories/GradeScaleFactory.php`** — definition + forSchool state
- **`database/factories/GradeScaleStepFactory.php`** — definition + forGradeScale state
- **Update `TeacherAssignmentFactory`** — Add `subject_id => Subject::factory()` + forSubject state

---

## Phase 3: Service

### `app/Services/GradebookService.php`
Key methods:
- `getStudentsForGrading(ClassStreamAssignment, Subject, Term)` — enrolled students
- `saveGrades(AssessmentPlan, ClassStreamAssignment, array $grades, User)` — updateOrCreate batch
- `computeStudentTermTotal(Student, Subject, Term, GradeScale)` — weighted total + grade resolution
- `computeClassResults(ClassStreamAssignment, Subject, Term, GradeScale)` — all students + ranking
- `resolveGrade(float $percentage, GradeScale)` — map % to letter grade
- `isTeacherAuthorizedForGrading(User, ClassStreamAssignment, Subject)` — check TeacherAssignment
- `lockGrades(AssessmentPlan, ClassStreamAssignment)` — prevent further edits

---

## Phase 4: Form Requests

- **`app/Http/Requests/Admin/StoreAssessmentPlanRequest.php`** — authorize via `exams.create`, validate term_id, subject_id, name, max_score, weight, unique constraint
- **`app/Http/Requests/Admin/UpdateAssessmentPlanRequest.php`** — same with ignore
- **`app/Http/Requests/Admin/StoreGradeScaleRequest.php`** — authorize via `grading.configure`, validate name, steps array
- **`app/Http/Requests/Admin/UpdateGradeScaleRequest.php`** — same with ignore
- **`app/Http/Requests/Teacher/StoreGradeRequest.php`** — authorize via `exams.enter-marks`, validate assessment_plan_id, class_stream_assignment_id, grades array with score <= max_score

---

## Phase 5: Controllers

### Admin Controllers
- **`app/Http/Controllers/Admin/AssessmentPlanController.php`** — index, create, store, edit, update, destroy
  - Renders: `admin/settings/assessment-plans/Index`, `Create`, `Edit`
  - Passes: terms, subjects, assessmentPlans (paginated)

- **`app/Http/Controllers/Admin/GradeScaleController.php`** — index, create, store, edit, update, destroy
  - Renders: `admin/settings/grade-scales/Index`, `Create`, `Edit`
  - Store/update uses DB::transaction for scale + steps

### Teacher Controller
- **`app/Http/Controllers/Teacher/GradebookController.php`** — constructor injects GradebookService
  - `index()` — teacher's assigned class/subject combos → `teacher/gradebook/Index`
  - `show(Request)` — grade entry grid for specific class/subject/term → `teacher/gradebook/Show`
  - `store(StoreGradeRequest)` — save grades via service
  - `summary(Request)` — computed results + rankings → `teacher/gradebook/Summary`

---

## Phase 6: Routes

### `routes/admin.php` — Inside existing `settings` prefix group
```
assessment-plans: index, create, store, {assessmentPlan}/edit, update, destroy
grade-scales: index, create, store, {gradeScale}/edit, update, destroy
```

### `routes/teacher.php` — After timetable routes
```
permission:exams.view → GET /gradebook, GET /gradebook/show, GET /gradebook/summary
permission:exams.enter-marks → POST /gradebook
```

---

## Phase 7: Frontend (all lowercase `resources/js/pages/`)

### Admin Assessment Plans
- `pages/admin/settings/assessment-plans/Index.vue` — Table with term/subject filters, using AdminSettingsLayout
- `pages/admin/settings/assessment-plans/Create.vue` — Modal form (term, subject, name, max_score, weight)
- `pages/admin/settings/assessment-plans/Edit.vue` — Modal form pre-populated

### Admin Grade Scales
- `pages/admin/settings/grade-scales/Index.vue` — Cards/table showing scales with step previews
- `pages/admin/settings/grade-scales/Create.vue` — Modal form with dynamic step repeater
- `pages/admin/settings/grade-scales/Edit.vue` — Modal form pre-populated

### Teacher Gradebook
- `pages/teacher/gradebook/Index.vue` — Cards showing assigned class/subject combos with term selector
- `pages/teacher/gradebook/Show.vue` — Grade entry grid (rows=students, cols=assessments, cells=score inputs)
- `pages/teacher/gradebook/Summary.vue` — Read-only results table with totals, grades, ranks

### Navigation Updates
- **`resources/js/composables/useAdminSettingsNavigation.ts`** — Add "Assessment Plans" and "Grade Scales" nav items
- **`resources/js/navigation/index.ts`** — Add "Gradebook" to teacher and head_teacher sidebar nav

---

## Phase 8: Tests

### `tests/Feature/Admin/AssessmentPlanTest.php`
- displays assessment plans for the school
- does not show plans from other schools
- can create assessment plan
- validates required fields
- enforces unique constraint (term+subject+name)
- can update / delete
- prevents deletion if grades exist

### `tests/Feature/Admin/GradeScaleTest.php`
- displays grade scales
- can create scale with steps
- validates step ranges
- can update / delete

### `tests/Feature/Teacher/GradebookTest.php`
- shows assigned class/subject combos
- shows grade entry grid with students and assessments
- stores grades successfully
- validates score <= max_score
- prevents grading unauthorized class/subject
- prevents grading locked assessments
- shows summary with computed totals and rankings

### `tests/Feature/Services/GradebookServiceTest.php`
- computes weighted total correctly
- resolves grade from percentage
- ranks students by total score
- handles missing scores
- checks teacher authorization
- locks grades

---

## Execution Order

1. Migrations (1a, 1b) → `php artisan migrate`
2. Models (1c, 1d)
3. Factories (Phase 2)
4. GradebookService (Phase 3)
5. Form Requests (Phase 4)
6. Controllers (Phase 5)
7. Routes (Phase 6) → `php artisan wayfinder:generate`
8. Vue pages + navigation updates (Phase 7)
9. Tests (Phase 8)
10. `vendor/bin/pint --dirty`
11. `php artisan test`

---

## Verification

1. Run `php artisan migrate` — confirm tables created
2. Run `php artisan test --filter=AssessmentPlan` — admin CRUD tests pass
3. Run `php artisan test --filter=GradeScale` — admin scale tests pass
4. Run `php artisan test --filter=Gradebook` — teacher gradebook tests pass
5. Run `npm run build` — frontend compiles
6. Manual: visit admin settings, create assessment plans and grade scales
7. Manual: visit teacher gradebook, enter marks, view summary
