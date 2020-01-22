<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'Field :attribute harus antara true atau false.',
    'confirmed'            => ':attribute confirmation tidak sesuai.',
    'date'                 => ':attribute bukan berupa tanggal.',
    'date_format'          => 'Field :attribute tidak sesuai dengan format :format.',
    'different'            => 'Field :attribute dan :other harus berbeda.',
    'digits'               => 'Field :attribute harus :digits digit.',
    'digits_between'       => 'Field :attribute harus antara :min dan :max digit.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'Field :attribute memiliki nilai yang sama.',
    'email'                => 'Field :attribute harus berupa alamat email.',
    'exists'               => 'Pilihan :attribute invalid.',
    'file'                 => ':attribute harus berupa file.',
    'filled'               => ':attribute field harus diisi.',
    'image'                => ':attribute harus berupa image.',
    'in'                   => 'Pilihan :attribute invalid.',
    'in_array'             => ':attribute field tidak terdapat dalam :other.',
    'integer'              => ':attribute harus berupa integer.',
    'ip'                   => ':attribute harus berupa valid IP address.',
    'ipv4'                 => ':attribute harus berupa valid IPv4 address.',
    'ipv6'                 => ':attribute harus berupa valid IPv6 address.',
    'json'                 => ':attribute harus berupa valid JSON string.',
    'max'                  => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'file'    => ':attribute tidak boleh lebih dari :max kilobytes.',
        'string'  => ':attribute tidak boleh lebih dari :max karakter.',
        'array'   => ':attribute tidak boleh lebih dari :max item.',
    ],
    'mimes'                => ':attribute harus berupa tipe file: :values.',
    'mimetypes'            => ':attribute harus berupa tipe file: :values.',
    'min'                  => [
        'numeric' => ':attribute must be at least :min.',
        'file'    => 'Ukuran :attribute harus kurang dari :min kilobytes.',
        'string'  => ':attribute harus kurang dari :min karakter.',
        'array'   => ':attribute harus memiliki minimal :min item.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'Format field :attribute tidak sesuai.',
    'required'             => 'Field :attribute wajib diisi.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'uploaded'             => 'The :attribute failed to upload.',
    'url'                  => 'The :attribute format is invalid.',

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

    'custom' => [
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

    'attributes' => [],

];
