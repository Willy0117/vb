<template>
  <AppLayout>
    <template #header>
      {{ permission ? t('permissions.edit_permission') : t('permissions.create_permission') }}
    </template>

    <div class="p-6">
      <div class="space-y-4">
        <form @submit.prevent="submitForm">
          <!-- Permission Name -->
          <div>
            <label class="block mb-1">{{ t('name') }}</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Permission Name"
              class="border rounded px-3 py-2 w-full"
            />
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
          </div>

          <!-- Tenant 選択 (super_admin のみ) -->
          <div v-if="isSuperAdmin" class="mt-4">
            <label class="block mb-1">{{ t('tenant') }}</label>
            <select v-model="form.tenant_id" class="border rounded px-3 py-2 w-full">
              <option :value="null">{{ t('select_tenant') }}</option>
              <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                {{ tenant.name }}
              </option>
            </select>
          </div>

          <!-- Buttons -->
          <div class="flex space-x-2 mt-6">
            <button
              type="submit"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
              {{ permission ? t('update') : t('create') }}
            </button>
            <button
              @click="router.get(route('admin.permissions.index', filters), { preserveState: true })"
              type="button"
              class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400"
            >
              {{ t('cancel') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  permission: Object,  // null = 新規作成, オブジェクト = 編集
  tenants: Array,      // super_admin のみ
  user: Object,        // 現在のログインユーザー
  filters: Object,      // Index画面の検索条件
  mode: { type: String, default: '' }
})

const { t } = useI18n()

// super_admin 判定
const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super_admin')
)

// フォーム初期値
const form = reactive({
  name: props.permission ? props.permission.name : '',
  tenant_id: props.permission
    ? props.permission.tenant_id
    : (isSuperAdmin.value ? null : props.user?.tenant_id ?? null)
})

// エラー管理
const errors = reactive({
  name: ''
})

// 送信処理
const submitForm = () => {
  if (props.permission && props.mode !== 'copy') {
    // 編集
    router.put(route('admin.permissions.update', props.permission.id), form, {
      preserveState: true,
      onError: (err) => Object.assign(errors, err),
      onSuccess: () => router.get(route('admin.permissions.index', props.filters))
    })
  } else {
    // 新規作成
    router.post(route('admin.permissions.store'), form, {
      preserveState: true,
      onError: (err) => Object.assign(errors, err),
      onSuccess: () => router.get(route('admin.permissions.index', props.filters))
    })
  }
}
</script>
