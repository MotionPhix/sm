<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { type BreadcrumbItem } from '@/types';
    import { redirect } from '@/routes/dashboard';
    import { Head } from '@inertiajs/vue3';
    
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: redirect().url,
        },
    ];
    </script>
    
    <template>
        <Head title="Dashboard" />
    
        <AppLayout :breadcrumbs="breadcrumbs">
            <section class="p-5">
                <h1 class="text-2xl font-semibold">
                    Dashboard (School Context Coming Next)
                </h1>

                <pre>
                    {{ $page.props.activeSchool }}
                </pre>
            </section>
        </AppLayout>
    </template>
    