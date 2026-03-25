<?php

return [

    'required' => ':attribute が未入力または形式が間違っています。',
    'required_if' => ':attribute が未入力またはファイルが選択されていません。',

    'attributes' => [
        'company_name'=> '会社名',
        'company_kana'=> '会社名(カナ)',
        'address1'=> '市町村区',
        'address2'=> '番地',
        'tel'=> '電話番号',
        'fax'=> 'FAX',
        'history_certificate' => '履歴事項全部証明書',
        'mail_address_certificate' => '送付先住所確認書類',
        'bank_name' => '銀行名',
        'bank_code' => '銀行コード',
        'branch_name' => '支店名',
        'account_no' => '口座番号',
        'branch_code' => '支店コード',
        'rep_last_kana' => '姓（カナ）',
        'rep_first_kana' => '名（カナ）',
        'rep_last_name' => '姓',
        'rep_first_name' => '名',
        'agent.company_name' => '代理店名',
        'corp.email' => 'メールアドレス', 
        'corp.address1' => '市町村区', 
        'corp.address2' => '番地', 
    ],
];
