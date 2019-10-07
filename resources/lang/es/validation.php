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
    
    'accepted'       => 'El campo :attribute debe aceptarse.',
    'active_url'       => 'El campo :attribute no es una URL válida.',
    'after'        => 'El campo :attribute debe ser una fecha posterior a la fecha :date.',
    'after_or_equal'     => 'El campo :attribute debe ser una fecha posterior o igual a la fecha :date.',
    'alpha'        => 'El campo :attribute solo puede contener letras.',
    'alpha_dash'       => 'El campo :attribute solo puede contener letras, números, y guiones.',
    'alpha_num'      => 'El campo :attribute solo puede contener letras y números.',
    'array'        => 'El campo :attribute debe ser una lista.',
    'before'         => 'El campo :attribute debe ser una fecha anterior a la fecha :date.',
    'before_or_equal'    => 'El campo :attribute debe ser una fecha anterior o igual a la fecha :date.',
    'between'        => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file'  => 'El campo :attribute debe estar entre :min y :max kilobytes.',
        'string'  => 'El campo :attribute debe estar entre :min y :max caracteres.',
        'array'   => 'El campo :attribute debe tener entre :min y :max ítems.',
    ],
    'boolean'        => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed'      => 'El campo :attribute no se encuentra confirmado.',
    'date'         => 'El campo :attribute no es una fecha válida.',
    'date_format'      => 'El campo :attribute no coincide con el formato :format.',
    'different'      => 'El campo :attribute y :other deben ser diferentes.',
    'digits'         => 'El campo :attribute debe ser de :digits dígitos.',
    'digits_between'     => 'El campo :attribute debe estar entre :min y :max dígitoss.',
    'dimensions'       => 'El campo :attribute contiene dimensiones inválidas de imagen.',
    'distinct'       => 'El campo :attribute tiene una valor duplicado.',
    'email'        => 'El campo :attribute debe ser una dirección de correo.',
    'exists'         => 'La selección :attribute es inválida.',
    'file'         => 'El campo :attribute debe ser un archivo.',
    'filled'         => 'El campo :attribute debe contener un valor.',
    'gt'           => [
        'numeric' => 'El campo :attribute debe ser mayor a :value.',
        'file'  => 'El campo :attribute debe ser mayor a :value kilobytes.',
        'string'  => 'El campo :attribute debe ser mayor a :value caracteres.',
        'array'   => 'El campo :attribute debe tener mas de :value ítems.',
    ],
    'gte'          => [
        'numeric' => 'El campo :attribute debe ser mayor o igual a :value.',
        'file'  => 'El campo :attribute debe ser mayor o igual a :value kilobytes.',
        'string'  => 'El campo :attribute debe ser mayor o igual a :value caracteres.',
        'array'   => 'El campo :attribute debe tener :value ítems o más.',
    ],
    'image'        => 'El campo :attribute debe ser una imagen.',
    'in'           => 'La selección :attribute es inválida.',
    'in_array'       => 'El campo :attribute no existe en :other.',
    'integer'        => 'El campo :attribute debe ser un entero.',
    'ip'           => 'El campo :attribute debe ser una dirección IP.',
    'ipv4'         => 'El campo :attribute debe ser una dirección IPv4.',
    'ipv6'         => 'El campo :attribute debe ser una dirección IPv6.',
    'json'         => 'El campo :attribute debe ser una cadena JSON.',
    'lt'           => [
        'numeric' => 'El campo :attribute debe ser menor a :value.',
        'file'  => 'El campo :attribute debe ser menor a :value kilobytes.',
        'string'  => 'El campo :attribute debe ser menor a :value caracteres.',
        'array'   => 'El campo :attribute debe tener menos que :value ítems.',
    ],
    'lte'          => [
        'numeric' => 'El campo :attribute debe ser menor o igual a :value.',
        'file'  => 'El campo :attribute debe ser menor o igual a :value kilobytes.',
        'string'  => 'El campo :attribute debe ser menor o igual a :value caracteres.',
        'array'   => 'El campo :attribute no debe contener mas de :value ítems.',
    ],
    'max'          => [
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'file'  => 'El campo :attribute no puede ser mayor que :max kilobytes.',
        'string'  => 'El campo :attribute no puede ser mayor que :max caracteres.',
        'array'   => 'El campo :attribute no debe tener mas de :max ítems.',
    ],
    'mimes'        => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'mimetypes'      => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'min'          => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'file'  => 'El campo :attribute debe ser de al menos :min kilobytes.',
        'string'  => 'El campo :attribute debe ser de al menos :min caracteres.',
        'array'   => 'El campo :attribute debe tener al menos :min ítems.',
    ],
    'not_in'         => 'La selección :attribute es inválida.',
    'not_regex'      => 'El formato del campo :attribute es inválido.',
    'numeric'        => 'El campo :attribute debe ser un número.',
    'present'        => 'El campo :attribute debe estar presente.',
    'regex'        => 'El formato del campo :attribute es inválido.',
    'required'       => 'El campo :attribute es requerido.',
    'required_if'      => 'El campo :attribute es requerido cuando :other es :value.',
    'required_unless'    => 'El campo :attribute es requerido unless :other está entre :values.',
    'required_with'    => 'El campo :attribute es requerido cuando :values está presente.',
    'required_with_all'  => 'El campo :attribute es requerido cuando :values está presente.',
    'required_without'   => 'El campo :attribute es requerido cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es requerido cuando ninguno de los valores :values está presente.',
    'same'         => 'El campo :attribute y :other deben coincidir.',
    'size'         => [
        'numeric' => 'El campo :attribute debe ser igual a :size.',
        'file'  => 'El campo :attribute debe ser igual a :size kilobytes.',
        'string'  => 'El campo :attribute debe contener :size caracteres.',
        'array'   => 'El campo :attribute debe contener :size ítems.',
    ],
    'string'         => 'El campo :attribute debe ser una cadena de texto.',
    'timezone'       => 'El campo :attribute debe ser una zona horaria.',
    'unique'         => 'El campo :attribute ya existe.',
    'uploaded'       => 'Fallo al cargar :attribute.',
    'url'          => 'El formato del campo :attribute es inválido.',
    
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
    
    'attributes' => [
        // User
        'first_name' => 'Primer Nombre',
        'last_name' => 'Apellido Paterno',
        'username' => 'Usuario',
        'password' => 'Contraseña',
        'position' => 'Cargo',
        'active' => 'Activo',
        // Affiliate
        'second_name' => 'Segundo Nombre',
        'mothers_last_name' => 'Apellido Materno',
        'birth_date' =>'fecha de nacimiento',
        'gender' => 'Genero',
        'city_birth_id' => 'Ciudad de Nacimiento',
        'city_identity_card_id' =>'Ciudad de expedición',
        'identity_card' =>'Carnet de Identidad'

    ],
    
];
