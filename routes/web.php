<?php

use App\Http\Controllers\InstallController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\User\CalendarController;
use App\Http\Controllers\User\TenantRoleController;
use App\Http\Controllers\User\TenantUserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use Modules\Properties\Entities\PropertyAttribute;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\OtpLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::prefix('install')->middleware(['web', 'prevent.if.installed'])->group(function () {
    Route::get('/', [InstallController::class, 'step1'])->name('install.step1');
    Route::post('/', [InstallController::class, 'processStep1'])->name('install.processStep1');

    Route::get('/step2', [InstallController::class, 'step2'])->name('install.step2');
    Route::post('/step2', [InstallController::class, 'processStep2'])->name('install.processStep2');

    Route::get('/step3', [InstallController::class, 'step3'])->name('install.step3');
    Route::post('/step3', [InstallController::class, 'processStep3'])->name('install.processStep3');
});


Route::middleware([
    'redirect.if.not.installed',
    'web',
])->group(function () {

    Route::get('/', [PageController::class, 'home'])->name('home');

    Route::get('/debug-attributes', function () {
        $details = PropertyAttribute::where('section', 'details')->get(['id', 'name']);
        $features = PropertyAttribute::where('section', 'features')->get(['id', 'name']);
        return response()->json(['details' => $details, 'features' => $features]);
    });

    // روت‌های Jetstream (لاگین، پروفایل و ...)
    // Jetstream روت‌های خود را به صورت خودکار در 'boot' (در RouteServiceProvider) ثبت می‌کند
    // و آنها نیز تحت تاثیر این گروه میدل‌ور قرار می‌گیرند.

    // روت خروج (برای دکمه خروج در داشبورد ادمین)
    // Jetstream از روت خروج متفاوتی استفاده می‌کند، اما پنل ادمین ما به این نیاز دارد
    /*Route::post('logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout')->middleware('auth');*/
});


Route::middleware(['web', 'auth'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/layout', [UserDashboardController::class, 'updateLayout'])->name('dashboard.update-layout');

    // widget and calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::post('/calendar/events', [CalendarController::class, 'storeEvent'])->name('calendar.events.store');
    Route::put('/calendar/events/{id}', [CalendarController::class, 'updateEvent'])->name('calendar.events.update');
    Route::delete('/calendar/events/{id}', [CalendarController::class, 'deleteEvent'])->name('calendar.events.delete');
    Route::get('/calendar/widget-data', [CalendarController::class, 'widgetData'])->name('calendar.widget-data');

    //tenant user management
    Route::prefix('users')->name('users.')->middleware(['permission:users.view'])->group(function () {
        Route::get('/', [TenantUserController::class, 'index'])->name('index');
        Route::get('/create', [TenantUserController::class, 'create'])->name('create')->middleware('permission:users.create');
        Route::post('/', [TenantUserController::class, 'store'])->name('store')->middleware('permission:users.create');
        Route::get('/{user}/edit', [TenantUserController::class, 'edit'])->name('edit')->middleware('permission:users.update');
        Route::put('/{user}', [TenantUserController::class, 'update'])->name('update')->middleware('permission:users.update');
        Route::delete('/{user}', [TenantUserController::class, 'destroy'])->name('destroy')->middleware('permission:users.delete');
    });

    // role management
    Route::prefix('roles')->name('roles.')->middleware(['permission:users.view'])->group(function () {
        Route::get('/', [TenantRoleController::class, 'index'])->name('index');
        Route::get('/create', [TenantRoleController::class, 'create'])->name('create');
        Route::post('/', [TenantRoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [TenantRoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [TenantRoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [TenantRoleController::class, 'destroy'])->name('destroy');
    });
});

// Override Fortify's default register routes
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// 2-Step & OTP Login routes
Route::middleware(['web', 'guest'])->prefix('login')->name('login.')->group(function () {
    Route::post('/check-identity', [OtpLoginController::class, 'checkIdentity'])->name('check-identity');
    Route::post('/send-otp', [OtpLoginController::class, 'sendOtp'])->name('send-otp');
    Route::post('/verify-otp', [OtpLoginController::class, 'verifyOtp'])->name('verify-otp');
});


Route::get('/ping', function () {
    return 'pong-crm';
});

if (app()->environment('local', 'testing')) {
    Route::middleware('web')->get('/_e2e/login', function () {
        $user = null;
        if (request('email')) {
            $user = User::where('email', request('email'))->first();
        } elseif (request('user_id')) {
            $user = User::find(request('user_id'));
        } else {
            $user = User::first();
        }
        if ($user) {
            auth()->login($user);
            session()->regenerate();
        }
        $target = request('redirect', '/user/reseller/dashboard');
        return redirect($target);
    });
}
