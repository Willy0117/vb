<template>
  <GuestLayout>
    <RegisterStep current="confirm" />
    <Head title="会員登録（確認）" />

    <div class="max-w-5xl mx-auto bg-white p-8 rounded shadow">
      <h2 class="text-2xl font-bold mb-2">{{ t('registers.members') }}</h2>
      <p class="text-sm text-gray-500 mb-8">以下の内容をご確認の上、「送信する」ボタンを押してください。</p>

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
        <ConfirmRow label="代表者名（フリガナ）">
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
          <p class="text-sm text-gray-500 px-4 py-2">法人住所と同じ</p>
        </template>
        <template v-else>
          <ConfirmRow :label="t('registers.zip_code')">〒{{ form.mail.postal_code }}</ConfirmRow>
          <ConfirmRow label="住所">
            {{ form.mail.address1 }}{{ form.mail.address2 }}{{ form.mail.address3 }}
          </ConfirmRow>
        </template>
      </ConfirmSection>

      <!-- 代理人 -->
      <ConfirmSection :title="t('registers.agent')" v-if="form.is_agent">
        <ConfirmRow :label="t('registers.company_name')">{{ form.agent.company_name }}</ConfirmRow>
        <ConfirmRow :label="t('registers.zip_code')">〒{{ form.agent.postal_code }}</ConfirmRow>
        <ConfirmRow label="住所">
          {{ form.agent.address1 }}{{ form.agent.address2 }}{{ form.agent.address3 }}
        </ConfirmRow>
        <ConfirmRow :label="t('registers.tel')">{{ form.agent.tel }}</ConfirmRow>
        <ConfirmRow :label="t('registers.fax')" v-if="form.agent.fax">{{ form.agent.fax }}</ConfirmRow>
        <ConfirmRow :label="t('registers.mobile')" v-if="form.agent.mobile">{{ form.agent.mobile }}</ConfirmRow>
        <ConfirmRow :label="t('registers.position')" v-if="form.agent.position">{{ form.agent.position }}</ConfirmRow>
        <ConfirmRow :label="t('registers.staff')">
          {{ form.agent.last_name }}　{{ form.agent.first_name }}
        </ConfirmRow>
      </ConfirmSection>

      <!-- 銀行情報 -->
      <ConfirmSection :title="t('registers.bank')">
        <ConfirmRow :label="t('banks.bank_category')">{{ form.bank_type }}</ConfirmRow>
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

      <!-- 必要書類 -->
      <ConfirmSection title="必要書類">
        <ConfirmRow label="現在事項全部証明書" v-if="form.type !== 'sole'">
          <span class="text-green-600 font-medium">✓ アップロード済み</span>
        </ConfirmRow>
        <ConfirmRow label="郵送先確認資料" v-if="!form.same_as_corp">
          <span class="text-green-600 font-medium">✓ アップロード済み</span>
        </ConfirmRow>
      </ConfirmSection>

      <!-- ボタン -->
      <div class="flex items-center justify-between mt-10">
        <SecondaryButton type="button" @click="goBack" :disabled="processing">
          ← 戻る
        </SecondaryButton>
        <PrimaryButton
          type="button"
          :disabled="processing"
          class="bg-blue-600 hover:bg-blue-700"
          @click="submit"
        >
          <span v-if="processing">送信中...</span>
          <span v-else>送信する</span>
        </PrimaryButton>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, defineComponent, h } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

import GuestLayout from '@/Layouts/GuestLayout.vue'
import RegisterStep from '@/Components/RegisterStep.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

// ── 確認用インラインコンポーネント ──────────────────────────────────────────
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
      h('div', { class: 'flex py-2 px-4 gap-4' }, [
        h('dt', { class: 'w-48 shrink-0 text-sm text-gray-500' }, props.label),
        h('dd', { class: 'text-sm text-gray-800 flex-1 break-all' }, slots.default?.())
      ])
  }
})
// ─────────────────────────────────────────────────────────────────────────────

const { t } = useI18n()
const page = usePage()

// confirm() でセッションから渡された表示用データ
const form = page.props.form ?? {}

const processing = ref(false)

/** 入力画面（register）へ戻る */
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

/**
 * 送信：pdfGenerate へ POST
 * ファイルはすでに confirm() でストレージ保存済み → パスのみ送る
 */
function submit() {
  processing.value = true

  router.post(
    route('members.pdfgenerate', { token: page.props.token }),
    {},   // データはセッションから取るので空でOK
    {
      preserveScroll: true,
      onError: () => { processing.value = false },
      onFinish: () => { processing.value = false },
    }
  )

}
</script>