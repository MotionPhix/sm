<script setup lang="ts">
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { toUrl } from '@/lib/utils'
import { usePage } from '@inertiajs/vue3'
import { type NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

interface Props {
    title: string
    description: string
    items: NavItem[]
}

const props = defineProps<Props>()
const page = usePage()

const isItemActive = computed(() => (item: any) => {
    // Don't match empty or hash-only hrefs
    if (!item.href || item.href === '#') {
        return false
    }
    
    const currentPath = page.url.split('?')[0].split('#')[0]
    const itemPath = toUrl(item.href).split('?')[0].split('#')[0]
    
    // Check if current path matches this item (exact or child)
    const isMatch = currentPath === itemPath || currentPath.startsWith(itemPath + '/')
    
    if (!isMatch) {
        return false
    }
    
    // If it matches, check if any sibling is a better match (longer path = more specific)
    for (const sibling of props.items) {
        if (sibling.href === item.href) continue
        
        const siblingPath = toUrl(sibling.href).split('?')[0].split('#')[0]
        const siblingMatches = currentPath === siblingPath || currentPath.startsWith(siblingPath + '/')
        
        // If sibling also matches AND is longer/more specific, this item is not active
        if (siblingMatches && siblingPath.length > itemPath.length) {
            return false
        }
    }
    
    return true
})
</script>

<template>
    <div class="px-4 py-6">
        <Heading :title="props.title" :description="props.description" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <!-- Sidebar Navigation -->
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Link
                        v-for="item in props.items"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start text-left',
                            {
                                'bg-muted text-foreground font-medium': isItemActive(item),
                            },
                            {
                                'text-muted-foreground hover:text-foreground': !isItemActive(item),
                            },
                        ]"
                        :as="Button"
                        :href="item.href">
                        <component v-if="item.icon" :is="item.icon" />
                        {{ item.title }}
                    </Link>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <!-- Main Content -->
            <div class="flex-1 md:max-w-4xl">
                <section class="space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
