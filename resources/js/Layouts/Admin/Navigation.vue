<template>
  <aside
    v-if="!isMobile"
    :class="[
      'bg-gray-50 text-gray-800 h-screen flex flex-col overflow-auto transition-all duration-300 z-50',
      collapsed ? 'w-16' : 'w-64'
    ]"
  >
    <!-- 折りたたみボタン -->
    <div class="flex justify-end p-2 flex-none">
      <button @click="toggleCollapse" class="text-gray-500 hover:text-gray-700">
        <span v-if="collapsed">➡</span>
        <span v-else>⬅</span>
      </button>
    </div>

    <!-- メニュー -->
    <nav class="flex-1 px-2 py-4 text-sm">
      <!-- Dashboard -->
      <Link
        :href="route('admin.dashboard')"
        class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        :class="isActive('dashboard') ? 'bg-gray-300 font-semibold' : ''"
      >
        <HomeIcon class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">{{ t('dashboard') }}</span>
      </Link>

      <!-- Members -->
      <Link
        :href="route('admin.member.index')"
        class="flex items-center py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        :class="isActive('members.index') ? 'bg-gray-300 font-semibold' : ''"
      >
        <UsersIcon class="w-5 h-5"/>
        <span v-if="!collapsed" class="ml-2">{{ t('members.member') }}</span>
      </Link>
      <!-- Access Control -->
      <div v-if="showAccessControl" class="mt-2">
        <button
          @click="toggleSubMenu('access')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <ShieldCheckIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('access_control') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'access' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <transition name="slide-fade">
          <div v-show="openSubMenu === 'access' && !collapsed" class="pl-6 mt-1 space-y-1">
            <Link
              v-if="can('manage tenants')"
              :href="route('admin.tenants.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
            >
              <BuildingOfficeIcon class="w-4 h-4 mr-1"/>
              {{ t('tenants') }}
            </Link>
            <Link
              v-if="can('manage roles')"
              :href="route('admin.roles.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
            >
              <UsersIcon class="w-4 h-4 mr-1"/>
              {{ t('roles') }}
            </Link>
            <Link
              v-if="can('manage permissions')"
              :href="route('admin.permissions.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
            >
              <TicketIcon class="w-4 h-4 mr-1"/>
              {{ t('permissions') }}
            </Link>
          </div>
        </transition>
      </div>

      <!-- Users サブメニュー -->
      <div class="mt-2">
        <button
          @click="toggleSubMenu('users')"
          class="flex items-center justify-between w-full py-2 px-2 rounded hover:bg-gray-200 transition-colors"
        >
          <div class="flex items-center">
            <UsersIcon class="w-5 h-5"/>
            <span v-if="!collapsed" class="ml-2">{{ t('user') }}</span>
          </div>
          <svg
            v-if="!collapsed"
            :class="{ 'rotate-90': openSubMenu === 'users' }"
            class="w-4 h-4 transform transition-transform duration-200"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <transition name="slide-fade">
          <div v-show="openSubMenu === 'users' && !collapsed" class="pl-6 mt-1 space-y-1">
            <Link
              :href="route('admin.users.index')"
              class="flex items-center py-2 px-2 rounded hover:bg-gray-100"
              :class="isActive('admin.users.index') ? 'bg-gray-200 font-semibold' : ''"
            >
              <UserIcon class="w-4 h-4 mr-1"/>
              {{ t('user') }}
            </Link>
          </div>
        </transition>
      </div>

    </nav>
  </aside>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  HomeIcon,
  UsersIcon,
  UserIcon,
  ShieldCheckIcon,
  BuildingOfficeIcon,
  ServerIcon,
  TicketIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()

const collapsed = ref(false)
const openSubMenu = ref(null)
const toggleCollapse = () => (collapsed.value = !collapsed.value)
const toggleSubMenu = (menu) => (openSubMenu.value = openSubMenu.value === menu ? null : menu)

const { props } = usePage()
const user = props.auth.user

// i18n
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const isActive = (routeName) => false
const hasApiFeatures = true
const showAccessControl = true
const can = (permission) => true

// ページURLに応じて初期サブメニューを決定
onMounted(() => {
  if (page.url.startsWith('/admin/tenants') ||
      page.url.startsWith('/admin/roles') ||
      page.url.startsWith('/admin/permissions')) {
    openSubMenu.value = 'access'
  }
  if (page.url.startsWith('/admin/users') ) {
    openSubMenu.value = 'users'
  }
})


</script>

<style>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  max-height: 0;
}
.slide-fade-enter-to,
.slide-fade-leave-from {
  opacity: 1;
  max-height: 500px;
}
</style>
