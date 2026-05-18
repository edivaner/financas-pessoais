<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['api' => 'financas-pessoais', 'version' => '1.0']));
