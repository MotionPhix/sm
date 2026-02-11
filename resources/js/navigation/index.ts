import {
  LayoutGrid,
  Users,
  BookOpen,
  Wallet,
  GraduationCap,
  Construction,
  Settings,
  Calendar,
  ClipboardCheck,
  ClipboardList,
  FileText,
  BarChart3,
  Megaphone,
  PenLine,
} from 'lucide-vue-next'
import type { NavItem } from '@/types'
import * as admin from '@/routes/admin'
import * as teacher from '@/routes/teacher'
import * as registrar from '@/routes/registrar'
import * as bursar from '@/routes/bursar'
import { redirect as dashboardRedirect } from '@/routes/dashboard'

export const navigation = {
  admin: <NavItem[]>[
    { title: 'Dashboard', href: admin.dashboard.url(), icon: LayoutGrid },
    { title: 'Staff', href: admin.default.staff.index.url(), icon: Users },
    { title: 'Enrollment', href: admin.default.enrollment.index.url(), icon: GraduationCap },
    { title: 'Promotion', href: admin.default.promotion.index.url(), icon: Construction },
    { title: 'Reports', href: admin.default.reports.index.url(), icon: BarChart3 },
  ],

  head_teacher: <NavItem[]>[
    { title: 'Dashboard', href: teacher.dashboard.url(), icon: LayoutGrid },
    { title: 'My Students', href: teacher.default.students.index.url(), icon: Users },
    { title: 'Timetable', href: teacher.default.timetable.index.url(), icon: Calendar },
    { title: 'Attendance', href: teacher.default.attendance.index.url(), icon: ClipboardCheck },
    { title: 'Gradebook', href: teacher.default.gradebook.index.url(), icon: ClipboardList },
    { title: 'Exam Marking', href: teacher.default.examMarking.index.url(), icon: PenLine },
    { title: 'Class Reports', href: teacher.default.classReports.index.url(), icon: FileText },
    { title: 'Announcements', href: teacher.default.announcements.index.url(), icon: Megaphone },
  ],

  teacher: <NavItem[]>[
    { title: 'Dashboard', href: teacher.dashboard.url(), icon: LayoutGrid },
    { title: 'My Students', href: teacher.default.students.index.url(), icon: Users },
    { title: 'Timetable', href: teacher.default.timetable.index.url(), icon: Calendar },
    { title: 'Attendance', href: teacher.default.attendance.index.url(), icon: ClipboardCheck },
    { title: 'Gradebook', href: teacher.default.gradebook.index.url(), icon: ClipboardList },
    { title: 'Exam Marking', href: teacher.default.examMarking.index.url(), icon: PenLine },
    { title: 'Class Reports', href: teacher.default.classReports.index.url(), icon: FileText },
    { title: 'Announcements', href: teacher.default.announcements.index.url(), icon: Megaphone },
  ],

  bursar: <NavItem[]>[
    { title: 'Dashboard', href: bursar.dashboard.url(), icon: LayoutGrid },
    { title: 'Invoices', href: bursar.default.invoices.index.url(), icon: FileText },
  ],

  registrar: <NavItem[]>[
    { title: 'Dashboard', href: registrar.dashboard.url(), icon: LayoutGrid },
    { title: 'Admissions', href: registrar.default.admissions.index.url(), icon: Users },
    { title: 'Students', href: registrar.default.students.index.url(), icon: GraduationCap },
  ],

  parent: <NavItem[]>[
    { title: 'Dashboard', href: dashboardRedirect.url(), icon: LayoutGrid },
  ],

  student: <NavItem[]>[
    { title: 'Dashboard', href: dashboardRedirect.url(), icon: LayoutGrid },
  ],
}
