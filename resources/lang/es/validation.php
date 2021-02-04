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

    'accepted' => 'El campo :attribute debe ser aceptado.',
    'active_url' => 'El valor ingresado no es una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha previa a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha previa o igual a :date.',
    'alpha' => 'El campo :attribute solo debe contener letras.',
    'alpha_dash' => 'El campo :attribute solo debe contener letras, números, guiones, y/o guines bajos.',
    'alpha_num' => 'El campo :attribute solo debe contener letras y/o números.',
    'array' => 'El campo :attribute debe ser un array.',
    'before' => 'El campo :attribute debe ser una fecha previa a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha previa o igual a :date.',
    'between' => [
        'numeric' => 'El campo :attribute debe ser un número entre :min y :max.',
        'file' => 'El campo :attribute debe pesar entre :min y :max kilobytes.',
        'string' => 'El campo :attribute debe contener :min and :max caractéres.',
        'array' => 'El campo :attribute debe contener :min y :max items.',
    ],
    'boolean' => 'El campo :attribute debe ser Verdadero o Falso.',
    'confirmed' => 'El campo :attribute y su confirmación no coinciden.',
    'date' => 'El campo :attribute no contiene una fecha válida.',
    'date_equals' => 'El campo :attribute debe ser una fecha igual a :date.',
    'date_format' => 'El campo :attribute debe ser del formato :format.',
    'different' => 'El campo :attribute y :other deben ser diferentes.',
    'digits' => 'El valor :attribute debe ser de :digits dígitos.',
    'digits_between' => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'Las dimensiones de la imágen en el campo :attribute no son aceptables.',
    'distinct' => 'El campo :attribute contiene uno o más valores repetidos.',
    'email' => 'El campo :attribute debe ser una dirección de email válida.',
    'ends_with' => 'El campo :attribute debe terminar con una de las siguientes opciones: :values.',
    'exists' => 'El atributo seleccionado :attribute es inválido.',
    'file' => 'El campo :attribute debe contener un archivo.',
    'filled' => 'El campo :attribute debe contener al menos un valor.',
    'gt' => [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'file' => 'El campo :attribute debe pesar más de :value kilobytes.',
        'string' => 'El campo :attribute debe tener más de :value caractéres.',
        'array' => 'El campo :attribute debe contener más de :value items.',
    ],
    'gte' => [
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
        'file' => 'El campo :attribute debe pesar igual o más que :value kilobytes.',
        'string' => 'El campo :attribute debe tener :value o más caractéres.',
        'array' => 'El campo :attribute debe tener :value items o más.',
    ],
    'image' => 'El campo :attribute debe contener una imágen.',
    'in' => 'El atributo seleccionado :attribute no es válido.',
    'in_array' => 'El campo :attribute no existe dentro de :other.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'ip' => 'El campo :attribute debe ser una dirección de IP válida.',
    'ipv4' => 'El campo :attribute debe ser una dirección de IPv4 válida.',
    'ipv6' => 'El campo :attribute debe ser una dirección de IPv6 válida.',
    'json' => 'El campo :attribute debe ser un string en formato JSON válido.',
    'lt' => [
        'numeric' => 'El campo :attribute debe ser menor que :value.',
        'file' => 'El campo :attribute debe pesar menos de :value kilobytes.',
        'string' => 'El campo :attribute debe tener menos de :value caractéres.',
        'array' => 'El campo :attribute debe tener menos de :value items.',
    ],
    'lte' => [
        'numeric' => 'El campo :attribute debe ser menor o igual que :value.',
        'file' => 'El campo :attribute debe pesar un máximo de :value kilobytes.',
        'string' => 'El campo :attribute debe tener un máximo de :value caractéres.',
        'array' => 'El campo :attribute no debe contener mas de :value items.',
    ],
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor de :max.',
        'file' => 'El campo :attribute no debe ser mayor de :max kilobytes.',
        'string' => 'El campo :attribute no debe tener más de :max caractéres.',
        'array' => 'El campo :attribute no debe tener más de :max items.',
    ],
    'mimes' => 'El campo :attribute debe ser un archivo de alguna de las siguientes opciones: :values.',
    'mimetypes' => 'El campo :attribute debe ser un archivo de alguna de las siguientes opciones: :values.',
    'min' => [
        'numeric' => 'El campo :attribute debe ser por lo menos :min.',
        'file' => 'El campo :attribute debe pesar por lo menos :min kilobytes.',
        'string' => 'El campo :attribute debe tener por lo menos :min caractéres.',
        'array' => 'El campo :attribute debe tener por lo menos :min items.',
    ],
    'multiple_of' => 'El campo :attribute debe ser múltiplo de :value.',
    'not_in' => 'El atributo seleccionado :attribute no es válido.',
    'not_regex' => 'El formato del campo :attribute no es válido.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'password' => 'La contraseña ingresada es incorrecta.',
    'present' => 'El campo :attribute debe estar presente.',
    'regex' => 'El formato del campo :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless' => 'El campo :attribute es obligatorio, a no ser que :other está incluido en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values están presentes.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de los siguientes :values están presentes.',
    'same' => 'El campo :attribute y :other deben ser iguales.',
    'size' => [
        'numeric' => 'El campo :attribute debe serde tamaño :size.',
        'file' => 'El campo :attribute debe pesar :size kilobytes.',
        'string' => 'El campo :attribute debe tener :size caractéres.',
        'array' => 'El campo :attribute debe contener :size items.',
    ],
    'starts_with' => 'El campo :attribute debe empezar con alguna de las siguientes opciones: :values.',
    'string' => 'El campo :attribute debe ser una cadena de texto.',
    'timezone' => 'El campo :attribute debe ser una zona horaria válida.',
    'unique' => 'El valor :attribute ya está tomado.',
    'uploaded' => 'El archivo :attribute falló en ser subido.',
    'url' => 'El formato del campo :attribute no es válido.',
    'uuid' => 'El campo :attribute debe ser un UUID válido.',

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
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
