<script setup lang="ts">
    import { Head } from '@inertiajs/vue3'
    import { Button } from '@/components/ui/button'
</script>

<template>
    <Head title="Select School" />

    <div class="max-w-md mx-auto mt-20 space-y-6">
        <h1 class="text-xl font-semibold text-center">
            Select a school
        </h1>

        <form method="post" action="/select-school" class="space-y-4">
            <div
                v-for="school in $page.props.schools"
                :key="school.id">
                <Button
                    type="submit"
                    name="school_id"
                    :value="school.id"
                    class="w-full"
                    variant="outline"
                >
                    {{ school.name }}
                </Button>
            </div>
        </form>
    </div>
</template>
    