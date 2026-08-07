<template>
  <GuestLayout>
    <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow text-center">
      <h2 class="text-xl font-bold mb-4">ご登録ありがとうございました</h2>
      <p class="text-blue-600">この度は、一般社団法人 全国中小建設工事業団体連合会への<br>
      入会申請ありがとうございました。<br><br>
      </p>
      <p class="text-gray-700 mt-1">※申込完了メールが届きますので、内容をご確認ください。<br><br>
メールが届かない場合は、当会までお問い合わせください。<br>
一般社団法人 全国中小建設工事業団体連合会<br>TEL：03-5651-7301<br><br>
      </p>
      <p class="text-red-500 mt-1">
      ※口座振替依頼書を必ず【全中連へ】ご郵送ください。<br>
      ※代理人申請の方は、委任状も必ず【全中連へ】ご郵送ください。<br>
      （詳しくは、申込完了メールをご参照ください）<br><br>
    </p>
      <p class="text-blue-600">
      入会の承認については、一定の審査をさせていただきますので、<br>
      どうぞ今しばらくお待ちください。<br>
      </p>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import RegisterStep from '@/Components/RegisterStep.vue'    

import { usePage } from '@inertiajs/vue3';

const { props } = usePage();
const message = props.message;
</script>
