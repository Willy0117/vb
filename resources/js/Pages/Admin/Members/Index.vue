<template>
  <AppLayout>
    <template #header>{{ t('members.member_list') }}</template>
    <div class="p-6">
      <div class="mb-4 flex flex-wrap items-center gap-2">
        <div>
          <select
            v-model.number="form.per_page"
            @change="submitSearch"
            class="border rounded px-3 py-2 h-10 w-24"
          >
            <option v-for="n in [10,20,30,50]" :key="n" :value="n">{{ n }}</option>
          </select>
        </div>
        <div>
          <Link
            :href="route('admin.member.create', persistQuery())"
            class="px-4 h-10 bg-green-500 text-white rounded hover:bg-green-600 flex items-center justify-center"
          >
            <UserIcon class="w-4 h-4"/>
            <span>{{ t('members.create') }}</span>
          </Link>
        </div>
        <div class="flex items-center space-x-2 flex-1 min-w-[300px]">

          <!-- フィールド選択 -->
          <select
            v-model="form.field"
            class="h-10 border border-gray-300 rounded-md px-3 text-sm bg-white"
          >
            <optgroup label="会員情報">
              <option value="number">外国人番号</option>
              <option value="last_name">姓</option>
              <option value="first_name">名</option>
              <option value="last_name_kana">セイ（カナ）</option>
              <option value="first_name_kana">メイ（カナ）</option>
            </optgroup>

            <optgroup label="会社情報">
              <option value="company_name">会社名</option>
              <option value="company_kana">会社名カナ</option>
              <option value="representative_name">代表者名</option>
              <option value="representative_kana">代表者カナ</option>
              <option value="tel">TEL</option>
              <option value="note">備考</option>
            </optgroup>
          </select>

          <!-- キーワード -->
          <TextInput
            v-model="form.keyword"
            type="text"
            class="border rounded px-2 py-2"
            placeholder="検索キーワード"
            @keyup.enter="search"
          />

        </div>
        <div>
          <SecondaryButton
            type="button"
            class="flex items-center gap-2 h-10"
            @click="exportCsv"
          >
            <ArrowDownTrayIcon class="w-4 h-4" />
            CSV
          </SecondaryButton> 
        </div>
        <div class="ml-auto whitespace-nowrap">
          {{ t('total') }} : {{ props.members.total }}
        </div>
      </div>

      <!-- 会員一覧テーブル -->
      <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200">
            <th class="px-3 py-2">
              <input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" />
            </th>
            <th v-if="isSuperAdmin">{{ t('tenant') }}</th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('type')">
              {{ t('members.type') }}
              <span v-if="form.sort_by==='type'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>            
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('agent')">
              {{ t('members.agent') }}
              <span v-if="form.sort_by==='agent'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>            
            <th v-if="form.status_id == null || [2,3].includes(Number(form.status_id))" class="px-3 py-2 cursor-pointer" @click="sortBy('number')">
              {{ t('members.number') }}
              <span v-if="form.sort_by==='number'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>            
            <th class="px-3 py-2 cursor-pointer min-w-[300px]" @click="sortBy('company_name')">
              {{ t('members.company_name') }}
              <span v-if="form.sort_by==='company_name'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('representative')">
              {{ t('members.applicant') }}
              <span v-if="form.sort_by==='representative'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <!-- 02.06 TEL 不要　th class="px-3 py-2 cursor-pointer" @click="sortBy('tel')">
              {{ t('members.tel') }}
              <span v-if="form.sort_by==='tel'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th -->
            <th class="px-3 py-2 cursor-pointer" @click="sortBy('address')">
              {{ t('members.address') }}
              <span v-if="form.sort_by==='address'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th
              class="px-3 py-2 cursor-pointer"
              @click="sortBy(sortField)"
            >
              {{ dateLabel }}
              <span v-if="form.sort_by === sortField">
                {{ form.sort_dir === 'asc' ? '▲' : '▼' }}
              </span>
            </th>
            <th class="px-3 py-2 text-center cursor-pointer" @click="sortBy('status_id')">
              {{ t('members.status') }}
              <span v-if="form.sort_by==='status_id'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>  
            <th v-if="props.filters?.status_id == 1" class="px-3 py-2 text-center cursor-pointer" @click="sortBy('progress_id')">
              {{ t('members.progress') }}
              <span v-if="form.sort_by==='progress_id'">{{ form.sort_dir==='asc'?'▲':'▼' }}</span>
            </th>
            <th class="px-3 py-2 text-center">{{ t('members.documents') }}</th>
            <th class="px-3 py-2 text-center">{{ t('actions.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="member in props.members?.data" :key="member.id" class="odd:bg-white even:bg-gray-100">

            <td class="px-3 py-2">
              <input type="checkbox" :value="member.id" v-model="selectedIds" />
            </td>
            <td v-if="isSuperAdmin">
              {{ tenants.find(t => t.id === member.tenant_id)?.name || '-' }}
            </td>            
            <td class="px-3 py-2">{{ member.type ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.agent ?? '-' }}</td>
            <td v-if="form.status_id == null || [0,2,3].includes(Number(form.status_id))" class="px-3 py-2">{{ member.number ?? '-' }}</td>
            <td class="px-3 py-2 min-w-[300px]">{{ member.organization?.name ?? '-' }}</td>
            <td class="px-3 py-2">{{ member.name ?? '-' }}</td>
            <!-- td class="px-3 py-2">{{ member.tel ?? '-' }}</td -->
            <td class="px-3 py-2">{{ member.address ?? '-' }}</td>
            <td
              class="px-3 py-2 leading-tight"
              v-html="member.display_date"
            ></td>
            <td class="px-3 py-2">
              <span
                :class="[
                  [1,2].includes(member.status_id)
                    ? 'cursor-pointer text-blue-600 hover:underline'
                    : 'text-gray-400 cursor-not-allowed'
                ]"
                @click="openStatus(member)"
              >
                {{ member.status.name ?? '-' }}
              </span>

            </td>
            <td v-if="props.filters?.status_id == 1" class="px-3 py-2">
              <span
                class="cursor-pointer text-blue-600 hover:underline"
                @click="openProgress(member)"
              >
                {{ member.progress.name }}
              </span>
            </td>

            <td class="px-3 py-2 text-center">
              <div class="flex justify-center gap-4">
                <template v-for="docType in documentTypes" :key="docType.id">
                  <div class="w-16 text-center">

                    <!-- 書類あり -->
                    <div
                      v-if="getCertificate(member, docType.id)"
                      :class="['w-16 h-16 border rounded mx-auto flex items-center justify-center',
                              typeBgClass(docType.id),
                              'cursor-pointer hover:opacity-80']"
                      @click="handleClick(member, docType.id)"
                    >
                      <img
                        :src="getCertificate(member, docType.id).thumbnail_path"
                        class="w-12 h-12 object-contain"
                      />
                    </div>

                    <!-- 未提出 -->
                    <div
                      v-else
                      :class="['w-16 h-16 border rounded mx-auto flex items-center justify-center',
                              typeBgClass(docType.id)]"
                    >
                      <span class="text-xs text-red-500">
                        {{ t('members.not_submitted') }}
                      </span>
                    </div>

                    <!-- 書類名 -->
                    <div class="text-xs mt-1 text-gray-600 truncate">
                      {{ shortName(docType.name) }}
                    </div>

                  </div>
                </template>
              </div>
            </td>


            <td class="px-3 py-2 text-center flex justify-center space-x-1">
              <Link :href="route('admin.member.show', { member: member.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <EyeIcon class="w-4 h-4"/>
              </Link>
              <Link
               v-if="can('member edit')"
               :href="route('admin.member.edit', { member: member.id, ...persistQuery() })" class="text-blue-500 hover:text-blue-700">
                <PencilIcon class="w-4 h-4"/>
              </Link>
              <button
                @click="openUpload(member)"
                class="text-green-500 hover:text-green-700"
              >
                <DocumentPlusIcon class="w-4 h-4"/>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- ページネーション -->
      <Pagination :paginator="props.members" :onPageChange="goPage" :startItem="startItem" :endItem="endItem"/>
    </div>

    <div>
      <DialogModal
        :show="!!previewPdf"
        maxWidth="7xl"
        @close="previewPdf = null"
      >
        <template #title>
          PDF プレビュー
        </template>

        <template #content>
          <div class="w-[90vw] h-[80vh]">
            <iframe
              v-if="previewPdf"
              :src="previewPdf"
              class="w-full h-full border"
            />
          </div>
        </template>

        <template #footer>
          <SecondaryButton @click="previewPdf = null">
            {{ t('closed') }}
          </SecondaryButton>
        </template>
      </DialogModal>

      <DialogModal :show="showStatusModal" @close="closeModal">
        <template #title>
            {{ t('members.status_change') }}
        </template>

        <template #content>
          <select
            v-model="statusForm.status_id"
            class="w-full border rounded px-3 py-2 mb-4"
          >
            <option
              v-for="s in statuses"
              :key="s.id"
              :value="s.id"
            >
              {{ s.name }}
            </option>
          </select>
          <!-- 日付入力 -->
          <input
            type="datetime-local"
            v-model="statusForm.date"
            class="w-full border rounded px-3 py-2 mb-4"
          />
        </template>
        <template #footer>
          <SecondaryButton @click="closeModal">
            <v-spacer />
            <v-btn text @click="showStatusModal = false">{{ t('cancel') }}</v-btn>
          </SecondaryButton>
          <PrimaryButton class="ms-3" @click="submitStatus">
              {{ t('actions.update') }}
          </PrimaryButton>
        </template>
      </DialogModal>

      <DialogModal :show="showProgressModal" @close="closeModal">
        <template #title>
          {{ t('members.progress_change') }}
        </template>
        <template #content>
            <select
              v-model="progressForm.progress_id"
              class="w-full border rounded px-3 py-2"
            >
              <option
                v-for="p in progresses"
                :key="p.id"
                :value="p.id"
              >
                {{ p.name }}
              </option>
            </select>
        </template>
        <template #footer>
          <SecondaryButton @click="closeModal">
            <v-spacer />
            <v-btn text @click="showProgressModal = false">{{ t('cancel') }}</v-btn>
          </SecondaryButton>
          <PrimaryButton class="ms-3" @click="submitProgress">
              {{ t('actions.update') }}
          </PrimaryButton>
        </template>
      </DialogModal>

      <DialogModal :show="showUploadModal" @close="closeModal">
        <template #title>{{ t('members.documents') }}</template>

        <template #content>
          <!-- 書類種別 -->
          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('members.documents') }}
            </label>

            <select
              v-model="uploadForm.type_id"
              class="w-full border rounded px-3 py-2"
            >
              <option value="">{{ t('selected') }}</option>

              <option
                v-for="d in documentTypes"
                :key="d.id"
                :value="d.id"
              >
                {{ d.name }}
              </option>
            </select>
          </div>

          <!-- ドラッグ＆ドロップ領域 -->
          <div
            class="mt-4 min-h-40 p-4 bg-[#e7dfc8] border-2 border-dashed border-gray-300 rounded text-center cursor-pointer hover:border-gray-500"
            @dragover.prevent
            @dragenter.prevent
            @drop.prevent="handleDrop"
            @click="fileInput.click()"
          >
            <p v-if="!file">{{ t('pdf_uploads') }}</p>
            <p v-else class="text-sm text-gray-700">{{ t('selected') }} {{ file.name }}</p>

            <!-- hidden file input -->
            <input
              type="file"
              ref="fileInput"
              class="hidden"
              accept="application/pdf"
              @change="onFileChange"
            />
          </div>
        </template>

        <template #footer>
          <div class="flex justify-end gap-3">
            <SecondaryButton @click="closeModal">
              {{ t('cancel') }}
            </SecondaryButton>

            <PrimaryButton @click="submitUpload">
              {{ t('upload') }}
            </PrimaryButton>
          </div>
        </template>
      </DialogModal>      
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
import { ArrowDownTrayIcon, PencilIcon, EyeIcon, MagnifyingGlassIcon, DocumentPlusIcon, PlusIcon, UserIcon} from '@heroicons/vue/24/outline'

const { props } = usePage()

console.log(props)

const user = props.auth.user

const { t } = useI18n()

const isSuperAdmin = computed(() =>
  props.user?.roles?.some(r => r.name.toLowerCase() === 'super_admin')
)

// 検索フォーム・per_page・sort・sort_dirを reactive で管理
const openDrawer = ref(false)

// 複数検索用に reactive 拡張
const form = useForm({
  name: props.filters.name,
  status_id: props.filters.status_id ?? null,
  tenant_id: props.filters.tenant_id,
  per_page: props.filters.per_page || 20,
  sort_by: props.filters.sort_by,   // ← 初期値を必ずセット
  sort_dir: props.filters.sort_dir,    // ← 初期値を必ずセット
  field: props.filters.field || 'company_name',
  keyword: props.filters.keyword || '',
})

const placeholder = computed(() => {
  switch (form.field) {
    case 'number': return '外国人番号で検索'
    case 'company_name': return '会社名で検索'
    case 'representative_name': return '代表者名で検索'
    default: return 'キーワード入力'
  }
})
// 権限チェック用関数
const can = (permission) => {
  return props.auth?.user?.permissions?.includes(permission)
}

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

const exportCsv = () => {
  window.location.href = route('admin.member.csv', persistQuery())
  //  window.location.href = route('admin.member.csv', persistQuery())

}

// persistQueryに各検索項目を追加
const persistQuery = () => ({
  tenant_id: form.tenant_id,
  company_name: form.company_name,
  name: form.name,
  status_id: form.status_id,
  per_page: form.per_page,
  sort_by: form.sort_by,
  sort_dir: form.sort_dir,
  field: form.field,
  keyword: form.keyword,
//  page: props.members.current_page
})

const search = () => {
  console.log(form)
  router.get(route('admin.member.index'), { ...persistQuery(), page: 1 }, {
    preserveState: false,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}
  
/*
const search = () => {
  form.get(route('admin.member.index'), {
    preserveState: true,
    preserveScroll: true,
  })
}
*/

const submitSearch = () => {
  console.log(persistQuery())
  router.get(route('admin.member.index'), { ...persistQuery(), page: 1 }, {
    preserveState: false,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}

// ページ番号クリック
const goPage = (page) => {
  router.get(route('admin.member.index'), { ...persistQuery(), page }, {
    preserveState: false,
    replace: true,
    onSuccess: () => resetSelectedIds()
  })
}

// 列ヘッダクリックでソート
const sortBy = (field) => {
  if (form.sort_by === field) form.sort_dir = form.sort_dir==='asc'?'desc':'asc'
  else { form.sort_by = field; form.sort_dir = 'desc' }
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
  console.log('PDF PATH:', pdfPath)
  if (!pdfPath) return

  // 例：フルパス化
  previewPdf.value = pdfPath

  // 例：ここで loading true
}

// 表示件数計算
const startItem = computed(() => props.members.per_page * (props.members.current_page - 1) + 1)
const endItem = computed(() => Math.min(props.members.per_page * props.members.current_page, props.members.total))

const showProgressModal = ref(false)

const progressForm = reactive({
  member_id: null,
  progress_id: null,
})


const progresses = ref([])

const openProgress = async (member) => {
  // ★ axios より前で判定
  if (member.status.id !== 1) {
    alert('申請中のデータのみ進捗を訂正できます。')
    return
  }

  // ここから先だけ axios
  const res = await axios.get(
    `/admin/member/${member.id}/progress/edit`
  )

  progressForm.member_id = member.id
  progressForm.progress_id = res.data.member.progress_id
  progresses.value = res.data.progresses

  showProgressModal.value = true
}


const submitProgress = async () => {
  await axios.put(
    `/admin/member/${progressForm.member_id}/progress`,
    {
      progress_id: progressForm.progress_id,
    }
  )

  closeModal()

  const memberIndex = props.members.data.findIndex(m => m.id === progressForm.member_id)
  if(memberIndex !== -1) {
    // 送った progress_id で差し替え
    props.members.data[memberIndex].progress_id = progressForm.progress_id
    props.members.data[memberIndex].progress = progresses.value.find(p => p.id === progressForm.progress_id)
  }
}

const showStatusModal = ref(false)
// 日付+時間
const now = () => {
  const d = new Date()
  const pad = (n) => String(n).padStart(2, '0')

  return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

const statusForm = ref({
  member_id: null,
  status_id: null,
  date: now(),
})

const statuses = ref([])

const openStatus = async (member) => {

  // ここでブロック
  if (![1,2].includes(member.status_id)) {
    return
  }

  const res = await axios.get(
    `/admin/member/${member.id}/status/edit`
  )

  statusForm.value.member_id = member.id
  statusForm.value.status_id = res.data.member.status_id
  statuses.value = res.data.statuses

  showStatusModal.value = true
}

const submitStatus = async () => {
  await axios.put(
    `/admin/member/${statusForm.value.member_id}/status`,
    { 
      status_id: statusForm.value.status_id,
      date: statusForm.value.date || new Date().toISOString().slice(0, 10),
    }
  )

  showStatusModal.value = false

  router.get(route('admin.member.index'), {...persistQuery(),})

}

const closeModal = () => {
  showProgressModal.value = false;
  showStatusModal.value = false;
  showUploadModal.value = false
  file.value = null
  uploadForm.value.type_id = null
}
const showUploadModal = ref(false)

const uploadForm = ref({
  member_id: null,
  type_id: null,
})

const file = ref(null)

const documentTypes = [
  { id: 1, name: '履歴事項全部証明書' },
  { id: 2, name: '郵送先確認書' },
  { id: 3, name: '口座振替依頼書' },
  { id: 4, name: '委任状' },
  { id: 5, name: '会員証明書' },
]

const shortName = (name) => {
  const map = {
    '履歴事項全部証明書': '履歴',
    '郵送先確認書': '郵送',
    '口座振替依頼書': '口振',
    '委任状': '委任',
  }
  return map[name] ?? name
}

const typeBgClass = (typeId) => {
  const map = {
    1: 'bg-blue-50 border-blue-200',
    2: 'bg-green-50 border-green-200',
    3: 'bg-yellow-50 border-yellow-200',
    4: 'bg-purple-50 border-purple-200',
    5: 'bg-cyan-50 border-cyan-200',
  }
  return map[typeId] ?? 'bg-gray-50 border-gray-200'
}
/**
 * モーダルを開く（status / progress と同型）
 */
const openUpload = (member) => {
  uploadForm.value.member_id = member.id
  showUploadModal.value = true
}

const fileInput = ref(null)

/**
 * drag & drop
 */
const handleDrop = (e) => {
  const droppedFiles = e.dataTransfer.files
  if (droppedFiles.length && droppedFiles[0].type === 'application/pdf') {
    file.value = droppedFiles[0]
  } else {
    alert('PDF ファイルを1つだけアップロードしてください')
  }
}

/**
 * file input
 */
const onFileChange = (e) => {
  const selected = e.target.files[0]
  if (selected && selected.type === 'application/pdf') {
    file.value = selected
  } else {
    alert('PDF ファイルを選択してください')
    file.value = null
  }
}

/**
 * submit（status と同型）
 */
const submitUpload = async () => {
  if (!file.value || !uploadForm.value.type_id)
    return alert('書類と種別を選択してください')

  const formData = new FormData()
  formData.append('document', file.value)   // ← controller と一致
  formData.append('type_id', uploadForm.value.type_id)
  console.log(formData);

  try {
    await axios.post(
      `/admin/member/${uploadForm.value.member_id}/upload-document`,
      formData,
      //      { headers: { 'Content-Type': 'multipart/form-data' } }
    )

    alert('アップロード完了')
    showUploadModal.value = false

    router.get(route('admin.member.index'), {...persistQuery(),})
    
  } catch (err) {
    console.error(err)
    console.log(err.response.data)
    alert('アップロード失敗')
  }
}
/**
 * type に一致する最初の書類を返す
 */
const getCertificate = (member, typeId) => {
  if (!member.documents || !Array.isArray(member.documents)) return null
  return member.documents.find(d => d.type === typeId) ?? null
}

/**
 * クリック制御
 */
const handleClick = (member, typeId) => {
  const cert = getCertificate(member, typeId)
  if (!cert?.path) return
  openPdf(cert.path)
}

const dateLabel = computed(() => {
  switch (Number(form.status_id)) {
    case 2:
      return '入会日'

    case 3:
      return '退会日'

    case 4:
      return '取消日'

    default:
      return '登録日'
  }
})

const sortField = computed(() => {
  switch (Number(form.status_id)) {
    case 2:
      return 'joined_at'

    case 3:
      return 'withdrawn_at'

    case 4:
      return 'canceled_at'

    default:
      return 'created_at'
  }
})

</script>