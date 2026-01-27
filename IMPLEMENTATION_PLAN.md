# Malawian School SaaS — Implementation Plan

This document tracks the scope, progress, and remaining work. Use the checkboxes to manage delivery.

Legend: [ ] not started, [~] in progress, [x] done

## 1) Tenancy and Context
- [~] Data scoping
  - [~] Enforce `school_id` and `academic_year_id` on tenant models (DB constraints + indexes)
  - [ ] Global scopes/services to guarantee tenant isolation on reads/writes
  - [ ] Soft-deletes safe per-tenant; restore protections
- [~] Middleware coverage
  - [~] Ensure School/Year/Onboarding/Role/Permission middlewares wrap all tenant routes
  - [ ] Add tests for middleware coverage and access denials
- [~] Multi-school users
  - [~] Active-school switcher UX + API safety (active_school_id column present; switching UX to confirm)
  - [~] Per-school roles/permissions integrity (roles/permissions tables and seeders present)
  - [ ] Cache/log/export isolation by tenant
- [ ] Auditability
  - [ ] Audit log for grades, fees/payments, enrollments, roles/permissions
  - [ ] Who/when/what diffs + export

## 2) Onboarding and Setup
- [~] Wizard steps
  - [~] School profile (type, EMIS code, contacts) (models/migrations in place; UI flow to confirm)
  - [~] Academic calendar (years) (AcademicYear model/service; term dates pending)
  - [~] Classes/streams/subjects (models/migrations exist)
  - [ ] Grading scheme & promotion rules (service stub present; configuration pending)
  - [ ] Finance setup (fee items by class/term, currency MK, discounts/bursaries)
  - [ ] Communications (SMS/email providers, templates)
  - [~] Staff invites and role assignment (invitation controller, mail, routes)
- [ ] Checklist & gating
  - [~] Block modules until minimum setup complete (EnsureOnboardingComplete middleware present)
  - [ ] Progress indicators & defaults

## 3) Academic Year, Terms, Calendar
- [~] Academic year lifecycle (create/lock/rollover) (service present; needs completion)
- [ ] Term definitions (3-term default, custom allowed)
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
  - [ ] Assessment plans/weights per term
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
- [~] Admissions
  - [~] Application windows & online forms (AdmissionCycle, Applicants; Registrar UI present)
  - [~] Document uploads; screening pipeline (basic status updates present; uploads to confirm)
  - [~] Offers, acceptance, enrollment to class/stream (flow partially present; linkage to enrollment pending)
- [~] Transfers/withdrawals
  - [ ] Mid-term enrollments; fee proration
  - [ ] Transfer letters & records
- [~] Student profile
  - [~] Bio, guardians, medical, IEPs, discipline, co-curricular (Student, Guardian, pivot exist; rest pending)

## 7) Staff Management
- [~] Profiles & qualifications (User model; details pending)
- [~] Contracts & workload (structure pending; assignments exist)
- [~] Assignments (subjects/streams) with load balancing (assignments exist; balancing pending)
- [ ] Appraisals & development tracking

## 8) Finance & Accounting
- [ ] Fee structures
  - [ ] Fee items by class/term; bursaries/discounts
  - [ ] Invoicing; proration for joiners/leavers
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
- [~] MFA (Fortify), session/device management (Fortify + 2FA columns present)
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
- [x] Seeders/factories for realistic demos (Role/Permission seeders + School, AcademicYear, Term, SchoolClass, Stream, ClassStreamAssignment, Student, StudentEnrollment, TeacherAssignment, AttendanceRecord factories)
- [x] Unit/integration tests (TimetableService clash detection tests, AttendanceController feature tests)
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
- [~] Enforce scoping & middleware; audit logs; baseline tests

2. Onboarding Wizard & School Switcher
- [~] Full setup flow; gating; defaults (onboarding middleware present; flows partially implemented)

3. Academic Year/Terms/Calendar + Timetable & Attendance
- [x] Year/term lifecycle; timetable; attendance MVP (clash detection, daily attendance with validation, TypeScript UI)

4. Gradebook, Grading Scales, Reports (PDF)
- [ ] Assessments, computation, PDFs

5. Admissions & Student Lifecycle
- [~] Applications→enrollment; transfers; student profiles (admissions UI + models; enrollment linkage pending)

6. Finance & Accounting + Payments
- [ ] Fees/invoices/receipts; arrears; integrations; GL

7. Communications & Portals
- [~] Messaging; noticeboard; parent/student portals (invitation email; portal routes scaffolded)

8. Analytics & Compliance Exports
- [ ] Dashboards; EMIS/government exports

9. Localization, Backups, Observability
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
