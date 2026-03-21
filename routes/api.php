<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\BankCategoryController;

use App\Models\PreRegister;
use App\Models\Member;
use Illuminate\Support\Carbon;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/banks', [BankController::class, 'index']);
Route::get('/branches', [BankController::class, 'branches']);
Route::get('/bank-categories', [BankCategoryController::class, 'index']);

Route::get('/zipcode/{zip}', function ($zip) {
    $zip = preg_replace('/[^0-9]/', '', $zip);

    if (strlen($zip) !== 7) {
        return response()->json(['results' => []]);
    }

    $response = Http::get(
        'https://zipcloud.ibsnet.co.jp/api/search',
        ['zipcode' => $zip]
    );

    return $response->json();
});

Route::get('/dashboard-counts', function() {
    $preRegisterToday = PreRegister::whereDate('created_at', Carbon::today())->count();
    $preRegisterMonth = PreRegister::whereMonth('created_at', Carbon::now()->month)
                                   ->whereYear('created_at', Carbon::now()->year)
                                   ->count();

    $memberToday = Member::whereDate('created_at', Carbon::today())->count();
    $memberMonth = Member::whereMonth('created_at', Carbon::now()->month)
                         ->whereYear('created_at', Carbon::now()->year)
                         ->count();

    return response()->json([
        'preRegister' => [
            'today' => $preRegisterToday,
            'month' => $preRegisterMonth,
        ],
        'member' => [
            'today' => $memberToday,
            'month' => $memberMonth,
        ],
    ]);
});
