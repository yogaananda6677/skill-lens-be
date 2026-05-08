<?php

use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ProdiController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API jalan',
    ]);
});

Route::get('/show_data', [MahasiswaController::class, 'index']);
Route::get('/get_nama_prodi', [ProdiController::class, 'getDataProdi']);
Route::post('/query_upd_del_ins', [MahasiswaController::class, 'queryUpdDelIns']);
Route::get('/check_service', [MahasiswaController::class, 'checkService']);
