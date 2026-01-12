<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SetLocaleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\MemberController as MemberRegController;
use App\Http\Controllers\BankSearchController;
use App\Http\Controllers\PreRegister\PreRegisterController;
use App\Http\Controllers\PreRegister\EmailVerifyController;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::prefix('admin')->name('admin.')->group(function () {

    // ログイン（Jetstream）
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware('guest')
        ->name('login');

    Route::post('/admin/login', [\App\Http\Controllers\Admin\LoginController::class, 'store'])
        ->middleware('guest');

    // 認証後
    Route::middleware(['auth', 'role:admin|super_admin'])->group(function () {

        Route::post('/logout', function () {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return Inertia::location('/admin/login');
        })->name('logout');
        
        Route::get('/dashboard', fn () => inertia('Admin/Dashboard'))
            ->name('dashboard');
        // Tenant
        Route::resource('tenants', \App\Http\Controllers\Admin\TenantController::class);
        Route::post('tenants/bulk-delete', [\App\Http\Controllers\Admin\TenantController::class, 'bulkDelete'])->name('tenants.bulkDelete');
        // Role
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::post('roles/bulk-delete', [\App\Http\Controllers\Admin\RoleController::class, 'bulkDelete'])->name('roles.bulkDelete');
        // Permission
        Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
        Route::post('permissions/bulk-delete', [\App\Http\Controllers\Admin\PermissionController::class, 'bulkDelete'])->name('permissions.bulkDelete');
        Route::post('permissions/assign', [\App\Http\Controllers\Admin\PermissionController::class, 'assign'])->name('permissions.assign');
        // user
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        Route::prefix('member')->name('member.')->group(function () {
            Route::get('/', [AdminMemberController::class, 'index'])->name('index');
            Route::get('/pdf/{id}', [AdminMemberController::class, 'pdfPreview'])->name('pdf.preview');
            Route::get('/{id}/edit', [AdminMemberController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminMemberController::class, 'update'])->name('update');
        });
    });
});

// メール仮登録
Route::prefix('pre-register')->name('pre-register.')->group(function () {
    // メール入力画面
    Route::get('/mail', function () { return inertia('PreRegister/Email'); })->name('mail');
    // 仮登録 → メール送信
    Route::post('/pre', [PreRegisterController::class, 'store'])->name('pre');
    // メール確認
    Route::get('/verify/{token}', [EmailVerifyController::class, 'verify'])->name('verify');
    // メール完了
    Route::get('/thanks', function () { return inertia('PreRegister/Thanks'); })->name('thanks'); 
});


Route::prefix('members')->group(function () {

    Route::get('pdf', [MemberRegController::class, 'pdf']);

    Route::get('register/{token}', 
        [MemberRegController::class, 'showRegistrationForm']
    )->name('members.register');
    Route::post('members/agree/{token}', [MemberRegController::class, 'agreeNext'])
    ->name('members.register.agree');
    Route::get('register/{token}/register', 
        [MemberRegController::class, 'showRegisterForm']
    )->name('members.register.register');
    Route::post('register/{token}', 
        [MemberRegController::class, 'completeRegistration']
    )->name('members.register.complete');
    // 完了画面GET
    Route::get('members/register/complete', function () {
        return Inertia::render('Members/Complete', [
            'success' => session('success'),
            'member_id' => session('member_id'), // 必要なら
        ]);
    })->name('members.complete');



    // 加盟団体加入で拒否された場合のメッセージ画面
    Route::get('register/{token}/rejected', [MemberRegController::class, 'showRejectedMessage'])
        ->name('members.register.rejected');
/*
    Route::get('pdfcreate', [MemberRegController::class, 'showPdfForm'])
    ->name('members.pdfcreate');
*/
    Route::get('bank', [MemberRegController::class, 'bank'])
        ->name('members.bank');

    Route::get('pdfcreate', [MemberRegController::class, 'pdfCreate'])
        ->name('members.pdfcreate');

    Route::post('pdfgenerate', [MemberRegController::class, 'pdfGenerate'])
        ->name('members.pdfgenerate');    
    Route::get('pdf-preview/{token}', 
        [MemberRegController::class, 'pdfPreview']
    )->name('members.pdf.preview');

});

Route::get('/banks/search', [BankSearchController::class, 'banks'])
  ->name('banks.search');

Route::get('/branches/search', [BankSearchController::class, 'branches'])
  ->name('branches.search');

  Route::get('/test', function () {
    return Inertia::render('Test');
});


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

Route::post('/locale', function (Request $request) {
    $locale = $request->input('locale', 'en');
    session(['locale' => $locale]);
    app()->setLocale($locale);
    return response()->json(['status' => 'ok']);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
