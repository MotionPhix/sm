<script setup lang="ts">
import { ChevronsUpDown, Plus } from 'lucide-vue-next'
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { store } from '@/routes/schools/select'
import { create as createSchool } from '@/routes/onboarding/school-setup'

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from '@/components/ui/sidebar'

const page = usePage()
const { isMobile } = useSidebar()

const user = computed(() => page.props.auth.user)

const schools = computed(() => user.value?.schools ?? [])
const activeSchool = computed(() => user.value?.activeSchool)

const isAdmin = computed(() =>
  ['admin', 'super_admin'].includes(user.value?.role)
)

function switchSchool(id: number) {
  router.post(store().url, { school_id: id }, {
      preserveState: false,
  })
}

function goToCreateSchool() {
  router.visit(createSchool().url)
}
</script>

<template>
  <SidebarMenu v-if="schools.length">
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton size="lg">
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-medium">
                {{ activeSchool?.name }}
              </span>
              <span class="truncate text-xs text-muted-foreground">
                {{ user.role_label }}
              </span>
            </div>
            <ChevronsUpDown class="ml-auto" />
          </SidebarMenuButton>
        </DropdownMenuTrigger>

        <DropdownMenuContent
          class="min-w-56 rounded-lg"
          align="start"
          :side="isMobile ? 'bottom' : 'right'"
          :side-offset="4">
          <DropdownMenuLabel class="text-xs text-muted-foreground">
            Schools
          </DropdownMenuLabel>

          <DropdownMenuItem
            v-for="school in schools"
            :key="school.id"
            @click="switchSchool(school.id)"
            class="cursor-pointer">
            {{ school.name }}
          </DropdownMenuItem>

          <template v-if="isAdmin">
            <DropdownMenuSeparator />
            <DropdownMenuItem
              class="cursor-pointer text-muted-foreground"
              @click="goToCreateSchool">
              <Plus class="mr-2 h-4 w-4" />
              Add School
            </DropdownMenuItem>
          </template>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
