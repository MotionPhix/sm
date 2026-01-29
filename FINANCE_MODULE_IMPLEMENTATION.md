# Finance Module Implementation - Fee Structures

## Overview

This document describes the comprehensive Finance Module implementation for the Malawian School SaaS platform, specifically the Fee Structures subsystem that enables schools to define and manage fee items and assign them to student classes.

## Architecture

The Finance module follows Laravel and the application's established best practices:

### Database Schema

#### `fee_items` Table
- **Purpose**: Defines all possible fee categories (tuition, exams, development levy, sports, etc.)
- **Key Columns**:
  - `school_id` (FK): Tenant scoping - each school has independent fee items
  - `name`: Display name (e.g., "Tuition", "Examination Fees")
  - `code`: Unique identifier (e.g., "TUI", "EXM") - always uppercase
  - `category`: Enum [tuition, exam, development, extra_curriculum, other]
  - `description`: Optional notes about the fee
  - `is_mandatory`: Boolean - whether all students must pay this fee
  - `is_active`: Boolean - soft delete alternative for archive capability

#### `fee_structures` Table  
- **Purpose**: Assigns fee amounts to specific classes and terms
- **Key Columns**:
  - `school_id` (FK): Tenant scoping
  - `academic_year_id` (FK): Links to academic year
  - `school_class_id` (FK): Links to class (Form 1, 2, etc.)
  - `term_id` (FK, nullable): Optional - can be year-wide (null) or term-specific
  - `fee_item_id` (FK): References a fee item
  - `amount`: Decimal(12,2) in Malawi Kwacha (MK)
  - `quantity`: Integer - for items with per-unit pricing
  - `is_active`: Boolean - toggle without deleting

- **Constraints**:
  - Unique: `[academic_year_id, school_class_id, term_id, fee_item_id]` - prevents duplicate assignments
  - Indexes on common query paths: school_id + academic_year_id + class, and academic_year + term

### Models

**FeeItem Model** (`app/Models/FeeItem.php`)
- Tenant-scoped via global scope (`TenantScoped`)
- Relationships:
  - `school()`: belongs to School
  - `feeStructures()`: has many FeeStructures
- Methods:
  - `getCategoryLabel()`: Returns human-readable category
  - `categories()`: Static method returning all category options
- Auto-sets `school_id` on creation if bound in app container

**FeeStructure Model** (`app/Models/FeeStructure.php`)
- Tenant-scoped via global scope
- Relationships:
  - `school()`, `academicYear()`, `schoolClass()`, `term()`, `feeItem()`
- Currency Formatting Methods:
  - `getTotalAmount()`: Returns amount × quantity as float
  - `getFormattedAmount()`: Returns "MK 50,000.00"
  - `getFormattedTotal()`: Returns formatted total

### Factories & Seeders

**FeeItemFactory** (`database/factories/FeeItemFactory.php`)
- `tuition()`: Creates tuition fee (mandatory)
- `exam()`: Creates exam fee
- `development()`: Creates development levy
- `sports()`: Creates sports fee (optional)
- `forSchool(School $school)`: Scopes to specific school

**FeeStructureFactory** (`database/factories/FeeStructureFactory.php`)
- Links fee items to classes
- Supports `withTerm()` for term-specific fees

### Controllers

**FeeItemController** (`app/Http/Controllers/Admin/FeeItemController.php`)
- Actions: `index`, `create`, `store`, `edit`, `update`, `destroy`
- Authorization: Checks `fees.create`, `fees.update`, `fees.delete` permissions via FormRequest
- Prevention: Blocks deletion if fee item is used in fee structures (database constraint check)
- Tenant Safety: Aborts with 403 for cross-school access attempts

**FeeStructureController** (`app/Http/Controllers/Admin/FeeStructureController.php`)
- Actions: `index`, `create`, `store`, `edit`, `update`, `destroy`
- Smart Creation: Updates existing structure if duplicate key is provided (prevents duplicates)
- Transactional: Bulk create/update within DB transaction
- Academic Year Filtering: Always works with current academic year

### Form Requests

**StoreFeeItemRequest** & **UpdateFeeItemRequest**
- Validates name uniqueness per school
- Validates code uniqueness globally (assumption: codes are application-wide unique)
- Enforces valid categories
- Custom error messages with context

**StoreFeeStructureRequest**
- Validates academic year belongs to school
- Validates class belongs to school
- Requires minimum 1 fee item
- Validates amount is numeric and non-negative
- Array validation for bulk fee items

### Routes

All routes registered in `routes/admin.php` under settings prefix:

```
/admin/settings/fee-items              -> index
/admin/settings/fee-items/create       -> create (modal)
/admin/settings/fee-items              -> store (POST)
/admin/settings/fee-items/{feeItem}    -> edit (modal)
/admin/settings/fee-items/{feeItem}    -> update (PUT)
/admin/settings/fee-items/{feeItem}    -> destroy (DELETE)

/admin/settings/fee-structures         -> index
/admin/settings/fee-structures/create  -> create (modal)
/admin/settings/fee-structures         -> store (POST)
/admin/settings/fee-structures/{id}    -> edit (modal)
/admin/settings/fee-structures/{id}    -> update (PUT)
/admin/settings/fee-structures/{id}    -> destroy (DELETE)
```

All routes wrapped with `auth`, `verified`, `school.context`, `role:admin` middleware.

## Frontend Implementation

### Vue Pages

#### Fee Items Index (`resources/js/pages/admin/settings/fee-items/Index.vue`)
- **UI Pattern**: Consistent with Terms/Admission Cycles pages
- **Features**:
  - Table display of all fee items with category badges
  - Color-coded categories (blue=tuition, purple=exam, green=development, orange=sports, gray=other)
  - Status badges (Active/Inactive)
  - Edit/Delete actions with modal support
  - Empty state with call-to-action
  - Success/error alerts
  - Navigation sidebar with tabs
  - Responsive design with dark mode support

#### Fee Items Create (`resources/js/pages/admin/settings/fee-items/Create.vue`)
- **Modal Form** with:
  - Name field (required, max 100)
  - Code field (required, auto-uppercased, max 10)
  - Category select with all options
  - Description textarea (optional)
  - Checkboxes for mandatory and active flags
  - Spinner loading state
  - Error display for validation failures

#### Fee Items Edit (`resources/js/pages/admin/settings/fee-items/Edit.vue`)
- **Similar to Create** but for updating existing fee items
- Pre-populated fields
- PUT request to update endpoint
- Same validation feedback

#### Fee Structures Index (`resources/js/pages/admin/settings/fee-structures/Index.vue`)
- **Complex Table** showing:
  - Class name
  - Term (or "All Year")
  - Fee item name + code
  - Amount in MK currency
  - Quantity
  - Total (amount × quantity)
  - Edit/Delete actions
- **Info Card**: Explains fee structure concept
- **Empty State**: Helpful copy about creating fee items first
- **Currency Formatting**: All amounts shown as "MK X,XXX.XX"

#### Fee Structures Create (`resources/js/pages/admin/settings/fee-structures/Create.vue`)
- **Complex Modal Form** with dynamic fee item assignment:
  - Class select (required)
  - Term select (optional - "All Year" default)
  - Dynamic rows for fee items:
    - Fee item select
    - Amount input (numeric, step 0.01)
    - Quantity input (defaults to 1)
    - Notes textarea per item
    - Trash button to remove row
    - Total display per row
  - "Add Item" button to add more fees in bulk
  - Submit creates all fees in transaction
  - Smart update: duplicate items are updated, not duplicated

### Design System Consistency

All pages follow the existing UI patterns:

- **Layout**: AppLayout with breadcrumbs, AdminSettingsLayout for settings
- **Components**:
  - Lucide Vue icons (Plus, Edit2, Trash2, DollarSign, Grid3X3, Tag, etc.)
  - Shadcn UI components (Button, Input, Select, Checkbox, Textarea, Table, Badge, Alert)
  - Modal system with ModalLink from inertiaui/modal-vue
  - Form field wrappers with labels and error display
- **Styling**: Tailwind CSS v4 with dark mode support
  - Consistent spacing, colors, shadows
  - Hover states and focus rings
  - Responsive breakpoints

## Tenant Isolation

All models use `TenantScoped` global scope to enforce `school_id` filtering:

1. **At Creation**: `school_id` auto-set from app('currentSchool') binding
2. **On Reads**: Global scope filters all queries to current school
3. **Cross-School Protection**: Controllers abort with 403 if attempting to access other school's data
4. **Middleware**: `EnsureSchoolContext` validates user belongs to active school before binding

## Permissions

The module respects the existing permission system:

- `fees.view` - View fee items and structures
- `fees.create` - Create new fees
- `fees.edit` - Update fees
- `fees.delete` - Delete fees

Checked via FormRequest `authorize()` method which calls:
```php
$this->user()->can('fees.create');
```

## Testing

Comprehensive test suite in `tests/Feature/Admin/`:

### FeeItemTest.php (18 tests)
- **Listing**: Displays items, filters by school, shows categories
- **Creation**: Creates item, validates required fields, enforces uniqueness, uppercase code
- **Updating**: Updates item, prevents duplicates, prevents cross-school modification
- **Deletion**: Deletes item, prevents deletion if in use, prevents cross-school deletion
- **Tenant Isolation**: Verifies global scope, auto-sets school_id
- **Model Methods**: Tests category labels and categories array

### FeeStructureTest.php (16 tests)
- **Listing**: Displays structures for academic year, filters by school
- **Creation**: Creates single/multiple items, validates amounts, smart updates for duplicates
- **Updating**: Updates amount/quantity, prevents cross-school mods
- **Deletion**: Deletes structures, prevents cross-school deletion
- **Tenant Isolation**: Verifies scoping
- **Model Methods**: Tests currency formatting and total calculations

All tests use:
- `RefreshDatabase` trait for clean state
- Factory builders for realistic test data
- Permission setup in `beforeEach`
- Assertions on HTTP responses, database state, and authorization

## Usage Flow

### School Admin Setting Up Fees

1. **Create Fee Items** (`/admin/settings/fee-items`)
   - Click "New Fee Item"
   - Enter name, code, select category
   - Mark as mandatory or optional
   - Save

2. **Assign Fees to Classes** (`/admin/settings/fee-structures`)
   - Click "Assign Fees"
   - Select class (e.g., Form 1)
   - Optionally select term (or leave for year-wide)
   - Add multiple fee items with amounts in bulk
   - Save transaction (all created at once)

3. **Manage Throughout Year**
   - Edit amounts if needed
   - View total per student (amount × quantity)
   - Delete specific class fees if structure changes

### Integration Points

- **Students Module**: Will query fee structures when creating invoices
- **Payments Module**: Will reference fee items and structures
- **Reports Module**: Will aggregate fees for financial statements
- **Admissions**: Can apply different fee structures per admission cycle if needed

## Future Enhancements

1. **Bursaries & Discounts**: Link to fee structures for partial waivers
2. **Fee Schedules**: Payment plans (e.g., 50% at term start, 50% mid-term)
3. **Proration**: Mid-year enrollments charged fraction of year fee
4. **Bulk Operations**: CSV import/export of fee structures
5. **Fee Analysis**: Dashboard showing collections vs. structure
6. **Historical Tracking**: Audit changes to fees for financial compliance

## Code Quality

- **Style**: Formatted with Laravel Pint
- **Type Hints**: Full return types and parameter types
- **PHPDoc**: Complete documentation blocks
- **Validation**: Robust validation at request layer
- **Error Handling**: Custom messages for user-friendly feedback
- **Security**: Tenant isolation, permission checks, CSRF protection
- **Performance**: Proper indexing, eager loading, pagination

## Deployment Notes

1. **Migration**: Run `php artisan migrate` to create fee_items and fee_structures tables
2. **Permissions**: Existing `PermissionSeeder` already includes fee.* permissions
3. **Routes**: Automatically registered in admin.php
4. **Frontend**: Vue components available immediately via Inertia
5. **Testing**: Run `php artisan test tests/Feature/Admin/FeeItem*Test` to verify

## Summary

This Finance Module provides a robust, enterprise-grade foundation for school fee management in Malawi. It follows all application conventions, ensures strict tenant isolation, includes comprehensive validation and error handling, and features a beautiful, intuitive UI consistent with the rest of the application.

The modular design allows future expansion for payments, invoicing, reports, and financial integrations without disrupting core functionality.
