<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import { dashboard } from '@/routes';
    import { type BreadcrumbItem } from '@/types';
    import { Head } from '@inertiajs/vue3';
    import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
    
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: dashboard().url,
        },
    ];
    </script>
    
    <template>
        <Head title="Dashboard" />
    
        <AppLayout :breadcrumbs="breadcrumbs">
            <h1 class="text-2xl font-semibold">
                Dashboard (School Context Coming Next)
            </h1>

            <pre>
                {{ $page.props.activeSchool }}
            </pre>
        </AppLayout>
    </template>
    