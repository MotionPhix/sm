import { computed } from 'vue';
import type { BreadcrumbItemType } from '@/types';
import { useRoleRoutes } from '@/composables/useRoleRoutes';

/**
 * Composable for generating role-based breadcrumbs
 * Automatically generates breadcrumbs based on user role
 */
export function useBreadcrumbs() {
    const { routePrefix } = useRoleRoutes();

    /**
     * Get the admin dashboard URL based on role prefix
     */
    const adminDashboardUrl = computed((): string => {
        return `/${routePrefix.value}/dashboard`;
    });

    /**
     * Generate breadcrumbs for admin settings pages
     */
    const adminSettingsBreadcrumbs = (currentPage: string): BreadcrumbItemType[] => [
        {
            title: 'Dashboard',
            href: adminDashboardUrl.value,
        },
        {
            title: 'Settings',
            href: '#',
        },
        {
            title: currentPage,
            href: '#',
        },
    ];

    return {
        adminDashboardUrl,
        adminSettingsBreadcrumbs,
    };
}
