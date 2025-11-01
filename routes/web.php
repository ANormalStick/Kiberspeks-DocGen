<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('main'); // resources/views/main.blade.php
});

Route::view('/docgen', 'docgen');