<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    GraduationCap,
    CreditCard,
    MessageSquare,
    Shield,
    MapPin,
    Zap,
    ClipboardList,
    BookOpen,
    UserCheck,
    ChevronRight,
    Check,
    BarChart3,
    Users,
    TrendingUp,
    Bell,
    Calendar,
    FileText,
    ArrowRight,
    Play,
} from 'lucide-vue-next'

const registerUrl = '/admin/register'
const loginUrl = '/login'

const features = [
    {
        title: 'Admissions',
        icon: ClipboardList,
        color: 'bg-blue-500',
        items: ['Application windows & forms', 'Document collection & screening', 'Offers, acceptance, enrollment'],
    },
    {
        title: 'Academics',
        icon: BookOpen,
        color: 'bg-emerald-500',
        items: ['Timetables & attendance', 'Assessments & grading scales', 'Reports, transcripts, promotions'],
    },
    {
        title: 'Finance',
        icon: CreditCard,
        color: 'bg-amber-500',
        items: ['Fee items & invoicing', 'Receipts & reconciliation', 'Arrears, statements, payment plans'],
    },
    {
        title: 'Engagement',
        icon: MessageSquare,
        color: 'bg-purple-500',
        items: ['SMS/email notifications', 'Announcements & calendar', 'Parent & student portals'],
    },
]

const roles = [
    {
        title: 'Registrar',
        icon: UserCheck,
        description: 'Control admission cycles, intake sizes, and automated placement into classes and streams.',
    },
    {
        title: 'Teachers',
        icon: GraduationCap,
        description: 'Take attendance in seconds, record assessments, and publish term reports with confidence.',
    },
    {
        title: 'Bursar & Accountant',
        icon: CreditCard,
        description: 'Set fees by class and term, issue invoices, reconcile receipts, and track arrears.',
    },
    {
        title: 'Head/Administrator',
        icon: BarChart3,
        description: 'See real-time dashboards for attendance, performance, and finance across all your schools.',
    },
]

const faqs = [
    {
        question: 'Can I manage multiple schools?',
        answer: 'Yes. Your account can own or join multiple schools. Switch contexts without signing out.',
    },
    {
        question: 'Does it support Malawi term structures?',
        answer: 'Yes. Define academic years, three terms, and term dates. Configure grading and report templates.',
    },
    {
        question: 'How do parents access information?',
        answer: 'Parents receive portal access to see attendance, results, invoices, and announcements.',
    },
    {
        question: 'What about payments?',
        answer: 'Record cash/bank receipts and connect local payment gateways for mobile money and cards.',
    },
]
</script>

<template>
    <Head title="School Management for Malawi" />

    <div class="min-h-screen bg-white text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100">
        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-neutral-100 bg-white/80 backdrop-blur-xl dark:border-neutral-800 dark:bg-neutral-950/80">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600">
                        <GraduationCap class="h-5 w-5 text-white" />
                    </div>
                    <span class="text-lg font-bold">
                        School<span class="text-blue-600">MW</span>
                    </span>
                </div>

                <nav class="hidden items-center gap-1 lg:flex">
                    <a
                        v-for="item in ['Features', 'How it works', 'Solutions', 'Security', 'FAQ']"
                        :key="item"
                        :href="`#${item.toLowerCase().replace(/\s+/g, '-')}`"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-neutral-600 transition-colors hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white"
                    >
                        {{ item }}
                    </a>
                </nav>

                <div class="flex items-center gap-3">
                    <Link
                        :href="loginUrl"
                        class="hidden rounded-lg px-4 py-2 text-sm font-medium text-neutral-700 transition-colors hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800 sm:inline-flex"
                    >
                        Sign in
                    </Link>
                    <Link
                        :href="registerUrl"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:shadow-xl hover:shadow-blue-500/30"
                    >
                        Get Started
                        <ChevronRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <!-- Background decorations -->
            <div class="absolute inset-0 -z-10">
                <div class="absolute left-1/2 top-0 h-[600px] w-[600px] -translate-x-1/2 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 opacity-50 blur-3xl dark:from-blue-900/30 dark:to-indigo-900/30" />
            </div>

            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                    <!-- Left column -->
                    <div class="max-w-xl">
                        <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                            <MapPin class="h-4 w-4" />
                            Built for Malawian schools
                        </div>

                        <h1 class="mt-8 text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">
                            Modern school
                            <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                operations
                            </span>
                            for Malawi
                        </h1>

                        <p class="mt-6 text-lg leading-relaxed text-neutral-600 dark:text-neutral-400">
                            Admissions, academics, fees, and communication in one platform—designed for Malawi's term structure, grading, MK currency, and reporting.
                        </p>

                        <div class="mt-10 flex flex-wrap items-center gap-4">
                            <Link
                                :href="registerUrl"
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3.5 text-base font-semibold text-white shadow-lg shadow-blue-500/30 transition-all hover:shadow-xl hover:shadow-blue-500/40"
                            >
                                Start free trial
                                <ArrowRight class="h-5 w-5" />
                            </Link>
                            <Link
                                :href="loginUrl"
                                class="inline-flex items-center gap-2 rounded-xl border-2 border-neutral-200 bg-white px-6 py-3.5 text-base font-semibold text-neutral-700 transition-all hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:border-neutral-600"
                            >
                                <Play class="h-4 w-4" />
                                Watch demo
                            </Link>
                        </div>

                        <p class="mt-5 text-sm text-neutral-500">
                            No credit card required. Free 14-day trial.
                        </p>

                        <!-- Stats -->
                        <div class="mt-12 flex items-center gap-8 border-t border-neutral-200 pt-8 dark:border-neutral-800">
                            <div>
                                <div class="text-3xl font-bold text-neutral-900 dark:text-white">5,000+</div>
                                <div class="mt-1 text-sm text-neutral-500">Students managed</div>
                            </div>
                            <div class="h-12 w-px bg-neutral-200 dark:bg-neutral-800" />
                            <div>
                                <div class="text-3xl font-bold text-neutral-900 dark:text-white">50+</div>
                                <div class="mt-1 text-sm text-neutral-500">Schools onboarded</div>
                            </div>
                            <div class="h-12 w-px bg-neutral-200 dark:bg-neutral-800" />
                            <div>
                                <div class="text-3xl font-bold text-neutral-900 dark:text-white">99.9%</div>
                                <div class="mt-1 text-sm text-neutral-500">Uptime</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right column - Dashboard mockup -->
                    <div class="relative lg:ml-8">
                        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-blue-500/20 via-indigo-500/20 to-purple-500/20 blur-2xl" />
                        <div class="relative overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
                            <!-- Browser chrome -->
                            <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-100 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-800">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full bg-red-400" />
                                    <div class="h-3 w-3 rounded-full bg-yellow-400" />
                                    <div class="h-3 w-3 rounded-full bg-green-400" />
                                </div>
                                <div class="flex-1 px-4">
                                    <div class="mx-auto max-w-xs rounded-md bg-white px-3 py-1 text-center text-xs text-neutral-400 dark:bg-neutral-700">
                                        app.schoolmw.com/dashboard
                                    </div>
                                </div>
                                <div class="w-14" />
                            </div>

                            <!-- Dashboard content -->
                            <div class="p-4">
                                <!-- Header -->
                                <div class="mb-4 flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-neutral-900 dark:text-white">Lilongwe Academy</div>
                                        <div class="text-xs text-neutral-500">Term 1, 2026</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800">
                                            <Bell class="h-4 w-4 text-neutral-500" />
                                        </div>
                                        <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-500" />
                                    </div>
                                </div>

                                <!-- Stats grid -->
                                <div class="mb-4 grid grid-cols-3 gap-3">
                                    <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/50">
                                        <div class="flex items-center gap-2">
                                            <Users class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                            <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Attendance</span>
                                        </div>
                                        <div class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-300">96%</div>
                                        <div class="mt-1 flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                                            <TrendingUp class="h-3 w-3" />
                                            +2.4%
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-blue-50 p-3 dark:bg-blue-950/50">
                                        <div class="flex items-center gap-2">
                                            <CreditCard class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                            <span class="text-xs font-medium text-blue-600 dark:text-blue-400">Fees</span>
                                        </div>
                                        <div class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300">MK 8.2M</div>
                                        <div class="mt-1 text-xs text-blue-600 dark:text-blue-400">This term</div>
                                    </div>
                                    <div class="rounded-xl bg-purple-50 p-3 dark:bg-purple-950/50">
                                        <div class="flex items-center gap-2">
                                            <FileText class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                            <span class="text-xs font-medium text-purple-600 dark:text-purple-400">Reports</span>
                                        </div>
                                        <div class="mt-2 text-2xl font-bold text-purple-700 dark:text-purple-300">412</div>
                                        <div class="mt-1 text-xs text-purple-600 dark:text-purple-400">Generated</div>
                                    </div>
                                </div>

                                <!-- Chart placeholder -->
                                <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-700 dark:bg-neutral-800">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">Weekly Attendance</span>
                                        <span class="text-xs text-neutral-500">This week</span>
                                    </div>
                                    <div class="flex h-20 items-end gap-2">
                                        <div class="flex-1 rounded-t bg-blue-200 dark:bg-blue-700" style="height: 60%" />
                                        <div class="flex-1 rounded-t bg-blue-300 dark:bg-blue-600" style="height: 75%" />
                                        <div class="flex-1 rounded-t bg-blue-400 dark:bg-blue-500" style="height: 85%" />
                                        <div class="flex-1 rounded-t bg-blue-500 dark:bg-blue-400" style="height: 90%" />
                                        <div class="flex-1 rounded-t bg-blue-600 dark:bg-blue-300" style="height: 95%" />
                                    </div>
                                    <div class="mt-2 flex justify-between text-[10px] text-neutral-400">
                                        <span>Mon</span>
                                        <span>Tue</span>
                                        <span>Wed</span>
                                        <span>Thu</span>
                                        <span>Fri</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pain Points -->
        <section class="border-y border-neutral-100 bg-neutral-50 py-20 dark:border-neutral-800 dark:bg-neutral-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                            Move away from paper, spreadsheets, and WhatsApp
                        </h2>
                        <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-400">
                            Many Malawian schools juggle admissions lists, fee tracking, and term reports across multiple tools. This slows down staff and hides what is really happening in your school.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="group rounded-2xl border border-neutral-200 bg-white p-5 transition-all hover:border-red-200 hover:shadow-lg dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-red-800">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 text-red-600 transition-colors group-hover:bg-red-500 group-hover:text-white dark:bg-red-900/30 dark:text-red-400">
                                <ClipboardList class="h-6 w-6" />
                            </div>
                            <h3 class="mt-4 font-semibold">Admissions</h3>
                            <p class="mt-2 text-sm text-neutral-500">Manual forms, lost documents, and unclear intake limits.</p>
                        </div>
                        <div class="group rounded-2xl border border-neutral-200 bg-white p-5 transition-all hover:border-red-200 hover:shadow-lg dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-red-800">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 text-red-600 transition-colors group-hover:bg-red-500 group-hover:text-white dark:bg-red-900/30 dark:text-red-400">
                                <CreditCard class="h-6 w-6" />
                            </div>
                            <h3 class="mt-4 font-semibold">Fees</h3>
                            <p class="mt-2 text-sm text-neutral-500">Fee balances in spreadsheets, delayed statements.</p>
                        </div>
                        <div class="group rounded-2xl border border-neutral-200 bg-white p-5 transition-all hover:border-red-200 hover:shadow-lg dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-red-800">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 text-red-600 transition-colors group-hover:bg-red-500 group-hover:text-white dark:bg-red-900/30 dark:text-red-400">
                                <BarChart3 class="h-6 w-6" />
                            </div>
                            <h3 class="mt-4 font-semibold">Reporting</h3>
                            <p class="mt-2 text-sm text-neutral-500">Term reports that take days to compile and share.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Everything your school needs</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-neutral-600 dark:text-neutral-400">
                        A cohesive system across Admissions, Academics, Finance, and Communications—designed for Malawi's curriculum and term structure.
                    </p>
                </div>

                <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="group rounded-2xl border border-neutral-200 bg-white p-6 transition-all hover:-translate-y-1 hover:shadow-xl dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <div :class="[feature.color, 'flex h-12 w-12 items-center justify-center rounded-xl text-white']">
                            <component :is="feature.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-4 text-lg font-semibold">{{ feature.title }}</h3>
                        <ul class="mt-4 space-y-2">
                            <li v-for="item in feature.items" :key="item" class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                                <Check class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it Works -->
        <section id="how-it-works" class="border-y border-neutral-100 bg-gradient-to-b from-neutral-50 to-white py-20 dark:border-neutral-800 dark:from-neutral-900/50 dark:to-neutral-950 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Get started in three steps</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-neutral-600 dark:text-neutral-400">
                        Set up a new school in minutes. Invite your staff, import students, and start the first term without needing an IT department.
                    </p>
                </div>

                <div class="mt-16 grid gap-8 lg:grid-cols-3">
                    <div class="relative rounded-2xl border border-neutral-200 bg-white p-8 dark:border-neutral-800 dark:bg-neutral-900">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-xl font-bold text-white">
                            1
                        </div>
                        <h3 class="mt-6 text-xl font-semibold">Add your school</h3>
                        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                            Capture basic details, define your term structure, and set up classes and streams.
                        </p>
                    </div>
                    <div class="relative rounded-2xl border border-neutral-200 bg-white p-8 dark:border-neutral-800 dark:bg-neutral-900">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-xl font-bold text-white">
                            2
                        </div>
                        <h3 class="mt-6 text-xl font-semibold">Import students & staff</h3>
                        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                            Use simple CSV templates or add records manually. Invite staff with role-based access.
                        </p>
                    </div>
                    <div class="relative rounded-2xl border border-neutral-200 bg-white p-8 dark:border-neutral-800 dark:bg-neutral-900">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-xl font-bold text-white">
                            3
                        </div>
                        <h3 class="mt-6 text-xl font-semibold">Run your first term</h3>
                        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                            Track admissions, collect fees, record attendance, and generate reports all in one place.
                        </p>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <Link
                        :href="registerUrl"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-blue-500/30 transition-all hover:shadow-xl hover:shadow-blue-500/40"
                    >
                        Start your free trial
                        <ArrowRight class="h-5 w-5" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Roles -->
        <section id="solutions" class="py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Built for every role</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-neutral-600 dark:text-neutral-400">
                        From registrars to teachers to administrators—everyone has the tools they need.
                    </p>
                </div>

                <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="role in roles"
                        :key="role.title"
                        class="group rounded-2xl border border-neutral-200 bg-white p-6 transition-all hover:border-blue-200 hover:shadow-lg dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-blue-800"
                    >
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white dark:bg-blue-900/30 dark:text-blue-400 dark:group-hover:bg-blue-600">
                            <component :is="role.icon" class="h-7 w-7" />
                        </div>
                        <h3 class="mt-4 text-lg font-semibold">{{ role.title }}</h3>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">{{ role.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security -->
        <section id="security" class="bg-neutral-900 py-20 text-white sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Built for security & reliability</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-neutral-400">
                        Enterprise-grade security with features designed specifically for Malawian schools.
                    </p>
                </div>

                <div class="mt-16 grid gap-8 lg:grid-cols-3">
                    <div class="rounded-2xl border border-neutral-800 bg-neutral-800/50 p-8">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-500/20 text-blue-400">
                            <Shield class="h-7 w-7" />
                        </div>
                        <h3 class="mt-6 text-xl font-semibold">Security that scales</h3>
                        <p class="mt-2 text-neutral-400">
                            Two-factor authentication, role permissions, audit trails, and strict tenant isolation.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-neutral-800 bg-neutral-800/50 p-8">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/20 text-emerald-400">
                            <MapPin class="h-7 w-7" />
                        </div>
                        <h3 class="mt-6 text-xl font-semibold">Malawi-ready</h3>
                        <p class="mt-2 text-neutral-400">
                            Term structures, grading expectations, MK currency, SMS routing, and government exports.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-neutral-800 bg-neutral-800/50 p-8">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500/20 text-amber-400">
                            <Zap class="h-7 w-7" />
                        </div>
                        <h3 class="mt-6 text-xl font-semibold">Reliable operations</h3>
                        <p class="mt-2 text-neutral-400">
                            Background jobs for PDFs, imports, and messaging. Fast dashboards with smart caching.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="py-20 sm:py-28">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Frequently asked questions</h2>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-2">
                    <div
                        v-for="faq in faqs"
                        :key="faq.question"
                        class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900"
                    >
                        <h3 class="font-semibold">{{ faq.question }}</h3>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">{{ faq.answer }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="bg-gradient-to-r from-blue-600 to-indigo-600 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-8 lg:flex-row">
                    <div class="text-center lg:text-left">
                        <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to modernize your school?</h2>
                        <p class="mt-2 text-lg text-blue-100">Start a free trial today and see how quickly your team adapts.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link
                            :href="registerUrl"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-semibold text-blue-600 shadow-lg transition-all hover:bg-blue-50"
                        >
                            Start free trial
                            <ArrowRight class="h-5 w-5" />
                        </Link>
                        <Link
                            :href="loginUrl"
                            class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 px-8 py-4 text-base font-semibold text-white transition-all hover:bg-white/10"
                        >
                            Sign in
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-neutral-200 bg-white py-12 dark:border-neutral-800 dark:bg-neutral-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600">
                            <GraduationCap class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-lg font-bold">
                            School<span class="text-blue-600">MW</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-6 text-sm text-neutral-500">
                        <a href="#" class="hover:text-neutral-900 dark:hover:text-white">Privacy</a>
                        <a href="#" class="hover:text-neutral-900 dark:hover:text-white">Terms</a>
                        <a href="#" class="hover:text-neutral-900 dark:hover:text-white">Contact</a>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            :href="loginUrl"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800"
                        >
                            Sign in
                        </Link>
                        <Link
                            :href="registerUrl"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
                <div class="mt-8 text-center text-sm text-neutral-400">
                    &copy; {{ new Date().getFullYear() }} SchoolMW. Built for Malawian schools.
                </div>
            </div>
        </footer>
    </div>
</template>
