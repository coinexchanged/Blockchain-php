<?php
return [
    'unique'               => ':attribute 已存在',
    'accepted'             => ':attribute 是被接受的',
    'active_url'           => ':attribute 必須是壹個合法的 URL',
    'after'                => ':attribute 必須是 :date 之後的壹個日期',
    'alpha'                => ':attribute 必須全部由字母字符構成。',
    'alpha_dash'           => ':attribute 必須全部由字母、數字、中劃線或下劃線字符構成',
    'alpha_num'            => ':attribute 必須全部由字母和數字構成',
    'array'                => ':attribute 必須是個數組',
    'before'               => ':attribute 必須是 :date 之前的壹個日期',
    'between'              => [
        'numeric' => ':attribute 必須在 :min 到 :max 之間',
        'file'    => ':attribute 必須在 :min 到 :max KB之間',
        'string'  => ':attribute 必須在 :min 到 :max 個字符之間',
        'array'   => ':attribute 必須在 :min 到 :max 項之間',
    ],
    'boolean'              => ':attribute 字符必須是 true 或 false',
    'confirmed'            => ':attribute 二次確認不匹配',
    'date'                 => ':attribute 必須是壹個合法的日期',
    'date_format'          => ':attribute 與給定的格式 :format 不符合',
    'different'            => ':attribute 必須不同於:other',
    'digits'               => ':attribute 必須是 :digits 位',
    'digits_between'       => ':attribute 必須在 :min and :max 位之間',
    'email'                => ':attribute 必須是壹個合法的電子郵件地址。',
    'filled'               => ':attribute 的字段是必填的',
    'exists'               => '選定的 :attribute 是無效的',
    'image'                => ':attribute 必須是壹個圖片 (jpeg, png, bmp 或者 gif)',
    'in'                   => '選定的 :attribute 是無效的',
    'integer'              => ':attribute 必須是個整數',
    'ip'                   => ':attribute 必須是壹個合法的 IP 地址。',
    'max'                  => [
        'numeric' => ':attribute 的最大長度為 :max 位',
        'file'    => ':attribute 的最大為 :max',
        'string'  => ':attribute 的最大長度為 :max 字符',
        'array'   => ':attribute 的最大個數為 :max 個',
    ],
    'mimes'                => ':attribute 的文件類型必須是:values',
    'min'                  => [
        'numeric' => ':attribute 的最小長度為 :min 位',
        'string'  => ':attribute 的最小長度為 :min 字符',
        'file'    => ':attribute 大小至少為:min KB',
        'array'   => ':attribute 至少有 :min 項',
    ],
    'not_in'               => '選定的 :attribute 是無效的',
    'numeric'              => ':attribute 必須是數字',
    'regex'                => ':attribute 格式是無效的',
    'required'             => ':attribute 字段必須填寫',
    'required_if'          => ':attribute 字段是必須的當 :other 是 :value',
    'required_with'        => ':attribute 字段是必須的當 :values 是存在的',
    'required_with_all'    => ':attribute 字段是必須的當 :values 是存在的',
    'required_without'     => ':attribute 字段是必須的當 :values 是不存在的',
    'required_without_all' => ':attribute 字段是必須的當 沒有壹個 :values 是存在的',
    'same'                 => ':attribute 和 :other 必須匹配',
    'size'                 => [
        'numeric' => ':attribute 必須是 :size 位',
        'file'    => ':attribute 必須是 :size KB',
        'string'  => ':attribute 必須是 :size 個字符',
        'array'   => ':attribute 必須包括 :size 項',
    ],
    'url'                  => ':attribute 無效的格式',
    'timezone'             => ':attribute 必須個有效的時區',
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'attributes'           => [
        'username' => '用戶名',
        'account'  => '賬號',
        'captcha'  => '驗證碼',
        'mobile'   => '手機號',
        'password' => '密碼',
        'content'  => '內容',
        'identity' => '手機號/用戶名',
    ],
];
