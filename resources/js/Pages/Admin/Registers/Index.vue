<template>
  <AppLayout>
    <template #header>{{ t('preregisters.list') }}</template>
    <div class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-5 items-center gap-2 mb-4">
        <div>
          <select
            v-model.number="form.per_page"
            @change="submitSearch"
            class="border rounded px-3 py-2 h-10 w-24"
          >
            <option v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</option>
          </select>
        </div>
        <div class="md:text-right whitespace-nowrap">
          {{ t('total') }} : {{ page.props.preregisters.total }}
        </div>
      </div>

      <!-- 会員一覧テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('agent')">
              {{ t('members.agent') }}
              <span v-if="form.sort_by==='agent'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>            
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('email')">
              {{ t('registers.email') }}
              <span v-if="form.sort_by==='email'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('expires_at')">
              {{ t('preregisters.expires_at') }}
              <span v-if="form.sort_by==='expires_at'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('verified_at')">
              {{ t('preregisters.verified_at') }}
              <span v-if="form.sort_by==='verified_at'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('created_at')">
              {{ t('updated_at') }}
              <span v-if="form.sort_by==='created_at'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="preregister in props.preregisters?.data" :key="preregister.id" class="odd:bg-white even:bg-gray-100">

            <td class="px-3 py-2">{{ preregister.agent === 1 ? '代理申請' : '本人申請' }}</td>
            <td class="px-3 py-2">{{ preregister.email ?? '-' }}</td>
            <td class="px-3 py-2">{{ preregister.expires_at ? dayjs(preregister.expires_at).format('YYYY-MM-DD HH:mm') : '-' }}</td>
            <td class="px-3 py-2">{{ preregister.verified_at ? dayjs(preregister.verified_at).format('YYYY-MM-DD HH:mm') : '-' }}</td>
            <td class="px-3 py-2">{{ preregister.created_at ? dayjs(preregister.created_at).format('YYYY-MM-DD HH:mm') : '-' }}</td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="props.preregisters" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import axios from 'axios'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { ArrowDownTrayIcon, PencilIcon, EyeIcon, MagnifyingGlassIcon, DocumentPlusIcon} from '@heroicons/vue/24/outline'

const { t } = useI18n()

const page = usePage()

const user = page.props.auth.user

const isSuperAdmin = computed(() =>
  user?.roles?.some(r => r.name.toLowerCase() === 'super_admin')
)

const props = defineProps({
  preregisters: Object, 
  filters: Object,
})

const form = useForm({
  per_page: props.filters?.per_page || 20,
  sort_by: props.filters?.sort_by || 'created_at', 
  sort_dir: props.filters?.sort_dir || 'desc',
})

console.log(props)

// persistQueryに各検索項目を追加
const persistQuery = () => ({
  per_page: form.per_page,
  sort_by: form.sort_by,
  sort_dir: form.sort_dir,
})
  


const submitSearch = () => {
  console.log(form)
  console.log(persistQuery())
  router.get(route('admin.registers.index'), { ...persistQuery(), page: 1 }, {
    preserveState: false,
  })
}

// ページ番号クリック
const goPage = (page) => {
  router.get(route('admin.registers.index'), { ...persistQuery(), page }, {
    preserveState: false,
  })
}

// 列ヘッダクリックでソート
const sortBy = (field) => {
  if (form.sort_by === field) form.sort_dir = form.sort_dir==='asc'?'desc':'asc'
  else { form.sort_by = field; form.sort_dir = 'desc' }
  submitSearch()
}

</script>