import {
  LayoutGrid,
  Users,
  BookOpen,
  Wallet,
  GraduationCap,
  Settings,
  Calendar,
  ClipboardCheck,
} from 'lucide-vue-next'
import type { NavItem } from '@/types'
import * as admin from '@/routes/admin'
import * as teacher from '@/routes/teacher'
import * as registrar from '@/routes/registrar'
import { redirect as dashboardRedirect } from '@/routes/dashboard'

export const navigation = {
  admin: <NavItem[]>[
    { title: 'Dashboard', href: admin.dashboard.url(), icon: LayoutGrid },
    { title: 'Staff', href: admin.default.staff.index.url(), icon: Users },
    { title: 'Settings', href: admin.default.settings.academicYear.index.url(), icon: Settings },
  ],

  head_teacher: <NavItem[]>[
    { title: 'Dashboard', href: teacher.dashboard.url(), icon: LayoutGrid },
    { title: 'Timetable', href: teacher.default.timetable.index.url(), icon: Calendar },
    { title: 'Attendance', href: teacher.default.attendance.index.url(), icon: ClipboardCheck },
  ],

  teacher: <NavItem[]>[
    { title: 'Dashboard', href: teacher.dashboard.url(), icon: LayoutGrid },
    { title: 'Timetable', href: teacher.default.timetable.index.url(), icon: Calendar },
    { title: 'Attendance', href: teacher.default.attendance.index.url(), icon: ClipboardCheck },
  ],

  bursar: <NavItem[]>[
    { title: 'Dashboard', href: dashboardRedirect.url(), icon: LayoutGrid },
  ],

  registrar: <NavItem[]>[
    { title: 'Dashboard', href: registrar.dashboard.url(), icon: LayoutGrid },
    { title: 'Admissions', href: registrar.default.admissions.index.url(), icon: Users },
  ],

  parent: <NavItem[]>[
    { title: 'Dashboard', href: dashboardRedirect.url(), icon: LayoutGrid },
  ],

  student: <NavItem[]>[
    { title: 'Dashboard', href: dashboardRedirect.url(), icon: LayoutGrid },
  ],
}
  