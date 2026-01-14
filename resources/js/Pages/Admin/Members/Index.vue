<template>
  <AppLayout>
    <template #header>{{ t('members.member_list') }}</template>
    <div dir="rtl">
      <!-- 検索 トリガーボタン -->
        <div class="relative size-4 ...">
          <div class="absolute start-0 top-0 size-14 ...">
              <button
              @click="openDrawer = true"
              class="p-2 rounded hover:bg-gray-200 flex items-center justify-center"
            >
              <MagnifyingGlassIcon class="w-5 h-5 text-gray-600" />
            </button>
          </div>
        </div>
    </div>

    <div class="p-6">
      <!-- 右側 Drawer -->
      <div v-if="openDrawer" class="fixed inset-0 z-40">
        <!-- 背景オーバーレイ -->
        <div class="absolute inset-0 bg-black bg-opacity-30" @click="openDrawer = false"></div>

        <!-- 右側 Drawer -->
        <aside
          class="absolute top-0 right-0 h-full bg-white shadow-lg z-50 flex flex-col transition-all duration-300 overflow-hidden"
          :style="{ width: openDrawer ? '20rem' : '0rem' }"
        >      
          <div class="p-4 flex justify-between items-center border-b">
            <h2 class="text-lg font-bold">{{ t('search') }}</h2>
            <button @click="openDrawer = false" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>

          <div class="p-4 space-y-3">
            <select v-if="isSuperAdmin" v-model="form.tenant_id" class="border rounded px-3 py-2 w-full">
              <option value="">{{ t('please_select') }}</option>
              <option v-for="t in tenants" :key="t.id" :value="t.id">
                {{ t.name }}
              </option>
            </select>
            <!-- 既存 form をそのまま利用 -->
            <input v-model="form.code" type="text" :placeholder="t('code')" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.name" type="text" :placeholder="t('name')" class="border rounded px-3 py-2 w-full" />
            <select v-model="form.process_id" class="border rounded px-3 py-2 w-full">
              <option value="">{{ t('please_select') }}</option>
              <option v-for="p in processes" :key="p.id" :value="p.id">
                {{ p.name }}
              </option>
            </select>
            <select v-model="form.measurement" class="border rounded px-3 py-2 w-full">
              <option :value="null">{{ t('please_select')}}</option>
              <option value="0">{{ t('dont') }}</option>
              <option value="1">{{ t('do') }}</option>
            </select>

            <div class="flex justify-end space-x-2 mt-4">
              <button @click="submitSearch(); openDrawer = false"
                      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                {{ t('search') }}
              </button>
              <button @click="openDrawer = false"
                      class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                {{ t('close') }}
              </button>
            </div>
          </div>
        </aside>
      </div>       

      <div class="flex flex-wrap md:flex-nowrap md:justify-between mb-4 items-center gap-2">

        <!-- per_page + add -->
        <div class="flex items-center gap-2">
          <select
            v-model.number="form.per_page"
            @change="submitSearch"
            class="border rounded px-3 py-2 w-16 h-10"
          >
            <option v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</option>
          </select>

        </div>

        <!-- 複数削除ボタン -->
        <button
          @click="bulkDelete"
          :disabled="selectedIds.length === 0"
          class="px-4 h-10 bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50 flex items-center space-x-1"
        >
          <TrashIcon class="w-4 h-4"/>
          <span>{{ t('delete_selected') }}</span>
        </button>
      </div>

      <!-- 会員一覧テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <th v-if="isSuperAdmin">{{ t('tenant') }}</th>            
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('company_name')">
              {{ t('members.company_name') }}
              <span v-if="form.sort==='company_name'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('representative')">
              {{ t('members.applicant') }}
              <span v-if="form.sort==='representative'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('tel')">
              {{ t('members.tel') }}
              <span v-if="form.sort==='tel'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('address')">
              {{ t('members.address') }}
              <span v-if="form.sort==='address'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2">{{ t('updated_at') }}</th>
            <th class="px-3 py-2 text-center cursor-pointer" @click="sortBy('status')">
              {{ t('members.status') }}
              <span v-if="form.sort==='status'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>  
            <th class="px-3 py-2 text-center cursor-pointer" @click="sortBy('progress')">
              {{ t('members.progress') }}
              <span v-if="form.sort==='progress'">{{ form.direction==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 text-center">{{ t('history_certificate') }}</th>
            <th class="px-3 py-2 text-center">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="member in members.data" :key="member.id" class="odd:bg-white even:bg-gray-100">

            <td class="px-3 py-2">
              <input type="checkbox" :value="member.id" v-model="selectedIds" />
            </td>
            <td v-if="isSuperAdmin">
              {{ tenants.find(t => t.id === member.tenant_id)?.name || '-' }}
            </td>            
            <td class="px-3 py-2">{{ member.organization?.name ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.name ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.tel ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.address ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.created_at ? dayjs(member.created_at).format('YYYY/MM/DD') : '' }}</td>
            <td class="px-3 py-2">{{ member.status.name }}</td>
            <td class="px-3 py-2">{{ member.progress.name }}</td>
            <td class="px-3 py-2 text-center">
              <img
                v-if="member.history_certificate.thumbnail_path"
                :src="member.history_certificate.thumbnail_path"
                class="w-10 h-10 object-contain border rounded cursor-pointer hover:opacity-80"
                @click="openPdf(member.history_certificate_path)"
              />
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>            
            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <Link :href="route('admin.member.show', { member: member.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <PencilIcon class="w-4 h-4"/>
              </Link -->
              <button @click="deletemember(member.id)" class="text-red-500 hover:text-red-700">
                <TrashIcon class="w-4 h-4"/>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="members" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>
    <!-- PDF プレビューモーダル -->
    <div v-if="previewPdf" class="fixed inset-0 z-50 flex items-center justify-center">
      <div
        class="absolute inset-0 bg-black bg-opacity-60"
        @click="previewPdf = null"
      ></div>

      <div class="relative bg-white w-[90vw] h-[90vh] rounded shadow-lg overflow-hidden">
        <iframe
          :src="previewPdf"
          class="w-full h-full"
        ></iframe>

        <button
          class="absolute top-2 right-2 bg-white rounded-full px-2 py-1 shadow"
          @click="previewPdf = null"
        >
          ✕
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentDuplicateIcon} from '@heroicons/vue/24/outline'

const props = defineProps({
  members: Object,
  user: Object,
  tenants: Array,
  processes: Array,
  filters: {
    type: Object,
    default: () => ({
      company_name: '', representative: '', tel: '', tenant_id: '',
      per_page: 20, sort: 'id', direction: 'asc', page: 1
    })
  }
})
console.log('引継',props.members)

const { t } = useI18n()

const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super admin')
)

// 検索フォーム・per_page・sort・directionを reactive で管理
const openDrawer = ref(false)

// 複数検索用に reactive 拡張
const form = reactive({
  name: props.filters.name,
  tenant_id: props.filters.tenant_id,
  per_page: props.filters.per_page || 20,
  sort: props.filters.sort,
  direction: props.filters.direction  
})
// 選択削除
const selectedIds = ref([])

const toggleSelectAll = (checked) => {
  selectedIds.value = checked ? props.members.data.map(s => s.id) : []
}

const resetSelectedIds = () => {
  selectedIds.value = []
}

const selectAll = computed({
  get() {
    return selectedIds.value.length === props.members.data.length
  }
})

watch(() => props.members.current_page, () => {
  selectedIds.value = []
})


// persistQueryに各検索項目を追加
const persistQuery = () => ({
  tenant_id: form.tenant_id,
  company_name: form.company_name,
  representative: form.representative,
  tel: form.tel,
  per_page: form.per_page,
  sort_by: form.sort,
  sort_dir: form.direction,
  page: props.members.current_page
})

const submitSearch = () => {
  console.log(persistQuery())
  router.get(route('admin.member.index'), { ...persistQuery(), page: 1 }, {
    preserveState: true,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}

// ページ番号クリック
const goPage = (page) => {
  router.get(route('admin.member.index'), { ...persistQuery(), page }, {
    preserveState: true,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}

// 列ヘッダクリックでソート
const sortBy = (field) => {
  if (form.sort === field) form.direction = form.direction==='asc'?'desc':'asc'
  else { form.sort = field; form.direction = 'asc' }
  submitSearch()
}

// 行単位削除
const deletemember = (member_id) => {
  if (!confirm(t('confirm_delete'))) return
  router.delete(route('admin.member.destroy', member_id), {
    preserveState: true,
    onSuccess: () => {
      router.get(route('admin.member.index'), { ...persistQuery(), page: props.members.current_page }, { preserveState: true })
    }
  })
}
// 複数削除
const bulkDelete = () => {
  if (!confirm(t('confirm_delete_selected'))) return
  router.post(
    route('members.bulkDelete'),
    { ids: selectedIds.value },
    {
      preserveState: true,
      onSuccess: () => {
        // 削除後に検索条件・ページを保持して再取得
        router.get(route('admin.member.index'), { ...persistQuery(), page: props.members.current_page }, { preserveState: true })
      }
    }
  )
}
const previewPdf = ref(null)

const openPdf = (pdfPath) => {
  previewPdf.value = pdfPath
}

// 表示件数計算
const startItem = computed(() => props.members.per_page * (props.members.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.members.per_page * props.members.current_page, props.members.total))
</script>