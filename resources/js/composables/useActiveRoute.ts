import { usePage } from '@inertiajs/vue3';

/**
 * Composable for determining if a route is active
 * Supports nested routes intelligently:
 * - /admin/settings/academic-years/* -> Academic Years is active
 * - /admin/settings/terms/* -> Terms is active (NOT Academic Years)
 * - /admin/settings/admission-cycles/* -> Admission Cycles is active
 *
 * Key insight: When you have sibling routes at the same level, passing alternate hrefs
 * prevents false positives where a sibling is marked as active.
 *
 * Examples:
 * - isNavItemActive('/admin/settings/academic-years', ['/admin/settings/terms', '/admin/settings/admission-cycles'])
 *   -> Returns true only if current path starts with /admin/settings/academic-years
 *   -> Returns false if current path starts with /admin/settings/terms or /admin/settings/admission-cycles
 */
export function useActiveRoute() {
    const page = usePage();

    /**
     * Check if a segment looks like an action or reserved word (not an entity ID)
     */
    const isReservedSegment = (segment: string): boolean => {
        const reserved = [
            'create',
            'edit',
            'show',
            'delete',
            'destroy',
            'update',
            'store',
            'generate-defaults',
            'switch',
            'invite',
            'accept',
            'select',
            'setup',
            'verify',
        ];
        return reserved.includes(segment);
    };

    /**
     * Check if a segment looks like an ID (numeric or UUID)
     */
    const isIdLike = (segment: string): boolean => {
        return (
            /^[0-9]+$/.test(segment) || // numeric ID
            /^[0-9a-f-]{36}$/.test(segment) || // UUID format
            /^[0-9a-f-]{8}-[0-9a-f-]{4}-[0-9a-f-]{4}-[0-9a-f-]{4}-[0-9a-f-]{12}$/i.test(segment) // strict UUID
        );
    };

    /**
     * Extract the base entity path from a URL
     * Handles the app's route patterns correctly
     *
     * Examples:
     *   - /admin/settings/academic-years -> /admin/settings/academic-years
     *   - /admin/settings/academic-years/123 -> /admin/settings/academic-years
     *   - /admin/settings/academic-years/123/edit -> /admin/settings/academic-years
     *   - /teacher/attendance/123 -> /teacher/attendance
     *   - /registrar/admissions/create -> /registrar/admissions
     */
    const getBaseEntityPath = (path: string): string => {
        // Remove query params and hash
        const cleanPath = path.split('?')[0].split('#')[0];
        const segments = cleanPath.split('/').filter(Boolean);

        if (segments.length <= 2) {
            return '/' + segments.join('/');
        }

        // Find where the ID/action segments start
        let baseSegmentCount = segments.length;

        for (let i = 2; i < segments.length; i++) {
            const segment = segments[i];

            // If it's a reserved action, take everything before it
            if (isReservedSegment(segment)) {
                baseSegmentCount = i;
                break;
            }

            // If it's an ID, take everything before it (and stop looking)
            if (isIdLike(segment)) {
                baseSegmentCount = i;
                break;
            }
        }

        return '/' + segments.slice(0, baseSegmentCount).join('/');
    };

    /**
     * Get the section path from a route (e.g., /admin/settings from /admin/settings/academic-years)
     */
    const getSectionPath = (path: string): string => {
        const segments = path.split('/').filter(Boolean);
        // Return up to 2 segments: /admin/settings
        return '/' + segments.slice(0, 2).join('/');
    };

    /**
     * Determine if a navigation item should be marked as active
     * Handles nested routes intelligently with support for sibling routes
     *
     * @param itemHref - The href of the navigation item to check
     * @param alternateHrefs - Array of sibling/alternate routes that shouldn't trigger this item's active state
     *
     * Examples:
     * - isNavItemActive('/admin/settings/academic-years', ['/admin/settings/terms', '/admin/settings/admission-cycles'])
     *   When on /admin/settings/terms or /admin/settings/admission-cycles, still returns true (same section)
     * - isNavItemActive('/admin/dashboard', ['/admin/staff', '/admin/settings'])
     *   When on /admin/staff or /admin/settings/*, returns false (different section)
     */
    const isNavItemActive = (
        itemHref: string,
        alternateHrefs: (string | null | undefined)[] = [],
    ): boolean => {
        try {
            const currentPath = page.url;
            const itemPath = new URL(itemHref, 'http://localhost').pathname;

            // Exact match (highest priority)
            if (currentPath === itemPath) {
                return true;
            }

            // Get section paths to detect if we're in the same "section"
            const itemSection = getSectionPath(itemPath);
            const currentSection = getSectionPath(currentPath);

            // If we're in the same section (e.g., both under /admin/settings), consider it active
            if (itemSection === currentSection) {
                // But check if we're explicitly on a different section's sibling
                const alternatePaths = alternateHrefs
                    .filter((href): href is string => href != null)
                    .map((href) => new URL(href, 'http://localhost').pathname);

                for (const alternatePath of alternatePaths) {
                    const alternateSection = getSectionPath(alternatePath);
                    // If an alternate is in the same section, don't mark this as active
                    if (
                        alternateSection === currentSection &&
                        (currentPath.startsWith(alternatePath + '/') ||
                            currentPath === alternatePath)
                    ) {
                        return false;
                    }
                }

                return true;
            }

            // Child route match: /admin/settings/academic-years matches /admin/settings/academic-years/123
            if (currentPath.startsWith(itemPath + '/')) {
                return true;
            }

            return false;
        } catch {
            // Fallback for simple string comparison
            const cleanUrl = itemHref.split('?')[0].split('#')[0];
            const cleanCurrent = page.url.split('?')[0].split('#')[0];

            return (
                cleanCurrent === cleanUrl ||
                cleanCurrent.startsWith(cleanUrl + '/')
            );
        }
    };

    /**
     * Check if two paths are siblings (same parent level)
     * e.g., /admin/settings/academic-years and /admin/settings/terms are siblings
     */
    const isSibling = (path1: string, path2: string): boolean => {
        const segments1 = path1.split('/').filter(Boolean);
        const segments2 = path2.split('/').filter(Boolean);

        // Must have same depth
        if (segments1.length !== segments2.length) {
            return false;
        }

        // All segments except last must match
        for (let i = 0; i < segments1.length - 1; i++) {
            if (segments1[i] !== segments2[i]) {
                return false;
            }
        }

        // Last segment must be different
        return segments1[segments1.length - 1] !== segments2[segments2.length - 1];
    };

    /**
     * Check if a route URL is active
     * Used for nested item lists where you only want exact or child path matching
     *
     * @param url - The URL to check (can include query params)
     * @param exactOnly - If true, only exact path matches are considered active (default: false)
     * @param alternateUrls - Array of sibling/alternate routes to prevent false positives (optional)
     *
     * Examples:
     * - /admin/settings/academic-years matches /admin/settings/academic-years (exact)
     * - /admin/settings/academic-years matches /admin/settings/academic-years/123 (child)
     * - /admin/settings/academic-years does NOT match /admin/settings/terms
     * - With alternates: prevents marking item as active if we're in an alternate sibling's children
     */
    const isActiveRoute = (
        url: string,
        exactOnly: boolean = false,
        alternateUrls: (string | null | undefined)[] = [],
    ): boolean => {
        const currentPath = page.url;
        
        // Clean the URL (remove query params and hash)
        const cleanUrl = url.split('?')[0].split('#')[0];
        const cleanCurrent = currentPath.split('?')[0].split('#')[0];

        // Exact match (highest priority)
        if (cleanCurrent === cleanUrl) {
            return true;
        }

        // If exactOnly, don't check child paths
        if (exactOnly) {
            return false;
        }

        // If there are alternate paths that are CHILDREN of this item,
        // check if we're currently in any of their child paths - if so, this item is NOT active
        if (alternateUrls.length > 0) {
            const alternatePaths = alternateUrls
                .filter((href): href is string => href != null)
                .map((href) => href.split('?')[0].split('#')[0]);

            for (const alternatePath of alternatePaths) {
                // If the alternate path is a child of this item's path
                if (alternatePath.startsWith(cleanUrl + '/')) {
                    // And we're currently in that alternate's path, don't mark this as active
                    if (cleanCurrent.startsWith(alternatePath)) {
                        return false;
                    }
                }
            }
        }

        // Child path match (e.g., /admin/settings/academic-years/123 matches /admin/settings/academic-years)
        if (cleanCurrent.startsWith(cleanUrl + '/')) {
            return true;
        }

        return false;
    };

    /**
     * Check if two routes are for the same entity
     * e.g., /admin/settings/academic-years and /admin/settings/academic-years/123 are the same entity
     */
    const isSameEntity = (url1: string, url2: string): boolean => {
        const base1 = getBaseEntityPath(url1);
        const base2 = getBaseEntityPath(url2);
        return base1 === base2;
    };

    /**
     * Check if current page is in a specific section
     * Useful for breadcrumb and sidebar highlighting
     *
     * Examples:
     *   - isInSection('/admin/settings') - matches any /admin/settings/* page
     *   - isInSection('/teacher/attendance') - matches any /teacher/attendance/* page
     */
    const isInSection = (sectionPath: string): boolean => {
        const cleanSection = sectionPath.split('?')[0].split('#')[0];
        const cleanCurrent = page.url.split('?')[0].split('#')[0];

        return (
            cleanCurrent === cleanSection ||
            cleanCurrent.startsWith(cleanSection + '/')
        );
    };

    return {
        isActiveRoute,
        isNavItemActive,
        getBaseEntityPath,
        isSameEntity,
        isInSection,
        isSibling,
        isIdLike,
        isReservedSegment,
    };
}
