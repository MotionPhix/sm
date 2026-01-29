# useRoleRoutes Composable Guide

The `useRoleRoutes` composable provides a centralized way to handle role-based routing using **Wayfinder routes**. This ensures type safety and catches route changes at compile time.

## Why Wayfinder Routes?

Instead of hardcoding route names like `'admin.settings.terms.index'`, we use Wayfinder-generated route functions. This means:

✅ **Type-safe** - TypeScript catches route changes at compile time  
✅ **Refactor-safe** - If a route changes, compilation fails immediately  
✅ **No magic strings** - Routes are actual imported functions  
✅ **Single source of truth** - Routes defined in controllers auto-generate the functions  

## Available Roles

- `super_admin` - System-wide administrator
- `admin` - School administrator
- `head_teacher` - Head teacher
- `teacher` - Teacher
- `accountant` - Accountant
- `registrar` - Registrar
- `bursar` - Bursar
- `parent` - Parent/Guardian
- `student` - Student

## Basic Usage

```vue
<script setup lang="ts">
import { useRoleRoutes } from '@/composables/useRoleRoutes'

const { 
    userRole,                      // Current user's role
    routePrefix,                   // Computed route prefix based on role
    termsIndexRoute,               // Wayfinder route function
    canAccessAdmin,                // Boolean to check if user can access admin features
    canAccessTeacher,              // Boolean to check if user can access teacher features
} = useRoleRoutes()
</script>

<template>
    <div>
        <!-- Using Wayfinder routes - returns { url, method } object -->
        <Link :href="termsIndexRoute().url">Terms</Link>
        
        <!-- Conditional rendering based on permissions -->
        <div v-if="canAccessAdmin">
            <Link :href="admissionCyclesIndexRoute().url">Admission Cycles</Link>
        </div>
    </div>
</template>
```

## Available Route Properties

All route properties return **Wayfinder route functions** that return `{ url: string, method: string }` objects.

### Terms Management Routes
- `termsIndexRoute()` - GET /admin/settings/terms
- `termsCreateRoute()` - GET /admin/settings/terms/create (modal)
- `termsEditRoute(termId)` - GET /admin/settings/terms/{term}/edit (modal)
- `termsStoreRoute()` - POST /admin/settings/terms
- `termsUpdateRoute(termId)` - PUT /admin/settings/terms/{term}
- `termsDestroyRoute(termId)` - DELETE /admin/settings/terms/{term}
- `termsGenerateDefaultsRoute()` - POST /admin/settings/terms/generate-defaults

### Admission Cycles Routes
- `admissionCyclesIndexRoute()` - GET /admin/settings/admission-cycles
- `admissionCyclesCreateRoute()` - GET /admin/settings/admission-cycles/create (modal)
- `admissionCyclesEditRoute(cycleId)` - GET /admin/settings/admission-cycles/{cycle}/edit (modal)
- `admissionCyclesStoreRoute()` - POST /admin/settings/admission-cycles
- `admissionCyclesUpdateRoute(cycleId)` - PUT /admin/settings/admission-cycles/{cycle}
- `admissionCyclesDestroyRoute(cycleId)` - DELETE /admin/settings/admission-cycles/{cycle}

### Staff Management Routes
- `staffIndexRoute()` - GET /admin/staff
- `staffInviteRoute()` - GET /admin/staff/invite (modal)
- `staffStoreRoute()` - POST /admin/staff/invite

### Admissions Routes
- `admissionsIndexRoute()` - GET /registrar/admissions
- `admissionsCreateRoute()` - GET /registrar/admissions/create (modal)
- `admissionsEditRoute(admissionId)` - GET /registrar/admissions/{admission}/edit (modal)
- `admissionsStoreRoute()` - POST /registrar/admissions
- `admissionsUpdateRoute(admissionId)` - PUT /registrar/admissions/{admission}
- `admissionsDestroyRoute(admissionId)` - DELETE /registrar/admissions/{admission}
- `admissionsShowRoute(admissionId)` - GET /registrar/admissions/{admission}

### Dashboard Routes
- `dashboardRoute` - String route name (not yet migrated to Wayfinder)

### Permission Checks
- `canAccessAdmin` - Check if user can access admin features
- `canAccessRegistrar` - Check if user can access registrar features
- `canAccessTeacher` - Check if user can access teacher features
- `isSuperAdmin` - Check if user is super admin
- `isAdmin` - Check if user is school admin

## Usage Examples

### Example 1: Simple Navigation Link

```vue
<script setup lang="ts">
import { useRoleRoutes } from '@/composables/useRoleRoutes'

const { termsIndexRoute } = useRoleRoutes()
</script>

<template>
    <!-- Wayfinder routes return { url, method } objects -->
    <Link :href="termsIndexRoute().url">View Terms</Link>
</template>
```

### Example 2: With ID Parameters

```vue
<script setup lang="ts">
import { useRoleRoutes } from '@/composables/useRoleRoutes'

const { admissionCyclesEditRoute } = useRoleRoutes()
const cycleId = 123

// Pass the ID to the route function
<Link :href="admissionCyclesEditRoute(cycleId).url">Edit Cycle</Link>
</script>
```

### Example 3: Conditional Navigation Based on Role

```vue
<script setup lang="ts">
import { useRoleRoutes } from '@/composables/useRoleRoutes'

const { 
    userRole,
    canAccessAdmin,
    canAccessRegistrar,
    termsIndexRoute,
    admissionsIndexRoute,
} = useRoleRoutes()
</script>

<template>
    <nav>
        <p>User role: {{ userRole }}</p>
        
        <div v-if="canAccessAdmin">
            <Link :href="termsIndexRoute().url">Manage Terms</Link>
        </div>
        
        <div v-if="canAccessRegistrar">
            <Link :href="admissionsIndexRoute().url">Manage Admissions</Link>
        </div>
    </nav>
</template>
```

### Example 4: Using with ModalLink (InertiaUI)

```vue
<script setup lang="ts">
import { ModalLink } from '@inertiaui/modal-vue'
import { useRoleRoutes } from '@/composables/useRoleRoutes'

const { termsCreateRoute, canAccessAdmin } = useRoleRoutes()
</script>

<template>
    <div v-if="canAccessAdmin">
        <Button :as="ModalLink" :href="termsCreateRoute().url">
            Add New Term
        </Button>
    </div>
</template>
```

### Example 5: Using with Forms

```vue
<script setup lang="ts">
import { useRoleRoutes } from '@/composables/useRoleRoutes'

const { admissionCyclesStoreRoute } = useRoleRoutes()
const form = useForm({ name: '', starts_at: '' })
</script>

<template>
    <!-- Use Wayfinder route with Inertia Form -->
    <Form 
        :action="admissionCyclesStoreRoute().url" 
        method="post"
        @submit="form.post(admissionCyclesStoreRoute().url)"
    >
        <!-- form fields -->
    </Form>
</template>
```

## Benefits

1. **Type Safety** - TypeScript catches route changes at compile time
2. **Refactor-Safe** - If a route in the controller changes, compilation fails
3. **Single Source of Truth** - Routes defined in controllers auto-generate the functions
4. **Easy Maintenance** - No need to manually update hardcoded route names
5. **IDE Support** - Full autocomplete and documentation
6. **No Magic Strings** - All routes are actual imported functions

## Adding New Routes

When you add a new Wayfinder route:

1. Define the route in the controller
2. The route is automatically generated in `@/routes/...`
3. Import it at the top of `useRoleRoutes.ts`
4. Add a computed property to the composable
5. Export it in the return statement

```typescript
// In useRoleRoutes.ts
import { index as newIndex } from '@/routes/admin/new-module'

const newIndexRoute = computed(() => newIndex)

return {
    // ... other routes
    newIndexRoute,
}
```

Then use it in your components:

```vue
<Link :href="newIndexRoute().url">New Module</Link>
```
