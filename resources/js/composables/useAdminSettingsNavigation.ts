import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import type { NavItem } from '@/types'
import {
    Calendar,
    Clock,
    GraduationCap,
    DollarSign,
    Construction,
    BookOpen,
    SplitSquareHorizontal,
    Building2,
    School,
    ClipboardList,
    BarChart3,
} from 'lucide-vue-next'
import { 
    index as academicYearsIndex
} from '@/routes/admin/settings/academic-year'
import { 
    index as termsIndex 
} from '@/routes/admin/settings/terms'
import { 
    index as admissionCyclesIndex 
} from '@/routes/admin/settings/admission-cycles'
import { 
    index as feeItemsIndex 
} from '@/routes/admin/settings/fee-items'
import { 
    index as feeStructuresIndex 
} from '@/routes/admin/settings/fee-structures'
import {
    index as classesIndex
} from '@/routes/admin/settings/classes'
import {
    index as streamsIndex
} from '@/routes/admin/settings/streams'
import {
    index as subjectsIndex
} from '@/routes/admin/settings/subjects'
import {
    show as schoolProfileShow
} from '@/routes/admin/settings/school-profile'
import {
    index as assessmentPlansIndex
} from '@/routes/admin/settings/assessment-plans'
import {
    index as gradeScalesIndex
} from '@/routes/admin/settings/grade-scales'

/**
 * Composable for Admin Settings secondary navigation
 * Provides pre-configured navigation menus for different settings sections
 */
export function useAdminSettingsNavigation() {
    const page = usePage()

    /**
     * Determine if a navigation item should be marked as active
     * Handles nested routes intelligently:
     * - /admin/settings/academic-years/* -> Academic Years is active
     * - /admin/settings/terms/* -> Terms is active
     * - /admin/settings/admission-cycles/* -> Admission Cycles is active
     * - /admin/settings/fee-items/* -> Fee Items is active
     * - /admin/settings/fee-structures/* -> Fee Structures is active
     */
    const isNavItemActive = (itemHref: string, alternateHrefs: (string | null)[] = []): boolean => {
        const currentPath = page.url
        const itemPath = new URL(itemHref, 'http://localhost').pathname
        const alternatePaths = alternateHrefs
            .filter((href) => href !== null)
            .map((href) => new URL(href as string, 'http://localhost').pathname)

        // Exact match (highest priority)
        if (currentPath === itemPath) {
            return true
        }

        // If there are alternate paths that are CHILDREN of this item
        // Check if we're currently in any child path - if so, this item is NOT active
        for (const alternatePath of alternatePaths) {
            if (alternatePath.startsWith(itemPath + '/')) {
                // This is a child path of the item
                if (currentPath.startsWith(alternatePath)) {
                    return false
                }
            }
        }

        // Parent route match: /admin/settings/academic-years matches /admin/settings/academic-years/create, etc.
        if (currentPath.startsWith(itemPath + '/')) {
            return true
        }

        return false
    }

    /**
     * Get all admin settings navigation items
     * Used in all admin settings pages
     */
    const adminSettingsNavItems = computed((): NavItem[] => {
        const schoolProfileHref = schoolProfileShow.url()
        const academicYearsHref = academicYearsIndex.url()
        const termsHref = termsIndex.url()
        const classesHref = classesIndex.url()
        const streamsHref = streamsIndex.url()
        const subjectsHref = subjectsIndex.url()
        const admissionCyclesHref = admissionCyclesIndex.url()
        const feeItemsHref = feeItemsIndex.url()
        const feeStructuresHref = feeStructuresIndex.url()
        const assessmentPlansHref = assessmentPlansIndex.url()
        const gradeScalesHref = gradeScalesIndex.url()

        const allHrefs = [
            schoolProfileHref,
            academicYearsHref,
            termsHref,
            classesHref,
            streamsHref,
            subjectsHref,
            admissionCyclesHref,
            feeItemsHref,
            feeStructuresHref,
            assessmentPlansHref,
            gradeScalesHref,
        ]

        return [
            {
                title: 'School Profile',
                icon: School,
                href: schoolProfileHref,
                isActive: isNavItemActive(schoolProfileHref, allHrefs.filter(h => h !== schoolProfileHref)),
            },
            {
                title: 'Academic Years',
                icon: Calendar,
                href: academicYearsHref,
                isActive: isNavItemActive(academicYearsHref, allHrefs.filter(h => h !== academicYearsHref)),
            },
            {
                title: 'Terms',
                icon: Clock,
                href: termsHref,
                isActive: isNavItemActive(termsHref, allHrefs.filter(h => h !== termsHref)),
            },
            {
                title: 'Classes',
                icon: Building2,
                href: classesHref,
                isActive: isNavItemActive(classesHref, allHrefs.filter(h => h !== classesHref)),
            },
            {
                title: 'Streams',
                icon: SplitSquareHorizontal,
                href: streamsHref,
                isActive: isNavItemActive(streamsHref, allHrefs.filter(h => h !== streamsHref)),
            },
            {
                title: 'Subjects',
                icon: BookOpen,
                href: subjectsHref,
                isActive: isNavItemActive(subjectsHref, allHrefs.filter(h => h !== subjectsHref)),
            },
            {
                title: 'Admission Cycles',
                icon: GraduationCap,
                href: admissionCyclesHref,
                isActive: isNavItemActive(admissionCyclesHref, allHrefs.filter(h => h !== admissionCyclesHref)),
            },
            {
                title: 'Fee Items',
                icon: DollarSign,
                href: feeItemsHref,
                isActive: isNavItemActive(feeItemsHref, allHrefs.filter(h => h !== feeItemsHref)),
            },
            {
                title: 'Fee Structures',
                icon: Construction,
                href: feeStructuresHref,
                isActive: isNavItemActive(feeStructuresHref, allHrefs.filter(h => h !== feeStructuresHref)),
            },
            {
                title: 'Assessment Plans',
                icon: ClipboardList,
                href: assessmentPlansHref,
                isActive: isNavItemActive(assessmentPlansHref, allHrefs.filter(h => h !== assessmentPlansHref)),
            },
            {
                title: 'Grading Scales',
                icon: BarChart3,
                href: gradeScalesHref,
                isActive: isNavItemActive(gradeScalesHref, allHrefs.filter(h => h !== gradeScalesHref)),
            },
        ]
    })

    return {
        adminSettingsNavItems,
    }
}
