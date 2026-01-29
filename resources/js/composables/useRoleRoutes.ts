import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Composable to manage role-based access control and permissions.
 * Route imports should be done directly in components using Wayfinder routes.
 *
 * Roles in the system:
 * - super_admin: System-wide admin access
 * - admin: School administrator
 * - head_teacher: Head teacher
 * - teacher: Teacher
 * - accountant: Accountant
 * - registrar: Registrar
 * - bursar: Bursar
 * - parent: Parent/Guardian
 * - student: Student
 *
 * Usage:
 *   // In components, import Wayfinder routes directly:
 *   import { create as termsCreate } from '@/routes/admin/settings/terms'
 *   
 *   // Use the composable for permission checks:
 *   const { canAccessAdmin, userRole } = useRoleRoutes()
 *   
 *   // In templates:
 *   <Button :href="termsCreate().url" v-if="canAccessAdmin">Add Term</Button>
 */
export function useRoleRoutes() {
    type AuthPageProps = {
        auth?: {
            user?: {
                role?: string;
                role_label?: string;
            };
        };
    };

    const page = usePage<AuthPageProps>();

    /**
     * Get the user's role from page props (server-provided, source of truth).
     * Default to 'teacher' if role is not set.
     */
    const userRole = computed((): string => {
        return page.props.auth?.user?.role || 'teacher';
    });

    /**
     * Get the route prefix based on user role.
     * Maps roles to route prefixes:
     * - 'super_admin' → 'admin'
     * - 'admin' → 'admin'
     * - 'registrar' → 'registrar'
     * - 'teacher', 'head_teacher' → 'teacher'
     * - Others default to their role name or a public prefix
     */
    const routePrefix = computed((): string => {
        switch (userRole.value) {
            case 'super_admin':
            case 'admin':
            case 'accountant':
            case 'bursar':
                return 'admin';
            case 'registrar':
                return 'registrar';
            case 'teacher':
            case 'head_teacher':
                return 'teacher';
            default:
                return userRole.value;
        }
    });

    /**
     * Get the dashboard route for the current role.
     * Note: Dashboard routes are not yet migrated to Wayfinder.
     */
    const dashboardRoute = computed((): string => {
        return `${routePrefix.value}.dashboard`;
    });

    // ============================================================
    // Permission Checks
    // ============================================================

    /**
     * Check if the current role can access admin features.
     * Only admin, super_admin, accountant, and bursar can access admin routes.
     */
    const canAccessAdmin = computed((): boolean => {
        return ['super_admin', 'admin', 'accountant', 'bursar'].includes(userRole.value);
    });

    /**
     * Check if the current role can access registrar features.
     * Only registrar and super_admin can access registrar routes.
     */
    const canAccessRegistrar = computed((): boolean => {
        return userRole.value === 'registrar' || userRole.value === 'super_admin';
    });

    /**
     * Check if the current role can access teacher features.
     * Teacher and head_teacher can access teacher routes.
     */
    const canAccessTeacher = computed((): boolean => {
        return ['teacher', 'head_teacher'].includes(userRole.value);
    });

    /**
     * Check if the current role is a super admin.
     */
    const isSuperAdmin = computed((): boolean => {
        return userRole.value === 'super_admin';
    });

    /**
     * Check if the current role is a school admin.
     */
    const isAdmin = computed((): boolean => {
        return userRole.value === 'admin';
    });

    return {
        userRole,
        routePrefix,
        dashboardRoute,
        canAccessAdmin,
        canAccessRegistrar,
        canAccessTeacher,
        isSuperAdmin,
        isAdmin,
    };
}
