# Implementation Plan

## Two Features

### Feature A: Teacher Assignments Admin Management

### Feature B: Attendance Index URL-based Filtering

---

## Feature A: Teacher Assignments Admin Page

### Overview

Add a full CRUD admin page for managing teacher-class-subject assignments. This is the missing link between staff management and the teacher experience — admins need a way to assign teachers to specific class/stream + subject combinations.

### Location Decision

**Admin Settings section** (alongside Classes, Streams, Subjects) — because teacher assignments depend directly on those entities and are configured per academic year.

### Files to Create

1. **Controller**: `app/Http/Controllers/Admin/TeacherAssignmentController.php`
   - `index()` — List all assignments for current academic year, grouped/filterable by teacher. Passes: assignments (paginated), teachers list, classrooms list, subjects list
   - `create()` — Modal form. Passes: teachers (users with teacher/head_teacher role), classrooms (class_stream_assignments for current year), subjects
   - `store()` — Validate and create assignment. Unique constraint: (user_id, class_stream_assignment_id, subject_id)
   - `edit($id)` — Modal form pre-filled
   - `update($id)` — Validate and update
   - `destroy($id)` — Delete assignment

2. **Form Request**: `app/Http/Requests/Admin/StoreTeacherAssignmentRequest.php`
   - Rules: `user_id` (required, exists:users), `class_stream_assignment_id` (required, exists), `subject_id` (required, exists), unique composite

3. **Form Request**: `app/Http/Requests/Admin/UpdateTeacherAssignmentRequest.php`
   - Same as store but ignores current record for uniqueness

4. **Vue Pages**:
   - `resources/js/pages/admin/settings/teacher-assignments/Index.vue` — Table with teacher name, class, stream, subject, actions (edit/delete). Filter by teacher dropdown. Uses AdminSettingsLayout.
   - `resources/js/pages/admin/settings/teacher-assignments/Create.vue` — Modal form with teacher select, classroom select, subject select
   - `resources/js/pages/admin/settings/teacher-assignments/Edit.vue` — Modal form pre-filled

5. **Routes** in `routes/admin.php`:

   ```
   Route::resource('settings/teacher-assignments', TeacherAssignmentController::class)
       ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
       ->names('settings.teacher-assignments');
   ```

6. **Navigation**: Add "Teacher Assignments" to `useAdminSettingsNavigation.ts` with `UserCog` icon

7. **Test**: `tests/Feature/Admin/TeacherAssignmentTest.php`
   - List assignments
   - Create assignment
   - Prevent duplicate assignment (same teacher + classroom + subject)
   - Update assignment
   - Delete assignment
   - Tenant isolation (can't see/modify other school's assignments)
   - Non-admin forbidden

### Approach

- Follow the exact same pattern as Subjects CRUD (modal forms, AdminSettingsLayout, ConfirmDialog for delete)
- Show classroom as "Form 1 — A" (class name + stream name) in dropdowns
- Show assignments table: Teacher | Class | Stream | Subject | Actions
- Filter by teacher (dropdown at top)

---

## Feature B: Attendance Index URL-based Filtering

### Overview

Convert attendance Index.vue from client-side `fetch()` calls to server-driven URL-based filtering using Inertia's `router.get()` pattern. Filters (date, class_id, stream_id) should appear in the URL query string.

### Files to Modify

1. **Controller**: `app/Http/Controllers/Teacher/AttendanceController.php`
   - Modify `index()` to accept `class_id`, `stream_id` query params
   - When filters are present, load the roster (students + existing attendance) server-side and pass to the page
   - Return `filters` prop back to the component: `$request->only(['date', 'class_id', 'stream_id'])`

2. **Vue Page**: `resources/js/pages/teacher/attendance/Index.vue`
   - Replace `fetch()` call with `router.get()` using `preserveState: true, replace: true`
   - Initialize filter refs from props (server-provided filters)
   - Remove `fetchRoster()` async function
   - Receive `roster` and `existing` as Inertia props from server
   - On filter change, call `router.get(route, filters, { preserveState: true, replace: true })`

### Approach

- Follow the registrar/students `updateFilters()` pattern: `router.get(url, data, { preserveState: true, replace: true })`
- Controller uses `$request->filled('class_id')` to conditionally load roster
- Pass roster/existing as page props (empty arrays when no filters applied)
- Filters persist in URL, enabling bookmarking and browser back/forward

### Test

- Update existing attendance tests or add a test verifying that query params filter the index response correctly

---

## Execution Order

1. Feature B first (smaller, faster) — Attendance URL filtering
2. Feature A second (larger) — Teacher Assignments admin CRUD
