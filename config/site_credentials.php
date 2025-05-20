<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third-Party Site Credentials Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines the third-party sites that require credentials and
    | the fields needed for each site. Each site should have a unique key
    | and an array of fields with their properties.
    |
    | Field properties:
    | - name: The name of the field (required)
    | - label: The human-readable label for the field (required)
    | - type: The input type (text, password, email, url, etc.) (required)
    | - required: Whether the field is required (optional, defaults to true)
    | - placeholder: Placeholder text for the field (optional)
    | - help_text: Help text to display below the field (optional)
    |
    */

    'sites' => [
        'parts_base' => [
            'name' => 'Parts Base',
            'description' => 'Credentials for Parts Base Platform',
            'fields' => [
                [
                    'name' => 'username',
                    'label' => 'Username',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Enter your username',
                ],
                [
                    'name' => 'password',
                    'label' => 'Password',
                    'type' => 'password',
                    'required' => true,
                    'placeholder' => 'Enter your password',
                ]
            ],
        ],
        
        'second_supplier' => [
            'name' => 'Second Supplier',
            'description' => 'Credentials for the second supplier',
            'fields' => [
                [
                    'name' => 'username',
                    'label' => 'Username',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Enter your username',
                ],
                [
                    'name' => 'password',
                    'label' => 'Password',
                    'type' => 'password',
                    'required' => true,
                    'placeholder' => 'Enter your password',
                ]
            ],
        ],
        
        'third_supplier' => [
            'name' => 'Third Supplier',
            'description' => 'Credentials for the third supplier',
            'fields' => [
                [
                    'name' => 'username',
                    'label' => 'Username',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Enter your username',
                ],
                [
                    'name' => 'password',
                    'label' => 'Password',
                    'type' => 'password',
                    'required' => true,
                    'placeholder' => 'Enter your password',
                ]
            ],
        ],
    ],
];
