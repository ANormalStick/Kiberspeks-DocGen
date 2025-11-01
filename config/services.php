<?php

return [
    'groq' => [
        'key'   => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.1-70b-versatile'),
    ],

    'openai' => [
        'key'   => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
    ],

    'gemini' => [
        'key'           => env('GEMINI_API_KEY'),
        'default_model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ]
];
