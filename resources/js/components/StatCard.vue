<script setup lang="ts">
import { TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';
import { Card, CardContent } from '@/components/ui/card';
import type { LucideIcon } from 'lucide-vue-next';

interface Props {
    title: string;
    value: string | number;
    subtitle?: string;
    icon?: LucideIcon;
    trend?: number; // Percentage change
    trendLabel?: string;
    variant?: 'default' | 'primary' | 'success' | 'warning' | 'danger';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
});

const variantClasses: Record<string, string> = {
    default: 'bg-card',
    primary: 'bg-gradient-to-br from-blue-500 to-blue-600 text-white',
    success: 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white',
    warning: 'bg-gradient-to-br from-amber-500 to-amber-600 text-white',
    danger: 'bg-gradient-to-br from-red-500 to-red-600 text-white',
};

const iconClasses: Record<string, string> = {
    default: 'bg-muted text-muted-foreground',
    primary: 'bg-white/20 text-white',
    success: 'bg-white/20 text-white',
    warning: 'bg-white/20 text-white',
    danger: 'bg-white/20 text-white',
};

const textClasses: Record<string, string> = {
    default: 'text-muted-foreground',
    primary: 'text-white/80',
    success: 'text-white/80',
    warning: 'text-white/80',
    danger: 'text-white/80',
};
</script>

<template>
    <Card :class="variantClasses[variant]">
        <CardContent class="flex flex-col">
            <!-- Icon at top left -->
            <div
                v-if="icon"
                class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl"
                :class="iconClasses[variant]"
            >
                <component :is="icon" class="h-5 w-5" />
            </div>

            <!-- Title -->
            <span
                class="text-sm font-medium"
                :class="textClasses[variant]"
            >
                {{ title }}
            </span>

            <!-- Value -->
            <span class="mt-1 text-2xl font-bold tracking-tight">
                {{ value }}
            </span>

            <!-- Subtitle -->
            <span
                v-if="subtitle"
                class="mt-0.5 text-sm"
                :class="textClasses[variant]"
            >
                {{ subtitle }}
            </span>

            <!-- Trend at bottom with separator -->
            <template v-if="trend !== undefined">
                <div class="mt-4 border-t pt-3" :class="variant === 'default' ? 'border-border' : 'border-white/20'">
                    <div
                        class="flex items-center gap-1.5 text-sm"
                        :class="textClasses[variant]"
                    >
                        <span
                            class="flex items-center gap-0.5 font-medium"
                            :class="{
                                'text-emerald-500': trend > 0 && variant === 'default',
                                'text-red-500': trend < 0 && variant === 'default',
                                'text-white': variant !== 'default',
                            }"
                        >
                            <TrendingUp v-if="trend > 0" class="h-3.5 w-3.5" />
                            <TrendingDown v-else-if="trend < 0" class="h-3.5 w-3.5" />
                            <Minus v-else class="h-3.5 w-3.5" />
                            {{ Math.abs(trend) }}%
                        </span>
                        <span v-if="trendLabel">{{ trendLabel }}</span>
                    </div>
                </div>
            </template>
        </CardContent>
    </Card>
</template>
