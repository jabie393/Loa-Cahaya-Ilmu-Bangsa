<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (Bahasa Indonesia)
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attribute harus diterima.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'max' => [
        'numeric' => ':attribute maksimal bernilai :max.',
        'file' => ':attribute maksimal berukuran :max kilobita.',
        'string' => ':attribute maksimal berisi :max karakter.',
        'array' => ':attribute maksimal memiliki :max item.',
    ],
    'min' => [
        'numeric' => ':attribute minimal bernilai :min.',
        'file' => ':attribute minimal berukuran :min kilobita.',
        'string' => ':attribute minimal berisi :min karakter.',
        'array' => ':attribute minimal memiliki :min item.',
    ],
    'required' => ':attribute wajib diisi.',
    'same' => ':attribute dan :other harus sama.',
    'string' => ':attribute harus berupa string.',
    'unique' => ':attribute sudah terdaftar dalam sistem.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'nama lengkap',
        'email' => 'alamat email',
        'password' => 'kata sandi',
        'passwordConfirmation' => 'konfirmasi kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'remember' => 'ingat saya',
    ],

];
