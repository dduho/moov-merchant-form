<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/test-form', function (Request $request) {
    return response()->json([
        'message' => 'Test route',
        'content_type' => $request->header('Content-Type'),
        'method' => $request->method(),
        'has_files' => $request->hasFile(),
        'all_data' => $request->all(),
        'input_count' => count($request->all())
    ]);
});