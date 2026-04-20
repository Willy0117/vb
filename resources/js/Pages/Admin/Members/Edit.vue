<template>
  <AppLayout>
    <template #header>{{ t('members.edit') }}</template>

    <div class="max-w-5xl mx-auto bg-white p-8 rounded shadow">
      <h2 class="text-2xl font-bold mb-6">{{ t('registers.members') }}</h2>

      <form @submit.prevent="submitForm" class="space-y-8">
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
          <!--  ここから会社情報　-->

          <!-- 左カラム：会社情報 -->
          <div class="space-y-4">
 
            <div>
              <InputLabel :value="t('registers.company_name')" /> 

              <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end">
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
                    placeholder="〇〇商事"
                  />
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
            </div>
            <div>
              <InputLabel :value="t('registers.company_kana')" />

              <TextInput
                v-model="companyKana"
                :class="{
                  'border-red-500': getError('company_kana'),
                  'border-gray-300': !getError('company_kana')
                }"
                class="w-full"
              />
            </div>  
          </div>

         <!-- 右カラム：代表者/担当者 -->
          <div class="space-y-4">
            <div>
             <InputLabel value="代表者名" />
              <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end">
                <div class="flex-1">
                  <TextInput v-model="form.rep_last_name"
                    :class="{
                      'border-red-500': getError('rep_last_name'),
                      'border-gray-300': !getError('rep_last_name')
                    }"
                    class="w-full" :placeholder="t('registers.last_name')" />
                </div>
                <div class="flex-1">  
                  <TextInput v-model="form.rep_first_name"
                    :class="{
                      'border-red-500': getError('rep_first_name'),
                      'border-gray-300': !getError('rep_first_name')
                    }"
                    class="w-full" :placeholder="t('registers.first_name')" />
                </div>
              </div>
            </div>
            <div>
              <InputLabel value="代表者名（フリガナ）" />
              <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end">
                <div class="flex-1">
                  <TextInput v-model="form.rep_last_kana"
                    :class="{
                      'border-red-500': getError('rep_last_kana'),
                      'border-gray-300': !getError('rep_last_kana')
                    }"
                   class="w-full" :placeholder="t('registers.last_name_kana')" />
                </div>  
                <div class="flex-1">
                  <TextInput v-model="form.rep_first_kana"
                    :class="{
                      'border-red-500': getError('rep_first_kana'),
                      'border-gray-300': !getError('rep_first_kana')
                    }"
                    class="w-full" :placeholder="t('registers.first_name_kana')" />
                </div>
              </div>

            </div>
          </div>
        </div>
<!--  ここまでが会社情報　-->     
        <div>
          <InputLabel :value="t('registers.zip_code')" />
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
              <InputLabel :value="t('registers.address1')" />
              <TextInput v-model="form.corp.address1"
                :class="{
                  'border-red-500': getError('corp.address1'),
                  'border-gray-300': !getError('corp.address1')
                }"
                class="w-full"
              />
              <InputError :message="form.errors['corp.address1']" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address2')" />
              <TextInput v-model="form.corp.address2"
                :class="{
                  'border-red-500': getError('corp.address2'),
                  'border-gray-300': !getError('corp.address2')
                }"
                class="w-full" />
              <InputError :message="form.errors['corp.address2']" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address3')" />
              <TextInput v-model="form.corp.address3" class="w-full" />
            </div>
        </div>
        <div class="mb-4 grid grid-cols-4 gap-x-1 gap-y-3 sm:flex-row sm:items-start">
            <div>
              <InputLabel :value="t('registers.tel')" />
              <TextInput
                v-model="form.corp.tel"
                :class="{
                  'border-red-500': getError('corp.tel'),
                  'border-gray-300': !getError('corp.tel')
                }"
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

            <div>
              <InputLabel :value="t('registers.fax')" />
              <TextInput
                v-model="form.corp.fax"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'fax', e)"
                placeholder="03-1234-5678"
              />
              <p v-if="form.corp.fax" class="text-xs text-gray-500 mt-1">
                FAX番号は 03-1234-5678 の形式で入力してください
              </p>
            </div>
            <div>
              <InputLabel :value="t('registers.mobile')" />
              <TextInput
                v-model="form.corp.mobile"
                class="w-full"
                placeholder="090-xxxx-xxxx"
                maxlength="20"
                @input="e => onPhoneInput('corp', 'mobile', e)"
              />
              <p v-if="form.corp.mobile"
                class="text-xs text-gray-500 mt-1">
                携帯電話は 090-1234-5678 の形式で入力してください
              </p>
            </div>
            <div>
              <InputLabel>{{ t('registers.email') }}</InputLabel>

              <TextInput v-model="form.corp.email" class="w-full" />
              <p v-if="form.errors['corp.email']" class="text-red-500 text-sm mt-1" >
                {{ form.errors['corp.email'] }}
              </p>
            </div>
        </div>
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
          <!-- 肩書き -->
          <div class="flex-1">
              <InputLabel :value="t('registers.position')" class="h-5" />
              <TextInput v-model="form.corp.position" class="w-full" />
          </div>
          <!-- 氏名 -->
          <div class="flex-1">
              <InputLabel :value="t('registers.staff')" class="h-5" />
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
        <!-- セクション単位Saveボタン -->
        <div class="flex justify-end space-x-3 items-center mt-6">
          <!-- 更新ボタン -->
          <PrimaryButton
            type="button"
            class="bg-blue-600 hover:bg-blue-700"
            @click="saveBasic"
          >
            {{ t('update') }}
          </PrimaryButton>
          <!-- キャンセルボタン -->
          <SecondaryButton>
            <Link :href="route('admin.member.index', persistQuery())" class="inline-flex items-center">
              <ArrowLeftIcon class="w-4 h-4 mr-2"/>
              {{ t('actions.cancel') }}
            </Link>
          </SecondaryButton>
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
            <InputLabel :value="t('registers.zip_code')" />
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
              <InputLabel :value="t('registers.address1')" />
              <TextInput v-model="form.mail.address1" class="w-full" placeholder="○○県△△市xx区" />
              <InputError :message="form.errors['mail.address1']" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address2')" />
              <TextInput v-model="form.mail.address2" class="w-full" placeholder="○○丁目○○番地" />
              <InputError :message="form.errors['mail.address2']" />
            </div>
            <div class="flex-1">
              <InputLabel :value="t('registers.address3')" />
              <TextInput v-model="form.mail.address3" class="w-full" placeholder="xxxビル○○F" />
              <InputError :message="form.errors?.mail?.address3" />
            </div>
        </div>            
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
            <div>
              <InputLabel :value="t('registers.tel')" />
              <TextInput
                v-model="form.mail.tel"
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
            <div>
              <InputLabel :value="t('registers.fax')" />
              <TextInput
                v-model="form.mail.fax"
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
            <div>
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
            <!-- 肩書き -->
            <div class="flex-1">
              <div>
                <InputLabel :value="t('registers.position')" />
                <TextInput v-model="form.mail.position" class="w-full" />
              </div>
              <InputError :message="form.errors['mail.position']" />
            </div>

            <!-- 氏名 -->
            <div class="flex-1">
              <div>
                <InputLabel :value="t('registers.staff')" />
                <div class="flex gap-2">
                  <TextInput v-model="form.mail.last_name" class="w-full" />
                  <TextInput v-model="form.mail.first_name" class="w-full" />
                </div>
              </div>
              <InputError :message="form.errors['mail.last_name']" />
              <InputError :message="form.errors['mail.first_name']" />
            </div>
        </div>
          <div class="flex justify-end space-x-3 items-center mt-6">
            <!-- 更新ボタン -->
            <PrimaryButton
              type="button"
              class="bg-blue-600 hover:bg-blue-700"
              @click="saveMail"
            >
              {{ t('update') }}
            </PrimaryButton>
            <!-- キャンセルボタン -->
            <SecondaryButton>
              <Link :href="route('admin.member.index', persistQuery())" class="inline-flex items-center">
                <ArrowLeftIcon class="w-4 h-4 mr-2"/>
                {{ t('actions.cancel') }}
              </Link>
            </SecondaryButton>
          </div>  
        </div>
                  

        <!-- ここから代理人-->
        <div>

        <div class="p-4 bg-orange-50 border-l-4 border-orange-400 rounded shadow-sm mb-4">
          <h3 class="text-lg font-semibold text-blue-800">{{ t('registers.agent') }}</h3>
        </div>                

        <div class="">
          <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
            <div class="flex-1">
              <InputLabel :value="t('registers.zip_code')" />
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
                <InputLabel :value="t('registers.company_name')" /> 
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
                <InputLabel :value="t('registers.address1')" />
                <TextInput v-model="form.agent.address1" class="w-full" placeholder="○○県△△市xx区" />
                <InputError :message="form.errors['agent.address1']" />
              </div>
              <div class="flex-1">
                <InputLabel :value="t('registers.address2')" />
                <TextInput v-model="form.agent.address2" class="w-full" placeholder="○○丁目○○番地" />
                <InputError :message="form.errors['agent.address2']" />
              </div>
              <div class="flex-1">
                <InputLabel :value="t('registers.address3')" />
                <TextInput v-model="form.agent.address3" class="w-full" placeholder="xxxビル○○F" />
                <InputError :message="form.errors['agent.address3']" />
              </div>
          </div>            
          <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start">
              <div class="flex-1">
                <InputLabel :value="t('registers.tel')" />
                <TextInput
                  v-model="form.agent.tel"
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
                  maxlength="20"
                  @input="e => onPhoneInput('agent', 'fax', e)"
                  placeholder="03-1234-5678"
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
                  placeholder="090-xxxx-xxxx"
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
              <!-- 肩書き -->
              <div class="flex-1">
                <div>
                  <InputLabel :value="t('registers.position')" class="h-5" />
                  <TextInput v-model="form.agent.position" class="w-full" />
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

        <!-- セクション単位Saveボタン -->
        <div class="flex justify-end space-x-3 items-center mt-6">
          <!-- 更新ボタン -->
          <PrimaryButton
            type="button"
            class="bg-blue-600 hover:bg-blue-700"
            @click="saveAgent"
          >
            {{ t('update') }}
          </PrimaryButton>
          <!-- キャンセルボタン -->
          <SecondaryButton>
            <Link :href="route('admin.member.index', persistQuery())" class="inline-flex items-center">
              <ArrowLeftIcon class="w-4 h-4 mr-2"/>
              {{ t('actions.cancel') }}
            </Link>
          </SecondaryButton>
        </div>
      </div>
      <!-- ここまでが代理人-->
      <div class="p-4 bg-orenge-50 border-l-4 border-orenge-400 rounded shadow-sm mb-4">
        <h3 class="text-lg font-semibold text-blue-800">{{ t('registers.bank') }}</h3>
      </div>         
      <div class="">
        <!-- 2カラム -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- 左カラム -->
          <div class="space-y-4 max-w-xl mx-auto p-2">

            <!-- 銀行選択 -->
            <InputLabel :value="bankCategories.select_bank" />
            <div class="grid grid-cols-4 gap-2 mb-3">
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

            <!-- 銀行名 + 銀行コード -->
            <div class="grid grid-cols-2 gap-4 mb-2">
              <div>
                <Autocomplete
                  :key="bankKey"
                  v-model="selectedBank"
                  label="銀行名"
                  fetch-url="/api/banks"
                  :extra-params="form.bank.bank_type ? { category: form.bank.bank_type } : {}"
                  :initial="form.bank.bank_name"
                  @selected="handleBankSelected"
                />
                <p v-if="errors.bank_name" class="text-red-500 text-sm mt-1">
                  {{ errors.bank_name }}
                </p>
              </div>
              <div>
                <InputLabel :value="t('banks.bank_code')" />
                <TextInput v-model="form.bank.bank_code" class="w-full" />
              </div>
            </div>

            <!-- 支店名 + 支店コード -->
            <div class="grid grid-cols-2 gap-4">

              <!-- 支店名（通常銀行のみ） -->
              <div>
                <template v-if="form.bank.bank_code !== '9900'">
                  <Autocomplete
                    v-if="form.bank.bank_code"
                    :model-value="selectedBranch"
                    :label="t('banks.branch_name')"
                    fetch-url="/api/branches"
                    :extra-params="{ bank_code: form.bank.bank_code }"
                    :initial="form.bank.branch_name"
                    @selected="handleBranchSelected"
                  />
                  <p v-if="errors.branch_name" class="text-red-500 text-sm mt-1">
                    {{ errors.branch_name }}
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
                  :value="form.bank.bank_code === '9900'
                    ? '記号'
                    : t('banks.branch_code')"
                />

                <TextInput
                  v-model="form.bank.branch_code"
                  class="w-full"
                  :maxlength="form.bank.bank_code === '9900' ? 5 : 3"
                  :placeholder="form.bank.bank_code === '9900'
                    ? '記号（5桁）'
                    : '支店コード（3桁）'"
                />

                <p v-if="errors.branch_code" class="text-red-500 text-sm mt-1">
                  {{ errors.branch_code }}
                </p>
              </div>

            </div>

          </div>


          <!-- 右カラム -->
          <div class="space-y-4">

            <div>
            <InputLabel value="口座種別（普通 / 当座）" />
            <select v-model="form.bank.account_type" class="border p-2 w-full rounded">
                <option value="普通">普通</option>
                <option value="当座">当座</option>
            </select>
            </div>

            <div>
              <InputLabel value="口座番号" />
              <TextInput
                v-model="form.bank.account_no"
                :maxlength="bankAccountMaxLength"
                @input="validateAccountNo"
                class="w-full"
              />
              <p v-if="accountNoError" class="text-red-500 text-sm mt-1">{{ accountNoError }}</p>
            </div>

            <div>
                <InputLabel value="口座名義（フリガナ）" />
                <TextInput v-model="form.bank.account_kana" class="w-full" />
                <InputError :message="form.errors.account_kana" />
            </div>
            <div>
                <InputLabel value="口座名義" />
                <TextInput v-model="form.bank.account_name" class="w-full" />
                <InputError :message="form.errors.account_name" />
            </div>
                <!-- 注意文言 -->
            <p class="text-xs text-gray-500 mt-2">
                肩書を忘れないように！
            </p>
          </div>  
        </div>
      <!-- セクション単位Saveボタン -->
        <div class="flex justify-end space-x-3 items-center mt-6">
          <!-- 更新ボタン -->
          <PrimaryButton
            type="button"
            class="bg-blue-600 hover:bg-blue-700"
            @click="saveBank"
          >
            {{ t('update') }}
          </PrimaryButton>
          <!-- キャンセルボタン -->
          <SecondaryButton>
            <Link :href="route('admin.member.index', persistQuery())" class="inline-flex items-center">
              <ArrowLeftIcon class="w-4 h-4 mr-2"/>
              {{ t('actions.cancel') }}
            </Link>
          </SecondaryButton>
        </div> 
      </div>
                   


      <section class="bg-white rounded shadow p-4">
          <h2 class="font-bold mb-2">提出書類</h2>
          <div class="flex gap-4">
              <div v-for="d in form.documents" :key="d.type + d.path">
              <img
                  v-if="d.thumbnail_path"
                  :src="d.thumbnail_path"
                  class="w-24 cursor-pointer"
                  @click="openPdf(d.path)"
              />
              </div>
          </div>
      </section>  
<!--
      <div class="flex space-x-3 items-center mt-6">
        <PrimaryButton
          type="button"
          class="bg-blue-600 hover:bg-blue-700"
          @click="submitForm"
        >
          {{ t('update') }}
        </PrimaryButton>

        <SecondaryButton>
          <Link :href="route('admin.member.index', persistQuery())" class="inline-flex items-center">
            <ArrowLeftIcon class="w-4 h-4 mr-2"/>
            {{ t('actions.cancel') }}
          </Link>
        </SecondaryButton>
      </div>
-->      

      </form>

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
import { ref, onMounted, watch, nextTick, computed, toRef } from 'vue'
import { Link, router, useForm,usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

import AppLayout from '@/Layouts/Admin/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue'
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue'
import Autocomplete from '@/Components/Autocomplete.vue'
import axios from 'axios'
import { useZipcode } from '@/composables/useZipcode'
import { useI18n } from 'vue-i18n'
import { ArrowLeftIcon} from '@heroicons/vue/24/outline'


const { t } = useI18n()

const page = usePage()

console.log(page.props) // ← ここで form が見える
const regions = page.props.regions || []

const persistQuery = () => {
  return { ...page.props.filters }
}
const form = useForm({
  type: page.props.form?.type ?? 'corporation',
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
    note: page.props.form?.corp?.note ?? '',
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
    email: page.props.form?.agent?.email ?? '',
    position: page.props.form?.agent?.position ?? '',
    last_name: page.props.form?.agent?.last_name ?? '',
    first_name: page.props.form?.agent?.first_name ?? '',
  },
  bank:{
    bank_type: page.props.form?.bank_account?.bank_type ?? '',
    bank_name: page.props.form?.bank_account?.bank_name ?? '',
    bank_code: page.props.form?.bank_account?.bank_code ?? '',
    branch_name: page.props.form?.bank_account?.branch_name ?? '',
    branch_code: page.props.form?.bank_account?.branch_code ?? '',  
    account_type: page.props.form?.bank_account?.account_type ?? '普通',
    account_no: page.props.form?.bank_account?.account_no ?? '',
    account_kana: page.props.form?.bank_account?.account_kana ?? '',
    account_name: page.props.form?.bank_account?.account_name ?? '',
  },

  documents: page.props.form?.documents,
});

watch(
  () => form.errors,
  e => {
    console.log('form errors:', e)
  },
  { deep: true }
)

// エラー
//const errors = page.props.errors || {}
const errors = ref({})

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


const previewPdf = ref(null)

const openPdf = (pdfPath) => {
  console.log('PDF PATH:', pdfPath)
  if (!pdfPath) return

  // 例：フルパス化
  previewPdf.value = pdfPath

  // 例：ここで loading true
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

const companyKana = computed({
  get: () => form.company_kana,
  set: (value) => {
    form.company_kana = normalizeKana(value)
  },
})


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
  () => form.same_as_corp,
  (checked) => {
    if (!checked) return

    form.mail = {
      ...form.corp,
    }
  }
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
    // ===== 口座名義（フリガナ） =====
    // ===== 口座名義（フリガナ） =====
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
      `${form.corp.position ?? ''}` +
      `${form.rep_last_name ?? ''}` +
      `${form.rep_first_name ?? ''}`
    ).replace(/\s+/g, '')
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
/*
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
*/

//銀行コードまたは支店コードを半角数字化
const toHalfWidthNumber = (value) => {
  if (!value) return ''
  return value
    .replace(/[０-９]/g, (s) =>
      String.fromCharCode(s.charCodeAt(0) - 0xFEE0)
    )
    .replace(/[^0-9]/g, '')
}



watch(() => form.bank.bank_code, (val) => {
  form.bank.bank_code = toHalfWidthNumber(val)
})

watch(() => form.bank.branch_code, (val) => {
  form.bank.branch_code = toHalfWidthNumber(val)
})

const bankCategories = ref([])
const selectedCategory = ref(form.bank.bank_type) 

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
  form.bank.bank_type = category.value

  selectedBank.value = null
  form.bank.bank_name = ''
  form.bank.bank_code = ''
  form.bank.branch_name = ''
  form.bank.branch_code = ''

  if (category.value === 7) {
    selectedBank.value = {
        bank_code: '9900',
        label: 'ゆうちょ銀行',
        bank_category: 7,
    }
    form.bank.bank_code = '9900'
    form.bank.bank_name = 'ゆうちょ銀行'

  } else {
    form.bank.bank = null
  }

  bankKey.value += 1

}

const handleBankSelected = (item) => {
  console.log(item)
  selectedBank.value = item            // v-model にオブジェクトを入れる
  form.bank.bank_name = item.label          // form に銀行名を反映
  form.bank.bank_id = item.id               // form に銀行 id を反映
  form.bank.bank_code = item.bank_code
  // 支店は必ずリセット
  selectedBranch.value = null
  form.bank.branch_name = ''
  form.bank.branch_code = ''
}


const handleBranchSelected = (branch) => {
  selectedBranch.value = branch

  form.bank.branch_name = branch.label
  form.bank.branch_code = branch.branch_code
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
// 口座番号エラー表示用
const accountNoError = ref(null)

// 銀行コードに応じて maxlength を切り替え
const bankAccountMaxLength = computed(() => {
  if (form.bank.bank_code === '9900') {
    return 8 // ゆうちょ銀行は8桁
  }
  return 7 // それ以外は7桁
})

function toHalfWidth(str) {
  return str.replace(/[０-９]/g, s =>
    String.fromCharCode(s.charCodeAt(0) - 0xFEE0)
  )
}
// 入力チェック（桁数超過や数字以外の入力防止）
function validateAccountNo() {
  accountNoError.value = null

  // 全角→半角変換
  form.bank.account_no = toHalfWidth(form.bank.account_no)

  // 数字以外除去
  form.bank.account_no = form.bank.account_no.replace(/\D/g, '')

  if (form.bank.account_no.length > bankAccountMaxLength.value) {
    accountNoError.value =
      `口座番号は${bankAccountMaxLength.value}桁で入力してください`

    form.bank.account_no =
      form.bank.account_no.slice(0, bankAccountMaxLength.value)
  }
}

// 銀行コードが変わったら口座番号もリセットする場合
watch(() => form.bank.bank_code, () => {
  form.bank.account_no = ''
  accountNoError.value = null
})

const saveBasic = () => {
  form.post(`/admin/member/${page.props.form.id}/save-basic`, {
    preserveScroll: true,
    onSuccess: () => alert('法人情報を保存しました'),
  })
}

const saveMail = () => {
  form.post(`/admin/member/${page.props.form.id}/save-mail`, {
    preserveScroll: true,
    onSuccess: () => alert('郵送先情報を保存しました'),
  })
}

const saveAgent = () => {
  form.post(`/admin/member/${page.props.form.id}/save-agent`, {
    preserveScroll: true,
    onSuccess: () => alert('行政書士・監理団体・登録支援機関情報を保存しました'),
  })
}

const saveBank = () => {
  router.post(`/admin/member/${page.props.form.id}/save-bank`, form.bank, {
    preserveScroll: true,
    onSuccess: () => alert('銀行情報を保存しました'),
  })
}

function onAmountInput(e) {
  // 半角数字と小数点だけ残す
  e.target.value = e.target.value.replace(/[^\d.]/g, '')
  // Vue側のフォームに反映
  form.amount = e.target.value
}

const numberError = ref('')

const normalizeNumber = async (value) => {
  if (!value) return ''

  let v = value
    .replace(/[Ａ-Ｚａ-ｚ０-９]/g, s =>
      String.fromCharCode(s.charCodeAt(0) - 0xFEE0)
    )
    .toUpperCase()
    .replace(/[^A-Z0-9]/g, '')

  const matchAlpha = v.match(/^([A-Z])(\d+)$/)
  if (matchAlpha) {
    v = (matchAlpha[1] + matchAlpha[2].padStart(4, '0')).slice(0, 5)
  } else if (v.match(/^\d+$/)) {
    v = v.padStart(5, '0').slice(0, 5)
  } else {
    v = v.slice(0, 5)
  }

  // 重複チェック
  try {
    const res = await axios.post('/admin/member/check-number', { number: v })
    numberError.value = res.data.available ? '' : 'この番号は既に使用されています'
  } catch (e) {
    numberError.value = 'チェックに失敗しました'
  }

  return v
}

// blur ハンドラを作る
const handleNumberBlur = async () => {
  form.number = await normalizeNumber(form.number)
}
</script>
