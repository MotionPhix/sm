import { usePage } from '@inertiajs/vue3';

/**
 * Composable for settings navigation
 * Handles determining if a navigation item is active within the settings section
 */
export function useSettingsNavigation() {
    const page = usePage();

    /**
     * Check if the current page is within the settings section only
     * This is used to keep the main Settings link active in the sidebar
     */
    const isInSettingsSection = (): boolean => {
        const currentPath = page.url;
        return currentPath.startsWith('/admin/settings');
    };

    /**
     * Check if the current page is within the billing section only
     * This is used to keep the main Billing link active in the sidebar
     */
    const isInBillingSection = (): boolean => {
        const currentPath = page.url;
        return currentPath.startsWith('/admin/billing');
    };

    /**
     * Check if a specific settings route is active
     * Supports exact path matching for individual settings pages
     */
    const isSettingsRouteActive = (routePath: string): boolean => {
        try {
            const urlPath = new URL(routePath, 'http://localhost').pathname;
            const currentPath = page.url;

            // Exact match
            if (currentPath === urlPath) {
                return true;
            }

            return false;
        } catch {
            // Fallback for simple string comparison
            return page.url === routePath;
        }
    };

    return {
        isInSettingsSection,
        isInBillingSection,
        isSettingsRouteActive,
    };
}
