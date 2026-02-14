<template>
  <!-- Sidebar for mobile -->
  <TransitionRoot as="template" :show="sidebarOpen">
    <Dialog
      as="div"
      class="fixed inset-0 z-50 flex md:hidden"
      :open="sidebarOpen"
      static
      @close="sidebarOpen = false"
    >
      <TransitionChild
        as="template"
        enter="transition-opacity ease-linear duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="transition-opacity ease-linear duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-neutral-900/75 transition-opacity dark:bg-neutral-700/90"
        />
      </TransitionChild>

      <TransitionChild
        as="template"
        enter="transition ease-in-out duration-300 transform"
        enter-from="-translate-x-full rtl:translate-x-full"
        enter-to="translate-x-0"
        leave="transition ease-in-out duration-300 transform"
        leave-from="translate-x-0"
        leave-to="-translate-x-full rtl:translate-x-full"
      >
        <DialogPanel
          class="relative flex w-56 max-w-xs flex-col bg-[rgb(var(--sidebar-bg-color))] pb-4 pt-5 dark:bg-[rgb(var(--sidebar-dark-bg-color))]"
        >
          <TransitionChild
            as="template"
            enter="ease-in-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in-out duration-300"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="absolute right-0 top-0 -mr-12 pt-2 rtl:right-auto rtl:left-0 rtl:mr-0 rtl:-ml-12">
              <button
                type="button"
                class="ms-1 flex size-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                @click="sidebarOpen = false"
              >
                <span class="sr-only">Close sidebar</span>

                <Icon icon="XSolid" class="size-6 text-white" />
              </button>
            </div>
          </TransitionChild>

          <div class="flex shrink-0 items-center px-4">
            <ILink class="whitespace-normal" :to="{ name: 'dashboard' }" plain>
              <span v-if="!logo" class="font-bold text-white">
                {{ companyName }}
              </span>

              <img v-else class="h-10 max-h-14 w-auto" :src="logo" />
            </ILink>
          </div>

          <UserProfileDropdown />

          <div class="mt-5 h-0 flex-1 overflow-y-auto">
            <nav id="sidebar-sm" class="space-y-0.5 px-2 text-base sm:text-sm">
              <template v-for="item in sidebarItems" :key="item.id">
                <!-- Collapsible Group Item -->
                <div v-if="item.collapsible" class="space-y-0.5">
                  <button
                    type="button"
                    :class="[
                      'group relative flex w-full items-center rounded-md px-2 py-2 text-neutral-50 hover:bg-white/10 focus:outline-none',
                      hasActiveChild(item) ? 'bg-white/10 text-white' : '',
                      'sidebar-sm-item-' + item.id,
                    ]"
                    @click="toggleGroupCollapse(item.id)"
                  >
                    <Icon
                      v-if="item.icon"
                      class="me-4 size-6 shrink-0 text-neutral-300"
                      :icon="item.icon"
                    />

                    {{ item.name }}

                    <IBadge
                      v-if="item.badge"
                      class="absolute -start-px -top-px dark:bg-neutral-900"
                      :variant="item.badgeVariant"
                      :text="item.badge"
                      pill
                    />

                    <Icon
                      class="ms-auto size-4 text-neutral-300"
                      :icon="
                        isGroupCollapsed(item.id)
                          ? 'ChevronRightSolid'
                          : 'ChevronDownSolid'
                      "
                    />
                  </button>

                  <!-- Child Items -->
                  <div
                    v-show="!isGroupCollapsed(item.id)"
                    class="ms-5 mt-0.5 space-y-0.5 border-s border-neutral-700 ps-1.5"
                  >
                    <ILink
                      v-for="child in item.children"
                      :key="child.id"
                      active-class="bg-white/10 text-white"
                      inactive-class="text-neutral-50 hover:bg-white/10"
                      :class="[
                        'group relative flex items-center rounded-md px-2 py-2 text-sm focus:outline-none',
                        'sidebar-sm-child-item-' + child.id,
                      ]"
                      :to="child.route"
                      plain
                    >
                      <Icon
                        v-if="child.icon"
                        class="me-3 size-5 shrink-0 text-neutral-300"
                        :icon="child.icon"
                      />

                      {{ child.name }}

                      <IBadge
                        v-if="child.badge"
                        class="absolute -start-px -top-px dark:bg-neutral-900"
                        :variant="child.badgeVariant"
                        :text="child.badge"
                        pill
                      />

                      <ILink
                        v-if="child.inQuickCreate"
                        :to="child.quickCreateRoute"
                        :class="[
                          'ms-auto rounded-md hover:bg-neutral-800 dark:hover:bg-neutral-900',
                          $route.path === child.quickCreateRoute
                            ? 'hidden'
                            : '',
                        ]"
                        plain
                      >
                        <Icon icon="PlusSolid" class="size-4" />
                      </ILink>
                    </ILink>
                  </div>
                </div>

                <!-- Regular Menu Item -->
                <ILink
                  v-else
                  active-class="bg-white/10 text-white"
                  inactive-class="text-neutral-50 hover:bg-white/10"
                  :class="[
                    'group relative flex items-center rounded-md px-2 py-2 focus:outline-none',
                    'sidebar-sm-item-' + item.id,
                  ]"
                  :to="item.route"
                  plain
                >
                  <Icon
                    v-if="item.icon"
                    class="me-4 size-6 shrink-0 text-neutral-300"
                    :icon="item.icon"
                  />

                  {{ item.name }}

                  <IBadge
                    v-if="item.badge"
                    class="absolute -start-px -top-px dark:bg-neutral-900"
                    :variant="item.badgeVariant"
                    :text="item.badge"
                    pill
                  />

                  <ILink
                    v-if="item.inQuickCreate"
                    :to="item.quickCreateRoute"
                    :class="[
                      'ms-auto rounded-md hover:bg-neutral-800 dark:hover:bg-neutral-900',
                      $route.path === item.quickCreateRoute ? 'hidden' : '',
                    ]"
                    plain
                  >
                    <Icon icon="PlusSolid" class="size-5" />
                  </ILink>
                </ILink>
              </template>
            </nav>
          </div>

          <TheSidebarMetrics />
        </DialogPanel>
      </TransitionChild>
    </Dialog>
  </TransitionRoot>

  <!-- Static sidebar for desktop -->
  <div
    v-show="['404', '403', 'not-found'].indexOf($route.name) === -1"
    class="hidden bg-[rgb(var(--sidebar-bg-color))] dark:bg-[rgb(var(--sidebar-dark-bg-color))] md:flex md:shrink-0"
  >
    <div class="flex w-56 flex-col">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex grow flex-col overflow-y-auto pb-4 pt-5">
        <div class="flex shrink-0 items-center px-4">
          <ILink class="whitespace-normal" :to="{ name: 'dashboard' }" plain>
            <span v-if="!logo" class="font-bold text-white">
              {{ companyName }}
            </span>

            <img v-else class="h-10 max-h-14 w-auto" :src="logo" />
          </ILink>
        </div>

        <UserProfileDropdown />

        <!-- Sidebar links -->
        <div class="mt-6 flex h-0 flex-1 flex-col overflow-y-auto">
          <nav id="sidebar-lg" class="flex-1 space-y-1 px-2">
            <template v-for="item in sidebarItems" :key="item.id">
              <!-- Collapsible Group Item -->
              <div v-if="item.collapsible" class="space-y-1">
                <button
                  type="button"
                  :class="[
                    'group relative flex w-full items-center rounded-md px-2 py-2 text-sm text-neutral-50 hover:bg-white/10 focus:outline-none',
                    hasActiveChild(item) ? 'bg-white/10 text-white' : '',
                    'sidebar-lg-item-' + item.id,
                  ]"
                  @click="toggleGroupCollapse(item.id)"
                >
                  <Icon
                    v-if="item.icon"
                    class="me-3 size-6 shrink-0 text-neutral-300"
                    :icon="item.icon"
                  />

                  {{ item.name }}

                  <IBadge
                    v-if="item.badge"
                    class="absolute -start-px -top-px dark:bg-neutral-900"
                    :variant="item.badgeVariant"
                    :text="item.badge"
                    pill
                  />

                  <Icon
                    class="ms-auto size-4 text-neutral-300"
                    :icon="
                      isGroupCollapsed(item.id)
                        ? 'ChevronRightSolid'
                        : 'ChevronDownSolid'
                    "
                  />
                </button>

                <!-- Child Items -->
                <div
                  v-show="!isGroupCollapsed(item.id)"
                  class="ms-[18px] mt-0.5 space-y-1 border-s border-neutral-700 ps-1.5"
                >
                  <ILink
                    v-for="child in item.children"
                    :key="child.id"
                    active-class="bg-white/10 text-white"
                    inactive-class="text-neutral-50 hover:bg-white/10"
                    :class="[
                      'group relative flex items-center rounded-md px-2 py-2 text-sm focus:outline-none',
                      'sidebar-lg-child-item-' + child.id,
                    ]"
                    :to="child.route"
                    plain
                  >
                    <Icon
                      v-if="child.icon"
                      class="me-3 size-5 shrink-0 text-neutral-300"
                      :icon="child.icon"
                    />

                    {{ child.name }}

                    <IBadge
                      v-if="child.badge"
                      class="absolute -start-px -top-px dark:bg-neutral-900"
                      :variant="child.badgeVariant"
                      :text="child.badge"
                      pill
                    />

                    <ILink
                      v-if="child.inQuickCreate"
                      :to="child.quickCreateRoute"
                      :class="[
                        'ms-auto hidden rounded-md hover:bg-neutral-800 dark:hover:bg-neutral-900',
                        $route.path !== child.quickCreateRoute
                          ? 'group-hover:block'
                          : '',
                      ]"
                      plain
                    >
                      <Icon icon="PlusSolid" class="size-4" />
                    </ILink>
                  </ILink>
                </div>
              </div>

              <!-- Regular Menu Item -->
              <ILink
                v-else
                active-class="bg-white/10 text-white"
                inactive-class="text-neutral-50 hover:bg-white/10"
                :class="[
                  'group relative flex items-center rounded-md px-2 py-2 text-sm focus:outline-none',
                  'sidebar-lg-item-' + item.id,
                ]"
                :to="item.route"
                plain
              >
                <Icon
                  v-if="item.icon"
                  class="me-3 size-6 shrink-0 text-neutral-300"
                  :icon="item.icon"
                />

                {{ item.name }}

                <IBadge
                  v-if="item.badge"
                  class="absolute -start-px -top-px dark:bg-neutral-900"
                  :variant="item.badgeVariant"
                  :text="item.badge"
                  pill
                />

                <ILink
                  v-if="item.inQuickCreate"
                  :to="item.quickCreateRoute"
                  :class="[
                    'ms-auto hidden rounded-md hover:bg-neutral-800 dark:hover:bg-neutral-900',
                    $route.path !== item.quickCreateRoute
                      ? 'group-hover:block'
                      : '',
                  ]"
                  plain
                >
                  <Icon icon="PlusSolid" class="size-5" />
                </ILink>
              </ILink>
            </template>
          </nav>
        </div>

        <TheSidebarMetrics />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'

import { useApp } from '@/Core/composables/useApp'

import TheSidebarMetrics from './TheSidebarMetrics.vue'
import UserProfileDropdown from './UserProfileDropdown.vue'

const { sidebarItems, sidebarOpen, scriptConfig } = useApp()
const route = useRoute()

const companyName = computed(() => scriptConfig('company_name'))
const logo = scriptConfig('logo_light')

// Collapsible state management
const collapsedGroups = ref(new Set())

// Check if a child item is currently active
function isChildActive(child) {
  return route.path === child.route || route.path.startsWith(child.route + '/')
}

// Check if any child in a group is active
function hasActiveChild(item) {
  if (!item.collapsible || !item.children) return false

  return item.children.some(child => isChildActive(child))
}

// Initialize collapsed state and handle active children
function initializeCollapsedState() {
  const newCollapsedGroups = new Set()

  sidebarItems.value.forEach(item => {
    if (item.collapsible) {
      // If group has an active child, don't collapse it
      if (hasActiveChild(item)) {
        // Keep expanded
      } else if (item.defaultCollapsed) {
        newCollapsedGroups.add(item.id)
      }
    }
  })

  collapsedGroups.value = newCollapsedGroups
}

// Initialize collapsed state based on default settings and active routes
onMounted(() => {
  initializeCollapsedState()
})

// Watch for changes in sidebar items to initialize new groups
watch(
  sidebarItems,
  () => {
    initializeCollapsedState()
  },
  { deep: true }
)

// Watch for route changes to expand groups with active children
watch(
  () => route.path,
  () => {
    sidebarItems.value.forEach(item => {
      if (item.collapsible && hasActiveChild(item)) {
        collapsedGroups.value.delete(item.id)
      }
    })
  }
)

function toggleGroupCollapse(groupId) {
  if (collapsedGroups.value.has(groupId)) {
    collapsedGroups.value.delete(groupId)
  } else {
    collapsedGroups.value.add(groupId)
  }
}

function isGroupCollapsed(groupId) {
  return collapsedGroups.value.has(groupId)
}
</script>
