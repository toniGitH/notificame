<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Messages
    |--------------------------------------------------------------------------
    */
    'required' => 'The :attribute field is required.',
    'email' => [
        'invalid_format' => 'The :attribute must be in a valid format (example@domain.com).',
        'missing_dot' => 'The :attribute must include a domain with a dot (example@domain.com).',
    ],
    'min' => [
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'confirmed' => 'The :attribute confirmation does not match.',
    'unique' => 'The :attribute is already registered.',
    'string' => 'The :attribute must be a string.',
    'regex' => [
        'password' => 'The :attribute must include at least one uppercase letter, one lowercase letter, one number and one special character.',
        'email' => 'The :attribute must have a valid format (example@domain.com).',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */
    'attributes' => [
        'name' => 'name',
        'email' => 'email',
        'password' => 'password',
        'password_confirmation' => 'password confirmation',
    ],
];