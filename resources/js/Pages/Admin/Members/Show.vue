<template>
  <AppLayout>
    <template #header>{{ t('members.detail') }}</template>

    <div class="space-y-6 p-6">

      <!-- 申請者 -->
      <section class="bg-white rounded shadow p-4">
        <h2 class="font-bold mb-2 flex items-center justify-between">
          申請者
        </h2>
        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p class="col-1 text-blue-700">{{ t('members.joined_at') }} : {{ props.member?.joined_at ?? '-' }}</p>
          <p class="col-1">{{ t('registers.join_month')}} ： {{ props.member.desired_join_month ? dayjs(props.member.desired_join_month).format('YYYY年MM月') : '-' }} </p>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p class="col-1">{{ t('members.withdrawn_at') }} : {{ props.member?.withdrawn_at ?? '-' }}</p>
          <p class="col-1">申込日時 : {{ props.member.created_at ? dayjs(props.member.created_at).format('YYYY年MM月DD日 HH時mm分') : '-' }}</p>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p class="col-1">
            申請区分 ： {{ props.member.agent ? '代理人申請' : '本人申請' }}
          </p>
          <p class="col-1"></p>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p class="col-1">
            事業形態 ： {{ props.member.type === 'corporation' ? '法人' : '個人事業主' }}
          </p>
          <p class="col-1 text-red-500">
            {{ t('members.updated') }} ：
            {{ props.member.updated_at ? dayjs(props.member.updated_at).format('YYYY-MM-DD HH:mm') : '未更新' }}
            {{ props.member.updated_by_user?.name ?? '未更新' }}
            <span v-if="props.member.updated_section">
              （{{ props.member.updated_section }} を更新）
            </span>
          </p>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p class="col-1">{{ t('status') }} ： {{ props.member.status.name }}</p>
          <p class="col-1 text-red-500">
            {{ t('members.updated') }} ：
            {{ props.member.status_meta?.updated_at
              ? dayjs(props.member.status_meta.updated_at).format('YYYY-MM-DD HH:mm') : '-' }}
            {{ props.member.status_meta?.user_name ?? '-' }}
            <span v-if="props.member.status_meta?.old_name && props.member.status_meta?.old_name !== props.member.status_meta?.new_name">
              （{{ props.member.status_meta.old_name }} → {{ props.member.status_meta.new_name }}）
            </span>
          </p>
        </div>  
        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p class="col-1">{{ t('members.progress') }} : {{ props.member.progress?.name ?? '-' }}</p>
          <p class="col-1 text-red-500">
            {{ t('members.updated') }} ：
            {{ props.member.progress_meta?.updated_at
              ? dayjs(props.member.progress_meta.updated_at).format('YYYY-MM-DD HH:mm') : '-' }}
            {{ props.member.progress_meta?.user_name ?? '-' }}
            <span v-if="props.member.progress_meta?.old_name && props.member.progress_meta?.old_name !== props.member.progress_meta?.new_name">
              （{{ props.member.progress_meta.old_name }} → {{ props.member.progress_meta.new_name }}）
            </span>
          </p>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p class="col-1">{{ t('members.region') }}: {{ props.member?.region }}</p>
          <p class="col-1">{{ t('members.number') }}: {{ props.member?.number }}</p>
        </div>  
        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p>{{ t('members.aplus_customer_no') }}: {{ props.member?.aplus_customer_no }}</p>
          <p>{{ t('members.jac_certification_no') }} : {{  props.member?.jac_certification_no }}</p>
       </div>   
        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p>{{ t('members.issued_at') }} : {{ props.member?.issued_at }}</p>
          <P>{{ t('members.paid_at') }} : {{ props.member?.paid_at }}</p>
       </div>   
        <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p>{{ t('members.amount') }} : {{ props.member?.amount }}</p>
       </div>   

       <div class="mt-4 grid grid-cols-2 gap-4 items-end">
          <p>{{ t('registers.note') }} : {{ props.member?.note }}</p>
       </div>   

      </section>

      <!-- 法人 -->
    <section class="bg-white rounded shadow p-4">
      <div class="flex justify-end mb-4">
        <button
          @click="toggleCompare"
          class="px-4 py-2 bg-blue-500 text-white rounded text-sm"
        >
          {{ isCompare ? '閉じる' : '申込時情報' }}
        </button>
      </div>
      <div class="space-y-6">
        <div
          v-for="type in [1,2,3]"
          :key="type"
          class="border rounded shadow p-4 overflow-hidden"
        >        
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold">{{ typeLabels[type] }}</h3>
          </div>

          <div class="flex gap-6">
            
            <!-- 左：現状 -->
            <div
              :class="isCompare ? 'w-1/2' : 'w-full'"
              class="transition-all duration-300 space-y-6"
            >
              <template v-if="getCurrentByType(type)">
                <p v-if="type !== 2"
                  :class="{
                    'text-red-600 font-semibold':
                      isDifferent(getCurrentByType(type), getAppByType(type), 'name')
                  }"
                >
                  会社名：{{ getCurrentByType(type).name || '-' }}
                </p>
                <p v-if="type !== 2"
                  :class="{
                    'text-red-600 font-semibold':
                      isDifferent(getCurrentByType(type), getAppByType(type), 'name_kana')
                  }"
                >
                  会社名（カナ）：{{ getCurrentByType(type).name_kana || '-' }}
                </p>
                <p v-if="type !== 2"
                    :class="{
                        'text-red-600 font-semibold':
                            isDifferent(getCurrentByType(type), getAppByType(type), 'position')
                    }"
                >
                    {{ type === 3 ? '行政書士登録番号' : '役職または肩書' }}: {{ getCurrentByType(type).position || '-' }}
                </p>
                <template v-if="type === 1">
                  <p
                    :class="{
                      'text-red-600 font-semibold': isDiff(
                        props.member.name,
                        props.member.application_name ?? props.member.name
                      )
                    }"
                  >
                    {{ t('members.representative') }}： {{ props.member.name }}
                  </p>
                </template>
                <template v-if="type === 1">
                  <p
                    :class="{
                      'text-red-600 font-semibold': isDiff(
                        props.member.name_kana,
                        props.member.application_name_kana ?? props.member.name_kana
                      )
                    }"
                  >
                    代表者名（カナ）： {{ props.member.name_kana }}
                  </p>
                </template>
                <p
                  :class="{
                    'text-red-600 font-semibold':
                      isDifferent(getCurrentByType(type), getAppByType(type), 'postal_code')
                      || isDifferent(getCurrentByType(type), getAppByType(type), 'address')
                  }"
                >
                  {{ getCurrentByType(type).postal_code || '-' }}
                  {{ getCurrentByType(type).address || '-' }}
                </p>
                <template v-if="type !== 2">

                  <p v-if="type !== 2"
                    :class="{
                      'text-red-600 font-semibold':
                        isDifferent(getCurrentByType(type), getAppByType(type), 'tel')
                        || isDifferent(getCurrentByType(type), getAppByType(type), 'fax')
                    }"
                  >
                    TEL: {{ getCurrentByType(type).tel || '-' }}
                    FAX: {{ getCurrentByType(type).fax || '-' }}
                  </p>

                  <p v-if="type !== 2"
                    :class="{
                      'text-red-600 font-semibold':
                        isDifferent(getCurrentByType(type), getAppByType(type), 'mobile')
                    }"
                  >
                    Mobile: {{ getCurrentByType(type).mobile || '-' }}
                  </p>

                  <p
                    :class="{
                      'text-red-600 font-semibold':
                        isDifferent(getCurrentByType(type), getAppByType(type), 'email')
                    }"
                  >
                    Email: {{ getCurrentByType(type).email || '-' }}
                  </p>

                  <p v-if="type !== 2"
                    :class="{
                      'text-red-600 font-semibold':
                        isDifferent(getCurrentByType(type), getAppByType(type), 'contact_name')
                    }"
                  >
                    担当者: {{ getCurrentByType(type).contact_name || '-' }}
                  </p>
                </template>

              </template>

              <p v-else class="text-gray-400">データなし</p>
            </div>
              <!-- 右：申込時 -->
            <transition name="slide-right">
            <div
              v-if="isCompare"
                class="w-1/2 bg-blue-50 p-4 rounded space-y-6"
            >
                <!-- ヘッダー -->
                <template v-if="getAppByType(type)">
                  <p v-if="type !== 2">会社名：{{ getAppByType(type).name || '-' }}</p>
                  <p v-if="type !== 2">会社名(カナ)：{{ getAppByType(type).name_kana || '-' }}</p>
                  <p v-if="type !== 2">
                      {{ type === 3 ? '行政書士登録番号' : '役職または肩書' }}: {{ getAppByType(type).position || '-' }}
                  </p>
                  <template v-if="type === 1">
                    <p class="col-1">{{ t('members.representative')}}： {{ props.member.application_name ?? props.member.name }}</p>                
                  </template>
                  <template v-if="type === 1">
                    <p class="col-1">代表者名（カナ）： {{ props.member.application_name_kana ?? props.member.name_kana }}</p>                
                  </template>
                  <p>
                    {{ getAppByType(type).postal_code || '-' }}
                    {{ getAppByType(type).address || '-' }}
                  </p>
                  <p v-if="type !== 2">
                    TEL: {{ getAppByType(type).tel || '-' }}
                    FAX: {{ getAppByType(type).fax || '-' }}
                  </p>
                  <p v-if="type !== 2">Mobile: {{ getAppByType(type).mobile || '-' }}</p>
                  <p v-if="type !== 2">Email: {{ getAppByType(type).email || '-' }}</p>

                  <p v-if="type !== 2">担当者: {{ getAppByType(type).contact_name || '-' }}</p>
                </template>

                <p v-else class="text-gray-400">データなし</p>
            </div>
            </transition> 
          </div>
        </div>

      </div>
    </section>
    <section class="bg-white rounded shadow p-4">
      <div class="space-y-6">
      <div class="border rounded shadow p-4 overflow-hidden">  
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold">{{ t('banks.bank') }}</h3>
        </div>
        <div class="flex gap-6">
        <!-- 左：現在 -->
        <div
              :class="isCompare ? 'w-1/2' : 'w-full'"
              class="transition-all duration-300 space-y-6"
            >
          <p :class="{ 'text-red-500 font-semibold': isDiff(member.bank_account?.bank_name, member.application_bank_account?.bank_name) }">
            {{ t('banks.bank_name') }}：{{ member.bank_account?.bank_name }}
          </p>

          <p :class="{ 'text-red-500 font-semibold': isDiff(member.bank_account?.bank_code, member.application_bank_account?.bank_code) }">
            {{ t('banks.bank_code') }}：{{ member.bank_account?.bank_code }}
          </p>

          <p :class="{ 'text-red-500 font-semibold': isDiff(member.bank_account?.branch_name, member.application_bank_account?.branch_name) }">
            {{ t('banks.branch_name') }}：{{ member.bank_account?.branch_name }}
          </p>

          <p :class="{ 'text-red-500 font-semibold': isDiff(member.bank_account?.branch_code, member.application_bank_account?.branch_code) }">
            {{ t('banks.branch_code') }}：{{ member.bank_account?.branch_code }}
          </p>

          <p :class="{ 'text-red-500 font-semibold': isDiff(member.bank_account?.account_type, member.application_bank_account?.account_type) }">
            {{ t('banks.account_type') }}：{{ member.bank_account?.account_type }}
          </p>

          <p :class="{ 'text-red-500 font-semibold': isDiff(member.bank_account?.account_no, member.application_bank_account?.account_no) }">
            {{ t('banks.account_no') }}：{{ member.bank_account?.account_no }}
          </p>

          <p :class="{ 'text-red-500 font-semibold': isDiff(member.bank_account?.account_name, member.application_bank_account?.account_name) }">
            {{ t('banks.account_name') }}：{{ member.bank_account?.account_name }}
          </p>

          <p :class="{ 'text-red-500 font-semibold': isDiff(member.bank_account?.account_kana, member.application_bank_account?.account_kana) }">
            {{ t('banks.account_kana') }}：{{ member.bank_account?.account_kana }}
          </p>
        </div>

        <!-- 右：申請 -->
        <transition name="slide-right">
          <div
            v-if="isCompare"
              class="w-1/2 bg-blue-50 p-4 rounded space-y-6"
          >
            <p>{{ t('banks.bank_name') }}：{{ member.application_bank_account?.bank_name }}</p>
            <p>{{ t('banks.bank_code') }}：{{ member.application_bank_account?.bank_code }}</p>
            <p>{{ t('banks.branch_name') }}：{{ member.application_bank_account?.branch_name }}</p>
            <p>{{ t('banks.branch_code') }}：{{ member.application_bank_account?.branch_code }}</p>
            <p>{{ t('banks.account_type') }}：{{ member.application_bank_account?.account_type }}</p>
            <p>{{ t('banks.account_no') }}：{{ member.application_bank_account?.account_no }}</p>
            <p>{{ t('banks.account_name') }}：{{ member.application_bank_account?.account_name }}</p>
            <p>{{ t('banks.account_kana') }}：{{ member.application_bank_account?.account_kana }}</p>
          </div>
        </transition>
      </div>
      </div>
      </div>
    </section>




    <!-- 書類 -->
    <section class="bg-white rounded shadow p-4">
        <h2 class="font-bold mb-2">提出書類</h2>
        <div class="flex gap-4">
            <div v-for="d in props.member.documents" :key="d.type + d.path">
            <img
                v-if="d.thumbnail_path"
                :src="d.thumbnail_path"
                class="w-24 cursor-pointer"
                @click="openPdf(d.path)"
            />
            </div>
        </div>
    </section>
    <div class="flex gap-2">
        <!-- 編集ボタン -->
        <Link
        v-if="can('member edit')"
        :href="route('admin.member.edit', { member: props.member.id, ...persistQuery() })"
        class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
        >
        <PencilIcon class="w-4 h-4 mr-2"/>
        {{ t('actions.edit') }}
        </Link>

        <!-- キャンセル/戻るボタン -->
        <Link
        :href="route('admin.member.index', persistQuery())"
        class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"
        >
        <ArrowLeftIcon class="w-4 h-4 mr-2"/>
        {{ t('actions.cancel') }}
        </Link>
    </div>
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
            閉じる
          </SecondaryButton>
        </template>
      </DialogModal>
    </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/Admin/AppLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

import Pagination from '@/Components/Pagination.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ref, reactive, computed, watch} from 'vue'
import { useI18n } from 'vue-i18n'
import dayjs from 'dayjs'
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, DocumentDuplicateIcon, ArrowLeftIcon} from '@heroicons/vue/24/outline'

const { t } = useI18n()

const props = defineProps({
  member: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const { props: pageProps } = usePage()
console.log(pageProps)

const can = (permission) => {
  return pageProps.auth.user?.permissions?.includes(permission)
}

// persistQueryに各検索項目を追加
const persistQuery = () => {
  return { ...props.filters }
}


const isCompare = ref(false)

const toggleCompare = () => {
  isCompare.value = !isCompare.value
}

const previewPdf = ref(null)

const openPdf = (pdfPath) => {
  console.log('PDF PATH:', pdfPath)
  if (!pdfPath) return

  // 例：フルパス化
  previewPdf.value = pdfPath

  // 例：ここで loading true
}

const typeLabels = {
  1: '会社情報',
  2: '郵送先情報',
  3: '行政書士・監理団体・登録支援機関情報'
}

// typeごとに現状データ取得
const getCurrentByType = (type) => {
  return props.member.organizations?.find(o => o.type === type)
}

// typeごとに申込時データ取得
const getAppByType = (type) => {
  return props.member.applications?.find(a => a.type === type)
}

const isDifferent = (current, app, field) =>
  (current?.[field] ?? '') !== (app?.[field] ?? '')

const isDiff = (a, b) => (a ?? '') !== (b ?? '')

</script>
<style lang="css">
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all 0.3s ease;
}

.slide-right-enter-from {
  transform: translateX(30px);
  opacity: 0;
}

.slide-right-enter-to {
  transform: translateX(0);
  opacity: 1;
}
</style>
