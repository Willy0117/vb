<template>
  <GuestLayout>
    <RegisterStep current="bank" />
    <Head title="PDF確認" />
    <div class="max-w-5xl mx-auto bg-white p-8 rounded shadow">
      <h2 class="text-2xl font-bold mb-2">{{ t('registers.members') }}</h2>
      <p class="text-sm text-gray-500 mb-8">
        <ul class="text-sm text-gray-600 mb-8 bg-yellow-50 border border-yellow-200 rounded p-4 pl-8 space-y-1" style="list-style-type: disc;">
          <li>以下の入力情報をご確認ください。こちらの内容で登録いたします。内容に相違がある場合は、「訂正」ボタンを押下し、修正してください。</li>
          <li>登録完了後は、内容の確認はできません。登録内容を保管されたい場合は、プリントアウト等していただき、保管をお願いいたします。</li>
          <li>口座振替依頼書PDF(代理人申請の場合は、委任状も含む)を必ずダウンロードしてください。</li>
        </ul>
      </p>

      <!-- 申込種別 / 入会希望月 -->
      <ConfirmSection :title="t('registers.applicant') + ' / ' + t('registers.desired_join_month')">
        <ConfirmRow :label="t('registers.applicant')">
          {{ form.type === 'corporation' ? t('registers.corporation') : t('registers.sole') }}
        </ConfirmRow>
        <ConfirmRow :label="t('registers.desired_join_month')">
          {{ form.desired_join_month }}
        </ConfirmRow>
      </ConfirmSection>

      <!-- 会社情報 -->
      <ConfirmSection :title="t('registers.organization')">
        <ConfirmRow :label="t('registers.company_name')">
          {{ [form.company_type_prefix, form.company_name, form.company_type_suffix].filter(Boolean).join(' ') }}
        </ConfirmRow>
        <ConfirmRow :label="t('registers.company_kana')">
          {{ form.company_kana }}
        </ConfirmRow>
      </ConfirmSection>

      <!-- 代表者 -->
      <ConfirmSection title="代表者">
        <ConfirmRow :label="t('registers.position')" v-if="form.type !== 'sole'">
          {{ form.corp.position }}
        </ConfirmRow>
        <ConfirmRow label="代表者名">
          {{ form.rep_last_name }}　{{ form.rep_first_name }}
        </ConfirmRow>
        <ConfirmRow label="代表者名（カナ）">
          {{ form.rep_last_kana }}　{{ form.rep_first_kana }}
        </ConfirmRow>
      </ConfirmSection>

      <!-- 法人住所 -->
      <ConfirmSection :title="t('registers.corp')">
        <ConfirmRow :label="t('registers.zip_code')">〒{{ form.corp.postal_code }}</ConfirmRow>
        <ConfirmRow label="住所">
          {{ form.corp.address1 }}{{ form.corp.address2 }}{{ form.corp.address3 }}
        </ConfirmRow>
        <ConfirmRow :label="t('registers.tel')">{{ form.corp.tel }}</ConfirmRow>
        <ConfirmRow :label="t('registers.fax')" v-if="form.corp.fax">{{ form.corp.fax }}</ConfirmRow>
        <ConfirmRow :label="t('registers.mobile')" v-if="form.corp.mobile">{{ form.corp.mobile }}</ConfirmRow>
        <ConfirmRow :label="t('registers.email')" v-if="form.is_agent && form.corp.email">{{ form.corp.email }}</ConfirmRow>
        <ConfirmRow :label="t('members.staff')">
          {{ form.corp.last_name }}　{{ form.corp.first_name }}
        </ConfirmRow>
      </ConfirmSection>

      <!-- 郵送先 -->
      <ConfirmSection :title="t('registers.mail')">
        <template v-if="form.same_as_corp">
          <p class="text-sm text-gray-500 px-4 py-2">上記住所と同じ</p>
        </template>
        <template v-else>
          <ConfirmRow :label="t('registers.zip_code')">〒{{ form.mail.postal_code }}</ConfirmRow>
          <ConfirmRow label="住所">
            {{ form.mail.address1 }}{{ form.mail.address2 }}{{ form.mail.address3 }}
          </ConfirmRow>
        </template>
      </ConfirmSection>

      <!-- 代理人 -->
      <ConfirmSection :title="t('registers.agent')" v-if="Number(form.is_agent) === 1">
        <ConfirmRow :label="t('registers.company_name')">{{ form.agent.company_name }}</ConfirmRow>
        <ConfirmRow :label="t('registers.zip_code')">〒{{ form.agent.postal_code }}</ConfirmRow>
        <ConfirmRow label="住所">
          {{ form.agent.address1 }}{{ form.agent.address2 }}{{ form.agent.address3 }}
        </ConfirmRow>
        <ConfirmRow :label="t('registers.tel')">{{ form.agent.tel }}</ConfirmRow>
        <ConfirmRow :label="t('registers.fax')" v-if="form.agent.fax">{{ form.agent.fax }}</ConfirmRow>
        <ConfirmRow :label="t('registers.mobile')" v-if="form.agent.mobile">{{ form.agent.mobile }}</ConfirmRow>
        <ConfirmRow :label="t('registers.number')" v-if="form.agent.position">{{ form.agent.position }}</ConfirmRow>
        <ConfirmRow :label="t('registers.staff')">
          {{ form.agent.last_name }}　{{ form.agent.first_name }}
        </ConfirmRow>
      </ConfirmSection>

      <!-- 銀行情報 -->
      <ConfirmSection :title="t('registers.bank')">
        <ConfirmRow label="銀行名">
          {{ form.bank_name }}（{{ form.bank_code }}）
        </ConfirmRow>
        <ConfirmRow v-if="form.bank_code !== '9900'" :label="t('banks.branch_name')">
          {{ form.branch_name }}（{{ form.branch_code }}）
        </ConfirmRow>
        <ConfirmRow v-else label="記号">{{ form.branch_code }}</ConfirmRow>
        <ConfirmRow :label="t('banks.account_type')">{{ form.account_type }}</ConfirmRow>
        <ConfirmRow :label="t('banks.account_no')">{{ form.account_no }}</ConfirmRow>
        <ConfirmRow :label="t('banks.account_name')" v-if="form.account_name">{{ form.account_name }}</ConfirmRow>
        <ConfirmRow :label="t('banks.account_kana')" v-if="form.account_kana">{{ form.account_kana }}</ConfirmRow>
      </ConfirmSection>
      <div class="flex gap-4 items-center">
        <button
          @click="goBack"
          class="mt-6 h-10 px-4 flex items-center justify-center rounded bg-gray-300"
        >
          {{ t('revise') }}
        </button>
      </div>
    </div>
    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
      <h2 class="text-xl font-bold mb-4">
        口座振替申請書 確認(代理人申請の場合は、委任状も含む)
      </h2>

      <div
        id="pdf-container"
        class="space-y-6 overflow-y-auto max-h-[80vh] border p-4 bg-gray-50"
      ></div>

      <div class="space-y-3 mt-6">
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="confirmed" />
          <span>記載内容に相違ありません。</span>
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="downloaded" />
          <span>口座振替申請書PDF（代理人申請の場合は、委任状も含む）をダウンロードしました。</span>
        </label>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center gap-4 mt-6">
        <button
          @click="goBack"
          class="h-10 px-4 flex items-center justify-center rounded bg-gray-300"
        >
          {{ t('revise') }}
        </button>

        <a
            :href="confirmed ? pdfUrl : null"
            download
            class="h-10 px-4 flex items-center justify-center rounded text-white text-sm sm:text-base text-center"
            :class="confirmed
              ? 'bg-blue-600 cursor-pointer'
              : 'bg-gray-400 cursor-not-allowed pointer-events-none'
            "
          >
            口座振替申請書(代理人申請の場合は、委任状も含む)を{{ t('download') }}
        </a>

        <PrimaryButton
          type="button"
          class="h-10 px-4 flex items-center justify-center text-sm sm:text-base"
          :disabled="!canSubmit"
          @click="submitRegister"
        >
          申込完了
        </PrimaryButton>
      </div>

      <p class="text-red-500 text-sm mt-3">
        ※申込を完了するには、「申込完了」ボタンを必ず押してください
      </p>

      <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
        <p class="text-amber-800 text-sm leading-relaxed">
          ※口座振替依頼書は、口座名義人（カナ含む）の記入と
          押印（金融機関お届け印）をしたものをご郵送いただきますようお願いいたします。
        </p>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, onMounted, computed, defineComponent, h } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import RegisterStep from '@/Components/RegisterStep.vue'    
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const canvas = ref(null)
const page = usePage()
const pdfUrl = usePage().props.pdfUrl

console.log('page.props:', page.props)
console.log('form:', page.props.form)

const confirmed = ref(false)
const downloaded = ref(false)

const canSubmit = computed(() => {
  return confirmed.value && downloaded.value
})

const ConfirmSection = defineComponent({
  props: { title: String },
  setup(props, { slots }) {
    return () =>
      h('div', { class: 'mb-8' }, [
        h('div', { class: 'p-3 bg-gray-100 border-l-4 border-blue-400 rounded mb-1' }, [
          h('h3', { class: 'text-base font-semibold text-gray-700' }, props.title)
        ]),
        h('dl', { class: 'divide-y divide-gray-100' }, slots.default?.())
      ])
  }
})

const ConfirmRow = defineComponent({
  props: { label: String },
  setup(props, { slots }) {
    return () =>
      h('div', { class: 'flex flex-col sm:flex-row py-2 px-4 gap-1 sm:gap-4' }, [
        h('dt', { class: 'w-full sm:w-48 sm:shrink-0 text-sm text-gray-500' }, props.label),
        h('dd', { class: 'text-sm text-gray-800 flex-1 break-all' }, slots.default?.())
      ])
  }
})

// confirm() でセッションから渡された表示用データ
const form = page.props.form ?? {}
console.log(form)
const processing = ref(false)

onMounted(async () => {
  const pdfjsLib = window.pdfjsLib

  pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'

  const pdf = await pdfjsLib.getDocument({
    url: pdfUrl,
    cMapUrl: '/cmaps/',
    cMapPacked: true,
  }).promise

  const container = document.getElementById('pdf-container')

  // 全ページ描画
  for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
    const page = await pdf.getPage(pageNum)

    const viewport = page.getViewport({ scale: 1.5 })
    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')

    canvas.width = viewport.width
    canvas.height = viewport.height
    canvas.classList.add('shadow', 'mx-auto')

    container.appendChild(canvas)

    await page.render({
      canvasContext: context,
      viewport,
      renderInteractiveForms: true,
    }).promise
  }
})

const submitRegister = () => {
  router.get(
    route('members.completeRegistration', {
      token: page.props.token,
    })
  )
}

const goBack = () => {
  router.get(
    route('members.register.register', { token: page.props.token }),
    {},
    {
      preserveState: true,
      preserveScroll: true,
    }
  )
}
</script>
