import {
    LayoutGrid,
    Users,
    BookOpen,
    Wallet,
    GraduationCap,
    Settings,
  } from 'lucide-vue-next'
  import type { NavItem } from '@/types'
  
  export const navigation = {
    admin: <NavItem[]>[
      { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
      { title: 'Students', href: '/admin/students', icon: GraduationCap },
      { title: 'Staff', href: '/admin/staff', icon: Users },
      { title: 'Fees & Billing', href: '/admin/billing', icon: Wallet },
      { title: 'Settings', href: '/admin/settings', icon: Settings },
    ],
  
    head_teacher: <NavItem[]>[
      { title: 'Dashboard', href: '/teacher/dashboard', icon: LayoutGrid },
      { title: 'Classes', href: '/teacher/classes', icon: BookOpen },
      { title: 'Students', href: '/teacher/students', icon: GraduationCap },
    ],
  
    teacher: <NavItem[]>[
      { title: 'Dashboard', href: '/teacher/dashboard', icon: LayoutGrid },
      { title: 'My Classes', href: '/teacher/classes', icon: BookOpen },
    ],
  
    bursar: <NavItem[]>[
      { title: 'Dashboard', href: '/bursar/dashboard', icon: LayoutGrid },
      { title: 'Payments', href: '/bursar/payments', icon: Wallet },
    ],
  
    registrar: <NavItem[]>[
      { title: 'Dashboard', href: '/registrar/dashboard', icon: LayoutGrid },
      { title: 'Admissions', href: '/registrar/admissions', icon: Users },
    ],
  
    parent: <NavItem[]>[
      { title: 'Dashboard', href: '/parent/dashboard', icon: LayoutGrid },
      { title: 'My Children', href: '/parent/children', icon: GraduationCap },
    ],
  
    student: <NavItem[]>[
      { title: 'Dashboard', href: '/student/dashboard', icon: LayoutGrid },
      { title: 'Results', href: '/student/results', icon: BookOpen },
    ],
  }
  