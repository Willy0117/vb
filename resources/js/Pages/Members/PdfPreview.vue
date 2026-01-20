<template>
  <GuestLayout>
    <RegisterStep current="bank" />
    <Head title="PDF確認" />

    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
      <h2 class="text-xl font-bold mb-4">口座振替申請書 確認</h2>

      <div
        id="pdf-container"
        class="space-y-6 overflow-y-auto max-h-[80vh] border p-4 bg-gray-50"
      ></div>

      <!-- canvas ref="canvas" class="border w-full mb-4"></canvas -->

      <div class="flex gap-4">
        <a
          :href="pdfUrl"
          download
          class="bg-blue-600 text-white px-4 py-2 rounded"
        >
          ダウンロード
        </a>

        <button
          @click="goBack"
          class="bg-gray-300 px-4 py-2 rounded"
        >
          戻る
        </button>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import RegisterStep from '@/Components/RegisterStep.vue'    

const canvas = ref(null)
const page = usePage()
const pdfUrl = usePage().props.pdfUrl
/*
onMounted(async () => {
  // PDF.js CDN
  const pdfjsLib = window.pdfjsLib

  pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'

  const pdf = await pdfjsLib.getDocument({
    url: pdfUrl,
    cMapUrl: '/cmaps/',
    cMapPacked: true,
  }).promise

  const page1 = await pdf.getPage(1)

  const viewport = page1.getViewport({ scale: 1.5 })
  const context = canvas.value.getContext('2d')

  canvas.value.height = viewport.height
  canvas.value.width = viewport.width

  await page1.render({
    canvasContext: context,
    viewport,
    renderInteractiveForms: true,
  }).promise
})
*/
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
