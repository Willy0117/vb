<template>
  <AppLayout>
    <template #header>会員登録</template>

    <div class="max-w-5xl mx-auto bg-white p-8 rounded shadow">

      <form @submit.prevent="submitForm" class="space-y-8" @keydown.enter="focusNext">
                <div class="mt-4 grid grid-cols-8 gap-4 items-start">
          <!-- 会社種類（2W） -->
          <div class="col-span-2">
            <InputLabel :value="t('registers.applicant')" class="mb-2" />
            <div class="flex gap-4">
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  type="radio"
                  value="corporation"
                  v-model="form.type"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span>{{ t('registers.corporation') }}</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  type="radio"
                  value="sole"
                  v-model="form.type"
                  class="text-blue-600 focus:ring-blue-500"
                />
                <span>{{ t('registers.sole') }}</span>
              </label>
            </div>
            <p v-if="errors.type" class="text-red-500 text-sm mt-1">{{ errors.type }}</p>
          </div>
          <!-- 入会日（1W） -->
          <div class="col-span-2">
            <InputLabel :value="t('members.joined_at')" class="mb-1" />
            <TextInput type="date" v-model="form.joined_at" class="w-full" />
          </div>

          <!-- 退会日（1W） -->
          <div class="col-span-2">
            <InputLabel :value="t('members.withdrawn_at')" class="mb-1" />
            <TextInput type="date" v-model="form.withdrawn_at" class="w-full" />
          </div>

        </div>

        <div class="mt-4 grid grid-cols-8 gap-4 items-end">
          <!-- Region（1W） -->
          <div class="col-span-1">
            <InputLabel :value="t('members.region')" class="mb-1" />
            <select v-model="form.region_id" class="border rounded px-3 py-2 w-full">
              <option value="">{{ t('select region') }}</option>
              <option v-for="region in regions" :key="region.id" :value="region.id">
                {{ region.name }}
              </option>
            </select>
          </div>
          <div class="col-span-1">
            <InputLabel value=" " class="mb-1" />
            <div class="border rounded px-3 py-2 bg-gray-100 text-center">
              50
            </div>
          </div>
          <!-- 5桁番号（1W） -->
          <div class="col-span-2">
            <InputLabel :value="t('members.number')" class="mb-1" />
            <TextInput
              v-model="form.number"
              @blur="handleNumberBlur"
              maxlength="5"
              placeholder="A0000"
              :class="{
                'border-red-500': getError('number'),
                'border-gray-300': !getError('number')
              }"
              class="w-full"
            />
            <p v-if="getError('number')" class="text-red-500 text-sm mt-1">{{ getError('number') }}</p>
            <p v-if="numberError" class="text-red-500 text-sm mt-1">{{ numberError }}</p>
          </div>
          <!-- アプラス顧客番号 -->
          <div class="col-span-2">
            <InputLabel :value="t('members.aplus_customer_no')" class="mb-1" />
            <TextInput v-model="form.aplus_customer_no" class="w-full" />
          </div>

          <!-- JAC認定番号 -->
          <div class="col-span-2">
            <InputLabel :value="t('members.jac_certification_no')" class="mb-1" />
            <TextInput v-model="form.jac_certification_no" class="w-full" />
          </div>

        </div>
        <div class="mt-4 grid grid-cols-8 gap-4 items-end">
                    <!-- 入会日（1W） -->
          <div class="col-span-2">
            <InputLabel :value="t('members.issued_at')" class="mb-1" />
            <TextInput type="date" v-model="form.issued_at" class="w-full" />
          </div>

          <!-- 退会日（1W） -->
          <div class="col-span-2">
            <InputLabel :value="t('members.paid_at')" class="mb-1" />
            <TextInput type="date" v-model="form.paid_at" class="w-full" />
          </div>
          <!-- 入会金額 -->
          <div class="col-span-1">
            <InputLabel :value="t('members.amount')" class="mb-1" />
            <TextInput
              v-model="form.amount"
              class="w-full"
              @input="onAmountInput"
            />
          </div>

        </div>
        <!-- 2カラム -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="mt-4">
          </div>
          <div class="mt-4">
            <InputLabel :value="t('registers.join_month')" /> 
            <select v-model="form.desired_join_month" class="border rounded px-2 py-1 w-full">
              <option v-for="opt in joinMonthOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
            <InputError :message="form.errors?.desired_join_month" />
          </div>
        </div>
        <!-- 2カラム -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!--  ここから会社情報　-->

          <!-- 左カラム：会社情報 -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold mb-2">{{ t('registers.organization') }}</h3>

            <div class="sm:items-start">
              <InputLabel :value="t('registers.company_name')" required /> 

              <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
                <div class="flex-1">
                  <!-- 前 -->
                  <select
                    v-model="form.company_type_prefix"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2"
                  >
                    <option v-for="type in companyTypes" :key="type.value" :value="type.value">
                      {{ type.label }}
                    </option>
                  </select>

                </div>
                <div class="flex-1">
                  <!-- 会社名 -->
                  <TextInput
                    v-model="form.company_name"
                    :class="{
                      'border-red-500': getError('company_name'),
                      'border-gray-300': !getError('company_name')
                    }"
                    class="flex-1"
                    placeholder="〇〇建設"
                  />
                  <InputError :message="form.errors?.company_name" />
                </div>
                <div class="flex-1">
                <!-- 後 -->
                  <select
                    v-model="form.company_type_suffix"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2"
                  >
                    <option v-for="type in companyTypes" :key="type.value" :value="type.value">
                      {{ type.label }}
                    </option>
                  </select>
                </div>
              </div>
              <!-- p class="text-xs text-gray-500 mt-1">
                例）株式会社〇〇建設 ／ 〇〇建設株式会社
              </p -->
            </div>
            
            <div>
              <InputLabel :value="t('registers.company_kana')" required />

              <TextInput
                v-model="form.company_kana"
                @blur="form.company_kana = normalizeKana(form.company_kana)"
                :class="{
                  'border-red-500': getError('company_kana'),
                  'border-gray-300': !getError('company_kana')
                }"
                class="w-full"
              />
              <InputError :message="form.errors?.company_kana" />
           </div>

          </div>

         <!-- 右カラム：代表者/担当者 -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold mb-2">代表者</h3>
                      <!-- 肩書き -->
            <div class="flex-1">
                <InputLabel :value="t('registers.position')" class="h-5" />
                <TextInput v-model="form.corp.position" class="w-full" />
            </div>

            <div>
             <InputLabel value="代表者名" required />
              <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
                <div class="flex-1">
                  <TextInput v-model="form.rep_last_name"
                    :class="{
                      'border-red-500': getError('rep_last_name'),
                      'border-gray-300': !getError('rep_last_name')
                    }"
                    class="w-full" :placeholder="t('registers.last_name')" />
                  <InputError :message="form.errors?.rep_last_name" />
                </div>
                <div class="flex-1">  
                  <TextInput v-model="form.rep_first_name"
                    :class="{
                      'border-red-500': getError('rep_first_name'),
                      'border-gray-300': !getError('rep_first_name')
                    }"
                    class="w-full" :placeholder="t('registers.first_name')" />
                  <InputError :message="form.errors?.rep_first_name" />
                </div>
              </div>
            </div>
            <div>
              <InputLabel value="代表者名（カナ）" required />
              <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
                <div class="flex-1">
                  <TextInput v-model="form.rep_last_kana"
                    @blur="form.rep_last_kana = normalizeKana(form.rep_last_kana)"
                    :class="{
                      'border-red-500': getError('rep_last_kana'),
                      'border-gray-300': !getError('rep_last_kana')
                    }"
                   class="w-full" :placeholder="t('registers.last_name_kana')" />
                  <InputError :message="form.errors?.rep_last_kana" />
                </div>  
                <div class="flex-1">
                  <TextInput v-model="form.rep_first_kana"
                    @blur="form.rep_first_kana = normalizeKana(form.rep_first_kana)"
                    :class="{
                      'border-red-500': getError('rep_first_kana'),
                      'border-gray-300': !getError('rep_first_kana')
                    }"
                    class="w-full" :placeholder="t('registers.first_name_kana')" />
                  <InputError :message="form.errors?.rep_first_kana" />
                </div>
              </div>

            </div>

          </div>
        </div>
<!--  ここまでが会社情報　-->
        <h3></h3>
        <div class="p-4 bg-blue-50 border-l-4 border-blue-400 rounded shadow-sm mb-4">
          <h3 class="text-lg font-semibold text-blue-800">{{ t('registers.corp') }}</h3>
          <p class="text-sm text-blue-700 mt-1">法人の場合は、現在事項全部証明書(謄本)に記載してある住所を入力してください</p>
        </div>        
        <div>
          <InputLabel :value="t('registers.zip_code')" required />
          <TextInput
            v-model="form.corp.postal_code"
            placeholder="000-0000"
            maxlength="8"
            @input="onAddressZipInput"
            @keydown.enter.prevent
          />

          <ul v-if="candidates.length > 1" class="border rounded bg-white">
            <li
              v-for="candidate in candidates"
              :key="candidate.label"
              class="p-2 hover:bg-gray-100 cursor-pointer"
              @click="selectCandidate(candidate, 'corp.address1')"
            >
              {{ candidate.label }}
            </li>
          </ul>
          <InputError :message="form.errors['corp.postal_code']" />
        </div>

        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
            <div class="flex-1">
              <InputLabel :value="t('registers.address1')" required />
              <TextInput v-model="form.corp.address1"
                :class="{
                  'border-red-500': getError('corp.address1'),
                  'border-gray-300': !getError('corp.address1')
                }"
                class="w-full" placeholder="○○県△△市xx区"
              />
              <InputError :message="form.errors['corp.address1']" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address2')" required />
              <TextInput v-model="form.corp.address2"
                :class="{
                  'border-red-500': getError('corp.address2'),
                  'border-gray-300': !getError('corp.address2')
                }"
                class="w-full" placeholder="○○丁目○○番地" />
              <InputError :message="form.errors['corp.address2']" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address3')" />
              <TextInput v-model="form.corp.address3" class="w-full" placeholder="" />
            </div>
        </div>
        <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-1 gap-y-3 items-start">
            <div class="flex-1" >
              <InputLabel :value="t('registers.tel')" required />
              <TextInput
                v-model="form.corp.tel"
                :class="{
                  'border-red-500': getError('corp.tel'),
                  'border-gray-300': !getError('corp.tel')
                }"
                class="w-full"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'tel', e)"
                placeholder="03-1234-5678"
              />
              <InputError :message="form.errors['corp.tel']" />
              <p v-if="form.corp.tel"
                class="text-xs text-gray-500 mt-1">
                電話番号は 03-1234-5678 の形式で入力してください
              </p>
            </div>

            <div class="flex-1">
              <InputLabel :value="t('registers.fax')" />
              <TextInput
                v-model="form.corp.fax"
                class="w-full"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'fax', e)"
                placeholder=""
              />
              <p v-if="form.corp.fax" class="text-xs text-gray-500 mt-1">
                FAX番号は 03-1234-5678 の形式で入力してください
              </p>
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.mobile')" />
              <TextInput
                v-model="form.corp.mobile"
                class="w-full"
                placeholder=""
                maxlength="20"
                @input="e => onPhoneInput('corp', 'mobile', e)"
              />
              <p v-if="form.corp.mobile"
                class="text-xs text-gray-500 mt-1">
                携帯電話は 090-1234-5678 の形式で入力してください
              </p>
            </div>
        </div>
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
          <div class="flex-1">
              <InputLabel :value="t('registers.email')" required />
              <TextInput v-model="form.corp.email" class="w-full" />
              <p v-if="form.errors['corp.email']" class="text-red-500 text-sm mt-1" >
                {{ form.errors['corp.email'] }}
              </p>
          </div>
          <!-- 氏名 -->
          <div class="flex-1">
              <InputLabel :value="t('registers.staff')" class="h-5" required />
              <TextInput v-model="form.corp.last_name"
                  :class="{
                    'border-red-500': getError('corp.last_name'),
                    'border-gray-300': !getError('corp.last_name')
                    }"
                    class="w-full" />
              <InputError :message="form.errors['corp.last_name']" />
          </div>          
          <div class="flex-1">
              <InputLabel value="　" class="h-5" />
              <TextInput v-model="form.corp.first_name"
                  :class="{
                    'border-red-500': getError('corp.first_name'),
                    'border-gray-300': !getError('corp.first_name')
                  }"
                  class="w-full" />
              <InputError :message="form.errors['corp.first_name']" />
          </div>
        </div>
        <div class="mb-4 flex">
            <InputLabel :value="t('registers.note')" />
            <textarea
                v-model="form.corp.note"
                class="w-full border rounded px-3 py-2"
                rows="3"
            ></textarea>
            <InputError :message="form.errors.note" />
        </div>
        <div class="p-4 bg-green-50 border-l-4 border-green-400 rounded shadow-sm mb-4">
          <h3 class="text-lg font-semibold text-blue-800">{{ t('registers.mail') }}</h3>
        </div>                
        <!--     -->                  
        <div class="mb-4">
          <label class="flex items-center gap-2">
            <input
              type="checkbox"
              v-model="form.same_as_corp"
              class="rounded border-gray-300 text-indigo-600 shadow-sm"
            />
            <span class="text-sm text-gray-700">
              郵送先は現住所と同じ
            </span>
          </label>
        </div>
        <!-- チェックが入ると入力不可 -->
        <!-- ここから郵送先-->         
       <div
          :class="{
            'pointer-events-none opacity-50': form.same_as_corp
          }"
        > 
        <div>
            <InputLabel :value="t('registers.zip_code')" required />
            <TextInput
              v-model="form.mail.postal_code"
              placeholder="000-0000"
              maxlength="8"
              @input="onPostZipInput"
              @keydown.enter.prevent
            />
            <!-- 候補が2件以上ある場合は選択させる -->
            <ul v-if="candidates.length > 1" class="border rounded bg-white">
              <li
                v-for="candidate in candidates"
                :key="candidate.label"
                class="p-2 hover:bg-gray-100 cursor-pointer"
                @click="selectCandidate(candidate, 'mail.address1')"
              >
                {{ candidate.label }}
              </li>
            </ul>
            <InputError :message="form.errors['mail.post_zip']" />
        </div>
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
            <div class="flex-1">
              <InputLabel :value="t('registers.address1')" required />
              <TextInput v-model="form.mail.address1" class="w-full" placeholder="○○県△△市xx区" />
              <InputError :message="form.errors['mail.address1']" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address2')" required />
              <TextInput v-model="form.mail.address2" class="w-full" placeholder="○○丁目○○番地" />
              <InputError :message="form.errors['mail.address2']" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address3')" />
              <TextInput v-model="form.mail.address3" class="w-full" placeholder="" />
              <InputError :message="form.errors?.mail?.address3" />
            </div>
        </div>
<!--                    
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
            <div class="flex-1">
              <InputLabel :value="t('registers.tel')" />
              <TextInput
                v-model="form.mail.tel"
                class="w-full"
                maxlength="20"
                @input="e => onPhoneInput('mail', 'tel', e)"
                placeholder="03-1234-5678"
              />
              <InputError :message="form.errors['mail.tel']" />
              <p v-if="form.mail.tel"
                class="text-xs text-gray-500 mt-1">
                電話番号は 03-1234-5678 の形式で入力してください
              </p>
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.fax')" />
              <TextInput
                v-model="form.mail.fax"
                class="w-full"
                maxlength="20"
                @input="e => onPhoneInput('mail', 'fax', e)"
                placeholder="03-1234-5678"
              />
              <InputError :message="form.errors['mail.fax']" />
              <p v-if="form.mail.fax"
                class="text-xs text-gray-500 mt-1">
                FAX番号は 03-1234-5678 の形式で入力してください
              </p>
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.mobile')" />
              <TextInput
                v-model="form.mail.mobile"
                class="w-full"
                placeholder="090-xxxx-xxxx"
                maxlength="20"
                @input="e => onPhoneInput('mail', 'mobile', e)"
              />
              <InputError :message="form.errors['mail.mobile']" />
              <p v-if="form.mail.mobile"
                class="text-xs text-gray-500 mt-1">
                携帯電話は 090-1234-5678 の形式で入力してください
              </p>
            </div>
        </div>            

        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
            <div class="flex-1">
              <InputLabel :value="t('registers.position')" />
              <TextInput v-model="form.mail.position" class="w-full" />
              <InputError :message="form.errors['mail.position']" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.staff')" />
              <TextInput v-model="form.mail.last_name" class="w-full" />
              <InputError :message="form.errors['mail.last_name']" />
            </div>
            <div class="flex-1">
              <InputLabel value="　" />
              <TextInput v-model="form.mail.first_name" class="w-full" />
              <InputError :message="form.errors['mail.first_name']" />
            </div>
        </div>
-->
      </div>

      <div class="p-4 bg-orange-50 border-l-4 border-orange-400 rounded shadow-sm mb-4">
          <h3 class="text-lg font-semibold text-blue-800">{{ t('registers.agent') }}</h3>
      </div>                

      <div class="mb-4">
        <label class="flex items-center gap-2">
          <input
            type="checkbox"
            v-model="form.is_agent"
            class="rounded border-gray-300 text-indigo-600 shadow-sm"
          />
          <span class="text-sm text-gray-700">
            代理申請
          </span>
        </label>
      </div>        
        <!-- ここから代理人-->
      <div v-if="form.is_agent">

        <div class="">
          <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
            <div class="flex-1">
              <InputLabel :value="t('registers.zip_code')" required />
              <TextInput
                v-model="form.agent.postal_code"
                placeholder="000-0000"
                maxlength="8"
                @input="onAgentZipInput"
                @keydown.enter.prevent
              />
              <!-- 候補が2件以上ある場合は選択させる -->
              <ul v-if="candidates.length > 1" class="border rounded bg-white">
                <li
                  v-for="candidate in candidates"
                  :key="candidate.label"
                  class="p-2 hover:bg-gray-100 cursor-pointer"
                  @click="selectCandidate(candidate, 'agent.address1')"
                >
                  {{ candidate.label }}
                </li>
              </ul>
            </div>
            <div class="flex-[2]">
                <!-- 会社名 -->
                <InputLabel :value="t('registers.company_name')" required /> 
                <TextInput
                  v-model="form.agent.company_name"
                  :class="{
                    'border-red-500': getError('agent.company_name'),
                    'border-gray-300': !getError('agent.company_name')
                  }"
                  class="w-full"
                  placeholder="○○協同組合"
                />
                <InputError :message="form.errors['agent.company_name']" />
            </div>
          </div>
          <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
              <div class="flex-1">
                <InputLabel :value="t('registers.address1')" required />
                <TextInput v-model="form.agent.address1" class="w-full" placeholder="○○県△△市xx区" />
                <InputError :message="form.errors['agent.address1']" />
              </div>
              <div class="flex-1">
                <InputLabel :value="t('registers.address2')" required />
                <TextInput v-model="form.agent.address2" class="w-full" placeholder="○○丁目○○番地" />
                <InputError :message="form.errors['agent.address2']" />
              </div>
              <div class="flex-1">
                <InputLabel :value="t('registers.address3')" />
                <TextInput v-model="form.agent.address3" class="w-full" placeholder="" />
                <InputError :message="form.errors['agent.address3']" />
              </div>
          </div>            
          <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
              <div class="flex-1">
                <InputLabel :value="t('registers.tel')" required />
                <TextInput
                  v-model="form.agent.tel"
                  class="w-full"
                  maxlength="20"
                  @input="e => onPhoneInput('agent', 'tel', e)"
                  placeholder="03-1234-5678"
                />
                <InputError :message="form.errors['agent.tel']" />
                <p v-if="form.agent.tel"
                  class="text-xs text-gray-500 mt-1">
                  電話番号は 03-1234-5678 の形式で入力してください
                </p>
              </div>

              <div class="flex-1">
                <InputLabel :value="t('registers.fax')" />
                <TextInput
                  v-model="form.agent.fax"
                  class="w-full"
                  maxlength="20"
                  @input="e => onPhoneInput('agent', 'fax', e)"
                  placeholder=""
                />
                <InputError :message="form.errors['agent.fax']" />
                <p v-if="form.agent.fax"
                  class="text-xs text-gray-500 mt-1">
                  FAX番号は 03-1234-5678 の形式で入力してください
                </p>
              </div>
              <div class="flex-1">
                <InputLabel :value="t('registers.mobile')" />
                <TextInput
                  v-model="form.agent.mobile"
                  class="w-full"
                  placeholder=""
                  maxlength="20"
                  @input="e => onPhoneInput('agent', 'mobile', e)"
                />
                <InputError :message="form.errors['agent.mobile']" />
                <p v-if="form.agent.mobile"
                  class="text-xs text-gray-500 mt-1">
                  携帯電話は 090-1234-5678 の形式で入力してください
                </p>
              </div>
          </div>            
          <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
              <div class="flex-1">
                <InputLabel :value="t('registers.email')" />
                <TextInput v-model="form.agent.email" class="w-full" />
                <p v-if="form.errors['agent.email']" class="text-red-500 text-sm mt-1" >
                  {{ form.errors['agent.email'] }}
                </p>
              </div>
              <div class="flex-1"></div>
              <div class="flex-1"></div>
          </div>            
          <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
              <!-- 肩書き -->
              <div class="flex-1">
                <div>
                  <InputLabel :value="t('registers.number')" class="h-5" />
                  <TextInput
                    v-model="form.agent.position"
                    class="w-full"
                    :class="{ 'border-red-500': positionWarning }"
                    maxlength="8"
                    inputmode="numeric"
                    pattern="[0-9]{8}"
                    @input="e => {
                      const normalized = e.target.value
                        .replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0))
                        .replace(/[^0-9]/g, '')
                        .slice(0, 8)
                      form.agent.position = normalized
                      e.target.value = normalized
                      form.clearErrors('agent.position')
                    }"
                    @blur="positionWarning = form.agent.position.length > 0 && form.agent.position.length < 8"
                  />
                  <p v-if="positionWarning" class="text-red-500 text-sm mt-1">
                    8桁で入力してください
                  </p>
                </div>
                <InputError :message="form.errors['agent.position']" />
              </div>
              <!-- 氏名 -->
              <div class="flex-1">
                  <InputLabel :value="t('registers.staff')"  class="h-5" />
                  <TextInput v-model="form.agent.last_name" class="w-full" />
                  <InputError :message="form.errors['agent.last_name']" />
              </div>
              <div class="flex-1">
                <InputLabel value="　" class="h-5"/>
                <TextInput v-model="form.agent.first_name" class="w-full" />
                <InputError :message="form.errors['agent.first_name']" />
              </div>
          </div>
        </div>
        </div>
        <!-- ここまでが代理人--> 
        <div class="p-4 bg-blue-50 border-l-4 border-bule-400 rounded shadow-sm mb-4">
          <h3 class="text-lg font-semibold text-blue-800">{{ t('registers.bank') }}</h3>
        </div>    
        <!-- 2カラム -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">  
          <!-- 左カラム -->
          <div class="space-y-4 max-w-xl mx-auto p-2">

            <!-- 銀行選択 -->
            <InputLabel :value="t('banks.bank_category')" required />
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
              <button
                v-for="c in bankCategories"
                :key="c.id"
                type="button"
                @click="selectCategory(c)"
                :class="[
                  'px-4 py-1 rounded border text-sm',
                  selectedCategory === c.value
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-white text-gray-700 hover:bg-gray-100'
                ]"
              >
                {{ c.label }}
              </button>
            </div>
            <p v-if="form.errors?.bank_type" class="text-red-500 text-sm mt-1">
              {{ form.errors?.bank_type }}
            </p>
            <!-- 銀行名 + 銀行コード -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
              <div>
                <Autocomplete
                  :key="bankKey"
                  v-model="selectedBank"
                  label="銀行名"
                  required
                  fetch-url="/api/banks"
                  :extra-params="form.bank_type ? { category: form.bank_type } : {}"
                  :initial="form.bank_name"
                  @selected="handleBankSelected"
                />
                <p v-if="form.errors?.bank_name" class="text-red-500 text-sm mt-1">
                  {{ form.errors?.bank_name }}
                </p>
              </div>
              <div>
                <InputLabel :value="t('banks.bank_code')" required />
                <TextInput v-model="form.bank_code" class="w-full" />
                <InputError :message="form.errors?.bank_code" />
              </div>
            </div>

            <!-- 支店名 + 支店コード -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
              <!-- 支店名（通常銀行のみ） -->
              <div>
                <template v-if="form.bank_code !== '9900'">
                  <Autocomplete
                    v-if="form.bank_code"
                    :model-value="selectedBranch"
                    :label="t('banks.branch_name')"
                    fetch-url="/api/branches"
                    required
                    :extra-params="{ bank_code: form.bank_code }"
                    :initial="form.branch_name"
                    @selected="handleBranchSelected"
                  />
                  <p v-if="form.errors?.branch_name" class="text-red-500 text-sm mt-1">
                    {{ form.errors?.branch_name }}
                  </p>
                </template>
                <!-- ゆうちょ時のダミー表示（任意） -->
                <template v-else>
                  <InputLabel :value="t('banks.branch_name')" />
                  <p class="text-gray-400 text-sm mt-2">なし</p>
                </template>
              </div>

              <!-- 支店コード（常に入力可） -->
              <div>
                <InputLabel
                  :value="form.bank_code === '9900'
                    ? '記号'
                    : t('banks.branch_code')" required
                />

                <TextInput
                  v-model="form.branch_code"
                  class="w-full"
                  :maxlength="form.bank_code === '9900' ? 5 : 3"
                  :placeholder="form.bank_code === '9900'
                    ? '記号（5桁）'
                    : '支店コード（3桁）'"
                />

                <p v-if="form.errors.branch_code" class="text-red-500 text-sm mt-1">
                  {{ form.errors.branch_code }}
                </p>
              </div>

            </div>

          </div>


          <!-- 右カラム -->
          <div class="space-y-4">

            <div>
            <InputLabel value="口座種別（普通 / 当座）" />
            <select v-model="form.account_type" class="border p-2 w-full rounded">
                <option value="普通">普通</option>
                <option value="当座">当座</option>
            </select>
            </div>

            <div>
                <InputLabel value="口座番号" required />
                <TextInput v-model="form.account_no" class="w-full" />
                <InputError :message="form.errors.account_no" />
            </div>
            <div>
                <InputLabel value="口座名義" />
                <TextInput v-model="form.account_name" class="w-full" />
                <InputError :message="form.errors.account_name" />
            </div>
            <div>
                <InputLabel value="口座名義（カナ）" />
                <TextInput v-model="form.account_kana" class="w-full" />
                <InputError :message="form.errors.account_kana" />
            </div>
          </div>  
        </div>

        <div class="6">
          <PrimaryButton
            type="button"
            class="ml-auto bg-blue-600 hover:bg-blue-700"
            @click="submit"
          >
          {{ t('members.create') }}
          </PrimaryButton>
          <p class="text-xs text-gray-500 mt-2">入力内容に間違いがないか確認してください。</p>
        </div>

      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, computed, toRef } from 'vue'
import { Link, router, useForm,usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Autocomplete from '@/Components/Autocomplete.vue'
import axios from 'axios'
import { useZipcode } from '@/composables/useZipcode'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const page = usePage()
const bankInput = ref(null)

console.log(page.props) // ← ここで form が見える

const regions = page.props.regions || []

const persistQuery = () => {
  return { ...page.props.filters }
}

const form = useForm({
  type: page.props.form?.type ?? 'corporation',
  desired_join_month:page.props.form?.desired_join_month ?? '',
  company_kana: page.props.form?.company_kana ?? '',
  rep_last_kana: page.props.form?.rep_last_kana ?? '',
  rep_first_kana: page.props.form?.rep_first_kana ?? '',
  company_type_prefix: page.props.form?.company_type_prefix ?? '',
  company_name: page.props.form?.company_name ?? '',
  company_type_suffix: page.props.form?.company_type_suffix ?? '',
  rep_last_name: page.props.form?.rep_last_name ?? '',
  rep_first_name: page.props.form?.rep_first_name ?? '',
  same_as_corp: Boolean(Number(page.props.form?.same_as_corp)),
  is_agent: page.props.form?.is_agent,
  region_id: page.props.form?.region_id,
  number: page.props.form?.number,
  joined_at: page.props.form?.joined_at ? page.props.form.joined_at.slice(0, 10) : '',
  withdrawn_at: page.props.form?.withdrawn_at ? page.props.form?.withdrawn_at.slice(0, 10) : '',
  aplus_customer_no: page.props.form?.aplus_customer_no ?? '',
  jac_certification_no: page.props.form?.jac_certification_no ?? '',
  issued_at: page.props.form?.invoice?.issued_at ? page.props.form?.invoice?.issued_at.slice(0, 10) : '',
  paid_at: page.props.form?.invoice?.paid_at ? page.props.form?.invoice?.paid_at.slice(0, 10) : '',
  amount: page.props.form?.invoice?.amount ?? 0,

  corp: {
    type: 1,
    postal_code: page.props.form?.corp?.postal_code ?? '',
    address1: page.props.form?.corp?.address1 ?? '',
    address2: page.props.form?.corp?.address2 ?? '',
    address3: page.props.form?.corp?.address3 ?? '',
    tel: page.props.form?.corp?.tel ?? '',
    fax: page.props.form?.corp?.fax ?? '',
    mobile: page.props.form?.mail?.mobile ?? '',
    email: page.props.form?.corp?.email ?? '',
    position: page.props.form?.corp?.position ?? '',
    last_name: page.props.form?.corp?.last_name ?? '',
    first_name: page.props.form?.corp?.first_name ?? '',
    note:page.props.form?.corp?.note ?? '',
  },

  mail: {
    type: 2,
    postal_code: page.props.form?.mail?.postal_code ?? '',
    address1: page.props.form?.mail?.address1 ?? '',
    address2: page.props.form?.mail?.address2 ?? '',
    address3: page.props.form?.mail?.address3 ?? '',
    tel: page.props.form?.mail?.tel ?? '',
    fax: page.props.form?.mail?.fax ?? '',
    mobile: page.props.form?.mail?.mobile ?? '',
    email: page.props.form?.mail?.email ?? '',
    position: page.props.form?.mail?.position ?? '',
    last_name: page.props.form?.mail?.last_name ?? '',
    first_name: page.props.form?.mail?.first_name ?? '',
  },

  agent: {
    type: 3,
    company_name: page.props.form?.agent?.company_name ?? '',
    postal_code: page.props.form?.agent?.postal_code ?? '',
    address1: page.props.form?.agent?.address1 ?? '',
    address2: page.props.form?.agent?.address2 ?? '',
    address3: page.props.form?.agent?.address3 ?? '',
    tel: page.props.form?.agent?.tel ?? '',
    fax: page.props.form?.agent?.fax ?? '',
    mobile: page.props.form?.agent?.mobile ?? '',
    position: page.props.form?.agent?.position ?? '',
    last_name: page.props.form?.agent?.last_name ?? '',
    first_name: page.props.form?.agent?.first_name ?? '',
  },

  bank_type: page.props.form?.bank_type ?? '',
  bank_name: page.props.form?.bank_name ?? '',
  bank_code: page.props.form?.bank_code ?? '',
  bank_name_kana: page.props.form?.bank_name_kana ?? '',
  branch_name: page.props.form?.branch_name ?? '',
  branch_code: page.props.form?.branch_code ?? '',  
  branch_name_kana: page.props.form?.branch_name_kana ?? '',
  account_type: page.props.form?.account_type ?? '普通',
  account_no: page.props.form?.account_no ?? '',
  account_kana: page.props.form?.account_kana ?? '',
  account_name: page.props.form?.account_name ?? '',

});

const errors = ref({})

const positionWarning = ref(false)

//function getError(key) {
//  const keys = key.split('.')
//  return keys.reduce((acc, k) => (acc ? acc[k] : undefined), errors.value)
//}

function getError(key) {
  // ① Laravel array
  if (errors.value?.[key]) {
    const v = errors.value[key]
    return Array.isArray(v) ? v[0] : v
  }

  // ② フロント独自ネスト
  const keys = key.split('.')
  const nestedError = keys.reduce(
    (acc, k) => (acc ? acc[k] : undefined),
    errors.value
  )
  if (nestedError) return nestedError

  // ③ Inertia props（string）
  const pe = page.props.errors?.[key]
  return Array.isArray(pe) ? pe[0] : pe
}

const bankCategories = ref([])
const selectedCategory = ref(form.bank_type) 

/* 銀行 */
const selectedBank = ref(null)

/* 支店 */
const selectedBranch = ref(null)

// mounted 時に session の値で初期化
onMounted(() => {
  if (form.bank_name) {
  }
})

onMounted(async () => {
  const res = await axios.get('/api/bank-categories')

  bankCategories.value = res.data.map(c => ({
    value: c.id,
    label: c.bank_name
  }))
})

const bankCategory = ref('')
const bankKey = ref(0)

const selectCategory = async (category) => {
    console.log(category.value)
  // カテゴリ確定
  selectedCategory.value = category.value
  form.bank_type = category.value

  selectedBank.value = null
  form.bank_name = ''
  form.bank_code = ''
  form.bank_name_kana = ''
  form.branch_name = ''
  form.branch_code = ''
  form.branch_name_kana = ''

  if (category.value === 7) {
    selectedBank.value = {
        bank_code: '9900',
        label: 'ゆうちょ銀行',
        bank_category: 7,
    }
    form.bank_code = '9900'
    form.bank_name = 'ゆうちょ銀行'
    form.bank_name_kana = 'ﾕｳﾁﾖｷﾞﾝｺｳ'

  } else {
    form.bank = null
  }

  bankKey.value += 1

}

const handleBankSelected = (item) => {
  console.log(item)
  selectedBank.value = item            // v-model にオブジェクトを入れる
  form.bank_name = item.label          // form に銀行名を反映
  form.bank_id = item.id               // form に銀行 id を反映
  form.bank_code = item.bank_code
  form.bank_name_kana = item.name_kana
  // 支店は必ずリセット
  selectedBranch.value = null
  form.branch_name = ''
  form.branch_code = ''
  form.branch_name_kana = ''
}


const handleBranchSelected = (branch) => {
  selectedBranch.value = branch

  form.branch_name = branch.label
  form.branch_code = branch.branch_code
  form.branch_name_kana = branch.name_kana
//  console.log(branch,form.branch_name);
}

const fetchParams = computed(() => {
  console.log(selectedCategory.value ? { category: selectedCategory.value } : {})  
  return selectedCategory.value ? { category: selectedCategory.value } : {}
})

const extraParams = computed(() => {
    console.log(selectedBank.value);
  console.log(selectedBank.value ? { bank_code: selectedBank.value.bank_code } : {})

  return selectedBank.value ? { bank_code: selectedBank.value.bank_code } : {}
})

if (page.props.files?.history_certificate) {
  form.history_certificate_name = page.props.files.history_certificate_name ?? 'アップロード済みファイル';
  form.history_certificate = null; // File オブジェクトは再取得できないので null
}

if (page.props.files?.mail_address_certificate) {
  form.mail_address_certificate_name = page.props.files.mail_address_certificate_name ?? 'アップロード済みファイル';
  form.mail_address_certificate = null; // File オブジェクトは再取得できないので null
}

const submit = () => {
  form.post(route('admin.member.store'), {
    preserveScroll: true,
  })
}

const normalizePhone = (value) => {
  if (!value) return ''

  // 全角数字 → 半角
  value = value.replace(/[０-９]/g, s =>
    String.fromCharCode(s.charCodeAt(0) - 0xFEE0)
  )

  // 全角ハイフン → 半角
  value = value.replace(/[ー－―]/g, '-')

  // 数字とハイフン以外を除去
  return value.replace(/[^0-9-]/g, '')
}

/**
 * @param {'corp'|'mail'} target
 * @param {'tel'|'fax'|'mobile'} field
 */
const onPhoneInput = (target, field, e) => {
  form[target][field] = normalizePhone(e.target.value)
}


const normalizeKana = (value) => {
  if (!value) return ''

  // ひらがな → カタカナ
  value = value.replace(/[\u3041-\u3096]/g, s =>
    String.fromCharCode(s.charCodeAt(0) + 0x60)
  )

  // 全角カタカナ・長音・全角スペースのみ
  return value.replace(/[^\u30A0-\u30FFー　]/g, '')
}

//〒番号関係
const candidates = ref([])
/*
 * 郵便番号正規化
 * ・全角数字 → 半角
 * ・全角ハイフン → 半角
 * ・数字とハイフン以外を除去
 */
const normalizeZip = (value) => {
  if (!value) return ''

  // 全角数字 → 半角
  value = value.replace(/[０-９]/g, s =>
    String.fromCharCode(s.charCodeAt(0) - 0xFEE0)
  )

  // 全角ハイフン → 半角
  value = value.replace(/[ー－―]/g, '-')

  // 数字とハイフン以外を除去
  return value.replace(/[^0-9-]/g, '')
}
/**
 * 住所候補選択
 * @param {Object} candidate
 * @param {String} field  formのキー名
 */
function selectCandidate(candidate, field) {
  if (!candidate || !field) return
  if (!(field in form)) return

  form[field] = candidate.label
  candidates.value = []
}

const onAddressZipInput = (e) => {
  form.corp.postal_code = normalizeZip(e.target.value)
}

const onPostZipInput = (e) => {
  form.mail.postal_code = normalizeZip(e.target.value)
}

const onAgentZipInput = (e) => {
  form.agent.postal_code = normalizeZip(e.target.value)
}

useZipcode(
  toRef(form.corp, 'postal_code'),
  toRef(form.corp, 'address1')
)

useZipcode(
  toRef(form.mail, 'postal_code'),
  toRef(form.mail, 'address1')
)

useZipcode(
  toRef(form.agent, 'postal_code'),
  toRef(form.agent, 'address1')
)

const companyTypes = [
  { label: 'なし', value: '', kana: '' },
  { label: '株式会社', value: '株式会社', kana: 'カ' },
  { label: '有限会社', value: '有限会社', kana: 'ユ' },
  { label: '合名会社', value: '合名会社', kana: 'メ' },
  { label: '合資会社', value: '合資会社', kana: 'シ' },
  { label: '合同会社', value: '合同会社', kana: 'ゴ' },
]

const buildAccountKana = () => {
  const base = form.company_name_kana ?? ''

  // prefix（前）
  if (form.company_type_prefix) {
    const type = companyTypes.find(
      t => t.value === form.company_type_prefix
    )
    return type?.kana
      ? `${type.kana})${base}`
      : base
  }

  // suffix（後ろ）
  if (form.company_type_suffix) {
    const type = companyTypes.find(
      t => t.value === form.company_type_suffix
    )
    return type?.kana
      ? `${base}(${type.kana}`
      : base
  }

  return base
}

watch(
  () => [
    form.company_type_prefix,
    form.company_type_suffix,
    form.company_name_kana,
  ],
  () => {
    form.account_kana = buildAccountKana()
  }
)
// 郵送先が同じならコピーする
watch(
  () => [form.same_as_corp, { ...form.corp }],
  ([checked]) => {
    if (!checked) return

    form.mail = {
      ...form.corp,
    }
  },
  { deep: true }  // ← corp内のネストしたプロパティの変更も検知
)

// 会社情報から口座名カナ、口座名を自動設定する
const getCompanyTypeKana = (prefix) => {
  const type = companyTypes.find(t => t.value === prefix)
  return type?.kana ?? ''
}
// prefix 用
const prefixKana = getCompanyTypeKana(form.company_type_prefix)

// suffix 用（同じ関数をそのまま使う）
const suffixKana = getCompanyTypeKana(form.company_type_suffix)

watch(
  [
    () => form.company_type_prefix,
    () => form.company_kana,
    () => form.company_name,
    () => form.company_type_suffix,
    () => form.corp.position,
    () => form.rep_last_name,
    () => form.rep_first_name,
  ],
  () => {
    // ===== 口座名義（カナ） =====
    const prefixKana = getCompanyTypeKana(form.company_type_prefix)
    const suffixKana = getCompanyTypeKana(form.company_type_suffix)

    if (prefixKana) {
      // 株式会社○○ → カ）○○ / 有限会社○○ → ユ）○○
      form.account_kana = `${prefixKana}）${form.company_kana ?? ''}`
    } else if (suffixKana) {
      // ○○株式会社 → ○○（カ / ○○有限会社 → ○○（ユ
      form.account_kana = `${form.company_kana ?? ''}（${suffixKana}`
    } else {
      form.account_kana = form.company_kana ?? ''
    }

    form.account_kana = form.account_kana.replace(/\s+/g, '')


    // ===== 口座名義（漢字） =====

    form.account_name = (
      `${form.company_type_prefix ?? ''}` +
      `${form.company_name ?? ''}` +
      `${form.company_type_suffix ?? ''}` + 
      `${form.corp.position ? ' ' + form.corp.position : ''}` +
      `${form.rep_last_name ?? ''}` +
      `${form.rep_first_name ?? ''}`
    ).trim()//replace(/\s+/g, '')

  },
  { immediate: true }
)
// prefixまたはsuffixが入ったらもう一方は消す
watch(
  () => form.company_type_prefix,
  (val) => {
    if (val) form.company_type_suffix = ''
  }
)

watch(
  () => form.company_type_suffix,
  (val) => {
    if (val) form.company_type_prefix = ''
  }
)
// 会社情報の代表者名が入力されたら,corp.にも入れる
watch(
  () => [form.rep_last_name, form.rep_first_name],
  ([last, first]) => {
    if (last) {
      form.corp.last_name = last
    }
    if (first) {
      form.corp.first_name = first
    }
  },
  { immediate: true }
)


//銀行コードまたは支店コードを半角数字化
const toHalfWidthNumber = (value) => {
  if (!value) return ''
  return value
    .replace(/[０-９]/g, (s) =>
      String.fromCharCode(s.charCodeAt(0) - 0xFEE0)
    )
    .replace(/[^0-9]/g, '')
}

watch(() => form.bank_code, (val) => {
  form.bank_code = toHalfWidthNumber(val)
})

watch(() => form.branch_code, (val) => {
  form.branch_code = toHalfWidthNumber(val)
})

watch(() => form.account_no, (val) => {
  form.account_no = toHalfWidthNumber(val)
})

const focusNext = (e) => {

  if (e.isComposing) return

  const form = e.target.form
  const index = Array.prototype.indexOf.call(form, e.target)

  if (index > -1) {
    e.preventDefault()
    form.elements[index + 1]?.focus()
  }
}

const joinMonthOptions = computed(() => {
  const today = new Date()
  const day = today.getDate()

  const base = new Date(today.getFullYear(), today.getMonth(), 1)

  const addMonth = (date, n) => {
    return new Date(date.getFullYear(), date.getMonth() + n, 1)
  }

  const format = (date) => {
    const y = date.getFullYear()
    const m = String(date.getMonth() + 1).padStart(2, '0')
    return `${y}-${m}`
  }

  if (day <= 20) {
    return [
      { label: format(base), value: format(base) },           // 当月
      { label: format(addMonth(base, 1)), value: format(addMonth(base, 1)) } // 翌月
    ]
  } else {
    return [
      { label: format(addMonth(base, 1)), value: format(addMonth(base, 1)) }, // 翌月
      { label: format(addMonth(base, 2)), value: format(addMonth(base, 2)) }  // 翌々月
    ]
  }
})
</script>



