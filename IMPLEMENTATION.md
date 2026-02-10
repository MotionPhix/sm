# Malawian School SaaS — Implementation Plan

This document tracks the scope, progress, and remaining work. Use the checkboxes to manage delivery.

Legend: [ ] not started, [~] in progress, [x] done

## 1) Tenancy and Context

- [x] Data scoping
  - [x] Enforce `school_id` and `academic_year_id` on tenant models (DB constraints + indexes)
  - [x] Global scopes/services to guarantee tenant isolation on reads/writes
  - [ ] Soft-deletes safe per-tenant; restore protections
- [x] Middleware coverage
  - [x] Ensure School/Year/Onboarding/Role/Permission middlewares wrap all tenant routes
  - [ ] Add tests for middleware coverage and access denials
- [x] Multi-school users
  - [x] Active-school switcher UX + API safety (active_school_id column present; switching UX to confirm)
  - [x] Per-school roles/permissions integrity (roles/permissions tables and seeders present)
  - [ ] Cache/log/export isolation by tenant
- [ ] Auditability
  - [ ] Audit log for grades, fees/payments, enrollments, roles/permissions
  - [ ] Who/when/what diffs + export

## 2) Onboarding and Setup

- [x] Wizard steps
  - [x] School profile (type, EMIS code, contacts) (models/migrations in place; UI flow to confirm)
  - [x] Academic calendar (years) (AcademicYear model/service; term dates pending)
  - [x] Classes/streams/subjects (models/migrations exist)
  - [ ] Grading scheme & promotion rules (service stub present; configuration pending)
  - [x] Finance setup (fee items by class/term, currency MK, discounts/bursaries) - FeeItem and FeeStructure models/controllers implemented
  - [ ] Communications (SMS/email providers, templates)
  - [x] Staff invites and role assignment (invitation controller, mail, routes)
- [x] Checklist & gating
  - [x] Block modules until minimum setup complete (EnsureOnboardingComplete middleware present)
  - [ ] Progress indicators & defaults

## 3) Academic Year, Terms, Calendar

- [x] Academic year lifecycle (create/lock/rollover) (service present; needs completion)
- [x] Term definitions (3-term default, custom allowed) - Implemented with default generation
- [ ] School/class events & calendar; iCal export

## 4) Timetabling and Attendance

- [x] Timetable builder (foundational models)
  - [x] Subject-teacher-stream assignments (TeacherAssignment, ClassStreamAssignment models)
  - [x] Clash detection (teacher/class/time) — TimetableService with validateAssignmentClash, isTeacherAvailable, isClassAvailable
- [x] Attendance
  - [x] Daily student attendance with class/stream selection, date validation within academic year/term
  - [x] Teacher authorization check (must be assigned to class/stream)
  - [x] Duplicate prevention (updateOrCreate with unique constraint)
  - [x] Recorded_by/recorded_at metadata tracking
  - [x] TypeScript Vue pages (Index, Record, History)
  - [x] CSV export with filters
  - [~] Staff attendance (optional)
  - [ ] Period-based attendance (daily only for now)
  - [ ] Alerts for chronic absenteeism

## 5) Curriculum, Assessments, Grading, Reports

- [~] Curriculum
  - [~] Subjects per level; Malawi syllabus alignment (Subjects, SchoolClass, Streams exist; alignment pending)
  - [ ] Assessment plans/weights per term - Model exists, needs UI/implementation
- [ ] Gradebook
  - [ ] Secure entry, moderation, lock late changes
  - [ ] CSV import; computed totals/grades
- [ ] Grading scales & analytics
  - [ ] Numeric->grade mapping; class position/ranking
  - [ ] Aggregates (PSLCE/JCE/MSCE style where relevant)
- [ ] Reports
  - [ ] Report cards & transcripts (PDF, queue)
  - [ ] Performance dashboards (subject/class/teacher)

## 6) Admissions & Student Lifecycle

- [x] Admissions
  - [x] Application windows & online forms (AdmissionCycle, Applicants; Registrar UI present)
  - [~] Document uploads; screening pipeline (basic status updates present; uploads to confirm)
  - [x] Offers, acceptance, enrollment to class/stream (full linkage implemented with applicant_id tracking)
- [x] Student enrollment management
  - [x] Enroll student in class/stream with enrollment date tracking
  - [x] Create student from admitted applicant (auto-updates applicant status to 'enrolled')
  - [x] Direct student creation with optional immediate enrollment
  - [x] Enrollment history tracking with active/inactive status
  - [x] Current class assignment tracking on student model
- [x] Transfers/withdrawals
  - [x] Transfer students between classes/streams with reason and date tracking
  - [x] Prevent transfers to same class/stream
  - [x] Withdrawal with reason, date, and automatic enrollment deactivation
  - [x] Transfer history with transferred_in_at/transferred_out_at timestamps
  - [ ] Mid-term enrollments; fee proration
  - [ ] Transfer letters & records (PDF generation)
- [x] Student profile
  - [x] Bio, guardians, enrollment history (Student, Guardian, pivot, StudentEnrollment complete)
  - [x] Soft deletes for students
  - [x] Withdrawal status tracking
  - [ ] Medical records, IEPs, discipline, co-curricular

## 7) Staff Management

- [~] Profiles & qualifications (User model; details pending)
- [~] Contracts & workload (structure pending; assignments exist)
- [~] Assignments (subjects/streams) with load balancing (assignments exist; balancing pending)
- [ ] Appraisals & development tracking

## 8) Finance & Accounting

- [x] Fee structures
  - [x] Fee items by class/term; bursaries/discounts - FeeItem and FeeStructure models/controllers implemented
  - [x] Invoicing; proration for joiners/leavers
    - [x] Invoice, InvoiceItem, Payment models with UUID routing and tenant scoping
    - [x] InvoiceService for invoice generation from fee structures
    - [x] Payment recording with multiple methods (cash, bank_transfer, mobile_money, cheque, card, other)
    - [x] Automatic status transitions (draft → issued → partially_paid → paid/overdue)
    - [x] Proration for mid-term enrollments
    - [x] Invoice cancellation with audit trail
    - [x] Bursar controllers (InvoiceController, PaymentController)
    - [x] Form requests with validation (StoreInvoiceRequest, RecordPaymentRequest)
    - [x] Comprehensive tests (InvoiceGenerationTest, PaymentRecordingTest - 22 tests passing)
    - [x] Vue pages for invoice/payment management UI
      - [x] Bursar Dashboard with finance KPIs, recent invoices/payments, collection by method (DashboardController + Dashboard.vue)
      - [x] Invoice listing with stats, filters, pagination (invoices/Index.vue)
      - [x] Invoice creation form with student/year/term selection (invoices/Create.vue)
      - [x] Invoice detail with payment recording and cancellation modals (invoices/Show.vue)
      - [x] Payment receipt/detail view (payments/Show.vue)
      - [x] Student payment history with date/method filters (payments/History.vue)
      - [x] Bursar sidebar navigation (Dashboard + Invoices)
      - [x] Dashboard feature tests (9 tests — auth, role, stats, recent data, methods)
- [ ] Payments
  - [ ] Manual receipts; reconciliation
  - [ ] Integrations (Airtel Money, TNM Mpamba, card via aggregator)
  - [ ] Pending/failed handling; chargebacks
- [ ] Arrears management
  - [ ] Reminders (SMS/email), statements, payment plans
- [ ] Accounting
  - [ ] Chart of accounts; GL postings from fees/expenses
  - [ ] Approvals (bursar vs accountant); cashbook
  - [ ] Reports: aged debtors, term collection, income vs budget

## 9) Communications & Portals

- [~] Messaging
  - [~] Email templates & delivery (invitation mail implemented)
  - [ ] SMS/in-app templates & delivery status
  - [ ] Sender IDs; throttling/rate limits
- [~] Portals
  - [~] Parent portal (route scaffolding present)
  - [~] Student portal (route scaffolding present)
- [ ] Noticeboard with acknowledgements

## 10) Compliance & Localization

- [ ] Localization: English/Chichewa; currency MK; date/phone formats
- [ ] Malawi policies: term structures, exam conventions
- [ ] Government reporting/EMIS exports
- [ ] Privacy & data lifecycle (export/delete per tenant)

## 11) Security & Reliability

- [x] MFA (Fortify), session/device management (Fortify + 2FA columns present)
- [~] RBAC enforcement tests; secure webhooks (role/permission middleware present; tests pending)
- [ ] Backups; tenant-level export; disaster recovery
- [ ] Rate limiting; CSRF protections; content security headers

## 12) Performance & Scale

- [~] Queues for heavy jobs; job monitoring & DLQs (jobs/failed_jobs tables present)
- [ ] Caching strategy (permissions, dashboards, timetables)
- [ ] DB optimization: indexes, constraints, N+1 prevention, pagination
- [ ] S3-compatible storage; signed URLs

## 13) Integrations & Extensibility

- [ ] Payment gateways (local providers/aggregators)
- [ ] SMS providers with Malawi delivery
- [ ] Document e-sign (reports/certificates)
- [ ] Public API/Webhooks; API keys per school

## 14) Quality & Testing

- [x] Seeders/factories for realistic demos (Role/Permission seeders + School, AcademicYear, Term, SchoolClass, Stream, ClassStreamAssignment, Student, StudentEnrollment, TeacherAssignment, AttendanceRecord, Applicant, AdmissionCycle, Invoice, InvoiceItem, Payment factories)
- [x] Unit/integration tests (TimetableService clash detection tests, AttendanceController feature tests, FeeItem and FeeStructure tests, Student enrollment/transfer/withdrawal tests, Invoice generation and payment recording tests)
- [ ] E2E flows per role (registrar/teacher/bursar/admin/parent/student)
- [ ] CI with static analysis, coverage, coding standards

## 15) UX & Accessibility

- [~] Design system consistency; dark/light modes (HandleAppearance middleware; component library present)
- [ ] Mobile-first layouts (attendance, portals)
- [ ] Accessibility (keyboard, ARIA, contrast)
- [ ] Empty states, skeletons, tooltips, inline help

---

## Milestones/Epics

1. Foundation & Tenancy Hardening

- [x] Enforce scoping & middleware; audit logs; baseline tests

1. Onboarding Wizard & School Switcher

- [x] Full setup flow; gating; defaults (onboarding middleware present; flows partially implemented)

1. Academic Year/Terms/Calendar + Timetable & Attendance

- [x] Year/term lifecycle; timetable; attendance MVP (clash detection, daily attendance with validation, TypeScript UI)

1. Gradebook, Grading Scales, Reports (PDF)

- [ ] Assessments, computation, PDFs

1. Admissions & Student Lifecycle

- [x] Applications→enrollment; transfers; withdrawals; student profiles (full enrollment management with applicant conversion, transfers, history tracking)

1. Finance & Accounting + Payments

- [x] Fee items/structures setup - COMPLETED
- [x] Invoicing & payments UI - COMPLETED (Dashboard, Invoice CRUD, Payment Show/History, 31 tests passing)
- [ ] Arrears management, accounting, payment integrations

1. Communications & Portals

- [~] Messaging; noticeboard; parent/student portals (invitation email; portal routes scaffolded)

1. Analytics & Compliance Exports

- [ ] Dashboards; EMIS/government exports

1. Localization, Backups, Observability

- [ ] Chichewa/MK; backups; logging & error tracking

---

## Risks & Edge Cases

- [ ] Cross-tenant data leakage via caching/exports/logs
- [ ] Mid-term enrollments with fee proration and partial assessments
- [ ] Late grade edits after publication (lock & audit)
- [ ] Payment reconciliation mismatches/chargebacks
- [x] Teacher timetable clashes and last-minute changes — TimetableService validates clashes, TeacherAssignment model prevents saving conflicting schedules
- [ ] Transfers between streams/classes mid-term and promotions rules
- [ ] SMS deliverability/latency and cost management

## KPIs

- [ ] Time to onboard a new school (< 30 min)
- [ ] Attendance capture time per class (< 2 min)
- [ ] Report card generation throughput (queued PDFs/min)
- [ ] Payment reconciliation accuracy (> 99.5%)
- [ ] System error rate (< 0.1%) & P95 page load time

## Notes

- Use queues for heavy tasks (PDFs, imports, notifications)
- Tag logs with `school_id`, `user_id`, `academic_year_id`
- Prefer repository/service patterns for scoping and testability

---

## Next Phase Recommendation: Student Enrollment Management

Based on the current state, the most logical next module to implement is **Student Enrollment Management** because:

1. **Dependencies are ready**:
   - Academic years, terms, classes, streams are fully set up
   - Fee structures are configured
   - Admission cycles and applicants exist

2. **Natural workflow progression**:
   - Admissions → Applicants → Enrollment is the natural flow
   - Currently, applicants exist but cannot be converted to enrolled students
   - This bridges the gap between admissions and the academic system

3. **Enables downstream features**:
   - Attendance recording requires enrolled students
   - Gradebook needs enrolled students
   - Finance/invoicing needs student enrollments

4. **Core business value**:
   - Without enrollment, the system cannot track actual students
   - This is the critical link between prospective and active students

### Student Enrollment Implementation Scope

**Backend:**

- [ ] Create enrollment from admitted applicant
- [ ] Bulk enrollment import (CSV)
- [ ] Enrollment validation (class capacity, duplicate checks)
- [ ] Fee assignment on enrollment (link to fee structures)
- [ ] Enrollment history tracking
- [ ] Transfer between classes/streams
- [ ] Withdrawal/de-enrollment

**Frontend:**

- [ ] Enrollment management UI (list, create, edit)
- [ ] Applicant-to-student conversion flow
- [ ] Bulk import interface
- [ ] Enrollment reports

**Tests:**

- [ ] Enrollment creation tests
- [ ] Validation rule tests
- [ ] Bulk import tests
- [ ] Transfer/withdrawal tests
